Sonos PHP Module for IP-Symcon
===
IP-Symcon PHP module for accessing Sonos audio systems

**Content**

1. [Funktionsumfang](#1-funktionsumfang)
2. [Anforderungen](#2-anforderungen)
3. [Installation & Konfiguration](#3-installation--konfiguration)
4. [Variablen](#4-variablen)
5. [Hintergrund Skripte](#5-hintergrund-skripte)
6. [Funktionen](#6-funktionen)

## 1. Funktionsumfang
Dieses Modul is dazu gedacht um allgemeine Funktionalitäten in Sonso aus IP-Symcon heraus auszulösen.  
Die folgenden Funktionalitäten sind implementiert:
- verschiedene Quellen festlegen
  - Radiosender
    - ausgelieferte Sender
    - importierte Sonos TuneIn Favoriten
  - Playlisten
  - Analogen Eingang
  - SPDIF Eingang
- Gruppenhandling
- Play/Pause/Stop/Previous/Next
- Lautstärke anpassen (inkl. default volume)
- Mute, Loudness, Bass, Treble
- Balance
- Sleeptimer
- Ansagen  
Audiodateien von einem Samba-Share (z.B. Synology) oder HTTP Server abspielen und danach den vorherigen zustand wieder herstellen

## 2. Anforderungen

 - IPS 4.x
 - Sonos audio system

## 3. Installation & Konfiguration

### Installation in IPS 4.x
Füge im "Module Control" (Kern Instanzen->Modules) die URL "git://github.com/tkugelberg/SymconSonos.git" hinzu.  
Danach ist es möglich eine neue Sonos Instanz zu erstellen:
![Instanz erstellen](docu/create_instance.png?raw=true "Instanz erstellen")
### Konfiguration
![Instanz konfigurieren](docu/instance_config.png?raw=true "Instanz konfigurieren")
-  IP-Address/Host:  
Adresse unter der die Sonos Instanz erreichbar ist.Hierbei kann es sich um eine IP oder einen Hostnamen handeln.  
Wenn die Einstellungen gespeichert werden wird überprüft ob die Adresse erreichbar ist.
- Maximal ping timeout:  
Bevor ein Kommando an eine Sonos Instanz gecshickt wird, wird per Sys_Ping() überprüft ob diese erreichbar ist.  
Dieser Parameter steuert nach wie vielen Millisekunden angenommen wird, dass die Box nicht verfügbar ist.  
Wenn der Parameter auf 0 gesetzt wird, wird die Erreichbarkeit nicht überprüft.
-  Default Volume:  
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
- Update Grouping Frq:  
Dieser Parameter enthält die Frequenz in sekunden in der das Script _updateGrouping ausgeführt werden soll.  
Default: 120
- Force Grouping in Sonos:  
Bezüglich des Verhaltens was passieren soll, wenn ein Unterschied in der Gruppenzuordnung zwischen Sonos und IPS vorgefunden wird, gibt es 2 Alternativen:
  1. IPS übernimmt die Einstellungen aus Sonos --> Haken nicht gesetzt  
Diese Alternative hat den Vorteil, dass die Einstellungen die man über die Sonos APP getroffen hat sich im IPS wiederspiegeln. 
  2. Die Einstellungen aus IPS werden in Sonos gesetz --> Haken gesetzt  
Dies ist dafür gedacht, dass eine Box die stromlos geschaltet wurde automatisch wieder der richtigen Gruppe hinzugefügt wird.  
Hat aber leider den Nebeneffekt, dass eine Änderung in der Sonos APP wieder verworfen wird, wenn das _updateGrouping Script läuft.
- Enable Mute Control:  
Diese Option legt eine Variable "Mute" an und aktiviert dass diese auch über das Skript _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch eine Konpf auf dem WebFront auf, über den man dies Steuern kann.
- Enable Loudness Control:  
Diese Option legt eine Variable "Loudness" an und aktiviert dass diese auch über das Skript _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch eine Konpf auf dem WebFront auf, über den man dies Steuern kann.
- Enable Bass Control:  
Diese Option legt eine Variable "Bass" an und aktiviert dass diese auch über das Skript _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
- Enable Treble Control:  
Diese Option legt eine Variable "Treble" an und aktiviert dass diese auch über das Skript _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
- Enable Balance Control:  
Diese Option legt eine Variable "Balance" an und aktiviert dass diese auch über das Skript _updateStatus mit dem aktuellen Wert gepflegt wird. Weiterhin taucht dann auch einen Slider auf dem WebFront auf, über den man dies Steuern kann.
- Enable Sleeptimer Contorl:  
Diese Option legt eine Variable "Sleeptimer" an und aktiviert dass diese auch über das Skript _updateStatus mit dem aktuellen Wert gepflegt wird.
- Enable Playlist Control:  
Diese Option legt eine Variable "Playlist" an. Dadurch wird für jede Playlist die aus dem Sonos System importiert wurde ein Knopf auf dem WebFront angezeigt. Die Variable Playlist ist allerdings niemals gefüllt, da dies nicht der Logik in Sonos entspricht.  
Wenn eine Playliste gestartet wird, werden lediglich alle Titel der Liste der Queue hinzugefügt.  
Falls die Box einer Gruppe zugeordnet wird, wird diese Variable ausgeblendet.
- Include TuneIn favorites:  
Wenn dieser Haken gesetzt ist, werden neben den mitgelieferten Radio sendern auch die TuneIn Favoriten (Meine Radiosender) aus dem Sonos System ausgelesen und gespeichert. Sie werden im WebFront als blaue Köpfe angelegt.  
Dies Gesamtzahl der verfügbaren Radiosender kann allerdings 32 nicht übersteigen. Da die ausgelieferten Sender zuerst ausgelesen werden müssen diese evtl begrenzt werden, damit die TuneIn Sender hinzugefügt werden.
- Favorite Radio Station:  
Der hier ausgewählte Sender aus der ausgelieferten Sender Liste wird gestartet, wenn die Funktion
  ```php
  SNS_SetRadioFavorite(<InstranceID>);
  ```
  ausgeführt wird.
- Stations in WebFront:  
Dies ist eine Liste der Ausgelieferten Sender, die im WebFront als transparenter Knopf angezeigt werden sollen. Falls es sich um die "Favorite Station" handelt ist der Knopf gelb.  
Wenn alle angezeigt werden sollen ist <all> zu pflegen, wenn keiner angezeigt werden soll, ist dieser Parameter leer zu lassen.  
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
Achtung: Dieses Update ist dann für alle Sonos Instanzen gültig!
- Read RINCON from Sonos  
Bei betätigen dieses Knopfes wird die RINCON aus der Sonos Box ausgelesen und in der Instazkonfiguration gespeichert.  
ACHTUNG: Diese Änderung sieht man nicht sofort, sondern erst nach dem nächsten Öffnen der Istanzkonfiguration!

## 4. Variablen
- Coordinator  
Bei dieser versteckten Variable ist hinterlegt, ob es ich bei der Box zu dem aktuellen Zeitpunkt um einen Koordinator handelt.  
Auf einem Koordinator können z.B. Funktionen wie Play, Pause, Next oder der Sleeptimer verwendet werden.  
Sollte es sich bei einer Box nixht um einne Koordinator handel und der zuständige Koordinator in IPS verfügbar sein, werden diese Kommandos automatisch an den Gruppenkoordinator weitergeleitet.
- GroupMembers  
Diese Variable enthält eine Liste von Sonos Instanz IDs, die diesem Gruppen Koordinator zugewiesen sind.  
Wenn die Option "Force grouping in Sonos" aktiviert ist, wird diese Variable lediglich durch die Funktion
  ```php
  SNS_SetGroup(<InstanceID>,<CoordinatorInstanceID>);
  ```
  gesetzt.  
  Falls die Option "Force grouping in Sonos" nicht aktiviert ist, wird diese Variable zusätzlich von dem Skript _updateGrouping aktualisiert.
- GroupVolume  
Diese Variable wird automatisch eingeblendet, wenn eine Box als Gruppenmenber zugeordnet ist.  
Ihr Wert wird anhand der Lautstärke der einzelnen Gruppenmitglieder (Durchnittswert) berechnet.  
Er wird durch die Fuktionen
  ```php
  SNS_ChangeGroupVolume(<InstanceID>,<Increment>);
  SNS_SetDefaultGroupVolume(<InstanceID>);
  SNS_SetGroupVolume(<InstanceID>,<Volume>);
  ``` 
  und das Skript "_updateStatus" des Gruppenkoordinators aktualisiert.
- MemberOfGroup  
Diese Variable wird erstellt, wenn die Option "Group Coordinator" __nicht__ aktiviert ist.  
Sie enthält die InstanzID des Gruppenkoordinators der die Instanz zugeordnet ist.
- nowPlaying  
Diese Variable wird durch das Skript _updateStatus aktuell gehalten.  
Sie enthält Informationen über das was momentan gespielt wird.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
Wenn nicht kann sich der Wert auf 2 Arten zusammensetzen:
  1. Wenn das Feld "StreamContent" gefüllt ist, wird dieser übernommen (z.B.: bei Radiosendern)
  2. Ansonsten wird sie mit "<Titel>|<Artist>" gefüllt
- Radio  
Diese Variable enthält den aktuell laufenden Radiosender, sofern er in der Liste im WebFront verfügbaren Radiosender auftaucht (siehe Konfiguration).  
Eine Aktualisierung erfolgt durch das Skript _updateStatus.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
- Status  
Diese Variable enthält Informationen, in welchem Zustand sich die Sonos Instanz gerade befindet und wird von dem Script _updateStatus aktualisiert.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
Mögliche Werte sind:
  - 0 - Prev
  - 1 - Play
  - 2 - Pause
  - 3 - Stop
  - 4 - Next
  - 5 - Transition

  0 und 4 werden nur dazu genutzt um über das WebFront den Player zu steuern. 5 ist ein Wert der nur kurzfristig angenommen wird, wenn die Audioquelle gewechselt wird.
- Volume  
Diese Variable enthält die Aktuelle Lautstärke der Instanz und wird von dem Script _updateStatus aktualisiert.
- Mute  
Diese Variable wird nur erstellt, wenn die Option "Enable Mute Control" aktiviert ist.
Sie enthält den aktuelle Zustand ob die Instanz genuted ist und wird von dem Script _updateStatus aktualisiert.
- Loudness  
Diese Variable wird nur erstellt, wenn die Option "Enable Mute Control" aktiviert ist.  
Sie enthält den aktuellen Zustand ob die Instanz gemuted ist und wird von dem Script _updateStatus aktualisiert.
- Bass  
Diese Variable wird nur erstellt, wenn die Option "Enable Bass Control" aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von dem Script _updateStatus aktualisiert.
- Treble  
Diese Variable wird nur erstellt, wenn die Option "Enable Treble Control" aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von dem Script _updateStatus aktualisiert.
- Balance  
Diese Variable wird nur erstellt, wenn die Option "Enable Balance Control" aktiviert ist.  
Sie enthält die aktuellen Equalizer Einstellungen der Instanz und wird von dem Script _updateStatus aktualisiert.
- Sleeptimer  
Diese Variable wird nur erstellt, wenn die Option "Enable Sleeptimer Control" aktiviert ist.  
Sie enthält die aktuellen Wert des Sleeptimers der Instanz und wird von dem Script _updateStatus aktualisiert.  
Falls die Instanz Mitglied einer Gruppe ist, wird die Variable versteckt (hidden) und mit dem Wert aus dem Gruppenkoordinator befüllt.
- Playlist  
Diese Variable hat normalerweise keinen Wert gepflegt. Sie dient nur dazu vom WebFront aus eine Playliste anstarten zu können.

## 5. Hintergrund Skripte
Wenn eine Sonos Instanz erstellt wird, werden 2 Skripte angelegt und mit einem Timer gestartet.
1. _updateStatus  
Dieses Skript wird alle 5 Sekunden ausgeführt.  
Es aktualisiert die Variablen Voume, Mute, Loudness, Bass, Treble, Balance and Sleeptimer baserend auf den Werten in Sonos, sofern die relevanten Konfigurationsschalter dies erfordern.  
Weiterhin werden die Parameter Status, Radio and NowPlaying aktualisiert.  
Bei Gruppenkoordinatoren wird die Gruppenlautstärke (GroupVolume) berechnet.
2. _updateGrouping  
Dieses Skript wird alle "Update Grouping Frq" Sekunden ausgeführt. Hierbei handelt es sich um einen Konfigurationsparameter.  
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
SNS_Previous(integer $InstanceID)
```
Startet den vorhergehenden Titel in der Liste.  
Sollte das Kommando auf einem Gruppenmember ausgeführt werden, wird es automatisch an den zuständigen Koordinator weitergeleitet und gilt somit für die ganze Gruppe.

---
```php
SNS_SetAnalogInput(integer $InstanceID, integer $InputInstanceID)
```
Selektiert den Analogen Input einer Instanz als Audioquelle.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.

---
```php
SNS_SetBalance(integer $InstanceID, integer $balance)
```
Passt die Balance Einstellungen im Equalizer der Sonos Instanz an. Nur Sinnvoll bei Setreopaaren oder AMPS.  
Mögliche Werte liegen zwischen -100 (ganz links) und 100 (gnaz rechts).

---
```php
SNS_SetBass(integer $InstanceID, integer $bass)
```
Passt die Bass Einstellungen im Equalizer der Sonos Instanz an.  
Mögliche Werte liegen zwischen -10 und 10.

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
Der name der Playliste muss in Sonos bekannt sein.  
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
Sollte diese Funktion auf einem Gruppenkoordinator ausgeführt werden gilt die neue Audioquelle für die ganze Gruppe.

---
```php
SNS_SetRadioFavorite(integer $InstanceID)
```
Startet die Wiedergabe der "Favorite Radio Station".

---
```php
SNS_SetRadio(integer $InstanceID, string $radio)
```
Startet die Wiedergabe des in $radio mitgegebenen Radiosenders.  
Zunächst wird gesucht, ob der Sender in den ausgelieferten Sendern gefunden wird. Wenn er dort nicht gefunden wird, wird in den TuneIn Favoriten (Meine Radiosneder) gesucht.  
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
Sollte die Instanz sich gerade in einer Gruppe befinden, wird sie automatisch aus der Gruppe genommen und danach die neue Audiquelle gesetzt.  
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

