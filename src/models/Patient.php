<?php

class Patient {
    
    private int $id;
    private int $userId;
    private string $firstName;
    private string $lastName;
    private string $pesel;
    private string $dateOfBirth;
    private ?string $phone;
    private ?string $address;
    private ?string $city;
    private ?string $postalCode;
    private string $createdAt;
    private string $updatedAt;
    
    public function __construct(array $data) {
        $this->id = (int)$data['id'];
        $this->userId = (int)$data['user_id'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->pesel = $data['pesel'];
        $this->dateOfBirth = $data['date_of_birth'];
        $this->phone = $data['phone'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->postalCode = $data['postal_code'] ?? null;
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
    
    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }
    
    public function getPesel(): string {
        return $this->pesel;
    }
    
    public function getDateOfBirth(): string {
        return $this->dateOfBirth;
    }
    
    public function getPhone(): ?string {
        return $this->phone;
    }
    
    public function getAddress(): ?string {
        return $this->address;
    }
    
    public function getCity(): ?string {
        return $this->city;
    }
    
    public function getPostalCode(): ?string {
        return $this->postalCode;
    }
    
    public function getCreatedAt(): string {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): string {
        return $this->updatedAt;
    }
    
    public function getAge(): int {
        $birthDate = new DateTime($this->dateOfBirth);
        $today = new DateTime();
        return $today->diff($birthDate)->y;
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'pesel' => $this->pesel,
            'date_of_birth' => $this->dateOfBirth,
            'age' => $this->getAge(),
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}