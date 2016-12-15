<?php

// SONOS Acces Handler
// using PHO SoapClient

class SonosAccess{

  public function __construct( $address )
  {
    $this->address = $address;
  }

  public function AddToQueue($file)
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "AddURIToQueue",
                           array( 
                                  new SoapParam("0"                     ,"InstanceID"                     ),
                                  new SoapParam(htmlspecialchars($file) ,"EnqueuedURI"                    ),
                                  new SoapParam(""                      ,"EnqueuedURIMetaData"            ),
                                  new SoapParam("0"                     ,"DesiredFirstTrackNumberEnqueued"),
                                  new SoapParam("1"                     ,"EnqueueAsNext"                  )
                                ));
  }

  public function BrowseContentDirectory($objectID='SQ:',$browseFlag='BrowseDirectChildren',$requestedCount=100,$startingIndex=0,$filter='',$sortCriteria='')
  {
    return $this->processSoapCall("/MediaServer/ContentDirectory/Control",
                                  "urn:schemas-upnp-org:service:ContentDirectory:1",
                                  "Browse",
                                  array(
                                         new SoapParam($objectID      ,"ObjectID"      ),
                                         new SoapParam($browseFlag    ,"BrowseFlag"    ),
                                         new SoapParam($filter        ,"Filter"        ),
                                         new SoapParam($startingIndex ,"StartingIndex" ),
                                         new SoapParam($requestedCount,"RequestedCount"),
                                         new SoapParam($sortCriteria  ,"SortCriteria"  )
                                       ));
  }

  public function ClearQueue()
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "RemoveAllTracksFromQueue",
                            array(
                                   new SoapParam("0","InstanceID")
                                 ));
  }

  public function GetBass()
  {
    return (int)$this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                                       "urn:schemas-upnp-org:service:RenderingControl:1",
                                       "GetBass",
                                       array(
                                              new SoapParam("0"     ,"InstanceID"),
                                              new SoapParam("Master","Channel"   )
                                            ));
  }

  public function GetCrossfade()
  {
    return (int)$this->processSoapCall("/MediaRenderer/AVTransport/Control",
                                       "urn:schemas-upnp-org:service:AVTransport:1",
                                       "GetCrossfadeMode",
                                       array(
                                              new SoapParam("0","InstanceID")
                                            ));
  }

  public function GetLoudness()
  {
    return (int)$this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                                       "urn:schemas-upnp-org:service:RenderingControl:1",
                                       "GetLoudness",
                                       array(
                                              new SoapParam("0"     ,"InstanceID"),
                                              new SoapParam("Master","Channel"   )
                                            )); 
  }

  public function GetMediaInfo()
  {
    $mediaInfo = $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                                        "urn:schemas-upnp-org:service:AVTransport:1",
                                        "GetMediaInfo",
                                        array(
                                               new SoapParam("0","InstanceID")
                                             ));

    $xmlParser = xml_parser_create("UTF-8");
    xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parse_into_struct($xmlParser, $mediaInfo["CurrentURIMetaData"], $vals, $index);
    xml_parser_free($xmlParser);

    if (isset($index["DC:TITLE"]) and isset($vals[$index["DC:TITLE"][0]]["value"])){
      $mediaInfo["title"] = $vals[$index["DC:TITLE"][0]]["value"];
    }else{
      $mediaInfo["title"] = "";
    }

    return $mediaInfo;
  }

  public function GetMute()
  {
    return (int)$this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                                       "urn:schemas-upnp-org:service:RenderingControl:1",
                                       "GetMute",
                                       array(
                                              new SoapParam("0"     ,"InstanceID"),
                                              new SoapParam("Master","Channel"   )
                                            ));
  }

  public function GetPositionInfo()
  {
    $positionInfo = $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                                           "urn:schemas-upnp-org:service:AVTransport:1",
                                           "GetPositionInfo",
                                           array(
                                                  new SoapParam("0","InstanceID")
                                                 ));

    $xmlParser = xml_parser_create("UTF-8");
    xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parse_into_struct($xmlParser, $positionInfo["TrackMetaData"], $vals, $index);
    xml_parser_free($xmlParser);

    if (isset($index["DC:CREATOR"]) and isset($vals[$index["DC:CREATOR"][0]]["value"])){
      $positionInfo["artist"] = $vals[$index["DC:CREATOR"][0]]["value"];
    }else{
      $positionInfo["artist"] = "";
    }
    
    if (isset($index["DC:TITLE"]) and isset($vals[$index["DC:TITLE"][0]]["value"])){
      $positionInfo["title"] = $vals[$index["DC:TITLE"][0]]["value"];
    }else{
      $positionInfo["title"] = "";
    }

    if (isset($index["UPNP:ALBUM"]) and isset($vals[$index["UPNP:ALBUM"][0]]["value"])){
      $positionInfo["album"] = $vals[$index["UPNP:ALBUM"][0]]["value"];
    }else{
      $positionInfo["album"] = "";
    }

    if (isset($index["UPNP:ALBUMARTURI"]) and isset($vals[$index["UPNP:ALBUMARTURI"][0]]["value"])){
      if (preg_match('/^https?:\/\/[\w,.,\d,-,:]*\/\S*/',$vals[$index["UPNP:ALBUMARTURI"][0]]["value"]) == 1){
        $positionInfo["albumArtURI"] = $vals[$index["UPNP:ALBUMARTURI"][0]]["value"];
      }else{
        $positionInfo["albumArtURI"] = "http://" . $this->address . ":1400" . $vals[$index["UPNP:ALBUMARTURI"][0]]["value"];
      }
    }else{
      $positionInfo["albumArtURI"] = "";
    }

    if (isset($index["R:ALBUMARTIST"]) and isset($vals[$index["R:ALBUMARTIST"][0]]["value"])){
      $positionInfo["albumArtist"] = $vals[$index["R:ALBUMARTIST"][0]]["value"];
    }else{
      $positionInfo["albumArtist"] = "";
    }

    if (isset($index["R:STREAMCONTENT"]) and isset($vals[$index["R:STREAMCONTENT"][0]]["value"])){
      $positionInfo["streamContent"] = $vals[$index["R:STREAMCONTENT"][0]]["value"];
    }else{
      $positionInfo["streamContent"] = "";
    }

    return $positionInfo;
  }

  public function GetSleeptimer()
  {
    $remainingTimer = $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                                             "urn:schemas-upnp-org:service:AVTransport:1",
                                             "GetRemainingSleepTimerDuration",
                                             array(
                                                    new SoapParam("0","InstanceID")
                                                  ));
    return $remainingTimer["RemainingSleepTimerDuration"];
 
  }
  
  public function GetTransportInfo()
  {
    $returnContent = $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                                            "urn:schemas-upnp-org:service:AVTransport:1",
                                            "GetTransportInfo",
                                            array(
                                                   new SoapParam("0","InstanceID")
                                                 ));
    
    switch ($returnContent["CurrentTransportState"]){
      case "PLAYING":
        return 1;
      case "PAUSED_PLAYBACK":
        return 2;
      case "STOPPED":
        return 3;
      case "TRANSITIONING":
        return 5;
      default:
        throw new Exception("Unknown Transport State: ".$returnContent["CurrentTransportState"]); 
    }
  }

  public function GetTransportSettings()
  {
    $returnContent = $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                                            "urn:schemas-upnp-org:service:AVTransport:1",
                                            "GetTransportSettings",
                                            array(
                                                   new SoapParam("0","InstanceID")
                                                 ));

    switch ($returnContent["PlayMode"]){
      case "NORMAL":
        return 0;
      case "REPEAT_ALL":
        return 1;
      case "REPEAT_ONE":
        return 2;
      case "SHUFFLE_NOREPEAT":
        return 3;
      case "SHUFFLE":
        return 4;
      case "SHUFFLE_REPEAT_ONE":
        return 5;
      default:
        throw new Exception("Unknown Play Mode: ".$returnContent["CurrentTransportState"]);
    }
  }

  public function GetTreble()
  {
    return (int)$this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                                       "urn:schemas-upnp-org:service:RenderingControl:1",
                                       "GetTreble",
                                       array(
                                              new SoapParam("0"     ,"InstanceID"),
                                              new SoapParam("Master","Channel"   )
                                            ));
                                       
  }

  public function GetVolume($channel = 'Master')
  {
    return (int)$this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                                       "urn:schemas-upnp-org:service:RenderingControl:1",
                                       "GetVolume",
                                       array(
                                              new SoapParam("0"     ,"InstanceID"),
                                              new SoapParam($channel,"Channel"   )
                                            ));
  }

  public function GetZoneGroupAttributes()
  {
    return $this->processSoapCall("/ZoneGroupTopology/Control",
                                  "urn:schemas-upnp-org:service:ZoneGroupTopology:1",
                                  "GetZoneGroupAttributes",
                                  array() );
                                  
  }

  public function Next()
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "Next",
                           array(
                                  new SoapParam("0","InstanceID")
                                ));
  }

  public function Pause()
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "Pause",
                            array(
                                   new SoapParam("0","InstanceID")
                                 ));
  }

  public function Play()
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "Play",
                           array(
                                  new SoapParam("0","InstanceID"),
                                  new SoapParam("1","Speed"     )
                                ));
  }

  public function Previous()
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "Previous",
                            array(
                                   new SoapParam("0","InstanceID")
                                 ));
  }

  public function RampToVolume($rampType, $volume)
  {
    switch($rampType){
      case 1:
        $rampType = 'SLEEP_TIMER_RAMP_TYPE';
        break;
      case 2:
        $rampType = 'ALARM_RAMP_TYPE';
        break;
      case 3:
        $rampType = 'AUTOPLAY_RAMP_TYPE';
        break;
    }

    return $this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                                  "urn:schemas-upnp-org:service:RenderingControl:1",
                                  "RampToVolume",
                                   array(
                                          new SoapParam("0",      "InstanceID"),
                                          new SoapParam("Master", "Channel"),
                                          new SoapParam($rampType,"RampType"),
                                          new SoapParam($volume,  "DesiredVolume"),
                                          new SoapParam(0,        "ResetVolumeAfter"),
                                          new SoapParam("",       "ProgramURI")
                                        ));
  }

  public function RemoveFromQueue($track)
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "RemoveTrackFromQueue",
                           array(
                                  new SoapParam("0"          ,"InstanceID"),
                                  new SoapParam("Q:0/".$track,"ObjectID"  )
                                ));
  }

  public function Rewind()
  {
    $this->Seek("REL_TIME","00:00:00");
  }

  public function Seek($unit,$target)
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "Seek",
                           array(
                                  new SoapParam("0"    ,"InstanceID"),
                                  new SoapParam($unit  ,"Unit"      ),
                                  new SoapParam($target,"Target"    )
                                ));
  }

  public function SetAVTransportURI($tspuri,$MetaData="")
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "SetAVTransportURI",
                           array(
                                  new SoapParam("0"                      ,"InstanceID"        ),
                                  new SoapParam(htmlspecialchars($tspuri),"CurrentURI"        ),
                                  new SoapParam($MetaData                ,"CurrentURIMetaData")
                                ));
  }

  public function SetBass($bass)
  {
    $this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                           "urn:schemas-upnp-org:service:RenderingControl:1",
                           "SetBass",
                           array(
                                  new SoapParam("0"  ,"InstanceID" ),
                                  new SoapParam($bass,"DesiredBass")
                                ));
  }

  public function SetCrossfade($crossfade)
  {
    if($crossfade){
      $crossfade = "1";
    }else{
      $crossfade = "0";
    }

    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "SetCrossfadeMode",
                           array(
                                  new SoapParam("0"       ,"InstanceID"   ),
                                  new SoapParam($crossfade,"CrossfadeMode")
                                ));
  }

  public function SetLoudness($loud)
  {
    if($loud){
      $loud = "1";
    }else{
      $loud = "0";
    }

    $this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                           "urn:schemas-upnp-org:service:RenderingControl:1",
                           "SetLoudness",
                           array(
                                  new SoapParam("0"     ,"InstanceID"     ),
                                  new SoapParam("Master","Channel"        ),
                                  new SoapParam($loud   ,"DesiredLoudness")
                                ));
  }

  public function SetMute($mute)
  {
    if($mute){
      $mute = "1";
    }else{
      $mute = "0";
    }

    $this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                           "urn:schemas-upnp-org:service:RenderingControl:1",
                           "SetMute",
                           array(
                                  new SoapParam("0"     ,"InstanceID" ),
                                  new SoapParam("Master","Channel"    ),
                                  new SoapParam($mute   ,"DesiredMute")
                                ));
  }

  public function SetPlayMode($PlayMode)
  {
    switch ($PlayMode){
      case 0:
        $PlayMode = "NORMAL";
        break;
      case 1:
        $PlayMode = "REPEAT_ALL";
        break;
      case 2:
        $PlayMode = "REPEAT_ONE";
        break;
      case 3:
        $PlayMode = "SHUFFLE_NOREPEAT";
        break;
      case 4:
        $PlayMode = "SHUFFLE";
        break;
      case 5:
        $PlayMode = "SHUFFLE_REPEAT_ONE";
        break;
      default:
        throw new Exception("Unknown Play Mode: ".$PlayMode);
    }
  
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "SetPlayMode",
                           array(
                                  new SoapParam("0"      ,"InstanceID"  ),
                                  new SoapParam($PlayMode,"NewPlayMode" )
                                ));
  }

  public function SetQueue($queue)
  {
    $this->SetAVTransportURI($queue);
  }

  public function SetRadio($radio, $radio_name = "IP-Symcon Radio" )
  {
    $metaData = '<DIDL-Lite xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/" xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/"><item id="-1" parentID="-1" restricted="true"><dc:title>'.htmlspecialchars($radio_name).'</dc:title><upnp:class>object.item.audioItem.audioBroadcast</upnp:class><desc id="cdudn" nameSpace="urn:schemas-rinconnetworks-com:metadata-1-0/">SA_RINCON65031_</desc></item></DIDL-Lite>';

    $this->SetAVTransportURI($radio,$metaData);
  }

  public function SetSleeptimer($hours,$minutes,$seconds)
  {
    if( $hours == 0 && $minutes == 0 && $seconds == 0 ){
      $sleeptimer = '';
    }else{
      $sleeptimer = $hours.':'.$minutes.':'.$seconds;
    }

    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "ConfigureSleepTimer",
                           array(
                                  new SoapParam("0"        ,"InstanceID"           ),
                                  new SoapParam($sleeptimer,"NewSleepTimerDuration")
                                ));
  }

  public function SetTrack($track)
  {
    $this->Seek("TRACK_NR",$track);
  }

  public function SetTreble($treble)
  {
    $this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                           "urn:schemas-upnp-org:service:RenderingControl:1",
                           "SetTreble",
                           array(
                                  new SoapParam("0"    ,"InstanceID"   ),
                                  new SoapParam($treble,"DesiredTreble")
                                ));
  }

  public function SetVolume($volume, $channel = 'Master')
  {
    $this->processSoapCall("/MediaRenderer/RenderingControl/Control",
                           "urn:schemas-upnp-org:service:RenderingControl:1",
                           "SetVolume",
                           array(
                                  new SoapParam("0"     ,"InstanceID"   ),
                                  new SoapParam($channel,"Channel"      ),
                                  new SoapParam($volume ,"DesiredVolume")
                                ));
  }

  public function Stop()
  {
    $this->processSoapCall("/MediaRenderer/AVTransport/Control",
                           "urn:schemas-upnp-org:service:AVTransport:1",
                           "Stop",
                           array(
                                  new SoapParam("0","InstanceID")
                                ));
  }


  private function processSoapCall($path,$uri,$action,$parameter)
  {
    try{
      $client     = new SoapClient(null, array("location"   => "http://".$this->address.":1400".$path,
                                               "uri"        => $uri,
                                               "trace"      => true ));

      return $client->__soapCall($action,$parameter);
    }catch(Exception $e){
      $faultstring = $e->faultstring;
      $faultcode   = $e->faultcode;
      if(isset($e->detail->UPnPError->errorCode)){
        $errorCode   = $e->detail->UPnPError->errorCode;
        throw new Exception("Error during Soap Call: ".$faultstring." ".$faultcode." ".$errorCode." (".$this->resoveErrorCode($path,$errorCode).")");
      }else{
        throw new Exception("Error during Soap Call: ".$faultstring." ".$faultcode);
      }
    }
  }

  private function resoveErrorCode($path,$errorCode)
  {
   $errorList = array( "/MediaRenderer/AVTransport/Control"      => array(
                                                                           "701" => "ERROR_AV_UPNP_AVT_INVALID_TRANSITION",
                                                                           "702" => "ERROR_AV_UPNP_AVT_NO_CONTENTS",
                                                                           "703" => "ERROR_AV_UPNP_AVT_READ_ERROR",
                                                                           "704" => "ERROR_AV_UPNP_AVT_UNSUPPORTED_PLAY_FORMAT",
                                                                           "705" => "ERROR_AV_UPNP_AVT_TRANSPORT_LOCKED",
                                                                           "706" => "ERROR_AV_UPNP_AVT_WRITE_ERROR",
                                                                           "707" => "ERROR_AV_UPNP_AVT_PROTECTED_MEDIA",
                                                                           "708" => "ERROR_AV_UPNP_AVT_UNSUPPORTED_REC_FORMAT",
                                                                           "709" => "ERROR_AV_UPNP_AVT_FULL_MEDIA",
                                                                           "710" => "ERROR_AV_UPNP_AVT_UNSUPPORTED_SEEK_MODE",
                                                                           "711" => "ERROR_AV_UPNP_AVT_ILLEGAL_SEEK_TARGET",
                                                                           "712" => "ERROR_AV_UPNP_AVT_UNSUPPORTED_PLAY_MODE",
                                                                           "713" => "ERROR_AV_UPNP_AVT_UNSUPPORTED_REC_QUALITY",
                                                                           "714" => "ERROR_AV_UPNP_AVT_ILLEGAL_MIME",
                                                                           "715" => "ERROR_AV_UPNP_AVT_CONTENT_BUSY",
                                                                           "716" => "ERROR_AV_UPNP_AVT_RESOURCE_NOT_FOUND",
                                                                           "717" => "ERROR_AV_UPNP_AVT_UNSUPPORTED_PLAY_SPEED",
                                                                           "718" => "ERROR_AV_UPNP_AVT_INVALID_INSTANCE_ID"
                                                                         ),
                       "/MediaRenderer/RenderingControl/Control" => array(
                                                                           "701" => "ERROR_AV_UPNP_RC_INVALID_PRESET_NAME",
                                                                           "702" => "ERROR_AV_UPNP_RC_INVALID_INSTANCE_ID"
                                                                         ),
                       "/MediaServer/ContentDirectory/Control"   => array(
                                                                           "701" => "ERROR_AV_UPNP_CD_NO_SUCH_OBJECT",
                                                                           "702" => "ERROR_AV_UPNP_CD_INVALID_CURRENTTAGVALUE",
                                                                           "703" => "ERROR_AV_UPNP_CD_INVALID_NEWTAGVALUE",
                                                                           "704" => "ERROR_AV_UPNP_CD_REQUIRED_TAG_DELETE",
                                                                           "705" => "ERROR_AV_UPNP_CD_READONLY_TAG_UPDATE",
                                                                           "706" => "ERROR_AV_UPNP_CD_PARAMETER_NUM_MISMATCH",
                                                                           "708" => "ERROR_AV_UPNP_CD_BAD_SEARCH_CRITERIA",
                                                                           "709" => "ERROR_AV_UPNP_CD_BAD_SORT_CRITERIA",
                                                                           "710" => "ERROR_AV_UPNP_CD_NO_SUCH_CONTAINER",
                                                                           "711" => "ERROR_AV_UPNP_CD_RESTRICTED_OBJECT",
                                                                           "712" => "ERROR_AV_UPNP_CD_BAD_METADATA",
                                                                           "713" => "ERROR_AV_UPNP_CD_RESTRICTED_PARENT_OBJECT",
                                                                           "714" => "ERROR_AV_UPNP_CD_NO_SUCH_SOURCE_RESOURCE",
                                                                           "715" => "ERROR_AV_UPNP_CD_SOURCE_RESOURCE_ACCESS_DENIED",
                                                                           "716" => "ERROR_AV_UPNP_CD_TRANSFER_BUSY",
                                                                           "717" => "ERROR_AV_UPNP_CD_NO_SUCH_FILE_TRANSFER",
                                                                           "718" => "ERROR_AV_UPNP_CD_NO_SUCH_DESTINATION_RESOURCE",
                                                                           "719" => "ERROR_AV_UPNP_CD_DESTINATION_RESOURCE_ACCESS_DENIED",
                                                                           "720" => "ERROR_AV_UPNP_CD_REQUEST_FAILED"
                                                                         ) ); 

    if (isset($errorList[$path][$errorCode])){
      return $errorList[$path][$errorCode] ;
    }else{
      return "UNKNOWN";
    }
  }

}
?>
