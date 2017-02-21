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
        $this->RegisterPropertyInteger("TimeOut", 1000);
        $this->RegisterPropertyInteger("DefaultVolume", 15);
        $this->RegisterPropertyInteger("UpdateStatusFrequency", 5);
        $this->RegisterPropertyInteger("UpdateStatusFrequencyNA", 300);
        $this->RegisterPropertyInteger("UpdateGroupingFrequency", 120);
        $this->RegisterPropertyInteger("UpdateGroupingFrequencyNA", 900);
        $this->RegisterPropertyBoolean("GroupForcing", false);
        $this->RegisterPropertyBoolean("MuteControl", false);
        $this->RegisterPropertyBoolean("LoudnessControl", false);
        $this->RegisterPropertyBoolean("BassControl", false);
        $this->RegisterPropertyBoolean("TrebleControl", false);
        $this->RegisterPropertyBoolean("BalanceControl", false);
        $this->RegisterPropertyBoolean("SleeptimerControl", false);
        $this->RegisterPropertyBoolean("PlayModeControl", false);
        $this->RegisterPropertyInteger("PlaylistImport", 0);
        $this->RegisterPropertyBoolean("DetailedInformation", false);
        $this->RegisterPropertyBoolean("ForceOrder", false);
        $this->RegisterPropertyBoolean("IncludeTunein", "");
        $this->RegisterPropertyString("FavoriteStation", "");
        $this->RegisterPropertyString("WebFrontStations", "");
        $this->RegisterPropertyString("RINCON", "");
       
    }
    
    public function ApplyChanges()
    {
        $ipAddress = $this->ReadPropertyString("IPAddress");
        if ($ipAddress){
            $curl = curl_init();
            curl_setopt_array($curl, array( CURLOPT_RETURNTRANSFER => 1,
                                            CURLOPT_URL => 'http://'.$ipAddress.':1400/xml/device_description.xml' ));

            if(!curl_exec($curl))  die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            if(!$this->ReadPropertyString("RINCON"))
            {
                $this->UpdateRINCON();
                return true;
            }
        }

        //Never delete this line!
        parent::ApplyChanges();

                        
        
        // Start create profiles
        $this->RegisterProfileIntegerEx("Status.SONOS", "Information", "", "",   Array( Array(0, "prev",       "", -1),
                                                                                        Array(1, "play",       "", -1),
                                                                                        Array(2, "pause",       "", -1),
                                                                                        Array(3, "stop",       "", -1),
                                                                                        Array(4, "next",       "", -1),
                                                                                        Array(5, "trans",       "", -1) ));
        $this->RegisterProfileIntegerEx("PlayMode.SONOS", "Information", "", "",   Array( Array(0, "Normal",             "", -1),
                                                                                          Array(1, "Repeat all",         "", -1),
                                                                                          Array(2, "Repeat one",         "", -1),
                                                                                          Array(3, "Shuffle no repeat",  "", -1),
                                                                                          Array(4, "Shuffle",            "", -1),
                                                                                          Array(5, "Shuffle repeat one", "", -1) ));
        $this->RegisterProfileInteger("Volume.SONOS",   "Intensity",   "", " %",    0, 100, 1);
        $this->RegisterProfileInteger("Tone.SONOS",     "Intensity",   "", " %",  -10,  10, 1);
        $this->RegisterProfileInteger("Balance.SONOS",  "Intensity",   "", " %", -100, 100, 1);
        $this->RegisterProfileIntegerEx("Switch.SONOS", "Information", "",   "", Array( Array(0, "Off", "", 0xFF0000),
                                                                                        Array(1, "On",  "", 0x00FF00) ));
        
        //Build Radio Station Associations according to user settings
        if(!IPS_VariableProfileExists("Radio.SONOS"))
            $this->UpdateRadioStations();

        // Build Group Associations according Sonos Instance settings
        if(IPS_VariableProfileExists("Groups.SONOS"))
          IPS_DeleteVariableProfile("Groups.SONOS"); 
        $allSonosInstances = IPS_GetInstanceListByModuleID("{F6F3A773-F685-4FD2-805E-83FD99407EE8}");
        $GroupAssociations = Array(Array(0, "none", "", -1));

        foreach($allSonosInstances as $key=>$SonosID) {
            if (@GetValueBoolean(IPS_GetVariableIDByName("Coordinator",$SonosID)))
              $GroupAssociations[] = Array($SonosID, IPS_GetName($SonosID), "", -1);
        }

        $this->RegisterProfileIntegerEx("Groups.SONOS", "Network", "", "", $GroupAssociations);
        // End Create Profiles     
   
        // Start Register variables and Actions
        // with the following order:
        $positions = array ( 
                             ('Coordinator')     => 10,
                             ('GroupMembers')    => 11,
                             ('MemberOfGroup')   => 12,
                             ('GroupVolume')     => 13,
                             ('Details')         => 20,
                             ('CoverURL')        => 21,
                             ('ContentStream')   => 22,
                             ('Artist')          => 23,
                             ('Title')           => 24,
                             ('Album')           => 25,
                             ('TrackDuration')   => 26,
                             ('Position')        => 27,
                             ('StationID')       => 28,
                             ('nowPlaying')      => 29,
                             ('Radio')           => 40,
                             ('Playlist')        => 41,
                             ('Status')          => 49,
                             ('Volume')          => 50,
                             ('Mute')            => 51,
                             ('Loudness')        => 52,
                             ('Bass')            => 53,
                             ('Treble')          => 54,
                             ('Balance')         => 58,
                             ('Sleeptimer')      => 60,
                             ('PlayMode')        => 61,
                             ('Crossfade')       => 62,
                             ('_updateStatus')   => 98,
                             ('_updateGrouping') => 99
                           );
        // 1) general availabe
        IPS_SetHidden( $this->RegisterVariableBoolean("Coordinator", "Coordinator", "", $positions['Coordinator']), true);
        IPS_SetHidden( $this->RegisterVariableString("GroupMembers", "GroupMembers", "", $positions['GroupMembers']), true);
        $this->RegisterVariableInteger("MemberOfGroup", "MemberOfGroup", "Groups.SONOS", $positions['MemberOfGroup']);
        $this->RegisterVariableInteger("GroupVolume", "GroupVolume", "Volume.SONOS", $positions['GroupVolume']);
        $this->RegisterVariableString("nowPlaying", "nowPlaying", "", $positions['nowPlaying']);
        $this->RegisterVariableInteger("Radio", "Radio", "Radio.SONOS", $positions['Radio']);
        $this->RegisterVariableInteger("Status", "Status", "Status.SONOS", $positions['Status']);
        $this->RegisterVariableInteger("Volume", "Volume", "Volume.SONOS", $positions['Volume']);
        $this->EnableAction("GroupVolume");
        $this->EnableAction("MemberOfGroup");
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
            $this->RegisterVariableInteger("Bass", "Bass", "Tone.SONOS", $positions['Bass']);
            $this->EnableAction("Bass");
        }else{
            $this->removeVariableAction("Bass", $links);
        }

        // 2b) Treble
        if ($this->ReadPropertyBoolean("TrebleControl")){
            $this->RegisterVariableInteger("Treble", "Treble", "Tone.SONOS", $positions['Treble']);
            $this->EnableAction("Treble");
        }else{
            $this->removeVariableAction("Treble", $links);
        }

        // 2c) Mute
        if ($this->ReadPropertyBoolean("MuteControl")){
            $this->RegisterVariableInteger("Mute","Mute", "Switch.SONOS", $positions['Mute']);
            $this->EnableAction("Mute");
        }else{
            $this->removeVariableAction("Mute", $links);
        }

        // 2d) Loudness
        if ($this->ReadPropertyBoolean("LoudnessControl")){
            $this->RegisterVariableInteger("Loudness", "Loudness", "Switch.SONOS", $positions['Loudness']);
            $this->EnableAction("Loudness");
        }else{
            $this->removeVariableAction("Loudness", $links);
        }

        // 2e) Balance
        if ($this->ReadPropertyBoolean("BalanceControl")){
            $this->RegisterVariableInteger("Balance", "Balance", "Balance.SONOS", $positions['Balance']);
            $this->EnableAction("Balance");
        }else{
            $this->removeVariableAction("Balance", $links);
        }
        
        // 2f Sleeptimer
        if ($this->ReadPropertyBoolean("SleeptimerControl")){
            $this->RegisterVariableInteger("Sleeptimer", "Sleeptimer", "", $positions['Sleeptimer']);
        }else{
            $this->removeVariable("Sleeptimer", $links);
        }
     
        // 2g Playlists
        if ($this->ReadPropertyInteger("PlaylistImport")){
            if(!IPS_VariableProfileExists("Playlist.SONOS"))
                $this->RegisterProfileIntegerEx("Playlist.SONOS", "Database", "", "", Array());
            $this->RegisterVariableInteger("Playlist", "Playlist", "Playlist.SONOS", $positions['Playlist']);
            $this->EnableAction("Playlist");
        }else{
            $this->removeVariable("Playlist", $links);
        }

        // 2h) PlayMode + Crossfade
        if ($this->ReadPropertyBoolean("PlayModeControl")){
            $this->RegisterVariableInteger("PlayMode",  "PlayMode",  "PlayMode.SONOS", $positions['PlayMode']);
            $this->RegisterVariableInteger("Crossfade", "Crossfade", "Switch.SONOS",   $positions['Crossfade']);
            $this->EnableAction("PlayMode");
            $this->EnableAction("Crossfade");
        }else{
            $this->removeVariableAction("PlayMode", $links);
            $this->removeVariableAction("Crossfade", $links);
        }

        //2i) Detailed Now Playing informtion
        if ($this->ReadPropertyBoolean("DetailedInformation")){
            $this->RegisterVariableString("Details", "Details", "~HTMLBox", $positions['Details']);
            IPS_SetHidden($this->RegisterVariableString("CoverURL",      "CoverURL",      "",         $positions['CoverURL']),true);
            IPS_SetHidden($this->RegisterVariableString("ContentStream", "ContentStream", "",         $positions['ContentStream']),true);
            IPS_SetHidden($this->RegisterVariableString("Artist",        "Artist",        "",         $positions['Artist']),true);
            IPS_SetHidden($this->RegisterVariableString("Title",         "Title",         "",         $positions['Title']),true);
            IPS_SetHidden($this->RegisterVariableString("Album",         "Album",         "",         $positions['Album']),true);
            IPS_SetHidden($this->RegisterVariableString("TrackDuration", "TrackDuration", "",         $positions['TrackDuration']),true);
            IPS_SetHidden($this->RegisterVariableString("Position",      "Position",      "",         $positions['Position']),true);
            if(!@IPS_GetObjectIDByIdent("StationID", $this->InstanceID)){
              $vidStationID = $this->RegisterVariableString("StationID", "StationID", "", $positions['StationID']);
              IPS_SetHidden($vidStationID,true);
              //clear it 5 past the hour 
              $eid = IPS_CreateEvent(1);
              IPS_SetParent($eid, $vidStationID);
              IPS_SetEventCyclicTimeFrom($eid,0,5,0);
              IPS_SetEventCyclic($eid,0,0,0,3,3,1);
              IPS_SetEventScript($eid, "SetValueString($vidStationID,'');" );
              IPS_SetEventActive($eid, true);
            }
        }else{
            $this->removeVariableAction("Details",       $links);
            $this->removeVariableAction("CoverURL",      $links);
            $this->removeVariableAction("ContentStream", $links);
            $this->removeVariableAction("Artist",        $links);
            $this->removeVariableAction("Title",         $links);
            $this->removeVariableAction("Album",         $links);
            $this->removeVariableAction("TrackDuration", $links);
            $this->removeVariableAction("Position",      $links);
            $this->removeVariableAction("StationID",     $links);
        }

        // End Register variables and Actions
        
        // Start add scripts for regular status and grouping updates
        // 1) _updateStatus 
        $statusScriptID = @$this->GetIDForIdent("_updateStatus");
        if ( $statusScriptID === false ){
          $statusScriptID = $this->RegisterScript("_updateStatus", "_updateStatus", file_get_contents(__DIR__ . "/_updateStatus.php"), $positions['_updateStatus']);
        }else{
          IPS_SetScriptContent($statusScriptID, file_get_contents(__DIR__ . "/_updateStatus.php"));
        }

        IPS_SetHidden($statusScriptID,true);
        IPS_SetScriptTimer($statusScriptID, $this->ReadPropertyString("UpdateStatusFrequency")); 

        // 2) _updateGrouping
        $groupingScriptID = @$this->GetIDForIdent("_updateGrouping");
        if ( $groupingScriptID === false ){
          $groupingScriptID = $this->RegisterScript("_updateGrouping", "_updateGrouping", file_get_contents(__DIR__ . "/_updateGrouping.php"), $positions['_updateGrouping']);
        }else{
          IPS_SetScriptContent($groupingScriptID, file_get_contents(__DIR__ . "/_updateGrouping.php"));
        }

        IPS_SetHidden($groupingScriptID,true);
        IPS_SetScriptTimer($groupingScriptID, $this->ReadPropertyString("UpdateGroupingFrequency")); 

        // End add scripts for regular status and grouping updates

        // sorting
        if ($this->ReadPropertyBoolean("ForceOrder")){
            foreach($positions as $key=>$position) {
                $id = @$this->GetIDForIdent($key);
                if($id)
                    IPS_SetPosition($id, $position);
            } 
        }
    }
    
    /**
    * Start of Module functions
    */

    public function ChangeGroupVolume($increment)
    {
        if (!@GetValueBoolean($this->GetIDForIdent("Coordinator"))) die("This function is only allowed for Coordinators");

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
        $targetInstance = $this->findTarget();

        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            include_once(__DIR__ . "/sonosAccess.php");
            (new SonosAccess($ip))->SetSleeptimer(0,0,0);
        }else{
            SNS_DeleteSleepTimer($targetInstance);
        }
    }
    
    public function Next()
    {
        $targetInstance = $this->findTarget();

        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            include_once(__DIR__ . "/sonosAccess.php");
            (new SonosAccess($ip))->Next();
        }else{
            SNS_Next($targetInstance);
        }
    }
    
    public function Pause()
    {
        $targetInstance = $this->findTarget();

        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            SetValue($this->GetIDForIdent("Status"), 2);
            include_once(__DIR__ . "/sonosAccess.php");
            $sonos = new SonosAccess($ip);
            if($sonos->GetTransportInfo() == 1) $sonos->Pause();
        }else{
            SNS_Pause($targetInstance);
        }
    }

    public function Play()
    {
        $targetInstance = $this->findTarget();

        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            SetValue($this->GetIDForIdent("Status"), 1);
            include_once(__DIR__ . "/sonosAccess.php");
            (new SonosAccess($ip))->Play();
        }else{
            SNS_Play($targetInstance);
        }
    }

    public function PlayFiles(array $files, $volumeChange)
    {
        $ip = $this->getIP();

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);
    
        $positionInfo       = $sonos->GetPositionInfo();
        $mediaInfo          = $sonos->GetMediaInfo();
        $transportInfo      = $sonos->GetTransportInfo();
        $isGroupCoordinator = @GetValueBoolean($this->GetIDForIdent("Coordinator"));
        if($isGroupCoordinator){
          $volume = GetValueInteger($this->GetIDForIdent("GroupVolume")); 
        }else{
          $volume = GetValueInteger($this->GetIDForIdent("Volume")); 
        }

        //adjust volume if needed
        if($volumeChange != 0){
          // pause if playing or remove from group
          if(!$isGroupCoordinator){
            $this->SetGroup(0); 
          }elseif($transportInfo==1){
            try{
              $sonos->Pause();
            }catch (Exception $e){ 
              if ( $e->getMessage() != 'Error during Soap Call: UPnPError s:Client 701 (ERROR_AV_UPNP_AVT_INVALID_TRANSITION)') throw $e; 
            }
          }
          
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
        if($positionInfo["TrackDuration"] != "0:00:00" && $positionInfo["Track"] > 1)
          try {
            $sonos->Seek("TRACK_NR",$positionInfo["Track"]);
          } catch (Exception $e) { }
        if($positionInfo["TrackDuration"] != "0:00:00" && $positionInfo["RelTime"] != "NOT_IMPLEMENTED" )
          try {
            $sonos->Seek("REL_TIME",$positionInfo["RelTime"]);
          } catch (Exception $e) { }

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

    public function PlayFilesGrouping(array $instances, array $files, $volumeChange)
    {
        $ip = $this->getIP();

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos         = new SonosAccess($ip);
        $transportInfo = $sonos->GetTransportInfo();
        $volume        = GetValueInteger($this->GetIDForIdent("Volume"));

        // pause if playing
        if($transportInfo==1){
          try{
            $sonos->Pause();
          }catch (Exception $e){ 
            if ( $e->getMessage() != 'Error during Soap Call: UPnPError s:Client 701 (ERROR_AV_UPNP_AVT_INVALID_TRANSITION)') throw $e; 
          }
        }

        if($volumeChange != 0){
          // volume request absolte or relative?
          if($volumeChange[0] == "+" || $volumeChange[0] == "-"){
            $this->ChangeVolume($volumeChange);
          }else{
            $this->SetVolume($volumeChange);
          }
        }
        
    
        foreach ($instances as $instanceID => &$settings){
             $ip      = IPS_GetProperty($instanceID ,"IPAddress");
             $timeout = $this->ReadPropertyString("TimeOut");
             if ($timeout && Sys_Ping($ip, $timeout) != true){
                 if (Sys_Ping($ip, $timeout) != true){
                     $settings["available"] = false;
                     print $instanceID." is not available\n";
                     continue;
                 }
             }
             
             $settings["available"]     = true;
             $settings["sonos"]         = new SonosAccess($ip);
             $settings["mediaInfo"]     = $settings["sonos"]->GetMediaInfo();
             $settings["positionInfo"]  = $settings["sonos"]->GetPositionInfo();
             $settings["transportInfo"] = $settings["sonos"]->GetTransportInfo();
             $settings["group"]         = GetValueInteger(IPS_GetObjectIDByName("MemberOfGroup", $instanceID));
             $settings["volumeBefore"]  = GetValueInteger(IPS_GetObjectIDByName("Volume", $instanceID));

             if(isset($settings["volume"]) && $settings["volume"] != 0){
               // volume request absolte or relative?
               if($settings["volume"][0] == "+" || $settings["volume"][0] == "-"){
                 SNS_ChangeVolume($instanceID, $settings["volume"]);
               }else{
                 SNS_SetVolume($instanceID, $settings["volume"]);
               }
             }
             
             SNS_SetGroup($instanceID, $this->InstanceID);
        }
        unset($settings);

        $this->PlayFiles($files, 0);
 
        foreach ($instances as $instanceID => $settings){
          if($settings["available"] == false) continue;
          SNS_SetGroup($instanceID, $settings["group"]);
          $settings["sonos"]->SetAVTransportURI($settings["mediaInfo"]["CurrentURI"],$settings["mediaInfo"]["CurrentURIMetaData"]);
          if(@$settings["mediaInfo"]["Track"] > 1 )
            try {     
              $settings["sonos"]->Seek("TRACK_NR",$settings["mediaInfo"]["Track"]);
            } catch (Exception $e) { }
          if($settings["positionInfo"]["TrackDuration"] != "0:00:00" && $settings["positionInfo"]["RelTime"] != "NOT_IMPLEMENTED" )
            try {
              $settings["sonos"]->Seek("REL_TIME",$settings["positionInfo"]["RelTime"]);
            } catch (Exception $e) { }
          SNS_SetVolume($instanceID, $settings["volumeBefore"]);
          if($settings["transportInfo"]==1 && !$settings["group"]) SNS_Play($instanceID);
        }

        if($volumeChange != 0){
          // set back volume
          $this->SetVolume($volume); 
        }

        if($transportInfo==1) $sonos->Play();
    }

    public function Previous()
    {
        $targetInstance = $this->findTarget();

        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            include_once(__DIR__ . "/sonosAccess.php");
            (new SonosAccess($ip))->Previous();
        }else{
            SNS_Previous($targetInstance);
        }
    }
    
    public function RampToVolume($rampType,$volume)
    {
        $ip = $this->getIP();

        SetValue($this->GetIDForIdent("Volume"), $volume);
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->RampToVolume($rampType,$volume);
    }

    public function SetAnalogInput($input_instance)
    {
        $ip = $this->getIP();

        if(@GetValue($this->GetIDForIdent("MemberOfGroup")))
          $this->SetGroup(0);

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);
        
        $sonos->SetAVTransportURI("x-rincon-stream:".IPS_GetProperty($input_instance ,"RINCON"));
    }

    public function SetBalance($balance)	
    {
        $ip = $this->getIP();

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
        if (!$this->ReadPropertyBoolean("BalanceControl")) SetValue($this->GetIDForIdent("Balance"), $balance);
    }
    
    public function SetBass($bass)
    {
        $ip = $this->getIP();

        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetBass($bass);
        if (!$this->ReadPropertyBoolean("BassControl")) SetValue($this->GetIDForIdent("Bass"), $bass);
    }

    public function SetCrossfade($crossfade)
    {
        $targetInstance = $this->findTarget();
      
        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            include_once(__DIR__ . "/sonosAccess.php");
            (new SonosAccess($ip))->SetCrossfade($crossfade);
            if ($this->ReadPropertyBoolean("PlayModeControl")) SetValue($this->GetIDForIdent("Crossfade"), $crossfade);
        }else{
            SNS_SetCrossfade($targetInstance,$crossfade);
        }
    }

    public function SetDefaultGroupVolume()
    {
        if (!@GetValueBoolean($this->GetIDForIdent("Coordinator"))) die("This function is only allowed for Coordinators");

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
        // Instance has Memners, do nothing
        if(@GetValueString($this->GetIDForIdent("GroupMembers"))) return;
        // Do not try to assign to itself
        if($this->InstanceID === $groupCoordinator) $groupCoordinator = 0;

        $startGroupCoordinator = GetValue($this->GetIDForIdent("MemberOfGroup"));

        $ip = $this->getIP();

        // cleanup old group
        if($startGroupCoordinator){
            $groupMembersID = @IPS_GetObjectIDByIdent("GroupMembers",$startGroupCoordinator);
            $currentMembers = explode(",",GetValueString($groupMembersID));
            $currentMembers = array_filter($currentMembers, function($v) { return $v != ""; });
            $currentMembers = array_filter($currentMembers, function($v) { return $v != $this->InstanceID ; });
            SetValueString($groupMembersID,implode(",",$currentMembers));
            if(!count($currentMembers)){
                IPS_SetHidden(IPS_GetVariableIDByName("GroupVolume",$startGroupCoordinator),true);
                IPS_SetHidden(IPS_GetVariableIDByName("MemberOfGroup",$startGroupCoordinator),false);
            }
        }

        // get variable of coordinator members to be updated
        $currentMembers = Array();
        if($groupCoordinator){
            $groupMembersID = @IPS_GetObjectIDByIdent("GroupMembers",$groupCoordinator);
            $currentMembers = explode(",",GetValueString($groupMembersID));
            $currentMembers = array_filter($currentMembers, function($v) { return $v != ""; });
            $currentMembers = array_filter($currentMembers, function($v) { return $v != $this->InstanceID ; });
            if($groupCoordinator)
                $currentMembers[] = $this->InstanceID;

            SetValueString($groupMembersID,implode(",",$currentMembers));
            $uri            = "x-rincon:".IPS_GetProperty($groupCoordinator ,"RINCON");
            SetValueBoolean($this->GetIDForIdent("Coordinator"),false);
            @IPS_SetVariableProfileAssociation("Groups.SONOS", $this->InstanceID, "", "", -1);
        }else{
            $uri            = "";
            SetValueBoolean($this->GetIDForIdent("Coordinator"),true);
            @IPS_SetVariableProfileAssociation("Groups.SONOS", $this->InstanceID, IPS_GetName($this->InstanceID), "", -1);
        }
        
        // update coordinator members
        SetValue($this->GetIDForIdent("MemberOfGroup"), $groupCoordinator);
  
        
        // Set relevant variables to hidden/unhidden
        if ($groupCoordinator){
            $hidden = true;
            IPS_SetHidden(IPS_GetVariableIDByName("GroupVolume",$groupCoordinator),false);
            IPS_SetHidden(IPS_GetVariableIDByName("MemberOfGroup",$groupCoordinator),true);
        }else{
            $hidden = false;
        }
        @IPS_SetHidden($this->GetIDForIdent("nowPlaying"),$hidden);
        @IPS_SetHidden($this->GetIDForIdent("Radio"),$hidden);
        @IPS_SetHidden($this->GetIDForIdent("Playlist"),$hidden);
        @IPS_SetHidden($this->GetIDForIdent("PlayMode"),$hidden);
        @IPS_SetHidden($this->GetIDForIdent("Crossfade"),$hidden);
        @IPS_SetHidden($this->GetIDForIdent("Status"),$hidden);
        @IPS_SetHidden($this->GetIDForIdent("Sleeptimer"),$hidden);
        @IPS_SetHidden($this->GetIDForIdent("Details"),$hidden);
        // always hide GroupVolume, unhide executed on GroupCoordinator a few lines above
        @IPS_SetHidden(IPS_GetVariableIDByName("GroupVolume",$this->InstanceID),true);
        @IPS_SetHidden(IPS_GetVariableIDByName("MemberOfGroup",$this->InstanceID),false);

        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetAVTransportURI($uri);
    }

    public function SetGroupVolume($volume)
    {
        if (!@GetValueBoolean($this->GetIDForIdent("Coordinator"))) die("This function is only allowed for Coordinators");

        $this->ChangeGroupVolume($volume - GetValue($this->GetIDForIdent("GroupVolume")));
    }

    public function SetLoudness($loudness)
    {
        $ip = $this->getIP();
 
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetLoudness($loudness);
        if ($this->ReadPropertyBoolean("LoudnessControl")) SetValue($this->GetIDForIdent("Loudness"), $loudness);
    }

    public function SetMute($mute)
    {
        $ip = $this->getIP();

        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetMute($mute);
        if ($this->ReadPropertyBoolean("MuteControl")) SetValue($this->GetIDForIdent("Mute"), $mute);
    }
    
    public function SetPlaylist($name)
    {
        $ip = $this->getIP();

        if(@GetValue($this->GetIDForIdent("MemberOfGroup")))
          $this->SetGroup(0);

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);

        $uri = '';
        foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('SQ:','BrowseDirectChildren',999)['Result']))->container as $container) {
            if ($container->xpath('dc:title')[0] == $name){
              $uri = (string)$container->res;
              break;
            }
        }  

        if($uri === ''){
            foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('A:PLAYLISTS','BrowseDirectChildren',999)['Result']))->container as $container) {
                if (preg_replace($this->getPlaylistReplacementFrom(), $this->getPlaylistReplacementTo(), $container->xpath('dc:title')[0]) == $name){
                  $uri = (string)$container->res;
                  break;
                }
            }
        }

        if($uri === '')
            throw new Exception('Playlist \''.$name.'\' not found');

        $sonos->ClearQueue();
        $sonos->AddToQueue($uri);
        $sonos->SetAVTransportURI('x-rincon-queue:'.$this->ReadPropertyString("RINCON").'#0');

    }

    public function SetPlayMode($playMode)
    {
        $targetInstance = $this->findTarget();
      
        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            include_once(__DIR__ . "/sonosAccess.php");
            (new SonosAccess($ip))->SetPlayMode($playMode);
            if ($this->ReadPropertyBoolean("PlayModeControl")) SetValue($this->GetIDForIdent("PlayMode"), $playMode);
        }else{
            SNS_SetPlayMode($targetInstance,$playMode);
        }
    }

    public function SetRadioFavorite()
    {
        $this->SetRadio($this->ReadPropertyString("FavoriteStation"));
    }
    
    public function SetRadio($radio)
    {
        $ip = $this->getIP();

        if(@GetValue($this->GetIDForIdent("MemberOfGroup")))
          $this->SetGroup(0);

        include_once(__DIR__ . "/sonosAccess.php");
        include_once(__DIR__ . "/radio_stations.php");
        $sonos = new SonosAccess($ip);

        // try to find Radio Station URL
        $uri = get_station_url($radio);

        if( $uri == ""){
            // check in TuneIn Favorites
            foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('R:0/0')['Result']))->item as $item) {
                if ($item->xpath('dc:title')[0] == $radio){
                  $uri = (string)$item->res;
                  break;
                }
            }
        }
  
        if( $uri == "")
         throw new Exception("Radio station " . $radio . " is unknown" ); 

        $sonos->SetRadio($uri, $radio);
    }
    
    public function SetSleepTimer($minutes)
    {
        $targetInstance = $this->findTarget();

        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            $hours = 0;

            while( $minutes > 59 ){
                $hours   = $hours + 1;
                $minutes = $minutes - 60;
            }

            include_once(__DIR__ . "/sonosAccess.php");
            (new SonosAccess($ip))->SetSleeptimer($hours,$minutes,0);
        }else{
            SNS_SetSleepTimer($targetInstance,$minutes);
        }
    }

    public function SetSpdifInput($input_instance)
    {
        $ip = $this->getIP();

        if(@GetValue($this->GetIDForIdent("MemberOfGroup")))
          $this->SetGroup(0);

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);
        
        $sonos->SetAVTransportURI("x-sonos-htastream:".IPS_GetProperty($input_instance ,"RINCON").":spdif");
    }

    public function SetTreble($treble)	
    {
        $ip = $this->getIP();

        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetTreble($treble);
        if (!$this->ReadPropertyBoolean("TrebleControl")) SetValue($this->GetIDForIdent("Treble"), $treble);
    }
    
    public function SetVolume($volume)
    {
        $ip = $this->getIP();

        SetValue($this->GetIDForIdent("Volume"), $volume);
        include_once(__DIR__ . "/sonosAccess.php");
        (new SonosAccess($ip))->SetVolume($volume);
    }

    public function Stop()
    {
        $targetInstance = $this->findTarget();

        if($targetInstance === $this->InstanceID){
            $ip = $this->getIP();

            SetValue($this->GetIDForIdent("Status"), 3);
            include_once(__DIR__ . "/sonosAccess.php");
            $sonos = new SonosAccess($ip);
            if($sonos->GetTransportInfo() == 1) $sonos->Stop();
        }else{
            SNS_Stop($targetInstance);
        }
    }

    public function UpdatePlaylists()
    {
        $ip = $this->getIP();

        if(IPS_VariableProfileExists("Playlist.SONOS"))
            IPS_DeleteVariableProfile("Playlist.SONOS");

        include_once(__DIR__ . "/sonosAccess.php");
        $sonos = new SonosAccess($ip);

        $Associations          = Array();
        $Value                 = 1;
        $PlaylistImport        = $this->ReadPropertyInteger("PlaylistImport");

        if( $PlaylistImport === 1 || $PlaylistImport === 3  ){
            foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('SQ:')['Result']))->container as $container) {
                $Associations[] = Array($Value++, (string)$container->xpath('dc:title')[0], "", -1);
                // associations only support up to 32 variables
                if( $Value === 33 ) break;
            }
        }

        if(($PlaylistImport === 2 || $PlaylistImport === 3) && $Value < 33){
            foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('A:PLAYLISTS')['Result']))->container as $container) {
                $Associations[] = Array($Value++, (string)preg_replace($this->getPlaylistReplacementFrom(), $this->getPlaylistReplacementTo(), $container->xpath('dc:title')[0]), "", -1);
                // associations only support up to 32 variables
                if( $Value === 33 ) break;
            }
        }

        $this->RegisterProfileIntegerEx("Playlist.SONOS", "Database", "", "", $Associations);
    }

    public function UpdateRadioStations()
    {
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
                if( $Value === 33 ) break;
            }
        }
       
        if ($this->ReadPropertyString("IncludeTunein") && $Value < 33){
            $ip = $this->getIP();

            include_once(__DIR__ . "/sonosAccess.php");
            $sonos = new SonosAccess($ip);

            foreach ((new SimpleXMLElement($sonos->BrowseContentDirectory('R:0/0')['Result']))->item as $item) {
                $Associations[] = Array($Value++, (string)$item->xpath('dc:title')[0], "", 0x539DE1);
                // associations only support up to 32 variables
                if( $Value === 33 ) break;
            }
        }

        usort($Associations, function($a,$b){return strnatcmp($a[1], $b[1]);});

        $Value = 1;
        foreach($Associations as $Association) {
            $Associations[$Value-1][0] = $Value++ ;
        }

        if(IPS_VariableProfileExists("Radio.SONOS"))
            IPS_DeleteVariableProfile("Radio.SONOS");

        $this->RegisterProfileIntegerEx("Radio.SONOS", "Speaker", "", "", $Associations);
    
    }
 
    public function UpdateRINCON()
    {
        $ip = $this->getIP();

        $curl = curl_init();
        curl_setopt_array($curl, array( CURLOPT_RETURNTRANSFER => 1,
                                        CURLOPT_URL => "http://".$ip.":1400/xml/device_description.xml" ));

        $result = curl_exec($curl);

        if(!$result)
           throw new Exception("Device description could not be read from ".$ip);

        $xmlr = new SimpleXMLElement($result);
        $rincon = str_replace ( "uuid:" , "" , $xmlr->device->UDN );
        if($rincon){
            IPS_SetProperty($this->InstanceID, "RINCON", $rincon );
            IPS_ApplyChanges($this->InstanceID);
        }else{
            throw new Exception("RINCON could not be read from ".$ip);
        }
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
            case "Crossfade":
                $this->SetCrossfade($Value);
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
            case "PlayMode":
                $this->SetPlayMode($Value);
                break;
            case "Playlist":
                $this->SetPlaylist(IPS_GetVariableProfile("Playlist.SONOS")['Associations'][$Value-1]['Name']);
                SetValue($this->GetIDForIdent($Ident), $Value);
                $this->Play();
                sleep(1);
                SetValue($this->GetIDForIdent($Ident), 0);
                break;
            case "Radio":
                $this->SetRadio(IPS_GetVariableProfile("Radio.SONOS")['Associations'][$Value-1]['Name']);
                SetValue($this->GetIDForIdent($Ident), $Value);
                $this->Play();
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
    
    protected function getPlaylistReplacementFrom(){
        return  array( 
                       '/\.m3u$/',
                       '/\.M3U$/',
                       '/_/'
                     );
    }

    protected function getPlaylistReplacementTo(){
        return  array( 
                       '',
                       '',
                       ' ' 
                     );
    }

    protected function getIP(){
        $ip      = $this->ReadPropertyString("IPAddress");
        $timeout = $this->ReadPropertyString("TimeOut");

        if ($timeout && Sys_Ping($ip, $timeout) != true){
           if (Sys_Ping($ip, $timeout)!= true){
             throw new Exception('Sonos Box '.$ip.' is not available, TimeOut: '.$timeout.'ms.');
           }
        }
        return $ip;   
    }

    protected function findTarget(){
        // instance is a coordinator and can execute command
        if(GetValueBoolean($this->GetIDForIdent("Coordinator")) === true)
            return $this->InstanceID;

        $memberOfGroup = GetValueInteger($this->GetIDForIdent("MemberOfGroup"));
        if($memberOfGroup)
            return $memberOfGroup;
        die("Instance is not a coordinator and group coordinator could not be determined");
    }

    protected function removeVariable($name, $links){
        $vid = @$this->GetIDForIdent($name);
        if ($vid){
            // delete links to Variable
            foreach( $links as $key=>$value ){
                if ( $value['TargetID'] === $vid )
                     IPS_DeleteLink($value['LinkID']);
            }
            foreach(IPS_GetChildrenIDs($vid) as $key=>$cid){
              if(IPS_EventExists($cid)) IPS_DeleteEvent($cid);
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
            foreach(IPS_GetChildrenIDs($vid) as $key=>$cid){
              if(IPS_EventExists($cid)) IPS_DeleteEvent($cid);
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
