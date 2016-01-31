Backlog für das SymconSonos PHP Modul
===============
Dies ist das priorisierte Backlog für die Weiterentwicklung des SymconSonos Moduls für IPS.  
Je höher etwas in der Liste auftaucht, desto eher wird es gemacht.
Wenn es noch wünsche oder Anregungen gibt, können diese gerne im IPS-Forum diskutiert werden: https://www.symcon.de/forum/threads/27500-Sonos-Modul

1. Gruppenansatz überarbeiten
   - Gruppen wie in Sonos dynamisieren
   - überführen der Property GroupCoordinator in eine Variable
   - überdenken des _updateGouping Ansatzes, wird das auf jeder Instanz benötigt?
   - Property für die Frequenz des Update angeben (alle x Sekunden)
1. Robustheit des _updateGrouping Scriptes
   - https://www.symcon.de/forum/threads/27500-Sonos-Modul?p=280494#post280494
   - was passiert, wenn die die GroupCoordinatorID nicht gefunden wird?
1. RINCON-Ermittlung überarbeiten
   - https://www.symcon.de/forum/threads/27500-Sonos-Modul?p=280336#post280336
   - Wird an zu vielen Stellen benötigt.  
   - RINCON nicht im _updateGrouping Script füllen, eher in ApplyChanges()
1. detaillierte "now Playing" Informationen
   - https://www.symcon.de/forum/threads/27500-Sonos-Modul?p=270974#post270974
   - Neue Variablen für
     - Album
     - Artist
     - Title
     - Cover
     - ...
1. Play mode
   - https://www.symcon.de/forum/threads/27500-Sonos-Modul?p=270974#post270974
   - Shuffle, repeat, ...
1. group Mute
1. "Volume Factor" einführen 
   - https://www.symcon.de/forum/threads/27500-Sonos-Modul?p=277503#post277503
   - Das Volume wird hiermit multipliziert/dividiert.
   - bei Factor 3 entspricht 33% in Sonos 99% in IPS
   - Begrenzung der Maximallautstärke
   - einfachere Schrittweite im WebFront
1. Ist eine Splitter/Konfigurator Instanz notwendig?!
1. Wecker Funktionen?!
   - https://www.symcon.de/forum/threads/27500-Sonos-Modul?p=279946#post279946
