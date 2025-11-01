# MedFlow - System Zarządzania Przychodnią

System zarządzania przychodnią usprawniający pracę personelu medycznego i ułatwiający pacjentom dostęp do opieki zdrowotnej.

## Technologie

- Docker
- PHP 8.3 (OOP)
- PostgreSQL 16
- Nginx
- HTML5, CSS3, JavaScript (Fetch API)
- Git

## Architektura

Aplikacja wykorzystuje architekturę MVC z wyraźnym podziałem na warstwy:
- **Models** - reprezentacja danych
- **Repository** - warstwa dostępu do danych
- **Services** - logika biznesowa
- **Controllers** - obsługa żądań HTTP
- **Views** - warstwa prezentacji

## Informacje

- Aplikacja będzie dostępna pod adresem: http://localhost:8080
- pgAdmin będzie dostępny pod adresem: http://localhost:5050

## Baza danych

# Baza Danych MedFlow

## Struktura

### Relacje między tabelami:

1. **users (1) ↔ (1) patients** - jeden użytkownik może być jednym pacjentem
2. **users (1) ↔ (1) doctors** - jeden użytkownik może być jednym lekarzem
3. **roles (1) ↔ (N) users** - jedna rola może mieć wielu użytkowników
4. **doctors (N) ↔ (M) specializations** - wielu lekarzy może mieć wiele specjalizacji
5. **patients (1) ↔ (N) appointments** - jeden pacjent może mieć wiele wizyt
6. **doctors (1) ↔ (N) appointments** - jeden lekarz może mieć wiele wizyt
7. **appointments (1) ↔ (1) medical_records** - jedna wizyta ma jedną dokumentację

## Typy relacji:
- **1:1** - users ↔ patients, users ↔ doctors, appointments ↔ medical_records
- **1:N** - roles ↔ users, patients ↔ appointments, doctors ↔ appointments
- **N:M** - doctors ↔ specializations (przez doctor_specializations)

## Konta testowe:

| Email | Hasło | Rola |
|-------|-------|------|
| admin@medflow.com | haslo123 | Admin |
| jan.kowalski@medflow.com | haslo123 | Lekarz |
| anna.nowak@medflow.com | haslo123 | Lekarz |
| recepcja@medflow.com | haslo123 | Recepcja |
| pacjent1@example.com | haslo123 | Pacjent |

### Diagram ERD
(Diagram zostanie dodany)

### Struktura
(Opis tabel zostanie dodany)

## Funkcjonalności

### Role użytkowników
- **Administrator** - zarządzanie całym systemem
- **Lekarz** - prowadzenie dokumentacji medycznej
- **Recepcja** - zarządzanie wizytami
- **Pacjent** - umawianie wizyt, dostęp do dokumentacji

### Główne funkcje
- Logowanie i rejestracja
- Dashboard dla różnych ról
- Umawianie wizyt online
- Zarządzanie dokumentacją medyczną
- Kalendarz wizyt
- Panel administracyjny

## Testy

(Instrukcje testowania zostaną dodane)


## Autor

Alicja Kowalska