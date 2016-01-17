<?
class Sonos extends IPSModule
{
    
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("IPAddress", "");
        $this->RegisterPropertyInteger("TimeOut", 500);
        $this->RegisterPropertyInteger("DefaultVolume", 15);
        $this->RegisterPropertyBoolean("GroupCoordinator", false);
        $this->RegisterPropertyBoolean("GroupForcing", false);
        $this->RegisterPropertyBoolean("MuteControl", false);
        $this->RegisterPropertyBoolean("LoudnessControl", false);
        $this->RegisterPropertyBoolean("BassControl", false);
        $this->RegisterPropertyBoolean("TrebleControl", false);
        $this->RegisterPropertyBoolean("BalanceControl", false);
        $this->RegisterPropertyBoolean("SleeptimerControl", false);
        $this->RegisterPropertyBoolean("PlaylistControl", false);
        $this->RegisterPropertyString("FavoriteStation", "");
        $this->RegisterPropertyString("WebFrontStations", "<all>");
        $this->RegisterPropertyString("RINCON", "");
       
    }
    
    public function ApplyChanges()
    {
        $ipAddress = $this->ReadPropertyString("IPAddress");
        if ($ipAddress){
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://'.$ipAddress.':1400/xml/device_description.xml'
            ));

            $result = curl_exec($curl);

            if(!curl_exec($curl))  die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        }

        //Never delete this line!
        parent::ApplyChanges();
        
        // Start create profiles
        $this->RegisterProfileIntegerEx("Status.SONOS", "Information", "", "", Array(
                                             Array(0, "Prev",       "", -1),
                                             Array(1, "Play",       "", -1),
                                             Array(2, "Pause",      "", -1),
                                             Array(3, "Stop",       "", -1),
                                             Array(4, "Next",       "", -1),
                                             Array(5, "Transition", "", -1)
        ));
        $this->RegisterProfileInteger("Volume.SONOS", "Intensity", "", " %",   0, 100, 1);
        $this->RegisterProfileInteger("Tone.SONOS",   "Intensity", "", " %", -10,  10, 1);
        $this->RegisterProfileInteger("Balance.SONOS",   "Intensity", "", " %", -100,  100, 1);
        $this->RegisterProfileIntegerEx("Switch.SONOS", "Information", "", "", Array(
                                             Array(0, "Off", "", 0xFF0000),
                                             Array(1, "On",  "", 0x00FF00)
        ));
        
        //Build Radio Station Associations according to user settings
        include_once(__DIR__ . "/radio_stations.php");
        $Associations          = Array();
        $AvailableStations     = get_available_stations();
        $WebFrontStations      = $this->ReadPropertyString("WebFrontStations");
        $WebFrontStationsArray = array_map("trim", explode(",", $WebFrontStations));
        $FavoriteStation       = $this->ReadPropertyString("FavoriteStation");
        $Value                 = 1;
        
        foreach ( $AvailableStations as $key => $val ) {
            if (in_array( $val['name'], $WebFrontStationsArray) || $WebFrontStations === "<alle>" || $WebFrontStations === "<all>" ) {
                if  ( $val['name'] === $FavoriteStation ){
                    $Color = 0xFCEC00;
                } else {
                    $Color = -1;
                }
                $Associations[] = Array($Value++, $val['name'], "", $Color);
                // associations only support up to 32 variables
                if( $Value === 33 )
                        break;
            }
        }
        
        if(IPS_VariableProfileExists("Radio.SONOS"))
            IPS_DeleteVariableProfile("Radio.SONOS");
        
        $this->RegisterProfileIntegerEx("Radio.SONOS", "Speaker", "", "", $Associations);
        
        // Build Group Associations according Sonos Instance settings
        $allSonosInstances = IPS_GetInstanceListByModuleID("{F6F3A773-F685-4FD2-805E-83FD99407EE8}");
        $GroupAssociations = Array(Array(0, "none", "", -1));
        
        foreach($allSonosInstances as $key=>$SonosID) {
            if (@IPS_GetProperty($SonosID, "GroupCoordinator"))
                $GroupAssociations[] = Array($SonosID, IPS_GetName($SonosID), "", -1);
        }
        
        if(IPS_VariableProfileExists("Groups.SONOS")) 
            IPS_DeleteVariableProfile("Groups.SONOS");
        
        $this->RegisterProfileIntegerEx("Groups.SONOS", "Network", "", "", $GroupAssociations);
        // End Create Profiles     
   
        // Start Register variables and Actions
        // 1) general availabe
        $this->RegisterVariableString("nowPlaying", "nowPlaying", "", 20);
        $this->RegisterVariableInteger("Radio", "Radio", "Radio.SONOS", 21);
        $this->RegisterVariableInteger("Status", "Status", "Status.SONOS", 29);
        $this->RegisterVariableInteger("Volume", "Volume", "Volume.SONOS", 30);

        $this->EnableAction("Radio");
        $this->EnableAction("Status");
        $this->EnableAction("Volume");

        // 2) Add/Remove according to feature activation
        // create link list for deletion of liks if target is deleted
        $links = Array();
        foreach( IPS_GetLinkList() as $key=>$LinkID ){
            $links[] =  Array( ('LinkID') => $LinkID, ('TargetID') =>  IPS_GetLink($LinkID)['TargetID'] );
        }
        
          
        // 2a) Bass
        if ($this->ReadPropertyBoolean("BassControl")){
            $this->RegisterVariableInteger("Bass", "Bass", "Tone.SONOS", 36);
            $this->EnableAction("Bass");
        }else{
            $this->removeVariableAction("Bass", $links);
        }

        // 2b) Treble
        if ($this->ReadPropertyBoolean("TrebleControl")){
            $this->RegisterVariableInteger("Treble", "Treble", "Tone.SONOS", 37);
            $this->EnableAction("Treble");
        }else{
            $this->removeVariableAction("Treble", $links);
        }

        // 2c) Mute
        if ($this->ReadPropertyBoolean("MuteControl")){
            $this->RegisterVariableInteger("Mute","Mute", "Switch.SONOS", 31);
            $this->EnableAction("Mute");
        }else{
            $this->removeVariableAction("Mute", $links);
        }

        // 2d) Loudness
        if ($this->ReadPropertyBoolean("LoudnessControl")){
            $this->RegisterVariableInteger("Loudness", "Loudness", "Switch.SONOS", 35);
            $this->EnableAction("Loudness");
        }else{
            $this->removeVariableAction("Loudness", $links);
        }

        // 2e) Balance
        if ($this->ReadPropertyBoolean("BalanceControl")){
            $this->RegisterVariableInteger("Balance", "Balance", "Balance.SONOS", 38);
            $this->EnableAction("Balance");
        }else{
            $this->removeVariableAction("Balance", $links);
        }
        
        // 2f Sleeptimer
        if ($this->ReadPropertyBoolean("SleeptimerControl")){
            $this->RegisterVariableInteger("Sleeptimer", "Sleeptimer", "", 39);
        }else{
            $this->removeVariable("Sleeptimer", $links);
        }
     
        // 2g Playlists
        if ($this->ReadPropertyBoolean("PlaylistControl")){
            if(!IPS_VariableProfileExists("Playlist.SONOS"))
                $this->RegisterProfileIntegerEx("Playlist.SONOS", "Database", "", "", Array());
            $this->RegisterVariableInteger("Playlist", "Playlist", "Playlist.SONOS", 22);
            $this->EnableAction("Playlist");
        }else{
            $this->removeVariable("Playlist", $links);
        }



        // 2h) GroupVolume, GroupMembers, MemberOfGroup
        if ( $this->ReadPropertyBoolean("GroupCoordinator")){
            IPS_SetHidden( $this->RegisterVariableString("GroupMembers", "GroupMembers", "", 10), true);
            $this->RegisterVariableInteger("GroupVolume", "GroupVolume", "Volume.SONOS", 11);
            $this->EnableAction("GroupVolume");
            $this->removeVariableAction("MemberOfGroup", $links);
        }else{
            $this->RegisterVariableInteger("MemberOfGroup", "MemberOfGroup", "Groups.SONOS", 12);
            $this->EnableAction("MemberOfGroup");
            $this->removeVariableAction("GroupVolume",  $links);
            $this->removeVariable(      "GroupMembers", $links);
        }
        
        // 2i) Hide/unhide MemberOfGroup depending on presence of GroupCoordinators
        if (sizeof($GroupAssociations) === 1){
            // hide MemberOfGroup
            foreach($allSonosInstances as $key=>$SonosID) {
                $GroupingID = @IPS_GetVariableIDByName("MemberOfGroup",$SonosID);
                if ($GroupingID){
                    IPS_SetHidden($GroupingID,true);
                }
            }
        }else{
            // unhide MemberOfGroup
            foreach($allSonosInstances as $key=>$SonosID) {
                $GroupingID = @IPS_GetVariableIDByName("MemberOfGroup",$SonosID);
                if ($GroupingID){
                    IPS_SetHidden($GroupingID,false);
                }
            }
        }
        // End Register variables and Actions
        
        // Start add scripts for regular status and grouping updates
        // 1) _updateStatus 
        $statusScriptID = @$this->GetIDForIdent("_updateStatus");
        if ( $statusScriptID === false ){
          $statusScriptID = $this->RegisterScript("_updateStatus", "_updateStatus", file_get_contents(__DIR__ . "/_updateStatus.php"), 98);
        }else{
          IPS_SetScriptContent($statusScriptID, file_get_contents(__DIR__ . "/_updateStatus.php"));
        }

        IPS_SetHidden($statusScriptID,true);
        IPS_SetScriptTimer($statusScriptID, 5); 

        // 2) _updateGrouping
        $groupingScriptID = @$this->GetIDForIdent("_updateGrouping");
        if ( $groupingScriptID === false ){
          $groupingScriptID = $this->RegisterScript("_updateGrouping", "_updateGrouping", file_get_contents(__DIR__ . "/_updateGrouping.php"), 99);
        }else{
          IPS_SetScriptContent($groupingScriptID, file_get_contents(__DIR__ . "/_updateGrouping.php"));
        }

        IPS_SetHidden($groupingScriptID,true);
        IPS_SetScriptTimer($groupingScriptID, 300); 

        // End add scripts for regular status and grouping updates
    }
    
    /**
    * Start of Module functions
    */

    public function ChangeGroupVolume($increment)
    {
        if (!$this->ReadPropertyBoolean("GroupCoordinator")) die("This function is only allowed for GroupCoordinators");

        $groupMembers        = GetValueString(IPS_GetObjectIDByName("GroupMembers",$this->InstanceID ));
        $groupMembersArray   = Array();
        if($groupMembers)
            $groupMembersArray = array_map("intval", explode(",",$groupMembers));
        $groupMembersArray[] = $this->InstanceID;
            
        foreach($groupMembersArray as $key=>$ID) {
          $newVolume = (GetValueInteger(IPS_GetObjectIDByName("Volume",$ID)) + $increment);
          if ($newVolume > 100){
              $newVolume = 100;
          }elseif($newVolume < 0){
              $newVolume = 0;
          } 
          try{
            SNS_SetVolume($ID, $newVolume );
          }catch (Exception $e){}
        }

        $GroupVolume = 0;
        foreach($groupMembersArray as $key=>$ID) {
          $GroupVolume += GetValueInteger(IPS_GetObjectIDByName("Volume", $ID));
        }

        SetValueInteger(IPS_GetObjectIDByName("GroupVolume", $this->InstanceID), intval(round($GroupVolume / sizeof($groupMembersArray))));
    }

    public function ChangeVolume($increment)
    {
        $newVolume = (GetValueInteger($this->GetIDForIdent("Volume")) + $increment);
        try{
          $this->SetVolume($newVolume);
        }catch (Exception $e){throw $e;}
    }

    public function DeleteSleepTimer()
    {
        if (!$this->ReadPropertyBoolean("SleeptimerControl")) die("This function is not enabled for this instance");
 
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetSleeptimer(0,0,0);
    }
    
    public function Next()
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->Next();
    }
    
    public function Pause()
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        SetValue($this->GetIDForIdent("Status"), 2);
        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);
        if($sonos->GetTransportInfo() == 1) $sonos->Pause();
    }

    public function Play()
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        SetValue($this->GetIDForIdent("Status"), 1);
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->Play();
    }

    public function PlayFiles(array $files, $volumeChange)
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);
    
        $positionInfo       = $sonos->GetPositionInfo();
        $mediaInfo          = $sonos->GetMediaInfo();
        $transportInfo      = $sonos->GetTransportInfo();
        $isGroupCoordinator = $this->ReadPropertyBoolean("GroupCoordinator");
        if($isGroupCoordinator){
          $volume = GetValueInteger($this->GetIDForIdent("GroupVolume")); 
        }else{
          $volume = GetValueInteger($this->GetIDForIdent("Volume")); 
        }

        //adjust volume if needed
        if($volumeChange != 0){
          // pause if playing
          if($transportInfo==1) $sonos->Pause(); 
          
          // volume request absolte or relative?
          if($volumeChange[0] == "+" || $volumeChange[0] == "-"){
            if($isGroupCoordinator){
              $this->changeGroupVolume($volumeChange);
            }else{
              $this->ChangeVolume($volumeChange);
            }
          }else{
            if($isGroupCoordinator){
              $this->SetGroupVolume($volumeChange);
            }else{
              $this->SetVolume($volumeChange); 
            }
          }

        }

        foreach ($files as $key => $file) {
          // only files on SMB share or http server can be used
          if (preg_match('/^\/\/[\w,.,\d,-]*\/\S*/',$file) == 1){
            $uri = "x-file-cifs:".$file;
          }elseif (preg_match('/^http:\/\/[\w,.,\d,-,:]*\/\S*/',$file) == 1){
            $uri = $file;
          }else{
            throw new Exception("File (".$file.") has to be located on a Samba share (e.g. //ipsymcon.fritz.box/tts/text.mp3) or a HTTP server (e.g. http://ipsymcon.fritz.box/tts/text.mp3)");
          }

          $sonos->SetAVTransportURI($uri);
          $sonos->Play();
          IPS_Sleep(500);
          $fileTransportInfo = $sonos->GetTransportInfo();
          while ($fileTransportInfo==1 || $fileTransportInfo==5){ 
            IPS_Sleep(200);
            $fileTransportInfo = $sonos->GetTransportInfo();
          }
        }

        // reset to what was playing before
        $sonos->SetAVTransportURI($mediaInfo["CurrentURI"],$mediaInfo["CurrentURIMetaData"]);
        if($positionInfo["Track"] > 1 )
          $sonos->Seek("TRACK_NR",$positionInfo["Track"]);
        if($positionInfo["TrackDuration"] != "0:00:00" )
          $sonos->Seek("REL_TIME",$positionInfo["RelTime"]);
 

        if($volumeChange != 0){
          // set back volume
          if($isGroupCoordinator){
            $this->SetGroupVolume($volume);
          }else{
            $this->SetVolume($volume); 
          }
        }

        if ($transportInfo==1){
          $sonos->Play();
        }
    }

    public function Previous()
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->Previous();
    }
    
    public function SetAnalogInput($input_instance)
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        if(@GetValue($this->GetIDForIdent("MemberOfGroup")))
          $this->SetGroup(0);

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);
        
        $sonos->SetAVTransportURI("x-rincon-stream:".IPS_GetProperty($input_instance ,"RINCON"));
        $sonos->Play();
    }

    public function SetBalance($balance)	
    {
        if (!$this->ReadPropertyBoolean("BalanceControl")) die("This function is not enabled for this instance");

        SetValue($this->GetIDForIdent("Balance"), $balance);

        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        $leftVolume  = 100;
        $rightVolume = 100;     
        if ( $balance < 0 ){
          $rightVolume = 100 + $balance;
        }else{
          $leftVolume  = 100 - $balance;
        }

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = (new SonosAccess($ip));
        $sonos->SetVolume($leftVolume,'LF');
        $sonos->SetVolume($rightVolume,'RF');
    }
    
    public function SetBass($bass)
    {
        if (!$this->ReadPropertyBoolean("BassControl")) die("This function is not enabled for this instance");
 
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        SetValue($this->GetIDForIdent("Bass"), $bass);
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetBass($bass);
    }

    public function SetDefaultGroupVolume()
    {
        if (!$this->ReadPropertyBoolean("GroupCoordinator")) die("This function is only allowed for GroupCoordinators");

        $groupMembers        = GetValueString(IPS_GetObjectIDByName("GroupMembers",$this->InstanceID ));
        $groupMembersArray   = Array();
        if($groupMembers)
            $groupMembersArray = array_map("intval", explode(",",$groupMembers));
        $groupMembersArray[] = $this->InstanceID;

        foreach($groupMembersArray as $key=>$ID) {
          try{
            SNS_SetDefaultVolume($ID);
          }catch (Exception $e) {}
        }
        
        $GroupVolume = 0;
        foreach($groupMembersArray as $key=>$ID) {
          $GroupVolume += GetValueInteger(IPS_GetObjectIDByName("Volume", $ID));
        }

        SetValueInteger(IPS_GetObjectIDByName("GroupVolume", $this->InstanceID), intval(round($GroupVolume / sizeof($groupMembersArray))));
    }

    public function SetDefaultVolume()
    {
        try{
          $this->SetVolume($this->ReadPropertyInteger("DefaultVolume"));
        }catch(Exception $e){throw $e;}
    }
    
    public function SetGroup($groupCoordinator)
    {
        if ($this->ReadPropertyBoolean("GroupCoordinator")) return;

        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        // get variable of coordinator members to be updated
        if ($groupCoordinator){
            $groupMembersID = @IPS_GetObjectIDByIdent("GroupMembers",$groupCoordinator);
            $uri            = "x-rincon:".IPS_GetProperty($groupCoordinator ,"RINCON");
        }else{
            $groupMembersID = @IPS_GetObjectIDByIdent("GroupMembers",GetValue($this->GetIDForIdent("MemberOfGroup")));
            $uri            = "";
        }
        
        // update coordinator members
        SetValue($this->GetIDForIdent("MemberOfGroup"), $groupCoordinator);
        
        if($groupMembersID){
            $currentMembers = explode(",",GetValueString($groupMembersID));
            $currentMembers = array_filter($currentMembers, function($v) { return $v != ""; });
            $currentMembers = array_filter($currentMembers, function($v) { return $v != $this->InstanceID ; });
            if($groupCoordinator)
                $currentMembers[] = $this->InstanceID;
            
            SetValueString($groupMembersID,implode(",",$currentMembers));
        }
        
        // Set relevant variables to hidden/unhidden
        if ($groupCoordinator){
            $hidden = true ;
        }else{
            $hidden = false ;
        }
        IPS_SetHidden($this->GetIDForIdent("nowPlaying"),$hidden);
        IPS_SetHidden($this->GetIDForIdent("Radio"),$hidden);
        IPS_SetHidden($this->GetIDForIdent("Status"),$hidden);
        IPS_SetHidden($this->GetIDForIdent("Sleeptimer"),$hidden);
        
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetAVTransportURI($uri);
    }

    public function SetGroupVolume($volume)
    {
        if (!$this->ReadPropertyBoolean("GroupCoordinator")) die("This function is only allowed for GroupCoordinators");

        $this->ChangeGroupVolume($volume - GetValue($this->GetIDForIdent("GroupVolume")));
    }

    public function SetLoudness($loudness)
    {
        if (!$this->ReadPropertyBoolean("LoudnessControl")) die("This function is not enabled for this instance");

        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");
 
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetLoudness($loudness);
        SetValue($this->GetIDForIdent("Loudness"), $loudness);
    }

    public function SetMute($mute)
    {
        if (!$this->ReadPropertyBoolean("MuteControl")) die("This function is not enabled for this instance");

        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        SetValue($this->GetIDForIdent("Mute"), $mute);
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetMute($mute);
    }
    
    public function SetPlaylist($name){
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        if(@GetValue($this->GetIDForIdent("MemberOfGroup")))
          $this->SetGroup(0);

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);

        $uri = '';
        foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('SQ:')['Result']))->container as $container) {
            if ($container->xpath('dc:title')[0] == $name){
              $uri = (string)$container->res;
              break;
            }
        }  

        if($uri === '')
            throw new Exception('Playlist \''.$name.'\' not found');

        $sonos->ClearQueue();
        $sonos->AddToQueue($uri);
        $sonos->SetAVTransportURI('x-rincon-queue:'.$this->ReadPropertyString("RINCON").'#0');
        $sonos->Play();

    }

    public function SetRadioFavorite()
    {
        $this->SetRadio($this->ReadPropertyString("FavoriteStation"));
    }
    
    public function SetRadio($radio)
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        if(@GetValue($this->GetIDForIdent("MemberOfGroup")))
          $this->SetGroup(0);

        include_once(__DIR__ . "/sonosAccess.php");
        include_once(__DIR__ . "/radio_stations.php");
        $sonos = new SonosAccess($ip);
        $sonos->SetRadio( get_station_url($radio), $radio);
        $sonos->Play();
    }
    
    public function SetSleepTimer($minutes)
    {
        if (!$this->ReadPropertyBoolean("SleeptimerControl")) die("This function is not enabled for this instance");

        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        $hours = 0;

        while( $minutes > 59 ){
          $hours   = $hours + 1;
          $minutes = $minutes - 60;
        }

        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetSleeptimer($hours,$minutes,0);
    }

    public function SetSpdifInput($input_instance)
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        if(@GetValue($this->GetIDForIdent("MemberOfGroup")))
          $this->SetGroup(0);

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);
        
        $sonos->SetAVTransportURI("x-sonos-htastream:".IPS_GetProperty($input_instance ,"RINCON").":spdif");
        $sonos->Play();
    }

    public function SetTreble($treble)	
    {
        if (!$this->ReadPropertyBoolean("TrebleControl")) die("This function is not enabled for this instance");

        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        SetValue($this->GetIDForIdent("Treble"), $treble);
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetTreble($treble);
    }
    
    public function SetVolume($volume)
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        SetValue($this->GetIDForIdent("Volume"), $volume);
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetVolume($volume);
    }

    public function Stop()
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        SetValue($this->GetIDForIdent("Status"), 3);
        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);
        if($sonos->GetTransportInfo() == 1) $sonos->Stop();
    }

    public function UpdatePlaylists()
    {
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");
        if ($timeout && Sys_Ping($ip, $timeout) != true)
           throw new Exception("Sonos Box ".$ip." is not available");

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);

        $Associations          = Array();
        $Value                 = 1;

        foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('SQ:')['Result']))->container as $container) {

            $Associations[] = Array($Value++, (string)$container->xpath('dc:title')[0], "", -1);
            // associations only support up to 32 variables
            if( $Value === 33 ) break;
            
        }


        if(IPS_VariableProfileExists("Playlist.SONOS"))
            IPS_DeleteVariableProfile("Playlist.SONOS");

        $this->RegisterProfileIntegerEx("Playlist.SONOS", "Database", "", "", $Associations);
      
    }
    
    /**
    * End of Module functions
    */

    public function RequestAction($Ident, $Value)
    {
        switch($Ident) {
            case "Balance":
                $this->SetBalance($Value);
                break;
            case "Bass":
                $this->SetBass($Value);
                break;
            case "GroupVolume":
                $this->SetGroupVolume($Value);
                break;
            case "Loudness":
                $this->SetLoudness($Value);
                break;
            case "MemberOfGroup":
                $this->SetGroup($Value);
                break;
            case "Mute":
                $this->SetMute($Value);
                break;
            case "Playlist":
                $this->SetPlaylist(IPS_GetVariableProfile("Playlist.SONOS")['Associations'][$Value-1]['Name']);
                break;
            case "Radio":
                $this->SetRadio(IPS_GetVariableProfile("Radio.SONOS")['Associations'][$Value-1]['Name']);
                SetValue($this->GetIDForIdent($Ident), $Value);
                break;
            case "Status":
                switch($Value) {
                    case 0: //Prev
                        $this->Previous();
                        break;
                    case 1: //Play
                        $this->Play();
                        break;
                    case 2: //Pause
                        $this->Pause();
                        break;
                    case 3: //Stop
                        $this->Stop();
                        break;
                    case 4: //Next
                        $this->Next();
                        break;
                }
                break;
            case "Treble":
                $this->SetTreble($Value);
                break;
            case "Volume":
                $this->SetVolume($Value);
                break;
            default:
                throw new Exception("Invalid ident");
        }
    }
    
    protected function removeVariable($name, $links){
        $vid = @$this->GetIDForIdent($name);
        if ($vid){
            // delete links to Variable
            foreach( $links as $key=>$value ){
                if ( $value['TargetID'] === $vid )
                     IPS_DeleteLink($value['LinkID']);
            }
            $this->UnregisterVariable($name);
        }
    }

    protected function removeVariableAction($name, $links){
        $vid = @$this->GetIDForIdent($name);
        if ($vid){
            // delete links to Variable
            foreach( $links as $key=>$value ){
                if ( $value['TargetID'] === $vid )
                     IPS_DeleteLink($value['LinkID']);
            }
            $this->DisableAction($name);
            $this->UnregisterVariable($name);
        }
    }
 
    //Remove on next Symcon update
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
        
        if(!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if($profile['ProfileType'] != 1)
            throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
        
    }
    
    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        
        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
    }
}
?>
