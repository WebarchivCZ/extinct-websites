# Extinct Websites

<img width="1066" alt="Snímek obrazovky 2022-11-29 v 18 40 09" src="https://user-images.githubusercontent.com/62152053/204602538-963f65a9-cccf-4627-8c86-8e15203b3b8b.png">

## About App

The application serves as an automated solution for identifying and describing "dead sites" and then stores them in its own database and makes them available to curators who further manipulate, interpret and classify the information in the database.

In the first plan, the application identifies "dead sites" using status codes, according to which it categorizes the sites into groups that automate other processes such as verifying metadata from live sites, the WhoIS database, or historical metadata. The application identifies "dead sites" up to the level of 3rd order domains.

The application helps us grasp the concept of dead web, which is beneficial in terms of web archiving practice. In fact, by identifying the topology of the dead web and anchoring the term, it will be possible to monitor the disappearing sites in the long term and thus obtain an exclusive report on the disappearing web landscape.

The application was designed for the needs of the Czech Web Archive of the Czech Republic (Webarchiv). It can be used by web archives and memory institutions to streamline the monitoring of archival data.


## O aplikaci

Aplikace slouží jako automatizované řešení pro identifikaci a popis mrtvých webů. Následně je ukládá do vlastní databáze a zpřístupňuje kurátorům, kteří s informacemi v ní dále nakládají, interpretují je a obsah klasifikují. 

Aplikace v prvním plánu identifikuje mrtvé weby za pomocí stavových kódů, dle kterých weby kategorizuje na skupiny, jimiž jsou automatizovány další procesy jako je ověřování metadat z živých webů, databáze WhoIS, či historických metadat. Aplikace identifikuje mrtvé weby do úrovně domén 3. řádů.

Aplikace nám pomáhá uchopit pojem mrtvý web, což  je prospěšné z hlediska webarchivářské praxe. Určením topologie mrtvého webu a ukotvením termínu, bude totiž možné dlouhodobě monitorovat zanikajicí weby a získat tak exkluzivní zprávu o mizející webové krajině. 

Aplikace byla navržena pro potřeby Českého webového archivu NK ČR (Webarchiv). Může sloužit webovým archivům a paměťovým institucím pro zefektivnění dohledu nad archivními daty. 


## **Skladba**
WebBeat - skript pro extrakci obsahových, síťových a infrastrukturních dat z webových stránek

Logparser - skript pro analýzu dat z logů archivovaných webových stránek


# **Začínáme**
## **Prerekvizity**
* Operační systém Linux
* Webový server Apache 2
* PHP 7 (podpora php-json)
* Databáze MySQL v8
* Python 3.9+


## **Instalace**
* Zazipovanou aplikaci (extinctWebsitesApp.zip) stačí rozbalit do adresáře, kam je nakonfigurován webový server (nejčastěji /var/www)
* Dále je potřeba nahrát všechny PHP skripty ze složky server do stejného webového adresáře
* V MySQL je potřeba vytvořit novou databázi a naiportovat soubor “extinctWebsites.sql”
* Přihlašovací údaje je třeba vyplnit v PHP skriptu “connect.php” a nahrát ho do hlavního webového adresáře
* Automatické spouštění ověřování živosti webů lze cronem - např.: *      4       *       *       *       /usr/bin/php /var/www/autocheck/checkAll.php &> /dev/null


## **Koncepce**

* **Marie Haškovcová** -  *koncepce, teorie*
* **Luboš Svoboda** -  *koncepce, teorie, testování*
* **Zdenko Vozár** -  *architektura řešení*

## **Vývoj**

* **Jan Holomek** - *front-end app, db managment*
* **Petra Habetinova** -  *back-end app Logparser*
* **Zdenko Vozár** -  *back-end app WebBEAT*

## **Více**
Více informací a uživatelský manuál naleznete zde: https://github.com/WebarchivCZ/extinct-websites/wiki, https://github.com/WebarchivCZ/extinct-websites/wiki/Popis-aplikace

## Dedikace
Národní knihovna ČR

_Realizováno v rámci institucionálního výzkumu Národní knihovny České republiky financovaného Ministerstvem kultury ČR v rámci Dlouhodobého koncepčního rozvoje výzkumné organizace._
