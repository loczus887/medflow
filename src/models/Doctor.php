<?php

class Doctor {
    
    private int $id;
    private int $userId;
    private string $firstName;
    private string $lastName;
    private ?string $title;
    private string $licenseNumber;
    private ?string $phone;
    private ?string $bio;
    private string $createdAt;
    private string $updatedAt;
    
    public function __construct(array $data) {
        $this->id = (int)$data['id'];
        $this->userId = (int)$data['user_id'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->title = $data['title'] ?? null;
        $this->licenseNumber = $data['license_number'];
        $this->phone = $data['phone'] ?? null;
        $this->bio = $data['bio'] ?? null;
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'];
    }
    
    public function getId(): int {
        return $this->id;
    }
    
    public function getUserId(): int {
        return $this->userId;
    }
    
    public function getFirstName(): string {
        return $this->firstName;
    }
    
    public function getLastName(): string {
        return $this->lastName;
    }
    
    public function getTitle(): ?string {
        return $this->title;
    }
    
    public function getFullName(): string {
        $name = '';
        if ($this->title) {
            $name .= $this->title . ' ';
        }
        $name .= $this->firstName . ' ' . $this->lastName;
        return $name;
    }
    
    public function getLicenseNumber(): string {
        return $this->licenseNumber;
    }
    
    public function getPhone(): ?string {
        return $this->phone;
    }
    
    public function getBio(): ?string {
        return $this->bio;
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
            'user_id' => $this->userId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'title' => $this->title,
            'full_name' => $this->getFullName(),
            'license_number' => $this->licenseNumber,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}