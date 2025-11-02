<?php

class MedicalRecord {
    
    private int $id;
    private int $appointmentId;
    private int $patientId;
    private int $doctorId;
    private ?string $diagnosisIcd10;
    private ?string $diagnosisDescription;
    private ?string $symptoms;
    private ?string $treatment;
    private ?string $prescription;
    private ?string $recommendations;
    private ?string $nextVisitDate;
    private string $createdAt;
    private string $updatedAt;
    
    public function __construct(array $data) {
        $this->id = (int)$data['id'];
        $this->appointmentId = (int)$data['appointment_id'];
        $this->patientId = (int)$data['patient_id'];
        $this->doctorId = (int)$data['doctor_id'];
        $this->diagnosisIcd10 = $data['diagnosis_icd10'] ?? null;
        $this->diagnosisDescription = $data['diagnosis_description'] ?? null;
        $this->symptoms = $data['symptoms'] ?? null;
        $this->treatment = $data['treatment'] ?? null;
        $this->prescription = $data['prescription'] ?? null;
        $this->recommendations = $data['recommendations'] ?? null;
        $this->nextVisitDate = $data['next_visit_date'] ?? null;
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'];
    }
    
    public function getId(): int {
        return $this->id;
    }
    
    public function getAppointmentId(): int {
        return $this->appointmentId;
    }
    
    public function getPatientId(): int {
        return $this->patientId;
    }
    
    public function getDoctorId(): int {
        return $this->doctorId;
    }
    
    public function getDiagnosisIcd10(): ?string {
        return $this->diagnosisIcd10;
    }
    
    public function getDiagnosisDescription(): ?string {
        return $this->diagnosisDescription;
    }
    
    public function getSymptoms(): ?string {
        return $this->symptoms;
    }
    
    public function getTreatment(): ?string {
        return $this->treatment;
    }
    
    public function getPrescription(): ?string {
        return $this->prescription;
    }
    
    public function getRecommendations(): ?string {
        return $this->recommendations;
    }
    
    public function getNextVisitDate(): ?string {
        return $this->nextVisitDate;
    }
    
    public function getCreatedAt(): string {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): string {
        return $this->updatedAt;
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointmentId,
            'patient_id' => $this->patientId,
            'doctor_id' => $this->doctorId,
            'diagnosis_icd10' => $this->diagnosisIcd10,
            'diagnosis_description' => $this->diagnosisDescription,
            'symptoms' => $this->symptoms,
            'treatment' => $this->treatment,
            'prescription' => $this->prescription,
            'recommendations' => $this->recommendations,
            'next_visit_date' => $this->nextVisitDate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}