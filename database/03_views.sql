-- Database Views for MedFlow

-- View 1: Doctor appointments overview
-- Joins doctors, users, appointments, patients
CREATE OR REPLACE VIEW v_doctor_appointments AS
SELECT 
    d.id AS doctor_id,
    d.first_name AS doctor_first_name,
    d.last_name AS doctor_last_name,
    d.title AS doctor_title,
    u.email AS doctor_email,
    a.id AS appointment_id,
    a.appointment_date,
    a.appointment_time,
    a.duration,
    a.type AS appointment_type,
    a.status AS appointment_status,
    a.reason,
    p.id AS patient_id,
    p.first_name AS patient_first_name,
    p.last_name AS patient_last_name,
    p.pesel AS patient_pesel,
    p.phone AS patient_phone,
    CONCAT(d.title, ' ', d.first_name, ' ', d.last_name) AS doctor_full_name,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_full_name
FROM doctors d
JOIN users u ON d.user_id = u.id
LEFT JOIN appointments a ON d.id = a.doctor_id
LEFT JOIN patients p ON a.patient_id = p.id
WHERE u.status = 'active'
ORDER BY a.appointment_date DESC, a.appointment_time DESC;

COMMENT ON VIEW v_doctor_appointments IS 'Complete view of doctor appointments with patient details';

-- View 2: Patient medical history
-- Joins patients, appointments, doctors, medical_records
CREATE OR REPLACE VIEW v_patient_medical_history AS
SELECT 
    p.id AS patient_id,
    p.first_name AS patient_first_name,
    p.last_name AS patient_last_name,
    p.pesel,
    p.date_of_birth,
    a.id AS appointment_id,
    a.appointment_date,
    a.appointment_time,
    a.type AS appointment_type,
    a.status AS appointment_status,
    d.id AS doctor_id,
    d.first_name AS doctor_first_name,
    d.last_name AS doctor_last_name,
    d.title AS doctor_title,
    s.name AS specialization,
    mr.diagnosis_icd10,
    mr.diagnosis_description,
    mr.symptoms,
    mr.treatment,
    mr.prescription,
    mr.recommendations,
    mr.created_at AS record_created_at,
    CONCAT(d.title, ' ', d.first_name, ' ', d.last_name) AS doctor_full_name,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_full_name
FROM patients p
LEFT JOIN appointments a ON p.id = a.patient_id
LEFT JOIN doctors d ON a.doctor_id = d.id
LEFT JOIN doctor_specializations ds ON d.id = ds.doctor_id
LEFT JOIN specializations s ON ds.specialization_id = s.id
LEFT JOIN medical_records mr ON a.id = mr.appointment_id
WHERE a.status = 'completed'
ORDER BY a.appointment_date DESC, a.appointment_time DESC;

COMMENT ON VIEW v_patient_medical_history IS 'Complete patient medical history with all records';

-- View 3: Daily appointments schedule
-- Joins for receptionist view
CREATE OR REPLACE VIEW v_daily_schedule AS
SELECT 
    a.appointment_date,
    a.appointment_time,
    a.duration,
    a.type AS appointment_type,
    a.status,
    d.first_name AS doctor_first_name,
    d.last_name AS doctor_last_name,
    d.title AS doctor_title,
    p.first_name AS patient_first_name,
    p.last_name AS patient_last_name,
    p.phone AS patient_phone,
    s.name AS specialization,
    CONCAT(d.title, ' ', d.first_name, ' ', d.last_name) AS doctor_name,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    EXTRACT(HOUR FROM a.appointment_time) AS hour,
    CASE 
        WHEN a.type = 'nfz' THEN 'NFZ'
        ELSE 'Prywatna'
    END AS type_display
FROM appointments a
JOIN doctors d ON a.doctor_id = d.id
JOIN patients p ON a.patient_id = p.id
LEFT JOIN doctor_specializations ds ON d.id = ds.doctor_id
LEFT JOIN specializations s ON ds.specialization_id = s.id
ORDER BY a.appointment_date, a.appointment_time;

COMMENT ON VIEW v_daily_schedule IS 'Daily appointment schedule for reception desk';

-- View 4: Doctor statistics
-- Aggregate view with statistics
CREATE OR REPLACE VIEW v_doctor_statistics AS
SELECT 
    d.id AS doctor_id,
    d.first_name,
    d.last_name,
    d.title,
    COUNT(DISTINCT a.id) AS total_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END) AS completed_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'cancelled' THEN a.id END) AS cancelled_appointments,
    COUNT(DISTINCT CASE WHEN a.appointment_date >= CURRENT_DATE THEN a.id END) AS upcoming_appointments,
    COUNT(DISTINCT mr.id) AS total_records,
    STRING_AGG(DISTINCT s.name, ', ') AS specializations
FROM doctors d
LEFT JOIN appointments a ON d.id = a.doctor_id
LEFT JOIN medical_records mr ON a.id = mr.appointment_id
LEFT JOIN doctor_specializations ds ON d.id = ds.doctor_id
LEFT JOIN specializations s ON ds.specialization_id = s.id
GROUP BY d.id, d.first_name, d.last_name, d.title;

COMMENT ON VIEW v_doctor_statistics IS 'Statistics for each doctor';

-- View 5: Available appointment slots
-- Shows free slots for booking
-- View 5: Available appointment slots
CREATE OR REPLACE VIEW v_available_slots AS
SELECT 
    d.id AS doctor_id,
    d.first_name AS doctor_first_name,
    d.last_name AS doctor_last_name,
    d.title,
    s.name AS specialization,
    CURRENT_DATE + INTERVAL '1 day' * days.day AS available_date,
    (TIME '08:00:00' + INTERVAL '30 minutes' * slots.slot) AS available_time,
    CONCAT(d.title, ' ', d.first_name, ' ', d.last_name) AS doctor_name
FROM doctors d
CROSS JOIN generate_series(0, 6) AS days(day)
CROSS JOIN generate_series(0, 15) AS slots(slot)
LEFT JOIN doctor_specializations ds ON d.id = ds.doctor_id
LEFT JOIN specializations s ON ds.specialization_id = s.id
WHERE NOT EXISTS (
    SELECT 1 
    FROM appointments a 
    WHERE a.doctor_id = d.id 
    AND a.appointment_date = CURRENT_DATE + INTERVAL '1 day' * days.day
    AND a.appointment_time = (TIME '08:00:00' + INTERVAL '30 minutes' * slots.slot)
    AND a.status NOT IN ('cancelled')
)
AND (TIME '08:00:00' + INTERVAL '30 minutes' * slots.slot) < TIME '16:00:00'
ORDER BY available_date, available_time;

COMMENT ON VIEW v_available_slots IS 'Available appointment slots for next 7 days';