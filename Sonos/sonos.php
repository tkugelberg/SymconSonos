<?php

//Sonos PHP Script
//Copyright: Michael Maroszek
//Version: 1.0, 09.07.2009

class PHPSonos {
    private $address = "";

    public function __construct( $address ) {
        $this->address = $address;
    }

    public function AddToQueue($file)
    {
	
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(438+strlen(htmlspecialchars($file))).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#AddURIToQueue"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:AddURIToQueue xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><EnqueuedURI>'.htmlspecialchars($file).'</EnqueuedURI><EnqueuedURIMetaData></EnqueuedURIMetaData><DesiredFirstTrackNumberEnqueued>0</DesiredFirstTrackNumberEnqueued><EnqueueAsNext>1</EnqueueAsNext></u:AddURIToQueue></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }
	
    public function ClearQueue()
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 290
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#RemoveAllTracksFromQueue"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:RemoveAllTracksFromQueue xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:RemoveAllTracksFromQueue></s:Body></s:Envelope>';

       $this->sendPacket($content);
    }

    public function GetBass()
    {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 279
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetBass"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetBass xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel></u:GetBass></s:Body></s:Envelope>';

        return (int)$this->sendPacket($content);
    }

    public function GetLoudness()
    {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 293
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetLoudness"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetLoudness xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel></u:GetLoudness></s:Body></s:Envelope>';

        return (int)$this->sendPacket($content);
    }

    public function GetMediaInfo()
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 266
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetMediaInfo"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetMediaInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:GetMediaInfo></s:Body></s:Envelope>';

        $returnContent = $this->XMLsendPacket($content);

        $xmlParser = xml_parser_create("UTF-8");
        xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
        xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
        xml_parser_free($xmlParser);

        $mediaInfo = Array();
	
        if (isset($vals[$index["CURRENTURI"][0]]["value"])){
            $mediaInfo["CurrentURI"] = $vals[$index["CURRENTURI"][0]]["value"];
        }else{
            $mediaInfo["CurrentURI"] = "";
        }

