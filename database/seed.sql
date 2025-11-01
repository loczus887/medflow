-- Seed data for MedFlow

-- Insert roles
INSERT INTO roles (name, description) VALUES
('admin', 'Administrator systemu'),
('doctor', 'Lekarz'),
('receptionist', 'Recepcja'),
('patient', 'Pacjent');

-- Insert users (password: haslo123 - hashed with PASSWORD_DEFAULT)
-- Note: In production, these should be properly hashed
INSERT INTO users (email, password, role_id, status) VALUES
-- Admin
('admin@medflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'active'),
-- Doctors
('jan.kowalski@medflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'active'),
('anna.nowak@medflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'active'),
('michal.wisniewski@medflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'active'),
-- Receptionist
('recepcja@medflow.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'active'),
-- Patients
('pacjent1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'active'),
('pacjent2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'active'),
('pacjent3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'active');

-- Insert specializations
INSERT INTO specializations (name, description) VALUES
('Kardiologia', 'Choroby serca i układu krążenia'),
('Dermatologia', 'Choroby skóry'),
('Pediatria', 'Medycyna dzieci i młodzieży'),
('Ginekologia', 'Zdrowie kobiet'),
('Neurologia', 'Choroby układu nerwowego');

-- Insert doctors
INSERT INTO doctors (user_id, first_name, last_name, title, license_number, phone, bio) VALUES
(2, 'Jan', 'Kowalski', 'dr n. med.', 'PWZ1234567', '+48 500 100 200', 'Specjalista kardiolog z 15-letnim doświadczeniem'),
(3, 'Anna', 'Nowak', 'lek.', 'PWZ2345678', '+48 500 100 201', 'Dermatolog, specjalizuje się w leczeniu trądziku'),
(4, 'Michał', 'Wiśniewski', 'dr', 'PWZ3456789', '+48 500 100 202', 'Pediatra z pasją do medycyny rodzinnej');

-- Insert doctor specializations (many-to-many)
INSERT INTO doctor_specializations (doctor_id, specialization_id) VALUES
(1, 1), -- Jan Kowalski - Kardiologia
(2, 2), -- Anna Nowak - Dermatologia
(3, 3); -- Michał Wiśniewski - Pediatria

-- Insert patients
INSERT INTO patients (user_id, first_name, last_name, pesel, date_of_birth, phone, address, city, postal_code) VALUES
(6, 'Liam', 'Gallagher', '90010112345', '1990-01-01', '+48 600 200 300', 'ul. Zdrowa 1', 'Warszawa', '00-001'),
(7, 'Olivia', 'Chen', '85050567890', '1985-05-05', '+48 600 200 301', 'ul. Spokojna 5', 'Warszawa', '00-002'),
(8, 'Benjamin', 'Carter', '92121298765', '1992-12-12', '+48 600 200 302', 'ul. Cicha 10', 'Warszawa', '00-003');

-- Insert appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, duration, type, status, reason) VALUES
-- Upcoming appointments
(1, 1, CURRENT_DATE + 1, '09:00:00', 30, 'nfz', 'confirmed', 'Kontrola ciśnienia'),
(2, 2, CURRENT_DATE + 1, '10:00:00', 30, 'private', 'scheduled', 'Konsultacja dermatologiczna'),
(3, 3, CURRENT_DATE + 2, '11:00:00', 30, 'nfz', 'scheduled', 'Badanie pediatryczne'),
-- Past appointments
(1, 1, CURRENT_DATE - 30, '14:00:00', 30, 'nfz', 'completed', 'Badanie kontrolne serca'),
(2, 2, CURRENT_DATE - 20, '10:00:00', 30, 'private', 'completed', 'Leczenie trądziku');

-- Insert medical records for completed appointments
INSERT INTO medical_records (appointment_id, patient_id, doctor_id, diagnosis_icd10, diagnosis_description, symptoms, treatment, recommendations) VALUES
(4, 1, 1, 'I10', 'Nadciśnienie tętnicze samoistne', 'Bóle głowy, zawroty głowy', 'Przepisano lek hipotensyjny', 'Kontrola za 3 miesiące, dieta niskosodowa'),
(5, 2, 2, 'L70.0', 'Trądzik pospolity', 'Zmiany zapalne na twarzy', 'Kuracja antybiotykowa miejscowa', 'Unikać słońca, kontrola za miesiąc');