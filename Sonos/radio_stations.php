<?
function get_available_stations(){
  //Taken URLs from  http://wiki.ubuntuusers.de/internetradio/stationen 
  $RadioStations =  Array(
            Array( ('name') => "1LIVE",                    ('url') => "x-rincon-mp3radio://www.wdr.de/wdrlive/media/einslive.m3u" ),
            Array( ('name') => "Antenne 1",                ('url') => "x-rincon-mp3radio://stream.antenne1.de/stream1/livestream.mp3" ),
            Array( ('name') => "Antenne Bayern",           ('url') => "x-rincon-mp3radio://mp3channels.webradio.antenne.de/antenne" ),
            Array( ('name') => "Antenne MV",               ('url') => "x-rincon-mp3radio://streams.antennemv.de/antennemv-live/mp3-192/amv" ),
            Array( ('name') => "Antenne Thueringen",       ('url') => "x-rincon-mp3radio://xapp2023227392c40000.f.l.i.lb.core-cdn.net/40000mb/live/app2023227392/w2075033608/live_de_128.mp3"), 
            Array( ('name') => "Bayern 3",                 ('url') => "x-rincon-mp3radio://streams.br.de/bayern3_2.m3u" ),
            Array( ('name') => "bigFM",                    ('url') => "x-rincon-mp3radio://srv05.bigstreams.de/bigfm-mp3-96.m3u" ),
            Array( ('name') => "Bremen Vier",              ('url') => "x-rincon-mp3radio://httpmedia.radiobremen.de/bremenvier.m3u" ),
            Array( ('name') => "Deutschlandfunk",          ('url') => "x-rincon-mp3radio://www.dradio.de/streaming/dlf.m3u " ),
            Array( ('name') => "Energy",                   ('url') => "x-rincon-mp3radio://energyradio.de/nuernberg" ),
            Array( ('name') => "FFH",                      ('url') => "x-rincon-mp3radio://streams.ffh.de/radioffh/mp3/hqlivestream.m3u" ),
            Array( ('name') => "FFN",                      ('url') => "x-rincon-mp3radio://player.ffn.de/ffn.mp3" ),
            Array( ('name') => "Hitradio N1",              ('url') => "x-rincon-mp3radio://webstream.hitradion1.de/hitradion1" ),
            Array( ('name') => "HR3",                      ('url') => "x-rincon-mp3radio://metafiles.gl-systemhaus.de/hr/hr3_2.m3u" ),
            Array( ('name') => "KiRaKa",                   ('url') => "x-rincon-mp3radio://www.wdr.de/wdrlive/media/kiraka.m3u" ),
            Array( ('name') => "MDR1",                     ('url') => "x-rincon-mp3radio://avw.mdr.de/livestreams/mdr1_radio_sachsen_live_128.m3u" ),
            Array( ('name') => "MDR Jump",                 ('url') => "x-rincon-mp3radio://www.jumpradio.de/static/webchannel/jump_live_channel_high.m3u"),
            Array( ('name') => "NDR2",                     ('url') => "x-rincon-mp3radio://www.ndr.de/resources/metadaten/audio/m3u/ndr2.m3u" ),
            Array( ('name') => "N-JOY",                    ('url') => "x-rincon-mp3radio://www.ndr.de/resources/metadaten/audio/m3u/n-joy.m3u" ),
            Array( ('name') => "OE3",                      ('url') => "x-rincon-mp3radio://mp3stream7.apasf.apa.at:8000" ),
            Array( ('name') => "Radio 91.2",               ('url') => "x-rincon-mp3radio://lokalradiostream.de:8004" ),
            Array( ('name') => "Radio Duisburg",           ('url') => "x-rincon-mp3radio://edge.live.mp3.mdn.newmedia.nacamar.net/ps-radioduisburg/livestream.mp3.m3u" ),
            Array( ('name') => "Radio Essen",              ('url') => "x-rincon-mp3radio://edge.live.mp3.mdn.newmedia.nacamar.net/ps-radioessen/livestream.mp3.m3u" ),
            Array( ('name') => "Radio K.W.",               ('url') => "x-rincon-mp3radio://edge.live.mp3.mdn.newmedia.nacamar.net/ps-radiokw/livestream.mp3" ),
            Array( ('name') => "Radio Lippe",              ('url') => "x-rincon-mp3radio://edge.live.mp3.mdn.newmedia.nacamar.net/ps-radiolippe/livestream.mp3" ),
            Array( ('name') => "Radio Top40",              ('url') => "x-rincon-mp3radio://xapp2023227392c40000.f.l.i.lb.core-cdn.net/40000mb/live/app2023227392/w2075033610/live_de_128.mp3"),
            Array( ('name') => "RPR1",                     ('url') => "x-rincon-mp3radio://rpr1.fmstreams.de/stream1.m3u" ),
            Array( ('name') => "SWR1 BW",                  ('url') => "x-rincon-mp3radio://mp3-live.swr.de/swr1bw_m.m3u" ),
            Array( ('name') => "SWR1 RP",                  ('url') => "x-rincon-mp3radio://mp3-live.swr.de/swr1rp_m.m3u" ),
            Array( ('name') => "SWR3",                     ('url') => "x-rincon-mp3radio://mp3-live.swr3.de/swr3_m.m3u" ),
            Array( ('name') => "WDR2",                     ('url') => "x-rincon-mp3radio://www.wdr.de/wdrlive/media/wdr2.m3u" ),
            Array( ('name') => "Sunshine Live",            ('url') => "x-rincon-mp3radio://stream.hoerradar.de/sunshinelive-mp3-128" ),
            Array( ('name') => "Sunshine Live - classics", ('url') => "x-rincon-mp3radio://stream.hoerradar.de/sunshine-classics-mp3-128" ),
            Array( ('name') => "Sunshine Live - trance",   ('url') => "x-rincon-mp3radio://stream.hoerradar.de/sunshine-trance-mp3-128" ),
            Array( ('name') => "A State Of Trance",        ('url') => "x-sonosapi-stream:s142421?sid=254" ),
            Array( ('name') => "RevivalKult",              ('url') => "x-sonosapi-stream:s186710?sid=254" )
                         );

  return  $RadioStations ;
}

function get_station_url($name, $RadioStations = null){

  if ( $RadioStations === null ){ $RadioStations = get_available_stations(); };

  foreach ( $RadioStations as $key => $val ) {
      if ($val['name'] === $name) {
          return $RadioStations[$key]['url'];
      }
  }
   throw new Exception("Radio station " . $name . " is unknown" );
}

?>
