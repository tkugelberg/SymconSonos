<?
include_once("../modules/SymconSonos/Sonos/sonosAccess.php");
include_once("../modules/SymconSonos/Sonos/radio_stations.php");

$ip      = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "IPAddress");
$timeout = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "TimeOut");

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
$vidContentStream = @IPS_GetObjectIDByName("ContentStream", $vidInstance);
$vidArtist        = @IPS_GetObjectIDByName("Artist",        $vidInstance);
$vidTitle         = @IPS_GetObjectIDByName("Title",         $vidInstance);
$vidAlbum         = @IPS_GetObjectIDByName("Album",         $vidInstance);
$vidTrackDuration = @IPS_GetObjectIDByName("TrackDuration", $vidInstance);
$vidPosition      = @IPS_GetObjectIDByName("Position",      $vidInstance);

// If the Sonos instance is not available update of grouping makes no sense
if ( $timeout && Sys_Ping($ip, $timeout) == false )
    die('Sonos instance '.$ip.' is not available');

$sonos = new SonosAccess($ip);

$status = $sonos->GetTransportInfo();

SetValueInteger($vidVolume, $sonos->GetVolume());
if($vidMute)     SetValueInteger($vidMute,     $sonos->GetMute()     );
if($vidLoudness) SetValueInteger($vidLoudness, $sonos->GetLoudness() );
if($vidBass)     SetValueInteger($vidBass,     $sonos->GetBass()     );
if($vidTreble)   SetValueInteger($vidTreble,   $sonos->GetTreble()   );
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
  if($vidCoverURL)        SetValueString($vidCoverURL,      @$positionInfo['albumArtURI']);
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
    if(isset($positionInfo)){
      // SPDIF and analog 
      if(preg_match('/^RINCON_/', $mediaInfo['title']) ){
        $detailHTML = "";
      // Radio or stream(?)
      }elseif($mediaInfo['title']){
        // get stationID if playing via TuneIn
        $stationID = preg_replace("#(.*)x-sonosapi-stream:(.*?)\?sid(.*)#is",'$2',$mediaInfo['CurrentURI']);
        $image = "";
        if($stationID && $stationID[0]=="s"){
          $serial = substr(IPS_GetProperty($vidInstance ,"RINCON"), 7,12);
          $image = preg_replace('#(.*)<LOGO>(.*?)\</LOGO>(.*)#is','$2',@file_get_contents("http://opml.radiotime.com/Describe.ashx?c=nowplaying&id=".$stationID."&partnerId=IAeIhU42&serial=".$serial));
        }
        $detailHTML = "<div align=\"right\">
                       <table>
                         <tr>
                           <td valign=\"top\">
                             <table border=\"0\">
                               <tr><td align=\"right\"><b>".$positionInfo['streamContent']."</b></td></tr>
                               <tr><td>&nbsp;</td></tr>
                               <tr><td align=\"right\">".$mediaInfo['title']."</td></tr>
                             </table>
                           </td>
                           <td><img src=\"".$image."\"></td>
                         </tr>
                       </table>
                       </div>";
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
        $detailHTML = "<div align=\"right\">
                       <table>
                         <tr>
                           <td valign=\"top\">
                             <table border=\"0\">
                               <tr><td align=\"right\"><b>".$positionInfo['title']."</b></td></tr>
                               <tr><td>&nbsp;</td></tr>
                               <tr><td align=\"right\">".$positionInfo['artist']."</td></tr>
                               <tr><td align=\"right\">".$positionInfo['album']."</td></tr>
                               <tr><td align=\"right\">".$positionInfo['RelTime']." / ".$positionInfo['TrackDuration']."</td></tr>
                               <tr><td align=\"right\"><progress value=\"".$currentPositionSeconds."\" max=\"".$durationSeconds."\"></progress></td></tr>
                             </table>
                           </td>
                           <td><img src=\"".@$positionInfo['albumArtURI']."\" height=\"150\"></td>
                         </tr>
                       </table>
                       </div>";
      }
    }
    @SetValueString($vidDetails, $detailHTML);
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
