-- MedFlow Database Schema
-- Disable triggers temporarily for clean setup
SET session_replication_role = 'replica';

-- Drop existing tables if they exist
DROP TABLE IF EXISTS medical_records CASCADE;
DROP TABLE IF EXISTS appointments CASCADE;
DROP TABLE IF EXISTS doctor_specializations CASCADE;
DROP TABLE IF EXISTS specializations CASCADE;
DROP TABLE IF EXISTS doctors CASCADE;
DROP TABLE IF EXISTS patients CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

DROP TYPE IF EXISTS appointment_status CASCADE;
DROP TYPE IF EXISTS appointment_type CASCADE;
DROP TYPE IF EXISTS user_status CASCADE;

-- Re-enable triggers
SET session_replication_role = 'origin';

-- Enums
CREATE TYPE user_status AS ENUM ('active', 'inactive', 'suspended');
CREATE TYPE appointment_status AS ENUM ('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show');
CREATE TYPE appointment_type AS ENUM ('nfz', 'private');

-- Roles table
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table (base table for all users)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE RESTRICT,
    status user_status DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    CONSTRAINT email_format CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$')
);

-- Patients table (one-to-one with users)
CREATE TABLE patients (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    pesel VARCHAR(11) UNIQUE NOT NULL,
    date_of_birth DATE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pesel_length CHECK (LENGTH(pesel) = 11),
    CONSTRAINT pesel_format CHECK (pesel ~ '^[0-9]{11}$'),
    CONSTRAINT valid_birth_date CHECK (date_of_birth <= CURRENT_DATE)
);

-- Specializations table
CREATE TABLE specializations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctors table (one-to-one with users)
CREATE TABLE doctors (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    title VARCHAR(50),
    license_number VARCHAR(50) UNIQUE NOT NULL,
    phone VARCHAR(20),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctor-Specialization junction table (many-to-many)
CREATE TABLE doctor_specializations (
    doctor_id INTEGER NOT NULL REFERENCES doctors(id) ON DELETE CASCADE,
    specialization_id INTEGER NOT NULL REFERENCES specializations(id) ON DELETE CASCADE,
    PRIMARY KEY (doctor_id, specialization_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE appointments (
    id SERIAL PRIMARY KEY,
    patient_id INTEGER NOT NULL REFERENCES patients(id) ON DELETE CASCADE,
    doctor_id INTEGER NOT NULL REFERENCES doctors(id) ON DELETE CASCADE,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration INTEGER DEFAULT 30,
    type appointment_type NOT NULL,
    status appointment_status DEFAULT 'scheduled',
    reason TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT future_appointment CHECK (
        appointment_date >= CURRENT_DATE 
        OR status IN ('completed', 'cancelled', 'no_show')
    ),
    CONSTRAINT valid_duration CHECK (duration > 0 AND duration <= 240),
    CONSTRAINT unique_doctor_slot UNIQUE (doctor_id, appointment_date, appointment_time)
);

-- Medical Records table
CREATE TABLE medical_records (
    id SERIAL PRIMARY KEY,
    appointment_id INTEGER UNIQUE NOT NULL REFERENCES appointments(id) ON DELETE CASCADE,
    patient_id INTEGER NOT NULL REFERENCES patients(id) ON DELETE CASCADE,
    doctor_id INTEGER NOT NULL REFERENCES doctors(id) ON DELETE CASCADE,
    diagnosis_icd10 VARCHAR(10),
    diagnosis_description TEXT,
    symptoms TEXT,
    treatment TEXT,
    prescription TEXT,
    recommendations TEXT,
    next_visit_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT valid_next_visit CHECK (next_visit_date IS NULL OR next_visit_date > CURRENT_DATE)
);

-- Indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_patients_user ON patients(user_id);
CREATE INDEX idx_patients_pesel ON patients(pesel);
CREATE INDEX idx_doctors_user ON doctors(user_id);
CREATE INDEX idx_appointments_patient ON appointments(patient_id);
CREATE INDEX idx_appointments_doctor ON appointments(doctor_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_medical_records_patient ON medical_records(patient_id);
CREATE INDEX idx_medical_records_appointment ON medical_records(appointment_id);

-- Comments
COMMENT ON TABLE users IS 'Base table for all system users';
COMMENT ON TABLE patients IS 'Patient-specific information';
COMMENT ON TABLE doctors IS 'Doctor-specific information';
COMMENT ON TABLE appointments IS 'Medical appointments';
COMMENT ON TABLE medical_records IS 'Medical documentation from appointments';