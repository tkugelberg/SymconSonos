# Sonos PHP Modules for IP-Symcon

IP-Symcon PHP module for accessing Sonos audio systems

## Documentation

**Content**

1. [functional range](#1-functional--range) 
2. [requirements](#2-requirements)
3. [installation & configuration](#3-installation--configuration)
4. [functional reference](#4-functional--reference) 

## 1. functional range

 This module is meant to handle common actions in Sonos from IP-Symcon.

 The folowing functions are implemented:
 - Selection of pre-defined radio stations (incl. default radio station)
 - Group Handling
 - Volume control (incl. default volume) 
 - Mute, Loudness, Bass, Treble

## 2. requirements

 - IPS 4.x
 - Sonos audio system
 
## 3. installation & configuration

   - installation in IPS 4.x  
        add the following URL to 'Modul Control':  
        `git://github.com/tkugelberg/SymconSonos.git`  
   - configuration  
     <img src="http://www.kugelberg.de/SymconSonos/instance_config.png">
     -  IP-Address/Host: 
        address the Sonos component can be reached under. When applying the settings a connection test is performed.
     -  Default Volume:
        Volume that will be set when executing
       ```php
       SNS_SetDefaultVolume(<InstanceID>);
       ```
       or
       ```php
       SNS_SetDefaultGroupVolume(<InstanceID>);
       ```
     - RINCON:
       Not to be set in configuration. Will be set when automatic script "_updateGrouping" is executed  is executed the first time.
     - Group Coordinator:
       If this flag is set, the instance can be used as leading member of a group.
     - Force Grouping in Sonos:
       If this flag is set, the grouping settings from IP-Symcon will be set in Sonos, if they differ.
       If this Flag is not set, the grouping information in IP-Symcon will be updated if Sonos settings differ.
     - Enable Mute Control:
       If this flag is set, the function SNS_SetMute(<InstanceID>) is enabled and a variable "Mute" is added.
     - Enable Loudness Control:
       If this flag is set, the function SNS_SetLoudness(<InstanceID>) is enabled and a variable "Loudness" is added.
     - Enable Bass Control:
       If this flag is set, the function SNS_SetBass(<InstanceID>) is enabled and a variable "Bass" is added.
     - Enable Treble Control:
       If this flag is set, the function SNS_SetTreble(<InstanceID>) is enabled and a variable "Treble" is added.
     - Favorite Radio Station:
       This selection defines which radio station is started when function SNS_SetRadioFavorite(<InstranceID>) is executed.
     - Stations in WebFront:
       This is a comma separated list of Radio Stations which should appear as Buttion in WebFront. If it is set to "<all>", all are being displayed.
      
      

## 4. functional reference

```php
SNS_ChangeGroupVolume(integer $InstanceID, integer $increment)
```
Changes the Volume by the value provided in $increment.
Can be positive or negative.
If the volume becomes bigger than 100 or less than 0, the volume will be set to these values.

---  

```php
SNS_Next(integer $InstanceID)
```
Jumps to next song in Playlist.

---  

```php
SNS_Pause(integer $InstanceID)
```
Pauses playing.

---  

```php
SNS_Play(integer $InstanceID)
```
Resumes or starts playing.

---  

```php
SNS_PlayFiles(integer $InstanceID, array $files)
```
All files provided in the array $files have to be located on a Samba Share (CIFS).
They have to be provided with complete path (e.g. "//server.local.domain/share/file.mp3").
Current playing will be stopped and all provided files will be played.
After the files have been played, the previous list/radio will be resumed. 

If the Box is part of a group, it will be removed for playing the files and added again, once done.
If the playback was inside a playlist, it will continue playing at the very same position.
If nothing was being played, silence will return.
Playing several files could look like this:

```php
SNS_PlayFiles(17265, Array( "//ipsymcon.fritz.box/sonos/bla.mp3",
                            "//ipsymcon.fritz.box/sonos/blubb.mp3"));  
```

---  

```php
SNS_Previous(integer $InstanceID)
```
Will jump one track back in Playlist (or to the beginning of the track).

---  

```php
SNS_SetBass(integer $InstanceID, integer $bass)
```
Will modify the bass settings in the equlizer of the selected box.
Possible entry is between -10 and 10.

---  

```php
SNS_SetDefaultGroupVolume(integer $InstanceID)
```
Executes function SNS_SetDefaultVolume( ) for each member of a group.

---  

```php
SNS_SetDefaultVolume(integer $InstanceID)
```
Sets the volume of a box to the value defined in instance settings.

---  

```php
SNS_SetGroup(integer $InstanceID, integer $groupCoordinator)
```
Adds or removes a Box to or from a group.
If an instance ID of a GroupCoordinator is provided, the box will be added to this group.
If 0 is provided, the Box will be removed from all groups.

---  

```php
SNS_SetGroupVolume(integer $InstanceID, integer $volume)
```
Executes SNS_ChangeGroupVolume($volume - "current Volume" ).

---  

```php
SNS_SetLoudness(integer $InstanceID, boolean $loudness)
```
Switched the Loudness Flag in Equilizer settings of a box.
Accepts 0,1, true and false as input.

---  

```php
SNS_SetMute(integer $InstanceID, boolean $mute)
```
Mutes or unmutes a box.
Accepts 0,1, true and false as input.

---  

```php
SNS_SetRadioFavorite(integer $InstanceID)
```
Switches on the favorite Radio, that is set in instance settings.

---  

```php
SNS_SetRadio(integer $InstanceID, string $radio)
```
Switches on the provided radio station in $radio.
Currently available are:
- 1LIVE
- Antenne 1
- Antenne Bayern
- Antenne MV
- Bayern 3
- bigFM
- Deutschlandfunk
- FFH
- FFN
- HR3
- KiRaKa
- MDR1
- NDR2
- N-JOY
- OE3
- Radio Duisburg
- Radio Essen
- Radio K.W.
- Radio Lippe
- RPR1
- SWR1 BW
- SWR1 RP
- SWR3
- WDR2

---  

```php
SNS_SetTreble(integer $InstanceID, integer $treble)
```
Will modify the treble settings in the equlizer of the selected box.
Possible entry is between -10 and 10.

---  

```php
SNS_SetVolume(integer $InstanceID, integer $volume)
```
Sets the volme of a box
Allowed values are between 0 and 100.

---  

```php
SNS_Stop(integer $InstanceID)
```
Stops playing