        if (isset($vals[$index["CURRENTURIMETADATA"][0]]["value"])){
            $mediaInfo["CurrentURIMetaData"] = $vals[$index["CURRENTURIMETADATA"][0]]["value"];
			
	
            $xmlParser = xml_parser_create("UTF-8");
            xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
            xml_parse_into_struct($xmlParser, $mediaInfo["CurrentURIMetaData"], $vals, $index);
            xml_parser_free($xmlParser);

            if (isset($index["DC:TITLE"]) and isset($vals[$index["DC:TITLE"][0]]["value"])){
                $mediaInfo["title"] = $vals[$index["DC:TITLE"][0]]["value"];
            }else{
                $mediaInfo["title"] = "";
            }
        } else {
            $mediaInfo["CurrentURIMetaData"] = "";
        }
        return $mediaInfo;
    }
	
    public function GetMute()
    {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 286
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetMute"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetMute xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel></u:GetMute></s:Body></s:Envelope>';

        return (int)$this->sendPacket($content);
    }
	
    public function GetPositionInfo()
    {
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 272
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetPositionInfo"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetPositionInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:GetPositionInfo></s:Body></s:Envelope>';

        $returnContent = $this->sendPacket($content);
	
        $position = substr($returnContent, stripos($returnContent, "NOT_IMPLEMENTED") - 7, 7);

        $returnContent = substr($returnContent, stripos($returnContent, '&lt;'));
        $returnContent = substr($returnContent, 0, strrpos($returnContent, '&gt;') + 4);
        $returnContent = str_replace(array("&lt;", "&gt;", "&quot;", "&amp;", "%3a", "%2f", "%25"), array("<", ">", "\"", "&", ":", "/", "%"), $returnContent);
		
        $xmlParser = xml_parser_create("UTF-8");
        xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
        xml_parser_free($xmlParser);
	
        $positionInfo = Array ();
		
        $positionInfo["position"] = $position;
        $positionInfo["RelTime"] = $position;

        if (isset($index["RES"]) and isset($vals[$index["RES"][0]]["attributes"]["DURATION"])){
            $positionInfo["duration"] = $vals[$index["RES"][0]]["attributes"]["DURATION"];
            $positionInfo["TrackDuration"] = $vals[$index["RES"][0]]["attributes"]["DURATION"];
        }else{
            $positionInfo["duration"] = "";
            $positionInfo["TrackDuration"] = "";
        }

        if (isset($index["RES"]) and isset($vals[$index["RES"][0]]["value"])){
            $positionInfo["URI"] = $vals[$index["RES"][0]]["value"];
            $positionInfo["TrackURI"] = $vals[$index["RES"][0]]["value"];
        }else{
            $positionInfo["URI"] = "";
            $positionInfo["TrackURI"] = "";
        }
		
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
            $positionInfo["albumArtURI"] = "http://" . $this->address . ":1400" . $vals[$index["UPNP:ALBUMARTURI"][0]]["value"];
        }else{
            $positionInfo["albumArtURI"] = "";
        }

        if (isset($index["R:ALBUMARTIST"]) and isset($vals[$index["R:ALBUMARTIST"][0]]["value"])){
            $positionInfo["albumArtist"] = $vals[$index["R:ALBUMARTIST"][0]]["value"];
        }else{
            $positionInfo["albumArtist"] = "";
        }
		
        if (isset($index["UPNP:ORIGINALTRACKNUMBER"]) and isset($vals[$index["UPNP:ORIGINALTRACKNUMBER"][0]]["value"])){
            $positionInfo["albumTrackNumber"] = $vals[$index["UPNP:ORIGINALTRACKNUMBER"][0]]["value"];
        }else{
            $positionInfo["albumTrackNumber"] = "";
        }
		
        if (isset($index["R:STREAMCONTENT"]) and isset($vals[$index["R:STREAMCONTENT"][0]]["value"])){
            $positionInfo["streamContent"] = $vals[$index["R:STREAMCONTENT"][0]]["value"];
        }else{
            $positionInfo["streamContent"] = "";
        }
        // added br if this contains "rincon" we are slave to a coordinator mentioned in this field (otherwise path to the file is provided)!
        // implemented via second XMLsendpacket to not break michaels current code

        if (isset($index["RES"][0]) and isset($vals[($index["RES"][0])]["value"])){
            $positionInfo["trackURI"] = $vals[($index["RES"][0])]["value"];
        }else{
            $returnContent = $this->XMLsendPacket($content);
    
            $xmlParser = xml_parser_create("UTF-8");
            xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
            xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
            xml_parser_free($xmlParser);
        }
	 
        if (isset($index["TRACKURI"][0]) and isset($vals[($index["TRACKURI"][0])]["value"])){
            $positionInfo["trackURI"] = $vals[($index["TRACKURI"][0])]["value"];
            $positionInfo["TrackURI"] = $vals[($index["TRACKURI"][0])]["value"];
        }else{
            $positionInfo["trackURI"] = "";
        }
		
        // Track Number in Playlist
        $returnContent = $this->XMLsendPacket($content);

        $xmlParser = xml_parser_create("UTF-8");
        xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
        xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
        xml_parser_free($xmlParser);

        if (isset($index["TRACK"][0]) and isset($vals[($index["TRACK"][0])]["value"])){
            $positionInfo["Track"] = $vals[($index["TRACK"][0])]["value"];
        }else{
            $positionInfo["Track"] = "";
        }
	
        return $positionInfo;
    }
	
    public function GetTransportInfo()
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 274
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetTransportInfo"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetTransportInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:GetTransportInfo></s:Body></s:Envelope>';

        $returnContent = $this->sendPacket($content);
		
        if (strstr($returnContent, "PLAYING") !== false){
            return 1;
        }elseif (strstr($returnContent, "PAUSED_PLAYBACK") !== false){
            return 2;
        }elseif (strstr($returnContent, "STOPPED") !== false){
            return 3;
        }
    }

    public function GetTreble()
    {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 290
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetTreble"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetTreble xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel></u:GetTreble></s:Body></s:Envelope>';

        return (int)$this->sendPacket($content);
    }

    public function GetVolume()
    {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 290
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetVolume"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetVolume xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel></u:GetVolume></s:Body></s:Envelope>';

        return (int)$this->sendPacket($content);
    }
	
    public function GetZoneGroupAttributes()
    {

$content='POST /ZoneGroupTopology/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 266
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:ZoneGroupTopology:1#GetZoneGroupAttributes"

<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><u:GetZoneGroupAttributes xmlns:u="urn:schemas-upnp-org:service:ZoneGroupTopology:1"></u:GetZoneGroupAttributes></s:Body></s:Envelope>';

        return $this->XMLsendPacket($content);
    }

    public function Next()
    {
	
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 250
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Next"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Next xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Next></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }
	
    public function Pause()
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 252
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Pause"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Pause xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Pause></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }

    public function Play()
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 266
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Play"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Play xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Speed>1</Speed></u:Play></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }
	
    public function Previous()
    {
	
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 258
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Previous"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Previous xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Previous></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }
	
    public function RemoveFromQueue($track)
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(307+strlen($track)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#RemoveTrackFromQueue"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:RemoveTrackFromQueue xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><ObjectID>Q:0/'.$track.'</ObjectID></u:RemoveTrackFromQueue></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }
	
    public function Rewind()
    {
	
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 296
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Seek"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Seek xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Unit>REL_TIME</Unit><Target>00:00:00</Target></u:Seek></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }
	
    public function SetAVTransportURI($tspuri,$MetaData="")
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(342+strlen(htmlspecialchars($tspuri))+strlen($MetaData)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#SetAVTransportURI"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetAVTransportURI xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><CurrentURI>'.htmlspecialchars($tspuri).'</CurrentURI><CurrentURIMetaData>'.$MetaData.'.</CurrentURIMetaData></u:SetAVTransportURI></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }

    public function SetBass($bass)
    {
$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(288+strlen($bass)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetBass"

<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><u:SetBass xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><DesiredBass>'.$bass.'</DesiredBass></u:SetBass></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }

    public function SetLoudness($loud)
    {

        if($loud){
            $loud = "1";
        }else{
            $loud = "0";
        }

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 330
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetLoudness"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetLoudness xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel><DesiredLoudness>'.$loud.'</DesiredLoudness></u:SetLoudness></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }

    public function SetMute($mute)
    {

        if($mute){
            $mute = "1";
        }else{
            $mute = "0";
        }

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 314
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetMute"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetMute xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel><DesiredMute>'.$mute.'</DesiredMute></u:SetMute></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }
	
    public function SetQueue($queue)
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(342+strlen(htmlspecialchars($queue))).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#SetAVTransportURI"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetAVTransportURI xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><CurrentURI>'.htmlspecialchars($queue).'</CurrentURI><CurrentURIMetaData></CurrentURIMetaData></u:SetAVTransportURI></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }

    public function SetRadio($radio, $radio_name = "IP-Symcon Radio" )
    {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(959+strlen(htmlspecialchars($radio))+strlen(htmlspecialchars($radio_name))).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#SetAVTransportURI"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetAVTransportURI xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><CurrentURI>'.htmlspecialchars($radio).'</CurrentURI><CurrentURIMetaData>&lt;DIDL-Lite xmlns:dc=&quot;http://purl.org/dc/elements/1.1/&quot; xmlns:upnp=&quot;urn:schemas-upnp-org:metadata-1-0/upnp/&quot; xmlns:r=&quot;urn:schemas-rinconnetworks-com:metadata-1-0/&quot; xmlns=&quot;urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/&quot;&gt;&lt;item id=&quot;R:0/0/0&quot; parentID=&quot;R:0/0&quot; restricted=&quot;true&quot;&gt;&lt;dc:title&gt;'.htmlspecialchars($radio_name).'&lt;/dc:title&gt;&lt;upnp:class&gt;object.item.audioItem.audioBroadcast&lt;/upnp:class&gt;&lt;desc id=&quot;cdudn&quot; nameSpace=&quot;urn:schemas-rinconnetworks-com:metadata-1-0/&quot;&gt;SA_RINCON65031_&lt;/desc&gt;&lt;/item&gt;&lt;/DIDL-Lite&gt;</CurrentURIMetaData></u:SetAVTransportURI></s:Body></s:Envelope>';

        return (bool)$this->sendPacket($content);
    }
	
    public function SetTrack($track)
    {
	
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(288+strlen($track)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Seek"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Seek xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Unit>TRACK_NR</Unit><Target>'.$track.'</Target></u:Seek></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }

    public function SetTreble($treble)
    {
$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(296+strlen($treble)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetTreble"

<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><u:SetTreble xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><DesiredTreble>'.$treble.'</DesiredTreble></u:SetTreble></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }

    public function SetVolume($volume)
    {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(321+strlen($volume)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetVolume"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetVolume xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel><DesiredVolume>'.$volume.'</DesiredVolume></u:SetVolume></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }
	
    public function Stop()
    {
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 250
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Stop"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Stop xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Stop></s:Body></s:Envelope>';

        $this->sendPacket($content);
    }

/***************************************************************************
				Helper / sendPacket
***************************************************************************/

/**
 * XMLsendPacket
 *
 * - <b>NOTE:</b> This function does send of a soap query and DOES NOT filter a xml answer
 * - <b>Returns:</b> Answer as XML
 *
 * @return Array
 */
    private function XMLsendPacket( $content )
    {
        $fp = fsockopen($this->address, 1400 /* Port */, $errno, $errstr, 10);
        if (!$fp)
            throw new Exception("Error opening socket: ".$errstr." (".$errno.")");
		    
        fputs ($fp, $content);
        $ret = "";
        $buffer = "";
        while (!feof($fp)) {
            $ret.= fgets($fp,128);
        }

        fclose($fp);

        if(strpos($ret, "200 OK") === false)
            throw new Exception("Error sending command: ".$ret);
        $array = preg_split("/\n/", $ret);
		
        return $array[count($array) - 1];
    }

/**
 * sendPacket - communicate with the device
 *
 * - <b>NOTE:</b> This function does send of a soap query and may filter xml answers
 * - <b>Returns:</b> Answer
 *
 * @return Array
 */

    private function sendPacket( $content )
    {
        $fp = fsockopen($this->address, 1400 /* Port */, $errno, $errstr, 10);
        if (!$fp)
            throw new Exception("Error opening socket: ".$errstr." (".$errno.")");

        fputs ($fp, $content);
        $ret = "";
        while (!feof($fp)) {
            $ret.= fgetss($fp,128);
        }
        fclose($fp);

        if(strpos($ret, "200 OK") === false)
            throw new Exception("Error sending command: ".$ret);
		
        $array = preg_split("/\n/", $ret);

        return $array[count($array) - 1];
    }

}
?>
