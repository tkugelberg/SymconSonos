Sonos PHP Module for IP-Symcon
===
IP-Symcon PHP module for accessing Sonos audio systems

**Content**

1. [Features](#1-features)
2. [Requirements](#2-requirements)
3. [Installation & Configuration](#3-installation--configuration)
4. [Variables](#4-variables)
5. [Background Process](#5-background-process)
6. [Functions](#6-functions)
6. [FAQ](#7-faq)


## 1. Features
This module is intended to trigger general functionalities in Sonos from IP-Symcon.
The following functionalities are implemented:
- choose different sources
  - radio stations
    - delivered stations
    - imported Sonos TuneIn favorites
  - Playlists
  - Analog input
  - SPDIF input
- Group handling
- Play / Pause / Stop / Previous / Next
- Adjust Volume (including default volume)
- Mute, Loudness, Bass, Treble
- Balance
- Sleep timer
- announcements
- Cover  
- Play audio files from a Samba Share (e.g. Synology) or HTTP Server, and then restore the previous state

## 2. Requirements

 - IPS 4.1
 - Sonos audio system

## 3. Installation & Configuration

### Installation in IPS 4.1
Add the URL
```
git://github.com/tkugelberg/SymconSonos.git
```
in "Module Control" (core instances-> Modules).  
After that it is possible to create a new Sonos instance:
![Instanz erstellen](img/create_instance.png?raw=true "Instanz erstellen")

### Configuration
![Instanz konfigurieren](img/instance_config.png?raw=true "Instanz konfigurieren")

-  IP-Address / Host:  
Address under which the Sonos instance is accessible. This can be an IP or a hostname.
When the settings are saved, the address is checked.
- Maximal ping timeout:  
Before a command is sent to a Sonos instance, Sys_Ping () is used to check whether it is available.
This parameter controls how many milliseconds it is assumed that the box is not available.
If the parameter is set to 0, the availability is not checked.
-  Default Volume:  
The volume that is set when the functions  
   ```php 
    SNS_SetDefaultVolume(<InstanceID>);
   ```
   or
   ```php
   SNS_SetDefaultGroupVolume(<InstanceID>);
   ```
   are called.   
- RINCON:  
Contains the RINCON of the Sonos instance. This is a globally unique identifier in the format "RINCON_<MAC-address>1400", where the "<MAC-address>" contains only the digits and letters without hyphens.  
If this value is not maintained, it is determined automatically.
- Update Status Frq:  
This parameter contains the frequency in seconds in which the _updateStatus process is to be executed.  
Default: 5
- NA Update Status Frq:
If a box is unavailable (for example currentless), the frequency at which the _updateStatus process is executed is set to this value.  
Default 300
- Update Grouping Frq:  
This parameter contains the frequency in seconds in which the _updateGrouping process is to be executed.  
Default: 120
- NA Update Grouping Frq:
When a box is unavailable (for example currentless), the frequency at which the _updateGrouping process is executed is set to this value. 
Default: 900
- Force Grouping in Sonos:
Regarding the behavior that should happen when there is a difference in the group assignment between Sonos and IPS, there are 2 alternatives:
  1. IPS accepts the settings from Sonos :arrow_right: Hook not set  
This alternative has the advantage that the settings you have taken over the Sonos APP have been reflected in IP-Symcon. 
  2. The settings from IPS are set in Sonos :arrow_right: Hook is set  
This is intended to ensure that a box that has been switched off is automatically added back to the correct group.
But unfortunately has the side effect that a change in the Sonos APP is again discarded when the _updateGrouping script is running.
- Enable Mute Control:  
This option creates a variable "Mute" and enables it to be maintained with the current value via the _updateStatus script. Furthermore, then also a Konpf appears on the WebFront, over which one can control this.
- Enable Loudness Control:  
This option creates a variable "Loudness" and activates it by the script _updateStatus with the current value is maintained. Furthermore, then also a button appears on the WebFront, over which one can control this function.
- Enable Bass Control:  
This option creates a variable "Bass" and enables it to be maintained with the current value via the _updateStatus script. There is also a slider on the web front, which can be used to control this.
- Enable Treble Control:  
This option creates a variable "Treble" and activates it with the current value via the script _updateStatus. There is also a slider on the web front, which can be used to control this.
- Enable Balance Control: 
This option creates a variable "Balance" and enables it to be maintained with the current value via the _updateStatus script. There is also a slider on the web front, which can be used to control this.
- Enable Sleeptimer Contorl:
This option creates a variable "Sleeptimer" and activates it by the _updateStatus script with the current value.
- Enable Playmode Control:
This option specifies the "Playmode" and "Crossfade" variables and activates them with the current value via the _updateStatus script.
- Import Playlists:
This option can have the values ​​"none", "saved", "imported", and "saved & imported".
If "none" is selected, nothing happens. For the other values, select a variable "Playlist". This will display a button on the WebFront for each Playlist imported from the Sonos system. The variable Playlist is never filled, however, since this does not correspond to the logic in Sonos.
When a play list is started, only all the tracks in the list are added to the queue.
If the box is assigned to a group, this variable is hidden.  
- Enable detaild info:
This option specifies the "Details", "CoverURL", "ContentStream", "Artist", "Title", "Album", "TrackDuration", and "Position" variables that are filled by the _updateStatus script.
In the "Details" tag, an HTMLBox is created which can also be seen on the WebFront. All other variables are hidden. 
- Force Variable order: 
If this option is set, the sequence suggested by the module will be saved 
- Include TuneIn favorites:  
If this checkbox is set, the TuneIn Favorites (My Radiosender) from the Sonos system are read out and stored in addition to the supplied radio stations. They are created as blue heads in the WebFront.
However, the total number of radios available can not exceed 32. Since the delivered transmitters are first to be read out, these must possibly be limited, so that the TuneIn transmitters are added.
- Favorite Radio Station:  
The selected station from the delivered station list is started when the function
  ```php
  SNS_SetRadioFavorite(<InstranceID>);
  ```
  is performed.  
- Stations in WebFront:  
This is a list of delivered transmitters to be displayed as a transparent button in the WebFront. If it is "Favorite Station" the button is yellow.
If you want to display all &lt;all&gt;, if none is to be displayed, leave this parameter blank.  
These transmitters are available:
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

### Test environment

- Update Radio Stations  
This button updates the profile in which the radio stations available in the webfront are stored, according to the parameters "Include TuneIn favorites", "Favorite Radio Station" and "Stations in WebFront".
Attention: This update is then valid for all Sonos instances!
- Update Playlists
This button updates the profile in which the playlists available in the Webfront are stored.
Depending on the setting for the "Import Playlists" option, Playlists are saved and imported into Sonos.
Attention: This update is then valid for all Sonos instances!  
- Read RINCON from Sonos
When this button is pressed, the RINCON is read from the Sonos Box and stored in the instance configuration.
ATTENTION: You do not see this change immediately, but only after the next actual configuration is opened!

## 4. Variables
- Coordinator  
This hidden variable is used to determine whether the box is currently a coordinator at the current time.
On a coordinator, e.g. such functions as Play, Pause, Next, or the Sleeptimer can be used.
If a box is not available to a coordinator and the coordinator is available in IPS, these commands are automatically forwarded to the group coordinator.
- GroupMembers 
This variable contains a list of Sonos instance IDs assigned to this group coordinator.
If the "Force grouping in Sonos" option is activated, this variable is only activated by the function
  ```php
  SNS_SetGroup(<InstanceID>,<CoordinatorInstanceID>);
  ```
  If the "Force grouping in Sonos" option is not activated, this variable is additionally updated by the _updateGrouping script.

- GroupVolume  
This variable is automatically displayed when a box is assigned as a group menu.
Their value is calculated by the volume of the individual group members (average value).
It is updated by the functions
  ```php
  SNS_ChangeGroupVolume(<InstanceID>,<Increment>);
  SNS_SetDefaultGroupVolume(<InstanceID>);
  SNS_SetGroupVolume(<InstanceID>,<Volume>);
  ``` 
  and the _updateStatus process of the group coordinator.
- MemberOfGroup  
This variable is created when the Group Coordinator option is __not__ selected.
It contains the instance ID of the group coordinator that is associated with the instance.
- nowPlaying  
This variable is kept current by the _updateStatus process.
It contains information about what is currently being played.
If the instance is a member of a group, the variable is hidden and filled with the value from the group coordinator.
  
  If not, the value can be composed in two ways:
  1. If the field "StreamContent" is filled, this is accepted (eg: in the case of radio station)
  2. Otherwise "&lt;Title&gt;|&lt;Artist&gt;" is used
- Radio  
This variable contains the currently active radio transmitter, if it appears in the list of the available radio stations in the WebFront (see configuration).
An update is made by the _updateStatus process.
If the instance is a member of a group, the variable is hidden and filled with the value from the group coordinator.
- Status  
This variable contains information on the state in which the Sonos instance is currently located and is updated by the _updateStatus process.
If the instance is a member of a group, the variable is hidden and filled with the value from the group coordinator.
Possible values ​​are:
  - 0 - Prev
  - 1 - Play
  - 2 - Pause
  - 3 - Stop
  - 4 - Next
  - 5 - Transition

  0 and 4 are only used to control the player via the WebFront. 5 is a value which is only assumed for the short term when the audio source is changed.
- Volume  
This variable contains the current volume of the instance and is updated by the _updateStatus process.
- Mute  
This variable is created only when _"Enable Mute Control"_ is enabled. It contains the current state whether the instance is set to mute and is updated by the _updateStatus process.
- Loudness 
This variable is created only when _"Enable Mute Control"_ is enabled.
It contains the current state whether the instance using loudness and is updated by the _updateStatus process.
- Bass  
This variable is created only when _"Enable Bass Control"_ is enabled.
It contains the current equalizer settings of the instance and is updated by the _updateStatus process.
- Treble  
This variable is created only when _"Enable Treble Control"_ is enabled.
It contains the current equalizer settings of the instance and is updated by the _updateStatus process.
- Balance  
This variable is created only when _"Enable Balance Control"_ is enabled.
It contains the current equalizer settings of the instance and is updated by the _updateStatus process.
- Sleeptimer  
This variable is created only when _"Enable Sleeptimer Control"_ is enabled.
It contains the current value of the instance's sleeptimer and is updated by the _updateStatus process.
If the instance is a member of a group, the variable is hidden and filled with the value from the group coordinator.
- Playlist  
This variable has normally not maintained a value. It is only used to start a Playlist from the WebFront.
Only after pressing the button on the WebFront, the variable is set to the selected value for one second. This should give the user a short feedback.
- PlayMode  
This variable is created only when _"Enable Playmode Control"_ is enabled.
The current value of the Playmode is stored in these variables and is updated by the _updateStatus process. The possible values ​​are:
  - 0: "NORMAL"
  - 1: "REPEAT_ALL"
  - 2: "REPEAT_ONE"
  - 3: "SHUFFLE_NOREPEAT"
  - 4: "SHUFFLE"
  - 5: "SHUFFLE_REPEAT_ONE"
- Crossfade  
This variable is created only when _"Enable Playmode Control"_ is enabled.
It contains the current value of the crossfade settings and is updated by the _updateStatus process.
- CoverURL  
This variable is created only when _"Enable detailed info"_ is enabled.
It contains the URL to the cover that is currently displayed in Sonos. However, this applies only to tracks, not to streams.
The variable is updated by the _updateStatus process.
- ContentStream  
This variable is created only when _"Enable detailed"_ info is enabled.
It contains the contenstram in the case of streamed transmitters (e.g., current information) and is updated by the _updateStatus process.
- Artist  
This variable is only created when _"Enable detailed"_ info is enabled.
It contains the artist of the currently played title and is updated by the _updateStatus process.
- Album  
This variable is created only if _"Enable detailed info"_ is enabled.
It contains the album of the currently played title and is updated by the _updateStatus process.
- TrackDuration  
This variable is created only when _"Enable detailed info"_ is enabled.
It contains the length of the currently played title and is updated by the _updateStatus process.
- Position  
This variable is only created when _"Enable detailed info"_ is enabled.
It contains the current position in the currently played title and is updated by the _updateStatus process.
- Title  
This variable is created only when _"Enable detailed info"_ is enabled.
It contains the title of the currently played title and is updated by the _updateStatus process.
- Details  
This variable is only created when _"Enable detailed info"_ is enabled.
This is an HTMLBox which displays the cover, the title the artist, the album and position info:  

![Details Song](img/details_song.png?raw=true "Details song")

If a station is being streamed, only ContenStram and Title are included:  

![Details Radio](img/details_radio.png?raw=true "Details Radio")

- Song position
This variable is only created when the _ "Enable position info" _ option is activated.
It contains the current position of the track being played and is updated by the _updateStatus process.


## 5. Background Process
When a Sonos instance is created, 2 processes are created and linked with an internal timer, which can be set in the configuration form.
1. _updateStatus  
This process runs every 5 seconds.
It updates the _Volume_, _Mute_, _Loudness_, _Bass_, _Treble_, _Balance_, and _Sleeptimer_ variables based on Sonos values, if the relevant configuration switches require this.
The _Status_, _Radio_ and _NowPlaying_ parameters are also updated.
For Group Coordinators, the group volume (GroupVolume) is calculated.
2. _updateGrouping  
This script will run all "Update Grouping Frq" seconds. This is a configuration parameter.
The group settings are updated either in Sonos or in IP Symcon.

## 6. Functions
```php
SNS_ChangeGroupVolume(integer $InstanceID, integer $increment)
```
Changes the volume of each member of a group by the included value in $increment.
Can be positive or negative.
If the volume exceeds 100 or falls below 0, the volume is set to these values.

---
```php
SNS_ChangeVolume(integer $InstanceID, integer $increment)
```
Changes the volume of a Sonos instance by the included value in $increment.
Can be positive or negative.
If the volume exceeds 100 or falls below 0, the volume is set to these values.

---
```php
SNS_DeleteSleepTimer(integer $InstanceID)
```
Breaks the sleeptimer.
If the command is executed on a group member, it is automatically forwarded to the responsible coordinator and is valid for the whole group.

---
```php
SNS_Next(integer $InstanceID)
```
Jump to the next track.
If the command is executed on a group member, it is automatically forwarded to the responsible coordinator and is valid for the whole group.

---
```php
SNS_Pause(integer $InstanceID)
```
Pauses playback.
If the command is executed on a group member, it is automatically forwarded to the responsible coordinator and is valid for the whole group.

---
```php
SNS_Play(integer $InstanceID)
```
Resumes playback.
If the command is executed on a group member, it is automatically forwarded to the responsible coordinator and is valid for the whole group.

---
```php
SNS_PlayFiles(integer $InstanceID, array $files, string $volumeChange)
```
1. If something is playing, playback pauses
2. The volume is adjusted according to $volumeChange  
   - "0" would not change the volume  
   - "17" would set the volume to 17  
   - "+8" would increase the volume by 8  
   - "-8" would lower the volume by 8
3. All files that are specified in the $files array are played.  
Either from a Samba share (CIFS) (eg "//server.local.domain/share/file.mp3") or from an HTTP server (e.g.: "http://ipsymcon.fritz.box:3777/user/ansage/hallo.mp3 ")
4. The output volume is restored
5. The audio source is restored
6. If playback was active, it will be restarted

If the instance is assigned to a group, it is taken from the group for playback of the files, and then added again.
Multiple files could look like this:
```php
SNS_PlayFiles(17265, Array( "//ipsymcon.fritz.box/sonos/bla.mp3",
                            "http://www.sounds.com/blubb.mp3"), 0);
```

---

```php
SNS_PlayFilesGrouping(integer $InstanceID, array $instances, array $files, $volume)
```
This function calls the SNS_PlayFiles function. Correspondingly, the array $files is constructed in the same way.
Previously, the instances given in $instances are added to the $InstanceID group.
The array $instances contains an instance with the key "&lt;instance ID&lt" of the instance to add and an array with settings. This array currently only has an entry with the "volume" key with the volume value corresponding to the $volumeChange from the SNS_PlayFiles function.

Example:
```php
SNS_PlayFilesGrouping(46954 , array( 11774 => array( "volume" => 10),
                                     27728 => array( "volume" => "+10"),
                                     59962 => array( "volume" => 30) ), array( IVNTTS_saveMP3(12748, "Dieser Text wird angesagt")), 28 );
```
Instances 11774, 27728 und 59962 are added to the group with coordinator 46954.  
Instance 11774 is set to volume 10.  
For instance 27728, the volume is raised by 10 points.  
Instance 59962 is set to volume 30.  
The instance 46954 becomes group coordinator for the announcement (s) and is set to volume 28.
The text "This text is announced" is converted by the SymconIvona module (instance 12748) into an MP3, which is then played.

---
```php
SNS_Previous(integer $InstanceID)
```
Starts the previous track in the list.
If the command is executed on a group member, it is automatically forwarded to the responsible coordinator and is valid for the whole group.

---
```php
SNS_RampToVolume(integer $InstanceID, $rampType, $volume);
```
Calls the RampToVolume function in Sonos.
The $rampType parameter can be passed as integer or string.  
- 1 corresponds to SLEEP_TIMER_RAMP_TYPE
- 2 corresponds to ALARM_RAMP_TYPE
- 3 corresponds to AUTOPLAY_RAMP_TYPE

---
```php
SNS_SetAnalogInput(integer $InstanceID, integer $InputInstanceID)
```
Selects the analog input of an instance as the audio source.
If the instance is currently in a group, it is automatically removed from the group and then the new audio source is set.
If this function is executed on a group coordinator, the new audio source is valid for the entire group.

---
```php
SNS_SetBalance(integer $InstanceID, integer $balance)
```
Adjusts the balance settings in the equalizer of the Sonos instance. Only sensible for stereo pairs or AMPS.
Possible values ​​are between -100 (far left) and 100 (far right).

---
```php
SNS_SetBass(integer $InstanceID, integer $bass)
```
Adjusts the bass settings in the equalizer of the Sonos instance.
Possible values ​​are between -10 and 10.

---
```php
SNS_SetCrossfade(integer $InstanceID, boolean $crossfade)
```
Switches the crossfade mode on or off for an instance.
If the instance is part of a group, the command is automatically forwarded to the group coordinator.
0,1, true and false are valid values ​​for $loudness.

---
```php
SNS_SetDefaultGroupVolume(integer $InstanceID)
```
Performs the SNS_SetDefaultVolume( ) function for each member of a group.

---
```php
SNS_SetDefaultVolume(integer $InstanceID)
```
Changes the volume of an instance to the default volume.

---
```php
SNS_SetGroup(integer $InstanceID, integer $groupCoordinator)
```
Adds or removes the instance from a group.
If the instance ID of a group coordinator is specified, the instance of this group is added.
If 0 is given, the instance is removed from all groups.

---
```php
SNS_SetGroupVolume(integer $InstanceID, integer $volume)
```
Executes the SNS_ChangeGroupVolume function ($ volume - "current volume").

---
```php
SNS_SetLoudness(integer $InstanceID, boolean $loudness)
```
Sets the Loundess flag to an instance.
0,1, true and false are valid values ​​for $loudness.

---
```php
SNS_SetMute(integer $InstanceID, boolean $mute)
```
set mute or unmute an instance.
0,1, true and false are valid values ​​for $mute.

---
```php
SNS_SetPlaylist(integer $InstanceID, string $name)
```
Removes all tracks from a queue and adds all tracks to a playlist.
The playlist name must be known in Sonos.
It is first searched for the name in the stored playlists. If it is not found there, it is also searched in the imported Playlists. A dash ("_") is replaced with a space (" ") and the endings ".m3u" and ".M3U" are removed. Thus, e.g. the playlist with the name "3_Doors_Down.m3u" with the command SNS_SetPlaylist (12345, "3 Doors Down"); to be started.
If the instance is currently in a group, it is automatically removed from the group and then the new audio source is set.
If this function is executed on a group coordinator, the new audio source is valid for the entire group.

---
```php
SNS_SetPlaymode(integer $InstanceID, integer $playMode)
```
Sets the play mode of a Sonos instance.
If the instance is a member of a group, the command is automatically forwarded to the group coordinator.
Possible values ​​for the Playmode are:
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
Starts playback of "Favorite Radio Station".

---
```php
SNS_SetRadio(integer $InstanceID, string $radio)
```
Sets the audio source to the URL of the radio station that is given in $radio.
First, it is searched for whether the transmitter is found in the delivered transmitters. If it is not found there, it is searched for in the TuneIn Favorites (My RadioStations).
If the instance is currently in a group, it is automatically removed from the group and then the new Audiquelle is set.
If this function is executed on a group coordinator, the new audio source is valid for the entire group.

---
```php
SNS_SetSleepTimer(integer $InstanceID, integer $minutes)
```
Sets the sleeptimer to the specified number of minutes.
If the command is executed on a group member, it is automatically forwarded to the responsible coordinator and is valid for the whole group.

---

```php
SNS_SetSpdifInput(integer $InstanceID, integer $InputInstanceID)
```
Selects the SPDIF input of an instance as the audio source.
If the instance is currently in a group, it is automatically removed from the group and then the new audio source is set.
If this function is executed on a group coordinator, the new audio source is valid for the entire group.

---
```php
SNS_SetTreble(integer $InstanceID, integer $treble)
```
Adjusts the Treble settings in the equalizer of the Sonos instance.
Possible values ​​are between -10 and 10.

---
```php
SNS_SetVolume(integer $InstanceID, integer $volume)
```
Adjusts the volume of an instance.
Possible values ​​are between 0 and 100.

---
```php
SNS_Stop(integer $InstanceID)
```
Pause playback.
If the command is executed on a group member, it is automatically forwarded to the responsible coordinator and is valid for the whole group.

---
```php
SNS_UpdatePlaylists(integer $InstanceID)
```
Reads all playlists from Sonos and stores them in a variable profile in IPS.
This makes it possible to start a playlist in the WebFront.
This affects all Sonos instances.
The maximum number of entries in a variable profile is 32. Therefore, only the first 32 playlists are saved.
This function is also executed by the "Update Playlists" button in the instance configuration.

---
```php
SNS_UpdateRadioStations(integer $InstanceID)
```
Updates the variable profile for radiosender in IPS.
This results, for example the buttons in the WebFront with which one can start a radio station. 
- First, all delivered radios, which result from the configuration "Stations in WebFront", are added to the list. In the webfront, these stations are displayed in "transparent", the favorite in "yellow".
- If the configuration option "Include TuneIn favorites" is selected, all TuneIn Favorites (My Radiosender) will also be added to the list. On the webfront, these stations are displayed in "blue".
- If 32 stations are reached at any time, the list is full. This is due to the restriction of IPS that variable profiles can have a maximum of 32 entries.

Note: If only transmitters from Sonos are to be contained, the configuration parameter "Stations in WebFront" is to be stored __empty__!

---
```php
SNS_UpdateRINCON(integer $InstanceID)
```
Reads the RINCON of an instance and writes it to the instance configuration.

## 7. FAQ
### 7.1. How many variables are required for the module?  
Each instance (i.e. Sonos Box) requires between 13 and 30 variables.  
- Instance itself
- GroupMembers
- Coordinator
- GroupVolume
- MemberOfGroup
- nowPlaying
- Radio
- Status
- Volume
- Bass (if "Bass Control" is activated)
- Treble (if "Treble Control" is activated)
- Mute (if "Mute Control" is activated)
- Loudness (if "Loudness Control" is activated)
- Balance (if "Balance Control" is activated)
- Sleeptimer (if "Sleeptimer Control" is activated)
- Playlist (if "Playlist Control" is activated)
- PlayMode (if "Playmode Control" is activated)
- Crossfade (if "Playmode Control" is activated)
- CoverURL (if "Endable detailed info" is activated)
- ContentStream (if "Endable detailed info" is enabled)
- Artist (if "Endable detailed info" is activated)
- Album (if "Endable detailed info" is activated)
- TrackDuration (if "Endable detailed info" is activated)
- Position (if "Endable detailed info" is activated)
- Title (if "Endable detailed info" is activated)
- Details (if "Endable detailed info" is activated)
- Song progress (if _"Enable position info"_ is activated)