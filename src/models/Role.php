<?php

class Role {
    
    private int $id;
    private string $name;
    private ?string $description;
    private string $createdAt;
    
    public function __construct(array $data) {
        $this->id = (int)$data['id'];
        $this->name = $data['name'];
        $this->description = $data['description'] ?? null;
        $this->createdAt = $data['created_at'];
    }
    
    public function getId(): int {
        return $this->id;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function getDescription(): ?string {
        return $this->description;
    }
    
    public function getCreatedAt(): string {
        return $this->createdAt;
    }
    
    public function isAdmin(): bool {
        return $this->name === 'admin';
    }
    
    public function isDoctor(): bool {
        return $this->name === 'doctor';
    }
    
    public function isReceptionist(): bool {
        return $this->name === 'receptionist';
    }
    
    public function isPatient(): bool {
        return $this->name === 'patient';
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->createdAt
        ];
    }
}