-- Database Functions for MedFlow

-- Function 1: Get patient age
CREATE OR REPLACE FUNCTION get_patient_age(patient_id_param INTEGER)
RETURNS INTEGER AS $$
DECLARE
    birth_date DATE;
    patient_age INTEGER;
BEGIN
    SELECT date_of_birth INTO birth_date
    FROM patients
    WHERE id = patient_id_param;
    
    IF birth_date IS NULL THEN
        RETURN NULL;
    END IF;
    
    patient_age := DATE_PART('year', AGE(CURRENT_DATE, birth_date));
    RETURN patient_age;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION get_patient_age IS 'Calculate patient age from date of birth';

-- Function 2: Get doctor upcoming appointments count
CREATE OR REPLACE FUNCTION get_doctor_upcoming_appointments(doctor_id_param INTEGER)
RETURNS INTEGER AS $$
DECLARE
    appointment_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO appointment_count
    FROM appointments
    WHERE doctor_id = doctor_id_param
    AND appointment_date >= CURRENT_DATE
    AND status IN ('scheduled', 'confirmed');
    
    RETURN appointment_count;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION get_doctor_upcoming_appointments IS 'Count upcoming appointments for a doctor';

-- Function 3: Check if time slot is available
CREATE OR REPLACE FUNCTION is_slot_available(
    doctor_id_param INTEGER,
    appointment_date_param DATE,
    appointment_time_param TIME
)
RETURNS BOOLEAN AS $$
DECLARE
    slot_available BOOLEAN;
BEGIN
    SELECT NOT EXISTS (
        SELECT 1
        FROM appointments
        WHERE doctor_id = doctor_id_param
        AND appointment_date = appointment_date_param
        AND appointment_time = appointment_time_param
        AND status NOT IN ('cancelled')
    ) INTO slot_available;
    
    RETURN slot_available;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION is_slot_available IS 'Check if appointment slot is available for doctor';

-- Function 4: Get patient last visit
CREATE OR REPLACE FUNCTION get_patient_last_visit(patient_id_param INTEGER)
RETURNS TABLE (
    appointment_date DATE,
    doctor_name TEXT,
    diagnosis TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        a.appointment_date,
        CONCAT(d.title, ' ', d.first_name, ' ', d.last_name) AS doctor_name,
        mr.diagnosis_description AS diagnosis
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    LEFT JOIN medical_records mr ON a.id = mr.appointment_id
    WHERE a.patient_id = patient_id_param
    AND a.status = 'completed'
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION get_patient_last_visit IS 'Get patient most recent completed visit';

-- Function 5: Cancel appointment with cascade
CREATE OR REPLACE FUNCTION cancel_appointment(appointment_id_param INTEGER)
RETURNS BOOLEAN AS $$
BEGIN
    UPDATE appointments
    SET status = 'cancelled',
        updated_at = CURRENT_TIMESTAMP
    WHERE id = appointment_id_param
    AND status IN ('scheduled', 'confirmed');
    
    IF FOUND THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION cancel_appointment IS 'Cancel an appointment if it is not already completed';

-- Function 6: Get appointments by date range
CREATE OR REPLACE FUNCTION get_appointments_by_date_range(
    start_date_param DATE,
    end_date_param DATE,
    doctor_id_param INTEGER DEFAULT NULL
)
RETURNS TABLE (
    appointment_id INTEGER,
    appointment_date DATE,
    appointment_time TIME,
    doctor_name TEXT,
    patient_name TEXT,
    status appointment_status,
    type appointment_type
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        a.id AS appointment_id,
        a.appointment_date,
        a.appointment_time,
        CONCAT(d.title, ' ', d.first_name, ' ', d.last_name) AS doctor_name,
        CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
        a.status,
        a.type
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN patients p ON a.patient_id = p.id
    WHERE a.appointment_date BETWEEN start_date_param AND end_date_param
    AND (doctor_id_param IS NULL OR a.doctor_id = doctor_id_param)
    ORDER BY a.appointment_date, a.appointment_time;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION get_appointments_by_date_range IS 'Get appointments within date range, optionally filtered by doctor';