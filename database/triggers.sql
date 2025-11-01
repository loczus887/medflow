-- Database Triggers for MedFlow

-- Trigger 1: Update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_patients_updated_at
    BEFORE UPDATE ON patients
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_doctors_updated_at
    BEFORE UPDATE ON doctors
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_appointments_updated_at
    BEFORE UPDATE ON appointments
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_medical_records_updated_at
    BEFORE UPDATE ON medical_records
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

COMMENT ON FUNCTION update_updated_at_column IS 'Automatically updates updated_at timestamp';

-- Trigger 2: Prevent appointment overlap for doctors
CREATE OR REPLACE FUNCTION check_appointment_overlap()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM appointments 
        WHERE doctor_id = NEW.doctor_id
        AND appointment_date = NEW.appointment_date
        AND id != COALESCE(NEW.id, -1)
        AND status NOT IN ('cancelled')
        AND (
            (appointment_time, appointment_time + (duration || ' minutes')::INTERVAL) 
            OVERLAPS 
            (NEW.appointment_time, NEW.appointment_time + (NEW.duration || ' minutes')::INTERVAL)
        )
    ) THEN
        RAISE EXCEPTION 'Doctor already has an appointment at this time';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER prevent_appointment_overlap
    BEFORE INSERT OR UPDATE ON appointments
    FOR EACH ROW
    EXECUTE FUNCTION check_appointment_overlap();

COMMENT ON FUNCTION check_appointment_overlap IS 'Prevents overlapping appointments for same doctor';

-- Trigger 3: Auto-create medical record when appointment is completed
CREATE OR REPLACE FUNCTION auto_create_medical_record()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        IF NOT EXISTS (SELECT 1 FROM medical_records WHERE appointment_id = NEW.id) THEN
            INSERT INTO medical_records (appointment_id, patient_id, doctor_id)
            VALUES (NEW.id, NEW.patient_id, NEW.doctor_id);
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_medical_record_on_complete
    AFTER UPDATE ON appointments
    FOR EACH ROW
    WHEN (NEW.status = 'completed')
    EXECUTE FUNCTION auto_create_medical_record();

COMMENT ON FUNCTION auto_create_medical_record IS 'Auto-creates medical record when appointment is completed';