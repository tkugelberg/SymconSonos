<?
include_once("../modules/SymconSonos/Sonos/sonosAccess.php");
include_once("../modules/SymconSonos/Sonos/radio_stations.php");

$ip                    = gethostbyname(IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "IPAddress"));
$timeout               = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "TimeOut");
$frequency             = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "UpdateStatusFrequency");
$frequencyNotAvailable = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "UpdateStatusFrequencyNA");
$AlbumArtHeight        = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "AlbumArtHeight");

if (!isset($AlbumArtHeight) || $AlbumArtHeight == 0 ){
  $AlbumArtHeight = 170;	
} 

// Get all needed Variable IDs
$vidInstance      = IPS_GetParent($_IPS["SELF"]);
$vidVolume        = @IPS_GetObjectIDByName("Volume",        $vidInstance);
$vidMute          = @IPS_GetObjectIDByName("Mute",          $vidInstance);
$vidLoudness      = @IPS_GetObjectIDByName("Loudness",      $vidInstance);
$vidBass          = @IPS_GetObjectIDByName("Bass",          $vidInstance);
$vidTreble        = @IPS_GetObjectIDByName("Treble",        $vidInstance);
$vidBalance       = @IPS_GetObjectIDByName("Balance",       $vidInstance);
$vidMemberOfGroup = @IPS_GetObjectIDByName("MemberOfGroup", $vidInstance);
$vidStatus        = @IPS_GetObjectIDByName("Status",        $vidInstance);
$vidRadio         = @IPS_GetObjectIDByName("Radio",         $vidInstance);
$vidSleeptimer    = @IPS_GetObjectIDByName("Sleeptimer",    $vidInstance);
$vidNowPlaying    = @IPS_GetObjectIDByName("nowPlaying",    $vidInstance);
$vidGroupMembers  = @IPS_GetObjectIDByName("GroupMembers",  $vidInstance);
$vidDetails       = @IPS_GetObjectIDByName("Details",       $vidInstance);
$vidCoverURL      = @IPS_GetObjectIDByName("CoverURL",      $vidInstance);
$vidStationID     = @IPS_GetObjectIDByName("StationID",     $vidInstance);
$vidContentStream = @IPS_GetObjectIDByName("ContentStream", $vidInstance);
$vidArtist        = @IPS_GetObjectIDByName("Artist",        $vidInstance);
$vidTitle         = @IPS_GetObjectIDByName("Title",         $vidInstance);
$vidAlbum         = @IPS_GetObjectIDByName("Album",         $vidInstance);
$vidTrackDuration = @IPS_GetObjectIDByName("TrackDuration", $vidInstance);
$vidPosition      = @IPS_GetObjectIDByName("Position",      $vidInstance);
$vidCrossfade     = @IPS_GetObjectIDByName("Crossfade",     $vidInstance);
$vidPlaymode      = @IPS_GetObjectIDByName("PlayMode",      $vidInstance);

// If the Sonos instance is not available update of grouping makes no sense
if ( $timeout && Sys_Ping($ip, $timeout) == false ){
  @IPS_SetScriptTimer($_IPS["SELF"], $frequencyNotAvailable );
  return;
}

@IPS_SetScriptTimer($_IPS["SELF"], $frequency );

$sonos = new SonosAccess($ip);

$status = $sonos->GetTransportInfo();

SetValueInteger($vidVolume, $sonos->GetVolume());
if($vidMute)      SetValueInteger($vidMute,     $sonos->GetMute()             );
if($vidLoudness)  SetValueInteger($vidLoudness, $sonos->GetLoudness()         );
if($vidBass)      SetValueInteger($vidBass,     $sonos->GetBass()             );
if($vidTreble)    SetValueInteger($vidTreble,   $sonos->GetTreble()           );
if($vidCrossfade) SetValueInteger($vidCrossfade,$sonos->GetCrossfade()        );
if($vidPlaymode)  SetValueInteger($vidPlaymode, $sonos->GetTransportsettings());

if($vidBalance){
  $leftVolume  = $sonos->GetVolume("LF");
  $rightVolume = $sonos->GetVolume("RF");

  if ( $leftVolume == $rightVolume ){
    SetValueInteger($vidBalance, 0);
  }elseif ( $leftVolume > $rightVolume ){
    SetValueInteger($vidBalance, $rightVolume - 100 );
  }else{
    SetValueInteger($vidBalance, 100 - $leftVolume );
  }
}

$MemberOfGroup = 0;
if($vidMemberOfGroup) $MemberOfGroup = GetValueInteger($vidMemberOfGroup);

