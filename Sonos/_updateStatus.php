<?
include_once("../modules/SymconSonos/Sonos/sonosAccess.php");
include_once("../modules/SymconSonos/Sonos/radio_stations.php");

$ip      = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "IPAddress");
$timeout = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "TimeOut");

if ( !$timeout || Sys_Ping($ip, $timeout) == true ) {

    $sonos = new SonosAccess($ip);

    $status = $sonos->GetTransportInfo();
    SetValueInteger(IPS_GetObjectIDByName("Volume", IPS_GetParent($_IPS["SELF"])), $sonos->GetVolume());

    if (IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "MuteControl"))
        SetValueInteger(IPS_GetObjectIDByName("Mute", IPS_GetParent($_IPS["SELF"])), $sonos->GetMute());

    if (IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "LoudnessControl"))
        SetValueInteger(IPS_GetObjectIDByName("Loudness", IPS_GetParent($_IPS["SELF"])), $sonos->GetLoudness());

    if (IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "BassControl"))
        SetValueInteger(IPS_GetObjectIDByName("Bass", IPS_GetParent($_IPS["SELF"])), $sonos->GetBass());

    if (IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "TrebleControl"))
        SetValueInteger(IPS_GetObjectIDByName("Treble", IPS_GetParent($_IPS["SELF"])), $sonos->GetTreble());

    if (IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "BalanceControl")){
        $leftVolume  = $sonos->GetVolume("LF");
        $rightVolume = $sonos->GetVolume("RF");

        if ( $leftVolume == $rightVolume ){
          SetValueInteger(IPS_GetObjectIDByName("Balance", IPS_GetParent($_IPS["SELF"])), 0);
        }elseif ( $leftVolume > $rightVolume ){
          SetValueInteger(IPS_GetObjectIDByName("Balance", IPS_GetParent($_IPS["SELF"])), $rightVolume - 100 );
        }else{
          SetValueInteger(IPS_GetObjectIDByName("Balance", IPS_GetParent($_IPS["SELF"])), 100 - $leftVolume );
        }
    }

    $MemberOfGroupID = @IPS_GetObjectIDByName("MemberOfGroup", IPS_GetParent($_IPS["SELF"]));
    $MemberOfGroup = 0;
    if ($MemberOfGroupID)
    $MemberOfGroup = GetValueInteger($MemberOfGroupID);

    if ($MemberOfGroup){
        // If Sonos is member of a group, use values of Group Coordinator
        SetValueInteger(IPS_GetObjectIDByName("Status", IPS_GetParent($_IPS["SELF"])), GetValueInteger(IPS_GetObjectIDByName("Status", $MemberOfGroup)));
        $actuallyPlaying = GetValueString(IPS_GetObjectIDByName("nowPlaying", $MemberOfGroup));
        SetValueInteger(IPS_GetObjectIDByName("Radio", IPS_GetParent($_IPS["SELF"])), GetValueInteger(IPS_GetObjectIDByName("Radio", $MemberOfGroup)));
        if (IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "SleeptimerControl")){
          SetValueInteger(IPS_GetObjectIDByName("Sleeptimer", IPS_GetParent($_IPS["SELF"])), @GetValueInteger(IPS_GetObjectIDByName("Sleeptimer", $MemberOfGroup)));
        }
    }else{
        SetValueInteger(IPS_GetObjectIDByName("Status", IPS_GetParent($_IPS["SELF"])), $status);
        // Titelanzeige
        $currentStation = 0;

        if ( $status <> 1 ){
            // No title if not playing
            $actuallyPlaying = "";
        }else{
            $positionInfo = $sonos->GetPositionInfo();
            $mediaInfo    = $sonos->GetMediaInfo();

            if (strlen($positionInfo["streamContent"]) <> 0){
                $actuallyPlaying = $positionInfo["streamContent"];
            } else {
                $actuallyPlaying = utf8_decode($positionInfo["title"]." | ".$positionInfo["artist"]);
            }

            // start find current Radio in VariableProfile
            $radioStations       = get_available_stations();
            $playingRadioStation = '';
            foreach ($radioStations as $radioStation) {
              if($radioStation["url"] == htmlspecialchars_decode($mediaInfo["CurrentURI"])){
            	 $playingRadioStation = $radioStation["name"];
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
        SetValueInteger(IPS_GetObjectIDByName("Radio", IPS_GetParent($_IPS["SELF"])), $currentStation);

        // Sleeptimer
        if (IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "SleeptimerControl")){
            $sleeptimer = $sonos->GetSleeptimer();
            if($sleeptimer){
                $SleeptimerArray = explode(":",$sonos->GetSleeptimer());

                $SleeptimerMinutes = $SleeptimerArray[0]*60+$SleeptimerArray[1];
                if($SleeptimerArray[2])
                    $SleeptimerMinutes = $SleeptimerMinutes + 1;
            }else{
                $SleeptimerMinutes = 0;
            }

            SetValueInteger(IPS_GetObjectIDByName("Sleeptimer", IPS_GetParent($_IPS["SELF"])), $SleeptimerMinutes);
        }
        
    }

    $nowPlaying   = GetValueString(IPS_GetObjectIDByName("nowPlaying", IPS_GetParent($_IPS["SELF"])));

    if ($actuallyPlaying <> $nowPlaying) {
        SetValueString(IPS_GetObjectIDByName("nowPlaying", IPS_GetParent($_IPS["SELF"])), $actuallyPlaying);
    }
}

// Set Group Volume
if(IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "GroupCoordinator")){
  $groupMembers        = GetValueString(IPS_GetObjectIDByName("GroupMembers", IPS_GetParent($_IPS["SELF"])));
  $groupMembersArray   = Array();
  if($groupMembers)
    $groupMembersArray = array_map("intval", explode(",",$groupMembers));
  $groupMembersArray[] = IPS_GetParent($_IPS["SELF"]);

  $GroupVolume = 0;
  foreach($groupMembersArray as $key=>$ID) {
    $GroupVolume += GetValueInteger(IPS_GetObjectIDByName("Volume", $ID));
  }
  
  SetValueInteger(IPS_GetObjectIDByName("GroupVolume", IPS_GetParent($_IPS["SELF"])), intval(round($GroupVolume / sizeof($groupMembersArray))));
}
?>
