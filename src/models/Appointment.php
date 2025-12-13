<?php

class Appointment {
    
    private int $id;
    private int $patientId;
    private int $doctorId;
    private string $appointmentDate;
    private string $appointmentTime;
    private int $duration;
    private string $type;
    private string $status;
    private ?string $reason;
    private ?string $notes;
    private ?string $createdAt;
    private ?string $updatedAt;
    
    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->patientId = (int)$data['patient_id'];
        $this->doctorId = (int)$data['doctor_id'];
        $this->appointmentDate = $data['appointment_date'];
        $this->appointmentTime = $data['appointment_time'];
        $this->duration = (int)($data['duration'] ?? 30);
        $this->type = $data['type'] ?? 'nfz';
        $this->status = $data['status'] ?? 'scheduled';
        $this->reason = $data['reason'] ?? null;
        $this->notes = $data['notes'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
    }
    
    public function getId(): int {
        return $this->id;
    }
    
    public function getPatientId(): int {
        return $this->patientId;
    }
    
    public function getDoctorId(): int {
        return $this->doctorId;
    }
    
    public function getAppointmentDate(): string {
        return $this->appointmentDate;
    }
    
    public function getAppointmentTime(): string {
        return $this->appointmentTime;
    }
    
    public function getDuration(): int {
        return $this->duration;
    }
    
    public function getType(): string {
        return $this->type;
    }
    
    public function getStatus(): string {
        return $this->status;
    }
    
    public function getReason(): ?string {
        return $this->reason;
    }
    
    public function getNotes(): ?string {
        return $this->notes;
    }
    
    public function getFullDate(): string {
        return $this->appointmentDate . ' ' . $this->appointmentTime;
    }
    
    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }
    
    public function isScheduled(): bool {
        return $this->status === 'scheduled';
    }
    
    public function isConfirmed(): bool {
        return $this->status === 'confirmed';
    }
    
    public function isCompleted(): bool {
        return $this->status === 'completed';
    }
    
    public function isCancelled(): bool {
        return $this->status === 'cancelled';
    }
    
    public function isNFZ(): bool {
        return $this->type === 'nfz';
    }
    
    public function isPrivate(): bool {
        return $this->type === 'private';
    }
    
    public function getTypeDisplay(): string {
        return $this->isNFZ() ? 'NFZ' : 'Prywatna';
    }
    
    public function getStatusDisplay(): string {
        $statuses = [
            'scheduled' => 'Zaplanowana',
            'confirmed' => 'Potwierdzona',
            'completed' => 'Zakończona',
            'cancelled' => 'Anulowana',
            'no_show' => 'Nieobecność'
        ];
        return $statuses[$this->status] ?? $this->status;
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'patient_id' => $this->patientId,
            'doctor_id' => $this->doctorId,
            'appointment_date' => $this->appointmentDate,
            'appointment_time' => $this->appointmentTime,
            'duration' => $this->duration,
            'type' => $this->type,
            'type_display' => $this->getTypeDisplay(),
            'status' => $this->status,
            'status_display' => $this->getStatusDisplay(),
            'reason' => $this->reason,
            'notes' => $this->notes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}