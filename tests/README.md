# MedFlow - Dokumentacja Testów

## Przegląd

Projekt zawiera dwa rodzaje testów:
- **Unit tests** - testują pojedyncze komponenty w izolacji
- **Integration tests** - testują interakcje między komponentami

## Struktura testów

```
tests/
├── bootstrap.php                           # Inicjalizacja środowiska testowego
├── run_tests.php                          # Runner wszystkich testów
├── unit/                                  # Testy jednostkowe
│   ├── ValidationServiceTest.php         # Testy walidacji
│   ├── UserTest.php                      # Testy modelu User
│   └── AppointmentTest.php               # Testy modelu Appointment
└── integration/                           # Testy integracyjne
    └── AuthenticationIntegrationTest.php  # Testy flow autentykacji
```

## Uruchamianie testów

### Wszystkie testy

```bash
docker exec -it medflow-php-1 php /app/tests/run_tests.php
```

### Pojedynczy test unit

```bash
docker exec -it medflow-php-1 php /app/tests/unit/ValidationServiceTest.php
docker exec -it medflow-php-1 php /app/tests/unit/UserTest.php
docker exec -it medflow-php-1 php /app/tests/unit/AppointmentTest.php
```

### Pojedynczy test integracyjny

```bash
docker exec -it medflow-php-1 php /app/tests/integration/AuthenticationIntegrationTest.php
```

## Testy jednostkowe

### ValidationServiceTest

Testuje funkcje walidacji:
- Email validation (valid/invalid formats)
- Password validation (length requirements)
- PESEL validation (format + checksum)
- Phone validation (length + format)
- Date validation (format + validity)
- String sanitization (XSS prevention)
- Email sanitization

### UserTest

Testuje model User:
- User creation (constructor, getters)
- Password hashing (bcrypt)
- Password verification (correct/incorrect)
- User status (active/inactive)
- toArray() method
- toSessionArray() (bez hasła)


### AppointmentTest

Testuje model Appointment:
- Appointment creation
- Status checks (scheduled/completed/cancelled)
- Type checks (NFZ/private)
- Date/time formatting

## Testy integracyjne

### AuthenticationIntegrationTest

Testuje pełny flow autentykacji:
- User registration flow (validation → hashing)
- Password validation (correct/incorrect)
- Session data structure (bez hasła)
- Appointment booking validation
- Patient data validation (PESEL, DOB, phone, email)

**Flow testowy:**
```
Registration → Validation → Password Hash → Login → Session → Logout
```

## Metryki

Po uruchomieniu testów:
```
Test Summary:              
Total test suites: 4                  
Passed:            4                  
Failed:            0                  


All tests passed successfully!
```