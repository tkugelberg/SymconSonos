Sonos PHP Modul für IP-Symcon
===
IP-Symcon PHP Modul um Sonos Audio Systeme anzusteuern

**Content**

1. [Funktionsumfang](#1-funktionsumfang)
2. [Anforderungen](#2-anforderungen)
3. [Installation & Konfiguration](#3-installation--konfiguration)
4. [Variablen](#4-variablen)
5. [Hintergrund Prozesse](#5-hintergrund-prozesse)
6. [Funktionen](#6-funktionen)
6. [FAQ](#7-faq)

## 1. Funktionsumfang
Dieses Modul is dazu gedacht um allgemeine Funktionalitäten in Sonos aus IP-Symcon heraus auszulösen.  
Die folgenden Funktionalitäten sind implementiert:
- verschiedene Quellen festlegen
  - Radiosender
    - ausgelieferte Sender
    - importierte Sonos TuneIn Favoriten
  - Playlisten
  - Analogen Eingang
  - SPDIF Eingang
- Gruppenhandling
- Play / Pause / Stop / Previous / Next
- Lautstärke anpassen (inkl. default volume)
- Mute, Loudness, Bass, Treble
- Balance
- Sleeptimer
- Ansagen
- Coveranzeige  
- Audiodateien von einem Samba-Share (z.B. Synology) oder HTTP Server abspielen und danach den vorherigen Zustand wieder herstellen

## 2. Anforderungen

 - IPS 4.1
 - Sonos Audio System

## 3. Installation & Konfiguration

### Installation in IPS 4.1
Füge im "Module Control" (Kern Instanzen->Modules) die URL

   ``` 
   git://github.com/tkugelberg/SymconSonos.git
   ```
hinzu.  

Danach ist es möglich eine neue Sonos Instanz zu erstellen:
![Instanz erstellen](img/create_instance.png?raw=true "Instanz erstellen")
### Konfiguration
![Instanz konfigurieren](img/instance_config.png?raw=true "Instanz konfigurieren")
-  IP-Adresse / Host:  
Adresse unter der die Sonos Instanz erreichbar ist. Hierbei kann es sich um eine IP oder einen Hostnamen handeln.  
Wenn die Einstellungen gespeichert werden wird überprüft ob die Adresse erreichbar ist.
- Maximaler Ping Timeout:  
Bevor ein Kommando an eine Sonos Instanz gecshickt wird, wird per Sys_Ping() überprüft ob diese erreichbar ist.  
Dieser Parameter steuert nach wie vielen Millisekunden angenommen wird, dass die Box nicht verfügbar ist.  
Wenn der Parameter auf 0 gesetzt wird, wird die Erreichbarkeit nicht überprüft.
-  Standard Lautstärke:  
Die Lautstärke die eingestellt wird, wenn die Funktionen   
   ```php 
    SNS_SetDefaultVolume(<InstanceID>);
   ```
   oder
   ```php
   SNS_SetDefaultGroupVolume(<InstanceID>);
   ```
   aufgerufen wird.
- RINCON:  
Enthält die RINCON der Sonos Instanz. Dabei handelt es sich um einen weltweit eindeutigen Identifier in dem Format "RINCON_<MAC-Adresse>1400", wobei die "<MAC-Adresse>" nur die Ziffern und Buchstaben ohne Bindestriche enthält.  
Wenn dieser Wert nicht gepflegt wird, wird er automatisch ermittelt.
Update Frequenz (Sekunden)
- Status:  
Dieser Parameter enthält die Frequenz in Sekunden in der der Prozess _updateStatus ausgeführt werden soll.  
Default: 5
- NA Status:
Wenn eine Box nicht erreichbar ist (z.B. Stromlos) wird die Frequenz in der der Prozess _updateStatus ausgeführt wird auf diesen Wert gesetzt.  
Default 300
- Gruppen:  
Dieser Parameter enthält die Frequenz in Sekunden in der der Prozess _updateGrouping ausgeführt werden soll.  
Default: 120
- NA Gruppen:
Wenn eine Box nicht erreichbar ist (z.B. Stromlos) wird die Frequenz in der der Prozess _updateGrouping ausgeführt wird auf diesen Wert gesetzt.  
Default: 900
- Gruppieren in Sonos erzwingen:  
Bezüglich des Verhaltens was passieren soll, wenn ein Unterschied in der Gruppenzuordnung zwischen Sonos und IPS vorgefunden wird, gibt es 2 Alternativen:
  1. IPS übernimmt die Einstellungen aus Sonos :arrow_right: Haken nicht gesetzt  
Diese Alternative hat den Vorteil, dass die Einstellungen die man über die Sonos APP getroffen hat sich im IPS wiederspiegeln. 
  2. Die Einstellungen aus IPS werden in Sonos gesetz :arrow_right: Haken gesetzt  
Dies ist dafür gedacht, dass eine Box die stromlos geschaltet wurde automatisch wieder der richtigen Gruppe hinzugefügt wird.  
Hat aber leider den Nebeneffekt, dass eine Änderung in der Sonos APP wieder verworfen wird, wenn der _updateGrouping Prozess läuft.
- Mute Steuerung anzeigen:  
Diese Option legt eine Variable _"Stumm schalten"_ an und aktiviert dass diese auch über den Prozess _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch eine Knopf auf dem WebFront auf, über den man dies Steuern kann.
- Loudness Steuerung anzeigen:  
Diese Option legt eine Variable _"Loudness"_ an und aktiviert dass diese auch über den Prozess _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch eine Knopf auf dem WebFront auf, über den man dies Steuern kann.
- Bass Steuerung anzeigen:  
Diese Option legt eine Variable _"Bässe"_ an und aktiviert dass diese auch über den Prozess _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
- Höhen Steuerung anzeigen:  
Diese Option legt eine Variable _"Höhen"_ an und aktiviert dass diese auch über den Prozess _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
- Balance Steuerung anzeigen:  
Diese Option legt eine Variable _"Balance"_ an und aktiviert dass diese auch über den Prozess _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
- Sleeptimer Steuerung anzeigen:  
Diese Option legt eine Variable _"Sleeptimer"_ an und aktiviert dass diese auch über den Prozess _updateStatus mit dem aktuellen Wert gepflegt wird.
- Playmode Steuerung anzeigen:  
Diese Option legt die Variablen _"Wiedergabe Modus"_ und _"Überblendung"_ an und aktiviert dass diese auch über den Prozess _updateStatus mit dem aktuellen Wert gepflegt wird.
- Playlists Import:  
Diese Option kann die Werte "keine", "gespeicherte", "importierte" und "gespeicherte & importierte" annehmen.  
Wenn "keine" gewählt ist, passiert nichts.
Bei den anderen Werten eine Variable _"Playlist"_ an. Dadurch wird für jede Playlist die aus dem Sonos System importiert wurde ein Knopf auf dem WebFront angezeigt. Die Variable Playlist ist allerdings niemals gefüllt, da dies nicht der Logik in Sonos entspricht.  
Wenn eine Playliste gestartet wird, werden lediglich alle Titel der Liste der Queue hinzugefügt.  
Falls die Box einer Gruppe zugeordnet wird, wird diese Variable ausgeblendet.
- Detail Infomationen anzeigen:  
Diese Option legt die Variablen _"Details"_, _"Cover URL"_, _"Content Stream"_, _"Künstler"_, _"Titel"_, _"Album"_, _"Lied Dauer"_ und _"Position"_ an, die dann vom _updateStatus Prozess gefüllt werden.  
In der Variablen _"Details"_ wird eine HTMLBox erzeugt, die am WebFront auch zu sehen ist. Alle anderen Variablen werden versteckt. 
- Variablen Sortierung vorgeben:  
Wenn diese Option gesetzt ist, wird beim Speichern die vom Modul vorgeschlagene Reihenfolge der Vaiablen wieder hergestellt.
- TuneIn Favoriten einschließen:  
Wenn dieser Haken gesetzt ist, werden neben den mitgelieferten Radio sendern auch die TuneIn Favoriten (Meine Radiosender) aus dem Sonos System ausgelesen und gespeichert. Sie werden im WebFront als blaue Köpfe angelegt.  
Dies Gesamtzahl der verfügbaren Radiosender kann allerdings 32 nicht übersteigen. Da die ausgelieferten Sender zuerst ausgelesen werden müssen diese evtl begrenzt werden, damit die TuneIn Sender hinzugefügt werden.
- Radio Stationen Favoriten:  
Der hier ausgewählte Sender aus der ausgelieferten Sender Liste wird gestartet, wenn die Funktion
  ```php
  SNS_SetRadioFavorite(<InstranceID>);
  ```
  ausgeführt wird.
- Stationen im WebFront:  
Dies ist eine Liste der ausgelieferten Sender, die im WebFront als transparenter Knopf angezeigt werden sollen. Falls es sich um die "Favorite Station" handelt ist der Knopf gelb.  
Wenn alle angezeigt werden sollen ist &lt;all&gt; zu pflegen, wenn keiner angezeigt werden soll, ist dieser Parameter leer zu lassen.  
Diese Sender stehen zur Verfügung:
  - 1LIVE
  - 1LIVE DIGGI 
  - 917xfm
  - Antenne 1
  - Antenne Bayern
  - Antenne MV
  - Antenne Thueringen
  - A State Of Trance
  - Bayern 3
  - bigFM
  - Bremen Vier
  - Deutschlandfunk
  - Energy
  - FFH
  - FFN
  - Hitradio N1
  - HR3
  - HR-Info
  - KiRaKa
  - MDR1
  - MDR Jump
  - NDR2
  - N-JOY
  - OE3
  - Radio 91.2
  - Radio Duisburg
  - Radio Essen
  - Radio K.W.
  - Radio Lippe
  - Radio Top40
  - RevivalKult
  - RPR1
  - Sunshine Live
  - Sunshine Live - classics
  - Sunshine Live - trance
  - SWR1 BW
  - SWR1 RP
  - SWR3
  - WDR2
  - YouFM

### Testumgebung
- Update Radio Stations  
Dieser Knopf aktualisiert das Profil, in dem die im Webfront verfügbaren Radiosender hinterlegt sind, entsprechend der Parameter "Include TuneIn favorites", "Favorite Radio Station" und "Stations in WebFront".  
Achtung: Dieses Update ist dann für alle Sonos Instanzen gültig!
- Update Playlists  
Dieser Knopf aktualisiert das Profil, in dem die im Webfront verfügbaren Playlisten hinterlegt sind.  
Abhängig von der Einstellung für die Option "Import Playlists" werden gespeichert und in Sonos importiert und Playlisten angelegt.  
Achtung: Dieses Update ist dann für alle Sonos Instanzen gültig!
- RINCON von Sonos auslesen  
Bei betätigen dieses Knopfes wird die RINCON aus der Sonos Box ausgelesen und in der Instanzkonfiguration gespeichert.  
ACHTUNG: Diese Änderung sieht man nicht sofort, sondern erst nach dem nächsten Öffnen der Istanzkonfiguration!

## 4. Variablen
- Koordinator  
Bei dieser versteckten Variable ist hinterlegt, ob es sich bei der Box zu dem aktuellen Zeitpunkt um einen Koordinator handelt.  
Auf einem Koordinator können z.B. Funktionen wie Play, Pause, Next oder der Sleeptimer verwendet werden.  
Sollte es sich bei einer Box nicht um einen Koordinator handeln und der zuständige Koordinator in IPS verfügbar sein, werden diese Kommandos automatisch an den Gruppenkoordinator weitergeleitet.
- Gruppenmitglied  
Diese Variable enthält eine Liste von Sonos Instanz IDs, die diesem Gruppen Koordinator zugewiesen sind.  
Wenn die Option "Force grouping in Sonos" aktiviert ist, wird diese Variable lediglich durch die Funktion
  ```php
  SNS_SetGroup(<InstanceID>,<CoordinatorInstanceID>);
  ```
  gesetzt.  
  Falls die Option "Force grouping in Sonos" nicht aktiviert ist, wird diese Variable zusätzlich von dem Skript _updateGrouping aktualisiert.
- Gruppen Lautstärke  
Diese Variable wird automatisch eingeblendet, wenn eine Box als Gruppenmitglied zugeordnet ist.  
Ihr Wert wird anhand der Lautstärke der einzelnen Gruppenmitglieder (Durchnittswert) berechnet.  
Er wird durch die Fuktionen
  ```php
  SNS_ChangeGroupVolume(<InstanceID>,<Increment>);
  SNS_SetDefaultGroupVolume(<InstanceID>);
  SNS_SetGroupVolume(<InstanceID>,<Volume>);
  ``` 
  und den Prozess "_updateStatus" des Gruppenkoordinators aktualisiert.
- Mitglied der Gruppe  
Diese Variable wird erstellt, wenn die Option "Group Coordinator" __nicht__ aktiviert ist.  
Sie enthält die InstanzID des Gruppenkoordinators der die Instanz zugeordnet ist.
- jetzige Wiedergabe  
Diese Variable wird durch den Prozess _updateStatus aktuell gehalten.  
Sie enthält Informationen über das was momentan gespielt wird.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
Wenn nicht kann sich der Wert auf 2 Arten zusammensetzen:
  1. Wenn das Feld "StreamContent" gefüllt ist, wird dieser übernommen (z.B.: bei Radiosendern)
  2. Ansonsten wird sie mit "<Titel>|<Artist>" gefüllt
- Radio  
Diese Variable enthält den aktuell laufenden Radiosender, sofern er in der Liste im WebFront verfügbaren Radiosender auftaucht (siehe Konfiguration).  
Eine Aktualisierung erfolgt durch den Prozess _updateStatus.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
- Status  
Diese Variable enthält Informationen, in welchem Zustand sich die Sonos Instanz gerade befindet und wird von dem Prozess _updateStatus aktualisiert.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
Mögliche Werte sind:
  - 0 - Prev
  - 1 - Play
  - 2 - Pause
  - 3 - Stop
  - 4 - Next
  - 5 - Transition

  0 und 4 werden nur dazu genutzt um über das WebFront den Player zu steuern. 5 ist ein Wert der nur kurzfristig angenommen wird, wenn die Audioquelle gewechselt wird.
- Lautstärke  
Diese Variable enthält die Aktuelle Lautstärke der Instanz und wird von dem Prozess _updateStatus aktualisiert.
- Stumm schalten  
Diese Variable wird nur erstellt, wenn die Option _"Mute Steuerung anzeigen"_ aktiviert ist.
Sie enthält den aktuelle Zustand ob die Instanz gemuted ist und wird von dem Prozess _updateStatus aktualisiert.
- Loudness  
Diese Variable wird nur erstellt, wenn die Option _"Loudness Steuerung anzeigen"_ aktiviert ist.  
Sie enthält den aktuellen Zustand ob die Loudness aktiviert ist und wird von dem Prozess _updateStatus aktualisiert.
- Bässe  
Diese Variable wird nur erstellt, wenn die Option _"Bass Steuerung anzeigen"_ aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von dem Prozess _updateStatus aktualisiert.
- Höhen  
Diese Variable wird nur erstellt, wenn die Option _"Höhen Steuerung anzeigen"_ aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von dem Prozess _updateStatus aktualisiert.
- Balance  
Diese Variable wird nur erstellt, wenn die Option _"Balance Steuerung anzeigen"_ aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von dem Prozess _updateStatus aktualisiert.
- Sleeptimer  
Diese Variable wird nur erstellt, wenn die Option _"Sleeptimer Steuerung anzeigen"_ aktiviert ist.  
Sie enthält die aktuellen Wert des Sleeptimers der Instanz und wird von dem Prozess _updateStatus aktualisiert.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
- Widergabeliste  
Diese Variable hat normalerweise keinen Wert gepflegt. Sie dient nur dazu vom WebFront aus eine Playliste anstarten zu können.  
Lediglich direkt nach dem Drücken des Knopfes am WebFront wird die Variable für eine Sekunde auf den Gewählten Wert gesetzt. Dies soll dem Benutzer ein kurzes Feedback geben.
- Wiedergabe Modus  
Diese Variable wird nur erstellt, wenn die Option _"Playmode Steuerung anzeigen"_ aktiviert ist.  
In diese Variablen ist der aktuelle Wert des Play Mode abgelegt und wird von dem Prozess _updateStatus aktualisiert. Die möglichen Werte sind:
  - 0: "NORMAL"
  - 1: "REPEAT_ALL"
  - 2: "REPEAT_ONE"
  - 3: "SHUFFLE_NOREPEAT"
  - 4: "SHUFFLE"
  - 5: "SHUFFLE_REPEAT_ONE"
- Überblendung  
Diese Variable wird nur erstellt, wenn die Option _"Playmode Steuerung anzeigen"_ aktiviert ist.  
Sie enthält den aktuellen Wert der Crossfade Einstellungen und wird von dem Prozess _updateStatus aktualisiert.
- Cover URL  
Diese Variable wird nur erstellt, wenn die Option _"Detail Informationen anzeigen"_ aktiviert ist.  
Sie enthält die URL zu dem Cover das gerade in Sonos angezeigt wird. Dies gilt aber nur für Titel, nicht für Streams.  
Die Variable wird von dem Prozess _updateStatus aktualisiert.
- Content Stream  
Diese Variable wird nur erstellt, wenn die Option _"Detail Informationen anzeigen"_ aktiviert ist.  
Sie enthält den Conten Stram bei bei gestreamten Sender (z.B. aktuelle Informationen) und wird von dem Prozess _updateStatus aktualisiert.
- Künstler  
Diese Variable wird nur erstellt, wenn die Option _"Detail Informationen anzeigen"_ aktiviert ist.  
Sie enthält den Künster des aktuell abgespielten Titels und wird von dem Prozess _updateStatus aktualisiert.
- Album  
Diese Variable wird nur erstellt, wenn die Option _"Detail Informationen anzeigen"_ aktiviert ist.  
Sie enthält das Album des aktuell abgespielten Titels und wird von dem Prozess _updateStatus aktualisiert.
- Lied Dauer  
Diese Variable wird nur erstellt, wenn die Option _"Detail Informationen anzeigen"_ aktiviert ist.  
Sie enthält die länge des aktuell abgespielten Titels und wird von dem Prozess _updateStatus aktualisiert.
- Position  
Diese Variable wird nur erstellt, wenn die Option _"Detail Informationen anzeigen"_ aktiviert ist.  
Sie enthält die aktuelle Position in dem aktuell abgespielten Titels und wird von dem Prozess _updateStatus aktualisiert.
- Titel  
Diese Variable wird nur erstellt, wenn die Option _"Detail Informationen anzeigen"_ aktiviert ist.  
Sie enthält den Titel des aktuell abgespielten Titels und wird von dem Prozess _updateStatus aktualisiert.
- Details  
Diese Variable wird nur erstellt, wenn die Option _"Detail Informationen anzeigen"_ aktiviert ist.  
Dies ist eine HTMLBox die das Cover, den Titel den Künster, das Album und Positionsinfos anzeigt: 
 
![Details Song](img/details_song.png?raw=true "Details song")

Wenn gerade ein Sender gestreamt wird, sind nur ContenStram und Titel enthalten:  

![Details Radio](img/details_radio.png?raw=true "Details Radio")

- Song Position
Diese Variable wird nur erstellt, wenn die Option _"Song Position anzeigen"_ aktiviert ist.
Sie enthält die aktuelle Position des abgespielten Titels und wird von dem Prozess _updateStatus aktualisiert.

## 5. Hintergrund Prozesse
Wenn eine Sonos Instanz erstellt wird, werden 2 Prozesse angelegt und mit einem internen Timer, der im Konfigurationsformular eingestellt werden kann, verknüpft.
1. _updateStatus  
Dieser Prozess wird alle 5 Sekunden ausgeführt. Das Interval kann im Konfigurationsformular angepasst werden. 
Es aktualisiert die Variablen _Lautstärke_, _Stumm Schalten_, _Loudness_, _Bässe_, _Höhen_, _Balance_ und _Sleeptimer_ basierend auf den Werten in Sonos, sofern die relevanten Konfigurationsschalter dies erfordern.  
Weiterhin werden die Parameter _Status_, _Radio_ und _jetzige Wiedergabe_ aktualisiert.  
Bei Gruppenkoordinatoren wird die Gruppenlautstärke (_Gruppen Lautstärke_) berechnet.
2. _updateGrouping  
Dieser Prozess wird wie unter _Update Frequenz (Sekunden)_ mit dem Einstellungswert unter _Gruppen_ ausgeführt. Hierbei handelt es sich um einen Konfigurationsparameter.  
Die Gruppeneinstellungen werden entweder in Sonos oder in IP-Symcon aktualisiert.

## 6. Funktionen
```php
SNS_ChangeGroupVolume(integer $InstanceID, integer $increment)
```
Ändert die Lautstärke jedes Mitglieds einer Gruppe um den mitgelieferten Wert in $increment.  
Kann positiv oder negativ sein.  
Falls die Lautstärke 100 übersteigen oder 0 unterschreiten würde, wird die Lautstärke auf diese Werte gesetzt.

---
```php
SNS_ChangeVolume(integer $InstanceID, integer $increment)
```
Ändert die Lautstärke einer Sonos Instanz um den mitgelieferten Wert in $increment.  
Kann positiv oder negativ sein.  
Falls die Lautstärke 100 übersteigen oder 0 unterschreiten würde, wird die Lautstärke auf diese Werte gesetzt.

---
```php
SNS_DeleteSleepTimer(integer $InstanceID)
```
Bricht den Sleeptimer ab.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.

---
```php
SNS_Next(integer $InstanceID)
```
Springt zum nächsten Titel.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.

---
```php
SNS_Pause(integer $InstanceID)
```
Pausiert die Wiedergabe.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.

---
```php
SNS_Play(integer $InstanceID)
```
Setzt die Wiedergabe fort.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.

---
```php
SNS_PlayFiles(integer $InstanceID, array $files, string $volumeChange)
```
1. Falls gerade etwas wiedergegeben wird, wird die Wiedergabe pausiert
2. Die Lautstärke wird entsprechend $volumeChange angepasst  
   - "0" würde die Lautstärke nicht ändern  
   - "17" würde die Lautstärke auf 17 setzen  
   - "+8" würde die Lautstärke um 8 anheben  
   - "-8" würde die Lautstärke um 8 absenken
3. Alle Dateien, die in dem Array $filesangegeben wurden werden abgespielt.  
Entweder von einem Samba Share (CIFS) (z.B. "//server.local.domain/share/file.mp3") oder von einem HTTP Server (z.B.: "http://ipsymcon.fritz.box:3777/user/ansage/hallo.mp3")
4. Die Ausgangslautstärke wird wieder hergestellt
5. Die Audioquelle wird wieder hergestellt
6. Falls eine Wiedergabe aktiv war, wird sie wieder gestartet

Falls die Instanz einer Gruppe zugeordnet ist, wird sie für die Wiedergabe der Dateien aus der Gruppe genommen und danach wieder hinzugefügt.  
Mehrere Dateien anzuspielen könnte so aussehen:
```php
SNS_PlayFiles(17265, Array( "//ipsymcon.fritz.box/sonos/bla.mp3",
                            "http://www.sounds.com/blubb.mp3"), 0);
```

---

```php
SNS_PlayFilesGrouping(integer $InstanceID, array $instances, array $files, $volume)
```
Diese Funktion ruft die Funktion SNS_PlayFiles auf. Dementsprechend ist das array $files göleich aufgebaut.  
Vorher werden die in $instances mitgegebenen Instanzen zu der gruppe von $InstanceID hinzugefügt.  
Das array $instances beinhaltet pro hinzuzufügender instanz einen Eintrag mit dem Key "&lt;instance ID&gt;" der hinzuzufügenden instanz und einem Array mit settings. Diese Array kennt derzeit lediglich einen Eintrag mit dem Key "volume" mit dem Volume Wert entsprechend dem $volumeChange aus der Funktion SNS_PlayFiles.

Beispiel:
```php
SNS_PlayFilesGrouping(46954 , array( 11774 => array( "volume" => 10),
                                     27728 => array( "volume" => "+10"),
                                     59962 => array( "volume" => 30) ), array( IVNTTS_saveMP3(12748, "Dieser Text wird angesagt")), 28 );
```
Die Instanzen 11774, 27728 und 59962 werden der Gruppe mit dem Koordinator 46954 hinzugefügt.  
Die Instanz 11774 wird auf Lautstärke 10 gesetzt.  
Bei der Instanz 27728 wird die Lautstärke um 10 Punkte angehoben.  
Die Instanz 59962 wird auf Lautstärke 30 gesetzt.  
Die Instanz 46954 wird Gruppen Koordinator für die Ansage(n) und wird auf Lautstärke 28 gesetzt.  
Der Text "Dieser Text wird angesagt" wird vom dem SymconIvona Modul (Instanz 12748) in eine MP3 umgewandelt, welche dann abgespielt wird.

---
```php
SNS_Previous(integer $InstanceID)
```
Startet den vorhergehenden Titel in der Liste.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.

---
```php
SNS_RampToVolume(integer $InstanceID, $rampType, $volume);
```
Ruft die Funktion RampToVolume in Sonos auf.  
Der Parameter $rampType kann als integer oder als string übergeben werden.  
- 1 entspricht SLEEP_TIMER_RAMP_TYPE
- 2 entspricht ALARM_RAMP_TYPE
- 3 entspricht AUTOPLAY_RAMP_TYPE

---
```php
SNS_SetAnalogInput(integer $InstanceID, integer $InputInstanceID)
```
Selektiert den Analogen Input einer Instanz als Audioquelle.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audioquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.

---
```php
SNS_SetBalance(integer $InstanceID, integer $balance)
```
Passt die Balance Einstellungen im Equalizer der Sonos Instanz an. Nur Sinnvoll bei Stereopaaren oder AMPS.  
Mögliche Werte liegen zwischen -100 (ganz links) und 100 (ganz rechts).

---
```php
SNS_SetBass(integer $InstanceID, integer $bass)
```
Passt die Bass Einstellungen im Equalizer der Sonos Instanz an.  
Mögliche Werte liegen zwischen -10 und 10.

---
```php
SNS_SetCrossfade(integer $InstanceID, boolean $crossfade)
```
Schaltet den Crossfade Modus für eine Instanz ein oder aus.  
Falls die Instanz Teil einer Gruppe ist, wird das Kommando automatisch an den Gruppenkoordinator weitergeleitet.  
0,1, true und false sind gültige Werte für $loudness.

---
```php
SNS_SetDefaultGroupVolume(integer $InstanceID)
```
Führt die Funktion SNS_SetDefaultVolume( ) für jeden Mitglied einer Gruppe aus.

---
```php
SNS_SetDefaultVolume(integer $InstanceID)
```
Ändert die Lautstärke einer Instanz auf die Default Lautstärke.

---
```php
SNS_SetGroup(integer $InstanceID, integer $groupCoordinator)
```
Fügt die Instanz zu einer Gruppe hinzu oder entfernt es aus einer Gruppe.  
Wenn die InstanzID eines Gruppenkoordinators mitgegeben wird, wird die instanz dieser Gruppe hinzugefügt.  
Wenn 0 mitgegeben wird, wird die Instanz aus allen Gruppen entfernt.

---
```php
SNS_SetGroupVolume(integer $InstanceID, integer $volume)
```
Führt die Funktion SNS_ChangeGroupVolume($volume - "aktuelle Lautstärke" ) aus.

---
```php
SNS_SetLoudness(integer $InstanceID, boolean $loudness)
```
Setzt das Loundess Flag an einer Instanz.  
0,1, true und false sind gültige Werte für $loudness.

---
```php
SNS_SetMute(integer $InstanceID, boolean $mute)
```
Mutet or unmutet eine Instanz.
0,1, true und false sind gültige Werte für $mute.

---
```php
SNS_SetPlaylist(integer $InstanceID, string $name)
```
Entfernt alle Titel aus einer Queue und fügt alle Titel einer Playliste hinzu.  
Der Name der Playliste muss in Sonos bekannt sein.  
Es wird zunächst nach dem Namen in den gespeicherten Playlisten gesucht. Wird er dort nciht gefunden, wird ebenfalls in den Importierten Playlisten gesucht. Dabei wird ein Unterstrich ("_") such ein Leerzeichen (" ") ersetzt und die Endungen ".m3u" und ".M3U" werden entfernt. Somit kann z.B. die Playliste mit dem Name "3_Doors_Down.m3u" mit dem Befehl SNS_SetPlaylist(12345,"3 Doors Down"); gestartet werden.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audioquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.

---
```php
SNS_SetPlaymode(integer $InstanceID, integer $playMode)
```
Setzt den Play Mode einer Sonos Instanz.  
Falls die Instanz Mitglied einer Gruppe ist, wird das Kommando automatisch an den Gruppenkoordinator weitergeleitet.  
Mögliche Werte für den Play Mode sind:
- 0: "NORMAL"
- 1: "REPEAT_ALL"
- 2: "REPEAT_ONE"
- 3: "SHUFFLE_NOREPEAT"
- 4: "SHUFFLE"
- 5: "SHUFFLE_REPEAT_ONE"


---
```php
SNS_SetRadioFavorite(integer $InstanceID)
```
Startet die Wiedergabe der "Favorite Radio Station".

---
```php
SNS_SetRadio(integer $InstanceID, string $radio)
```
Setzt die Audioquelle auf die URL des in $radio mitgegebenen Radiosenders.  
Zunächst wird gesucht, ob der Sender in den ausgelieferten Sendern gefunden wird. Wenn er dort nicht gefunden wird, wird in den TuneIn Favoriten (Meine Radiosender) gesucht.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.

---
```php
SNS_SetSleepTimer(integer $InstanceID, integer $minutes)
```
Setzt den Sleeptimer auf die angegebene Anzahl an Minuten.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.

---

```php
SNS_SetSpdifInput(integer $InstanceID, integer $InputInstanceID)
```
Selektiert den SPDIF Input einer Instanz als Audioquelle.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audioquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.

---
```php
SNS_SetTreble(integer $InstanceID, integer $treble)
```
Passt die Treble Einstellungen im Equalizer der Sonos Instanz an.
Mögliche Werte liegen zwischen -10 und 10.

---
```php
SNS_SetVolume(integer $InstanceID, integer $volume)
```
Passt die Lautstärke einer Instanz an.
Mögliche Werte liegen zwischen 0 and 100.

---
```php
SNS_Stop(integer $InstanceID)
```
Hält die Wiedergabe an.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.

---
```php
SNS_UpdatePlaylists(integer $InstanceID)
```
Liest alle Playlisten aus Sonos aus und legt diese in einem Variablenprofil in IPS ab.  
Hierdurch wird es möglich eine Playliste im WebFront anzustarten.  
Dies hat Auswirkung auf alle Sonos Instanzen.  
Die Maximale anzahl an Einträgen in einem Variablenprofil beträgt 32. Daher werden auch nur die ersten 32 Playlisten gespeichert.  
Diese Funktion wird auch durch den Knopf "Update Playlists" in der Instanzkonfiguration ausgeführt.

---
```php
SNS_UpdateRadioStations(integer $InstanceID)
```
Aktualisiert das Variablenprofil für die Radiosender in IPS.  
Hierdurch ergeben sich z.B. die Knöpfe im WebFront mit denen man einen Radiosender anstarten kann.  
- Zunächst werden alle ausgelieferten Radiosender, die sich aus der Konfiguration "Stations in WebFront" ergeben, der Liste hinzugefügt. Im Webfront derden diese Sender in "transparent", der Favorit in "gelb" angezeigt.
- Falls die Konfigurationsoption "Include TuneIn favorites" gewählt ist, werden alle TuneIn Favoriten (Meine Radiosender) ebenfalls der Liste hinzugefügt. Im Webfront derden diese Sender in "blau" angezeigt.
- Wenn zu irgendeinem Zeitpunkt 32 Sender erreicht sind, ist die Liste voll. Dies liegt an der Beschränkung von IPS, dass Variabalenprofile maximal 32 Einträge haben dürfen.

Hinweis: Wenn nur Sender aus Sonos enthalten sein sollen, ist der Konfigurationsparameter "Stations in WebFront" __leer__ zu speichern!

---
```php
SNS_UpdateRINCON(integer $InstanceID)
```
Liest die RINCON einer Instanz aus und schreibt diese in die Instanzkonfiguration.

## 7. FAQ
### 7.1. Wie viele Variablen werden für das Modul benötigt?  
Jede Instanz (also eingebundene Sonos Box) benötigt zwischen 13 und 30 Variablen.  
- Instanz selber
- Gruppen Mitglied
- Koordinator
- Gruppen Lautstärke
- Mitglied der Gruppe
- jetzige Wiedergabe
- Radio
- Status
- Lautstärke
- Bässe (falls _"Bass Steuerung anzeigen"_ aktiviert ist)
- Höhen (falls _"Höhen Steuerung anzeigen"_ aktiviert ist)
- Stumm schalten (falls _"Mute Steuerung anzeigen"_ aktiviert ist)
- Loudness (falls _"Loudness Steuerung anzeigen"_ aktiviert ist)
- Balance (falls _"Balance Steuerung anzeigen"_ aktiviert ist)
- Sleeptimer (falls _"Sleeptimer Steuerung anzeigen"_ aktiviert ist)
- Wiedergabeliste (falls _"Playlist Import"_ aktiviert ist)
- Wiedergabe Modus (falls _"Playmode Steuerung anzeigen"_ aktiviert ist)
- Überblendung (falls "Playmode Control" aktiviert ist)
- Cover URL (falls _"Detail Informationen anzeigen"_ aktiviert ist)
- Content Stream (falls _"Detail Informationen anzeigen"_ aktiviert ist)
- Künstler (falls _"Detail Informationen anzeigen"_ aktiviert ist)
- Album (falls _"Detail Informationen anzeigen"_ aktiviert ist)
- Lied Dauer (falls _"Detail Informationen anzeigen"_ aktiviert ist)
- Position (falls _"Detail Informationen anzeigen"_ aktiviert ist)
- Titel (falls _"Detail Informationen anzeigen"_ aktiviert ist)
- Details (falls _"Detail Informationen anzeigen"_ aktiviert ist)
- Song Fortschritt (falls _"Song Position anzeigen"_ aktiviert ist)