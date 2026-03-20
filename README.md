# TechShop Security Lab

Een **opzettelijk onveilige** PHP-webshop om beveiligingslekken te leren herkennen en begrijpen.
Gebouwd voor MBO4 Software Developer studenten.

---

## Wat heb je nodig?

- **PHP 8.x** — download via [php.net](https://php.net/downloads)
- Een webbrowser (Chrome of Firefox)
- Dat is alles — geen XAMPP, geen MySQL, geen extra software

Controleer of PHP werkt:
```
php --version
```

---

## Installeren

### Stap 1 — Download het project

Klik op de groene **Code** knop bovenaan deze pagina en kies **Download ZIP**.
Pak het ZIP-bestand uit op je computer.

Of via git (als dat geïnstalleerd is):
```
git clone https://github.com/epvankleef/onveilige-webshop.git
cd onveilige-webshop
```

### Stap 2 — Start de server

Open een terminal in de projectmap:
```
php -S localhost:8080
```

### Stap 3 — Open de website

Ga naar: **http://localhost:8080**

De database wordt automatisch aangemaakt.

---

## Testaccounts

| Gebruikersnaam | Wachtwoord | Rol           |
|---------------|-----------|---------------|
| admin         | admin123  | Beheerder     |
| john          | password  | Gebruiker     |
| jane          | 123456    | Gebruiker     |
| test          | test      | Gebruiker     |

---

## De opdrachten

Er zijn **8 beveiligingslekken** te vinden, gebaseerd op OWASP Top 10.
Dit project werkt als een **CTF (Capture The Flag)** — bij elk lek is een vlag verborgen.

- Ga naar **http://localhost:8080/ctf.php** voor de uitdagingen en het scorebord
- Lees **HANDLEIDING.md** voor de volledige stap-voor-stap uitleg per lek

---

## Bestandsstructuur

```
onveilige-webshop/
├── index.php           Homepage met zoekfunctie
├── login.php           Inlogpagina
├── register.php        Registratiepagina
├── logout.php          Uitloggen
├── products.php        Productenoverzicht
├── product.php         Productdetail + reviews
├── admin.php           Admin dashboard
├── ctf.php             CTF-pagina (uitdagingen + scorebord)
├── contact.php         Contactformulier
├── config.php          Database + hulpfuncties
├── styles.css          Stijlen
├── includes/
│   ├── header.php      Navigatie
│   └── footer.php      Footer
├── webshop.db          SQLite database (auto aangemaakt)
├── logs/
│   └── actions.log     Actielog
└── HANDLEIDING.md      Volledige uitleg per lek
```

---

## Veelgestelde vragen

**De database is leeg?**
Verwijder `webshop.db` en herstart de server.

**Poort 8080 is bezet?**
Gebruik een andere poort: `php -S localhost:8181`

**Server stoppen?**
Druk op `Ctrl + C` in de terminal.

---

## Disclaimer

Dit project is **opzettelijk onveilig** voor educatieve doeleinden.
Gebruik deze kennis uitsluitend voor het beveiligen van eigen applicaties of met toestemming.
**Nooit gebruiken voor ongeautoriseerde toegang.**
