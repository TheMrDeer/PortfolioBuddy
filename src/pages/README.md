\### Datenbank-Zugangsdaten

Die Verbindungseinstellungen befinden sich in der Datei `src/pages/includes/dbaccess.php`. Standardmäßig ist das Projekt für folgende Zugangsdaten konfiguriert:

Config:

\* \*\*Host:\*\* `localhost`

\* \*\*Datenbank:\*\* `portfoliobuddy`

\* \*\*Benutzer:\*\* `portfoliobuddy\_db`

\* \*\*Passwort:\*\* `KUybM\[Ud/\_HOLvHw`



DB:

CREATE USER IF NOT EXISTS 'portfoliobuddy\_db'@'localhost' IDENTIFIED BY 'KUybM\[Ud/\_HOLvHw';

GRANT ALL PRIVILEGES ON portfoliobuddy.\* TO 'portfoliobuddy\_db'@'localhost';

FLUSH PRIVILEGES;


###Datenbank-Import: 

Bitte nutzen Sie die Datei "/sql/DB-Setup.sql"



Dort sind zur schnellen Durchsicht bereits 2 Test User hinterlegt:



User: 'testuser@testmail.com'
Admin: 'testadmin@testmail.com'



Passwords: ABCD-1234



