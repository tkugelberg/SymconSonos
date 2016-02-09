<?

$sonosInstanceID   = IPS_GetParent($_IPS["SELF"]);
$memberOfGoup      = GetValueInteger(IPS_GetObjectIDByName("MemberOfGroup", $sonosInstanceID));
$coordinatorInIPS  = GetValueBoolean(IPS_GetObjectIDByName("Coordinator", $sonosInstanceID));
$forceGrouping     = IPS_GetProperty($sonosInstanceID, "GroupForcing");
$ipAddress         = IPS_GetProperty($sonosInstanceID, "IPAddress");
$timeout           = IPS_GetProperty($sonosInstanceID, "TimeOut");
$rinconMapping     = Array();
$allSonosInstances = IPS_GetInstanceListByModuleID("{F6F3A773-F685-4FD2-805E-83FD99407EE8}");

// If the Sonos instance is not available update of grouping makes no sense
if ( $timeout && Sys_Ping($ipAddress, $timeout) == false )
    die('Sonos instance '.$ipAddress.' is not available');

$topology = new SimpleXMLElement(file_get_contents('http://'.$ipAddress.':1400/status/topology'));

foreach($allSonosInstances as $key=>$SonosID) {
    $rincon = IPS_GetProperty($SonosID ,"RINCON");
    foreach ($topology->ZonePlayers->ZonePlayer as $zonePlayer){
        if($zonePlayer->attributes()['uuid'] == $rincon){
            $group       = (string)$zonePlayer->attributes()['group'];
            if((string)$zonePlayer->attributes()['coordinator'] === "true"){
                $coordinatorInSonos = true;
            }else{
                $coordinatorInSonos = false;
            }
            break;
        }
    }
    $instance = Array( ("ID")          => $SonosID,
                       ("RINCON")      => $rincon,
                       ("COORDINATOR") => $coordinatorInSonos,
                       ("GROUP")       => $group  );
    $rinconMapping[] = $instance;
    if($SonosID === $sonosInstanceID){
        $mySettings       = $instance;
        if($memberOfGoup === 0) $MemberOfGroupIPS = $instance;
    }
    if($SonosID === $memberOfGoup){
        $MemberOfGroupIPS = $instance;
    }
}

foreach($rinconMapping as $key=>$instance){
    if( $instance['GROUP'] === $mySettings['GROUP'] && $instance['COORDINATOR'] == true){
        $MemberOfGroupSonos = $instance;
        break;
    }
}

if(!isset($MemberOfGroupSonos))
    die ("Coordinator Instance for Group of Sonos Instance ".$sonosInstanceID." not found");

if($MemberOfGroupIPS['ID'] != $MemberOfGroupSonos['ID']){
    if($forceGrouping){
        $groupToSet = $MemberOfGroupIPS['ID'];
    }else{
        $groupToSet = $MemberOfGroupSonos['ID'];
    }
    SNS_SetGroup($sonosInstanceID,$groupToSet);
}elseif($mySettings['COORDINATOR'] != $coordinatorInIPS){
    if(!$mySettings['COORDINATOR']){
        SetValueBoolean(IPS_GetObjectIDByName("Coordinator", $sonosInstanceID),false);
        @IPS_SetVariableProfileAssociation("Groups.SONOS", $sonosInstanceID, "", "", -1);
    }else{
        SetValueBoolean(IPS_GetObjectIDByName("Coordinator", $sonosInstanceID),true);
        @IPS_SetVariableProfileAssociation("Groups.SONOS", $sonosInstanceID, IPS_GetName($sonosInstanceID), "", -1);
    }
} 

?>
