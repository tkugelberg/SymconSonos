<?
include_once("../modules/SymconSonos/Sonos/sonosAccess.php");

// Nothing to do if Instance is Group Coordinator
if(IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "GroupCoordinator")) return;

$groupForcing      = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "GroupForcing");
$rinconMapping     = Array();
$allSonosInstances = IPS_GetInstanceListByModuleID("{F6F3A773-F685-4FD2-805E-83FD99407EE8}");
$MemberOfGroupID   = @IPS_GetObjectIDByName("MemberOfGroup", IPS_GetParent($_IPS["SELF"]));
$MemberOfGroup     = 0;
if ($MemberOfGroupID)  $MemberOfGroup = GetValueInteger($MemberOfGroupID);

//ensure that all rincons are known
foreach($allSonosInstances as $key=>$SonosID) {
    $rincon = IPS_GetProperty($SonosID ,"RINCON");
    if (!$rincon){
        // Get RINCON
        // Not sure why, but when executed in ApplyChanges of module.php RINCON is not alway set
        $ipAddress = IPS_GetProperty($SonosID, "IPAddress");
        if ($ipAddress){
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "http://".$ipAddress.":1400/xml/device_description.xml"
            ));

            $result = curl_exec($curl);

            if(!curl_exec($curl)){
                continue;
            }

            $xmlr = new SimpleXMLElement($result);
            $rincon = str_replace ( "uuid:" , "" , $xmlr->device->UDN );
            IPS_SetProperty($SonosID, "RINCON", $rincon );
            IPS_ApplyChanges($SonosID);
        }
    }
    $rinconMapping[] = Array( ("ID") => $SonosID, ("RINCON") => $rincon );
    if ($SonosID === IPS_GetParent($_IPS["SELF"])) $ownRincon = $rincon;
}

$ipAddress = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "IPAddress");
$timeout   = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "TimeOut");

if ( !$timeout || Sys_Ping($ipAddress, $timeout) == true ) {

    $sonos                    = new SonosAccess($ipAddress);
    $sonosGroupCoordinator    = explode(":",$sonos->GetZoneGroupAttributes()["CurrentZoneGroupID"])[0];

    foreach($rinconMapping as $key=>$value) {
        if($value["RINCON"] === $sonosGroupCoordinator ){
            $sonosGroupCoordinatorID = $value["ID"] ;
            break;
        }
    }

    // If groupCoordinator in Sonos = this instance --> set ID to 0 (does not belong to group)
    if ($sonosGroupCoordinatorID === IPS_GetParent($_IPS["SELF"]))  $sonosGroupCoordinatorID = 0;

    if ( $sonosGroupCoordinatorID !== $MemberOfGroup ){
        if($groupForcing){
            SNS_SetGroup(IPS_GetParent($_IPS["SELF"]),$MemberOfGroup);
            }else{
            SNS_SetGroup(IPS_GetParent($_IPS["SELF"]),$sonosGroupCoordinatorID);
        }
    }
}
?>
