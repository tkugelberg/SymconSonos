<?
include_once("../modules/SymconSonos/Sonos/sonosAccess.php");

$sonosInstanceID       = IPS_GetParent($_IPS["SELF"]);
$memberOfGoup          = GetValueInteger(IPS_GetObjectIDByName("MemberOfGroup", $sonosInstanceID));
$coordinatorInIPS      = GetValueBoolean(IPS_GetObjectIDByName("Coordinator", $sonosInstanceID));
$forceGrouping         = IPS_GetProperty($sonosInstanceID, "GroupForcing");
$ipAddress             = gethostbyname(IPS_GetProperty($sonosInstanceID, "IPAddress"));
$timeout               = IPS_GetProperty($sonosInstanceID, "TimeOut");
$frequency             = IPS_GetProperty($sonosInstanceID, "UpdateGroupingFrequency");
$frequencyNotAvailable = IPS_GetProperty($sonosInstanceID, "UpdateGroupingFrequencyNA");
$rinconMapping         = Array();
$allSonosInstances     = IPS_GetInstanceListByModuleID("{F6F3A773-F685-4FD2-805E-83FD99407EE8}");

// If the Sonos instance is not available update of grouping makes no sense
if ( $timeout && Sys_Ping($ipAddress, $timeout) == false ){
    // If the Box is not available, only ask every 15 Minutes...
    IPS_SetScriptTimer($_IPS["SELF"], $frequencyNotAvailable );
    die('Sonos instance '.$ipAddress.' is not available');
}

// If box is available reset to 120 Seconds interval
IPS_SetScriptTimer($_IPS["SELF"], $frequency);

$sonos = new SonosAccess($ipAddress);

$grouping = new SimpleXMLElement($sonos->GetZoneGroupState());

foreach($allSonosInstances as $key=>$SonosID) {
    $rincon = IPS_GetProperty($SonosID ,"RINCON");
	$coordinatorInSonos = false;
    foreach ($grouping->ZoneGroup as $zoneGroup){
    	if ( $zoneGroup->attributes()['Coordinator'] == $rincon ){  
		      $coordinatorInSonos = true; 
        }
		foreach ($zoneGroup->ZoneGroupMember as $zoneGroupMember){
		  if ( $zoneGroupMember->attributes()['UUID'] == $rincon){
		    $group = (string)$zoneGroup->attributes()['Coordinator'] ;
		    break;
		  }
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
