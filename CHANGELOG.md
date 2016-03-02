# Changelog 
### Version 1.3.4
- Fix bei Detailed Status wenn ANAOLG oder SPDIF ausgewählt --> kein HTML erzeugen

### Version 1.3.3
- Fix bei PlayFiles wenn ANAOLG oder SPDIF ausgewählt --> "NOT_IMPLEMETED", schon wieder!

### Version 1.3.2
- Fix wenn TrackDuration keine Zeit enhält, sondern "NOT_IMPLEMETED"
  - Tritt auf z.B., wenn als Input ANAOLG oder SPDIF ausgewählt ist


### Version 1.31
- Fix für "devision by zero" wenn SPDIF (und wohl auch Analog) als input gewählt ist.

### Version 1.3
- Das Profil für Gruppen wird nun bei jedem speichern der Konfiguration einer Instanz neu erzeugt.
  - dies hilft beim aufräumen von fragmenten bereits gelöschter Instanzen
  - Potentielle Fehler/Hickups werden bereiningt.
- PlayMode hinzugefügt, umfasst auch Crossfade
  - 0: "NORMAL"
  - 1: "REPEAT_ALL"
  - 2: "REPEAT_ONE"
  - 3: "SHUFFLE_NOREPEAT"
  - 4: "SHUFFLE"
  - 5: "SHUFFLE_REPEAT_ONE"
- Die Option "Enable detailed info" hinzugefügt
  - Beinhaltet die Variablen
    - Details
    - CoverURL
    - ContentStream
    - Artist
    - Title
    - Album
    - TrackDuration
    - Position
  - automatisches Füllen der Variablen
  - ersatellen einer HTML-Box in der Details Vaiablen

- Die Option "Force Variable order"
  - Diese Option bewirkt, dass die Sortiertreihenfolge auf jeden Fall so eingerichtet wird, wie von dem Modul vorgesehen.
  - Weiterhin wurde die vorgeschlagene Reihenfolge angepasst, um die Detaillierten Infos besser anzeigen/einsortieren zu können
  - wenn aktiviert, wird bei jedem ApplyChanges (also auch beim update und starten von IPS) sichergestellt, dass die Sortierreihenfolge stimmt.

### Version 1.2
- Beheben eines Fehlers, durch den keine neuen Instanzen angelegt werden konnten.

### Version 1.1
- Einfürung der Versionierung ;-)
- Ermittlung der RINCON ins ApplyChanges() verlagert
  -  Wird jetzt automatisch gefüllt, wenn das Feld in der Konfigutration leer ist
  -  Manuelles Update jetzt möglich mit der Funktion "SNS_UpdateRINCON(<InstanceID>);"
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
  - DeleteSleepTimer, Next, Pause, Play, Previous, SetSleepTimer und Stop werden jetzt nur noch auf Koordinatoren ausgeführt
    - Wenn derf Koordinator ermittelt werden kann (sollte der Regelfall sein) wird das Kommando automatisch an den Gruppenkoordinator weitergeleitet
    - wenn der Gruppenkoordinator nicht ermittelt werden kann (eigentlich nur dann möglich, wenn nicht alle Sonos Boxen in IPS bekannt sind), wird ein Fehler geworfen.
- Es werden jetzt exceptions geworfen, wenn die Instanz bei einem _updateStatus oder _updateGrouping als nicht verfügbar angesehen wird.
- Default Timeout auf "1000" hochgesetzt