if ($MemberOfGroup){
  // If Sonos is member of a group, use values of Group Coordinator
  SetValueInteger($vidStatus, GetValueInteger(IPS_GetObjectIDByName("Status", $MemberOfGroup)));
  $actuallyPlaying = GetValueString(IPS_GetObjectIDByName("nowPlaying", $MemberOfGroup));
  SetValueInteger($vidRadio, GetValueInteger(IPS_GetObjectIDByName("Radio", $MemberOfGroup)));
  if($vidSleeptimer)    SetValueInteger($vidSleeptimer,   @GetValueInteger(IPS_GetObjectIDByName("Sleeptimer", $MemberOfGroup)));
  if($vidCoverURL)      SetValueString($vidCoverURL,      @GetValueString(IPS_GetObjectIDByName("CoverURL", $MemberOfGroup)));
  if($vidContentStream) SetValueString($vidContentStream, @GetValueString(IPS_GetObjectIDByName("ContentStream", $MemberOfGroup)));
  if($vidArtist)        SetValueString($vidArtist,        @GetValueString(IPS_GetObjectIDByName("Artist", $MemberOfGroup)));
  if($vidAlbum)         SetValueString($vidAlbum,         @GetValueString(IPS_GetObjectIDByName("Album", $MemberOfGroup)));
  if($vidTrackDuration) SetValueString($vidTrackDuration, @GetValueString(IPS_GetObjectIDByName("TrackDuration", $MemberOfGroup)));
  if($vidPosition)      SetValueString($vidPosition,      @GetValueString(IPS_GetObjectIDByName("Position", $MemberOfGroup)));
  if($vidTitle)         SetValueString($vidTitle,         @GetValueString(IPS_GetObjectIDByName("Title", $MemberOfGroup)));
  if($vidDetails)       SetValueString($vidDetails,       @GetValueString(IPS_GetObjectIDByName("Details", $MemberOfGroup)));
}else{
  SetValueInteger($vidStatus, $status);
  // Titelanzeige
  $currentStation = 0;

  if ( $status <> 1 ){
    // No title if not playing
    $actuallyPlaying = "";
  }else{
    $positionInfo = $sonos->GetPositionInfo();
    $mediaInfo    = $sonos->GetMediaInfo();

    if ($positionInfo["streamContent"]){
      $actuallyPlaying = $positionInfo["streamContent"];
    } else {
      $actuallyPlaying = $positionInfo["title"]." | ".$positionInfo["artist"];
    }

    // start find current Radio in VariableProfile
    $radioStations       = get_available_stations();
    $playingRadioStation = '';
    foreach ($radioStations as $radioStation) {
      if($radioStation["url"] == htmlspecialchars_decode($mediaInfo["CurrentURI"])){
        $playingRadioStation = $radioStation["name"];
        $image               = $radioStation["logo"];
        break;
      }
    }

    if( $playingRadioStation == ''){
      foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('R:0/0')['Result']))->item as $item) {
        if ($item->res == htmlspecialchars_decode($mediaInfo["CurrentURI"])){
          $playingRadioStation = (string)$item->xpath('dc:title')[0];
          break;
        }
      }
    }

    $Associations = IPS_GetVariableProfile("Radio.SONOS")["Associations"];
    if(isset($playingRadioStation)){
      foreach($Associations as $key=>$station) {
        if( $station["Name"] == $playingRadioStation ){
          $currentStation = $station["Value"];
          break;
        }
      }
    }
    // end find current Radio in VariableProfile
  }
  SetValueInteger($vidRadio, $currentStation);

  // detailed Information
  if($vidContentStream)   SetValueString($vidContentStream, @$positionInfo['streamContent']);
  if($vidArtist)          SetValueString($vidArtist,        @$positionInfo['artist']);
  if($vidAlbum)           SetValueString($vidAlbum,         @$positionInfo['album']);
  if($vidTrackDuration)   SetValueString($vidTrackDuration, @$positionInfo['TrackDuration'] );
  if($vidPosition)        SetValueString($vidPosition,      @$positionInfo['RelTime']);
  if($vidTitle){
    if(@$mediaInfo['title']){
      SetValueString($vidTitle, @$mediaInfo['title']);
    }else{
      SetValueString($vidTitle, @$positionInfo['title']);
    }
  }
  if($vidDetails){
    if (!isset($stationID)) $stationID = "";
    if(isset($positionInfo)){
      // SPDIF and analog
      if(preg_match('/^RINCON_/', $mediaInfo['title']) ){
        $detailHTML = "";
      // Radio or stream(?)
      }elseif($mediaInfo['title']){
        // get stationID if playing via TuneIn
       $stationID = preg_replace("#(.*)x-sonosapi-stream:(.*?)\?sid(.*)#is",'$2',$mediaInfo['CurrentURI']);
       if (!isset($image)) $image = "";
       if($stationID && $stationID[0]=="s"){
	 if(@GetValueString($vidStationID) == $stationID){
            $image = GetValueString($vidCoverURL);
	 }else{
            $serial = substr(IPS_GetProperty($vidInstance ,"RINCON"), 7,12);
            $image = preg_replace('#(.*)<LOGO>(.*?)\</LOGO>(.*)#is','$2',@file_get_contents("http://opml.radiotime.com/Describe.ashx?c=nowplaying&id=".$stationID."&partnerId=IAeIhU42&serial=".$serial));
	 }
       }else{
          $stationID = "";
       }
        $detailHTML =   "<table width=\"100%\">
                          <tr>
                            <td>
                              <div style=\"text-align: right;\">
                                <div><b>".$positionInfo['streamContent']."</b></div>
                                <div>&nbsp;</div>
                                <div>".$mediaInfo['title']."</div>
                              </div>
                            </td>";

			if(strlen($image) > 0) {
			   $detailHTML .= "<td width=\"170px\" valign=\"top\">
                              <div style=\"width: 170px; height: ".$AlbumArtHeight."px; perspective: 170px; right: 0px; margin-bottom: 10px;\">
                              	<img src=\"".@$image."\" style=\"max-width: 170px; max-height: 170px; -webkit-box-reflect: below 0 -webkit-gradient(linear, left top, left bottom, from(transparent), color-stop(0.88, transparent), to(rgba(255, 255, 255, 0.5))); transform: rotateY(-10deg) translateZ(-35px);\">
                              </div>
                            </td>";
			}

         $detailHTML .= "</tr>
                        </table>";

      // normal files
      }else{
        $durationSeconds        = 0;
        $currentPositionSeconds = 0;
        if($positionInfo['TrackDuration'] && preg_match('/\d+:\d+:\d+/', $positionInfo['TrackDuration']) ){
          $durationArray          = explode(":",$positionInfo['TrackDuration']);
          $currentPositionArray   = explode(":",$positionInfo['RelTime']);
          $durationSeconds        = $durationArray[0]*3600+$durationArray[1]*60+$durationArray[2];
          $currentPositionSeconds = $currentPositionArray[0]*3600+$currentPositionArray[1]*60+$currentPositionArray[2];
        }
        $detailHTML =   "<table width=\"100%\">
                          <tr>
                            <td>
                              <div style=\"text-align: right;\">
                                <div><b>".$positionInfo['title']."</b></div>
                                <div>&nbsp;</div>
                                <div>".$positionInfo['artist']."</div>
                                <div>".$positionInfo['album']."</div>
                                <div>&nbsp;</div>
                                <div>".$positionInfo['RelTime']." / ".$positionInfo['TrackDuration']."</div>
                              </div>
                            </td>";

         if(isset($positionInfo['albumArtURI'])) {
            $detailHTML .= "<td width=\"170px\" valign=\"top\">
                              <div style=\"width: 170px; height: ".$AlbumArtHeight."px; perspective: 170px; right: 0px; margin-bottom: 10px;\">
                              	<img src=\"".@$positionInfo['albumArtURI']."\" style=\"max-width: 170px; max-height: 170px; -webkit-box-reflect: below 0 -webkit-gradient(linear, left top, left bottom, from(transparent), color-stop(0.88, transparent), to(rgba(255, 255, 255, 0.5))); transform: rotateY(-10deg) translateZ(-35px);\">
                              </div>
                            </td>";
         }

         $detailHTML .= "</tr>
                        </table>";
      }
    }
    @SetValueString($vidDetails, $detailHTML);
    if($vidCoverURL){
		if((isset($image)) && (strlen($image) > 0)) {
		  SetValueString($vidCoverURL, $image);
		}else{
	     SetValueString($vidCoverURL, @$positionInfo['albumArtURI']);
		}
	 }
  SetValueString($vidStationID,$stationID);
  }

  // Sleeptimer
  if ($vidSleeptimer){
    $sleeptimer = $sonos->GetSleeptimer();
    if($sleeptimer){
      $SleeptimerArray = explode(":",$sonos->GetSleeptimer());

      $SleeptimerMinutes = $SleeptimerArray[0]*60+$SleeptimerArray[1];
      if($SleeptimerArray[2])
        $SleeptimerMinutes = $SleeptimerMinutes + 1;
    }else{
      $SleeptimerMinutes = 0;
    }

    SetValueInteger($vidSleeptimer, $SleeptimerMinutes);
  }
}

$nowPlaying   = GetValueString($vidNowPlaying);
if ($actuallyPlaying <> $nowPlaying)
    SetValueString($vidNowPlaying, $actuallyPlaying);

// Set Group Volume
$groupMembers        = GetValueString($vidGroupMembers);
$groupMembersArray   = Array();
if($groupMembers)
  $groupMembersArray = array_map("intval", explode(",",$groupMembers));
$groupMembersArray[] = $vidInstance;

$GroupVolume = 0;
foreach($groupMembersArray as $key=>$ID) {
  $GroupVolume += GetValueInteger(IPS_GetObjectIDByName("Volume", $ID));
}

SetValueInteger(IPS_GetObjectIDByName("GroupVolume", IPS_GetParent($_IPS["SELF"])), intval(round($GroupVolume / sizeof($groupMembersArray))));
?>
