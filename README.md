# Lærervurdering

En anonym nettside hvor elever kan rate lærere fra 1–5 stjerner og legge igjen kommentarer om hva som er bra og hva som kan bli bedre.

---

## Filstruktur

```
/
├── index.php           ← Forsiden – viser alle lærere med snittrating
├── teacher.php         ← Lærerside – viser reviews + skjema for ny vurdering
├── db.php              ← Databasetilkobling (inkluderes av alle PHP-filer)
├── style.css           ← Alt CSS for hele siden
├── script.js           ← Stjernevelger, søk, AJAX-innsending, DOM-oppdatering
└── api/
    └── submit.php      ← POST-endpoint – lagrer review, returnerer JSON
```

---

## Database

Dette kjøres i MySQL for å sette opp tabellene:

```sql
CREATE TABLE teachers (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  name    VARCHAR(100) NOT NULL,
  subject VARCHAR(100)
);

CREATE TABLE reviews (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id  INT NOT NULL,
  stars       TINYINT NOT NULL CHECK (stars BETWEEN 1 AND 5),
  pros        TEXT,
  cons        TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);
```

Lærere legges inn manuelt:

```sql
INSERT INTO teachers (name, subject) VALUES
  ('Kari Nordmann', 'Matematikk'),
  ('Ole Hansen', 'Programmering'),
  ('Lise Dahl', 'Engelsk');
```

---

## Oppsett

### 1. Last opp filer

Filene lastes opp til serveren. (f.eks. via FTP eller direkte på VPS). Mappestrukturen må bevares slik at `api/submit.php` ligger i en undermappe.

### 2. Tillatelser

Sørges for at PHP har lesetilgang til alle filer og at webserveren peker på rotkatalogen.

---

## Dataflyten

```
index.php
  → PHP henter alle lærere + AVG(stars) direkte fra DB
  → Viser lærerkort med snittrating
  → Live søk via JS (ingen reload)
  → Klikk på kort → teacher.php?id=X

teacher.php?id=X
  → PHP henter lærerinfo + alle reviews fra DB
  → Renders siden med data ferdig innbakt i HTML
  → Bruker fyller ut skjema (stjerner, pros, cons)
  → JS sender AJAX POST til api/submit.php
  → Ved suksess: ny review vises i DOM uten reload,
    snittrating i header oppdateres automatisk
```

---

## Sikkerhet

| Tiltak | Hvor |   
|---|---|
| Prepared statements (PDO `?`) | Alle DB-spørringer – ingen SQL injection |
| `htmlspecialchars()` | Alle steder brukerdata skrives ut i HTML |
| Input-validering | `api/submit.php` sjekker at `stars` er 1–5 og `teacher_id` er gyldig |
| Ingen sensitiv data lagres | Ingen IP, ingen brukernavn |

**Anbefalt videre:** legg til en `approved TINYINT DEFAULT 0`-kolonne i `reviews` og en enkel admin-side for moderering, eller rate-limiting per IP for å begrense spam.

---

## Teknisk stack

- **Frontend:** HTML5, CSS3, Vanilla JS (Fetch API)
- **Backend:** PHP 8+, PDO
- **Database:** MySQL / MariaDB