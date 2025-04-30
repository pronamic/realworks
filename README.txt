=== Realworks ===

Realworks koppeling en integratie

== Beschrijving ==

### Features

* Koppeling met meerdere Realworks accounts.
* Bijwerkgeschiedenis in kunnen zien, status van bijwerkproces live volgen en annuleren.
* Zoekvelden eenvoudig maar uitgebreid instellen vanuit de admin.
* Zoeken in omgeving door binnen een bepaalde straal te kunnen zoeken.
* Per zoekoptie weergeven hoeveel resultaten er voor zullen zijn.
* Direct bijwerken van resultaten bij wijzigen van zoekoptie.
* Mogelijkheid om resultaten op de gewenste manier te sorteren.
* Zeer uitgebreide controle over weergave van velden.
* Google Maps ondersteuning.
* Kwaliteit gewaarborgd door circa 400 geautomatiseerde tests.
* Alles is naar wens aan te passen.
* Uitgebreide documentatie.

== Installatie ==

Installeer de plugin door de plugin bestanden naar `/wp-content/plugins/realworks/` te uploaden. Activeer de plugin vervolgens vanuit de WordPress admin.

### Vereisten
* PHP 5.3 of hoger

Als hier niet aan voldaan wordt zal het activeren van de plugin mislukken of kan het zijn dat de site niet meer wordt geladen. In dat geval moet de plugin map worden verwijderd of hernoemd om het probleem te verhelpen.

Verder zijn de volgende vereisten noodzakelijk voor het soepel laten verlopen van updates:

* Geen `max_execution_time` limiet
* Geheugenlimiet minstens 128MB, hoger gewenst

### Licentie
Bij de aanshaf van de plugin heeft u een licentiecode verkregen. Het is van belang dat deze wordt ingevoerd om de plugin zonder restricties te laten werken.

### Schijfbevoegdheden
Na het activeren van de plugin in WordPress is het van belang om te controleren dat de plugin voldoende schijfbevoegdheden heeft. Als hier niet aan wordt voldaan zullen plugin instellingen niet kunnen worden opgeslagen en zal bijwerken foutmeldingen geven.

#### Instellingen
Alle instellingen van de plugin worden opgeslagen in de thema-map. Dit heeft als voordeel dat een site gemakkelijk gekopieerd kan worden waarbij alle instellingen ook worden overgenomen.

== Changelog ==

#### 3.5.4 (29 oktober 2020)
* De migratie die wordt uitgevoerd om logbestanden te verwijdern verloopt nu sneller om timeouts te voorkomen.

#### 3.5.3 (28 oktober 2020)
* Debug logging tijdens bijwerkmomenten is standaard uitgezet. Tevens worden bestaande error logs verwijderd vanuit veiligheidsoverwegingen.

#### 3.5.2 (4 augustus 2020)
* Probleem opgelost in de standaard configuratie van het automatisch verwijderen van objecten.

#### 3.5.1 (9 juli 2020)
* Probleem opgelost waarbij meerdere wijzigingen aan een object op dezelfde dag niet worden verwerkt.

#### 3.5.0 (3 juli 2020)
* Het bijwerken is geoptimaliseerd in situaties dat een object niet is aangepast sinds het voorgaande bijwerkmoment.
* Status van veld caches kan worden ingezien vanuit het admin dashboard, met de optie de cache te verwijderen.
* Probleem verholpen waarbij handmatige uploads andere objecten kon verwijderen terwijl "Niet koppelen" was geselecteerd.
* Een ongeldige configuratie voor titel of inhoud zal niet langer databasefouten veroorzaken.
* Het bepalen van media extensies is verbeterd.

#### 3.4.0 (15 september 2018)
* Overzicht van beschikbare add-ons vanuit het admin dashboard.
* Compatibiliteit met WPML plugin.
* Het opslaan van wijzingen in de admin is versnelt.
* Probleem opgelost waarbij het laden van het admin dashboard (te) lang duurde.

#### 3.3.0 (10 oktober 2017)
* Nieuw: ondersteuning toegevoegd voor vertraagd verwijderen van objecten.
* Compatibiliteit met Realworks BOG v20.
* Diverse optimalisaties in het bijwerken van zoekopties.
* Illuminate 4.1 dependencies zijn gerepackaged onder een custom namespace, om conflicten met andere plugins/themas te voorkomen.
* Probleem opgelost wanneer verwerking van media op de achtergrond halverwege faalde en resterende media niet meer verwerkt werd.
* Probleem opgelost met zoeken waarbij deselecteren van de default checkboxes direct wordt teruggevallen op de defaults als er niets meer geselecteerd is.

