# PortfolioBuddy

PortfolioBuddy ist eine webbasierte Anwendung zum Verwalten von Aktienportfolios. Benutzer können sich registrieren, ihre Depots tracken und Kaufbelege zu ihren Positionen hochladen.

## Inhaltsverzeichnis
1. [Installation & Konfiguration](#1-installation--konfiguration)
2. [Datenbank Einrichtung](#2-datenbank-einrichtung)
3. [Systemarchitektur](#3-systemarchitektur)
4. [Ordnerstruktur & Uploads](#4-ordnerstruktur--uploads)

---

## 1. Installation & Konfiguration

### Voraussetzungen
* Webserver (z.B. Apache via XAMPP/MAMP oder Nginx)
* PHP (empfohlen >= 8.0)
* MySQL oder MariaDB Datenbank

### Datenbank-Zugangsdaten
Die Verbindungseinstellungen befinden sich in der Datei `src/pages/includes/dbaccess.php`. Standardmäßig ist das Projekt für folgende Zugangsdaten konfiguriert:

* **Host:** `localhost`
* **Datenbank:** `portfoliobuddy`
* **Benutzer:** `portfoliobuddy_db`
* **Passwort:** `KUybM[Ud/_HOLvHw`

Stelle sicher, dass diese Daten mit deiner lokalen Datenbank übereinstimmen.

---

## 2. Datenbank Einrichtung

Führe die folgenden SQL-Befehle in deinem Datenbank-Client (z.B. phpMyAdmin) aus, um das System aufzusetzen.

### Schritt A: Datenbank und User erstellen
```sql
-- 1. Datenbank anlegen
CREATE DATABASE IF NOT EXISTS `portfoliobuddy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 2. Datenbank auswählen
USE `portfoliobuddy`;

-- 3. (Optional) Spezifischen User anlegen
-- Falls du root nutzt, ist dieser Schritt nicht zwingend, aber empfohlen.
CREATE USER IF NOT EXISTS 'portfoliobuddy_db'@'localhost' IDENTIFIED BY 'KUybM[Ud/_HOLvHw';
GRANT ALL PRIVILEGES ON portfoliobuddy.* TO 'portfoliobuddy_db'@'localhost';
FLUSH PRIVILEGES;
'''
```
```sql

--User Tabele
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(125) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--Asset Tabelle
CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `isin` varchar(20) NOT NULL,
  `quantity` decimal(10,4) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `asset_type` varchar(50) DEFAULT 'Stock',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
## 3. Systemarchitektur

Das System basiert auf einer relationalen Datenbank und PHP-Sessions.
Authentifizierung & Sicherheit

    Login: E-Mail und Passwort werden abgefragt. Das Passwort wird mittels password_verify() gegen den in der DB gespeicherten Hash geprüft.

    Session: Nach erfolgreichem Login wird die user_id in $_SESSION['user'] gespeichert. Alle geschützten Seiten (dashboard.php, positions.php) prüfen zu Beginn, ob diese Session existiert.

    SQL-Injection Schutz: Alle Datenbankabfragen nutzen Prepared Statements ($stmt->prepare(...) und $stmt->bind_param(...)), um Angriffe zu verhindern.

--Beziehungen (Relationen)

    1:n Beziehung: Ein User kann viele Assets haben.

    Foreign Key Constraint: Die Tabelle assets ist fest mit users verbunden.

    On Delete Cascade: Wenn ein Benutzer aus der Tabelle users gelöscht wird, löscht die Datenbank automatisch alle seine Einträge in der Tabelle assets. Das verhindert Datenmüll ("verwaiste Einträge").

## 4. Ordnerstruktur & Uploads

Dateien (wie Profilbilder oder Kaufbelege) werden nicht in der Datenbank gespeichert, sondern im Dateisystem, um die Performance zu schonen.

Die Struktur wird dynamisch generiert:
```Plaintext

src/pages/user_uploads/
├── {USER_ID}/                  # Ordner für jeden Benutzer (z.B. "1")
│   ├── profilepicture/         # Profilbilder
│   │   └── avatar.png
│   └── asset_attachment/       # Kaufbelege
│       ├── {ASSET_ID}/         # Unterordner pro Aktie (z.B. "42")
│       │   └── rechnung.pdf
```
    Logik: Beim Erstellen einer Position (INSERT) wird die neue asset_id ermittelt und der entsprechende Ordner angelegt.

    Vorteil: Auch wenn ein User mehrmals die gleiche Aktie kauft, kommen sich die Dateien nicht in die Quere, da sie in Ordnern basierend auf der eindeutigen ID der Transaktion liegen.
