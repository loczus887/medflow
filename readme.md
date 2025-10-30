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