#### 3.2.2 (18 augustus 2016)
* Incompatibiliteit met WordPress 4.6.0 opgelost.
* Compatibiliteitsproblemen op Windows verholpen.
* Probleem verholpen met verwerken van afbeeldingen thumbnails.
* Problemen omtrent verschillen in tijdzones opgelost.

#### 3.2.1 (29 maart 2016)
* Ondersteuning toegevoegd om woning permalinks op te splitsen in componenten.
* De provincie is beschikbaar in het veld *provincie*, alleen indien Google Maps is ingeschakeld.
* Opslaan van instellingen duurt minder lang.
* Probleem opgelost met tellen van array velden.

#### 3.2.0 (5 januari 2016)
* Vernieuwde API voor handmatig zoeken naar woningen in het thema.
* Eenvoudig nabijgelegen woningen opvragen via `$woning->nearby('10km')`.
* Een geldige licentie is verplicht voor dagelijks bijwerken en verdere plugin updates.
* Ondersteuning voor PHP 7.0 toegevoegd.

#### 3.1.1 (10 december 2015)
* Problemen met bijwerken worden voorkomen door altijd de nieuwste versie van de koppeling te gebruiken.

#### 3.1.0 (9 december 2015)
* Ondersteuning toegevoegd voor Nieuwbouw en A&LV koppelingen.
* Ondersteuning voor infinite scrollen bij zoekresultaten toegevoegd.
* Eenvoudig meerdere pagina's aanmaken zodat zoekresultaten kunnen worden onderverdeeld.
* Ondersteuning voor het weergeven van array velden in een lijst, in plaats van alleen kommagescheiden.
* Uitgebreidere mogelijkheden om listings aan te passen per veld.
* Alle opties van een zoekveld zijn beschikbaar als JSON voor gebruik vanuit Javascript.
* Wijzigingen doorgevoerd waardoor het laden tot 2.5 keer sneller is, dit vereist wijzigingen in het thema.
* Menu toegevoegd in WordPress admin bar voor snellere toegang tot instellingen.
* Het veld *medialijst* is beschikbaar met de exacte media informatie zoals beschikbaar in de XML.
* Iedere verdieping heeft naast het veld *badkamer* nu ook lijst van alle badkamers in het veld *badkamers*.
* Foutmeldingen bij downloaden van data vanaf Realworks zijn weer beschikbaar.
* Bij het downloaden van woningen en afbeeldingen is de kans op timeouts verkleind.
* Verwijderen van woningen zal ook de WordPress media posts verwijderen, naast de media bestanden.
* Probleem opgelost met zoeken binnen straal wanneer voor geen van de woningen een locatie bekend is.
* Probleem opgelost waarbij overzichtspagina's geen objecten toonden in WordPress 4.4.
* Probleem opgelost waarbij de plugin niet geactiveerd kan worden met PHP ouder dan 5.3.9.
* Probleem opgelost waarbij items in collections beschikbaar blijven terwijl niet langer in bijwerkfeed.
* Probleem opgelost waarbij bepaalde `include_path` instellingen verhinderen de plugin te activeren.

#### 3.0.1 (20 maart 2015)
* Bijwerkgeschiedenis overzicht is verbeterd, met inzicht in gewijzigde waardes.
* Mogelijkheid toegevoegd om een bijwerkmoment opnieuw uit te voeren.
* Uitgebreidere mogelijkheid tot het beïnvloeden van de zichtbaarheid van een woning.
* Probleem met verwijderen van niet langer beschikbare woningen opgelost.
* Veld *woning.aantal* hernoemd naar *woning.aantalVerdiepingen*.

#### 3.0.0 (1 maart 2015)
* Volledig nieuwe Realworks plugin.

== Upgrade Notice ==

#### 3.3.0
Nieuwe versie van Realworks beschikbaar om compatibel te blijven met Realworks BOG aanbod.

Ondersteuning voor BOG met oudere versies stopt na 13 oktober 2017!

#### 3.2.0
Nieuwe versie van Realworks beschikbaar.

Zorg voor een geldige licentie om dagelijks bijwerken voort te zetten.
