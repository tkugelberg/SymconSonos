# Changelog 
### Version 1.1
- Einfürung der Versionierung ;-)
- Ermittlung der RINCON ins ApplyChanges() verlagert
  -  Wird jetzt automatisch gefüllt, wenn das Feld in der Konfigutration leer ist
  -  Manuelles Update jetzt möglich mit der Funktion 
     ```php
     SNS_UpdateRINCON(<InstanceID>);
     ```
- Fehlerhandling in _updateGrouping
  - exception wenn die RINCON des Gruppen Koordinators nicht bekannt ist
- Property "Update Grouping Frequency" eingeführt
   - häufigkeit der Ausführung des  _updateGrouping Skriptes
- Defauling von "Stations in WebFront" auf leer
- Default für "Include TuneIn favorites" auf true
- Gruppenkonzept komplett überarbeitet
  - Coordinator kann jetzt nicht mehr in der Instanzkonfiguration gesetzt werden, sondern wird dynamisch ermittelt
  - Die Annahme, dass eine Gruppenrincon immer die RINCON des Koordinators enthält ausgebaut
  - Profile Association der verfügbaren Gruppen wird dynamisch angepasst (alle Koordinatoren können als Gruppe gewählt werden)
  - DeleteSleepTimer, Next, Pause, Play, Previous, SetSleepTimer und Stop werden jetzti nur noch auf Koordinatoren ausgeführt
    - Wenn derf Koordinator ermittelt werden kann (sollte der Regelfall sein) wird das Kommando automatisch an den Gruppenkoordinator weitergeleitet
    - wenn der Gruppenkoordinator nicht ermittelt werden kann (eigentlich nur dann möglich, wenn nicht alle Sonos Boxen in IPS bekannt sind), wird ein Fehler geworfen.
