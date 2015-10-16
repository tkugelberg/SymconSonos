<?
function get_available_stations(){
  //Taken URLs from  http://wiki.ubuntuusers.de/internetradio/stationen 
  $RadioStations =  Array(
            Array( ('name') => "FFN",              ('url') => "x-rincon-mp3radio://player.ffn.de/ffn.mp3" ),
            Array( ('name') => "FFH",              ('url') => "x-sonosapi-stream:s17490?sid=254&amp;flags=32" ),
            Array( ('name') => "Radio Lippe",      ('url') => "x-rincon-mp3radio://edge.live.mp3.mdn.newmedia.nacamar.net/ps-radiolippe/livestream.mp3" ),
            Array( ('name') => "OE3",              ('url') => "x-rincon-mp3radio://mp3stream7.apasf.apa.at:8000" ),
            Array( ('name') => "Antenne 1",        ('url') => "x-rincon-mp3radio://stream.antenne1.de/stream1/livestream.mp3" ),
            Array( ('name') => "Antenne Bayern",   ('url') => "x-rincon-mp3radio://mp3channels.webradio.antenne.de/antenne" ),
            Array( ('name') => "Antenne MV",       ('url') => "x-rincon-mp3radio://streams.antennemv.de/antennemv-live/mp3-192/amv" ),
            Array( ('name') => "Bayern 3",         ('url') => "x-rincon-mp3radio://srv05.bigstreams.de/bigfm-mp3-96.m3u" ),
            Array( ('name') => "bigFM",            ('url') => "x-rincon-mp3radio://streams.br.de/bayern3_2.m3u" ),
            Array( ('name') => "Deutschlandfunk",  ('url') => "x-rincon-mp3radio://www.dradio.de/streaming/dlf.m3u " ),
            Array( ('name') => "HR3",              ('url') => "x-rincon-mp3radio://metafiles.gl-systemhaus.de/hr/hr3_2.m3u" ),
            Array( ('name') => "NDR2",             ('url') => "x-rincon-mp3radio://www.ndr.de/resources/metadaten/audio/m3u/ndr2.m3u" ),
            Array( ('name') => "N-JOY",            ('url') => "x-rincon-mp3radio://www.ndr.de/resources/metadaten/audio/m3u/n-joy.m3u" ),
            Array( ('name') => "RPR1",             ('url') => "x-rincon-mp3radio://rpr1.fmstreams.de/stream1.m3u" ),
            Array( ('name') => "SWR3",             ('url') => "x-rincon-mp3radio://mp3-live.swr3.de/swr3_m.m3u" ),
            Array( ('name') => "SWR1 BW",          ('url') => "x-rincon-mp3radio://mp3-live.swr.de/swr1bw_m.m3u" ),
            Array( ('name') => "SWR1 RP",          ('url') => "x-rincon-mp3radio://mp3-live.swr.de/swr1rp_m.m3u" ),
            Array( ('name') => "WDR2",             ('url') => "x-rincon-mp3radio://www.wdr.de/wdrlive/media/wdr2.m3u" ),
            Array( ('name') => "KiRaKa",           ('url') => "x-rincon-mp3radio://www.wdr.de/wdrlive/media/kiraka.m3u" ),
            Array( ('name') => "1LIVE",            ('url') => "x-rincon-mp3radio://www.wdr.de/wdrlive/media/einslive.m3u" )
                         );

   // sort by name
  foreach ($RadioStations as $key => $row) {
      $dates[$key]  = $row['name']; 
  }

  array_multisort($dates, SORT_ASC, $RadioStations);

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
