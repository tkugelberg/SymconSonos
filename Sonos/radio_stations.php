<?
function get_available_stations(){
  //Taken URLs from  http://wiki.ubuntuusers.de/internetradio/stationen 
  $RadioStations =  Array(
            Array( ('name') => "1LIVE",                    ('url') => "x-sonosapi-stream:s100193?sid=254" ),
            Array( ('name') => "A State Of Trance",        ('url') => "x-sonosapi-stream:s142421?sid=254" ),
            Array( ('name') => "Antenne 1",                ('url') => "x-sonosapi-stream:s25770?sid=254" ),
            Array( ('name') => "Antenne Bayern",           ('url') => "x-sonosapi-stream:s42824?sid=254" ),
            Array( ('name') => "Antenne MV",               ('url') => "x-sonosapi-stream:s16539?sid=254" ),
            Array( ('name') => "Antenne Thueringen",       ('url') => "x-sonosapi-stream:s85980?sid=254" ), 
            Array( ('name') => "Bayern 3",                 ('url') => "x-sonosapi-stream:s14991?sid=254" ),
            Array( ('name') => "bigFM",                    ('url') => "x-sonosapi-stream:s84203?sid=254" ),
            Array( ('name') => "Deutschlandfunk",          ('url') => "x-sonosapi-stream:s42828?sid=254" ),
            Array( ('name') => "FFH",                      ('url') => "x-sonosapi-stream:s17490?sid=254" ),
            Array( ('name') => "FFN",                      ('url') => "x-sonosapi-stream:s8954?sid=254" ),
            Array( ('name') => "HR3",                      ('url') => "x-sonosapi-stream:s57109?sid=254" ),
            Array( ('name') => "KiRaKa",                   ('url') => "x-sonosapi-stream:s55365?sid=254" ),
            Array( ('name') => "MDR1",                     ('url') => "x-sonosapi-stream:s1346?sid=254" ),
            Array( ('name') => "MDR Jump",                 ('url') => "x-sonosapi-stream:s6634?sid=254" ),
            Array( ('name') => "NDR2",                     ('url') => "x-sonosapi-stream:s17492?sid=254" ),
            Array( ('name') => "N-JOY",                    ('url') => "x-sonosapi-stream:s25531?sid=254" ),
            Array( ('name') => "OE3",                      ('url') => "x-sonosapi-stream:s8007?sid=254" ),
            Array( ('name') => "Radio Duisburg",           ('url') => "x-sonosapi-stream:s78341?sid=254" ),
            Array( ('name') => "Radio Essen",              ('url') => "x-sonosapi-stream:s47789?sid=254" ),
            Array( ('name') => "Radio K.W.",               ('url') => "x-sonosapi-stream:s84621?sid=254" ),
            Array( ('name') => "Radio Lippe",              ('url') => "x-sonosapi-stream:s96523?sid=254" ),
            Array( ('name') => "Radio Top40",              ('url') => "x-sonosapi-stream:s18355?sid=254" ),
            Array( ('name') => "RevivalKult",              ('url') => "x-sonosapi-stream:s186710?sid=254" ),
            Array( ('name') => "RPR1",                     ('url') => "x-sonosapi-stream:s9014?sid=254" ),
            Array( ('name') => "Sunshine Live",            ('url') => "x-sonosapi-stream:s10637?sid=254" ),
            Array( ('name') => "Sunshine Live - classics", ('url') => "x-sonosapi-stream:s237965?sid=254" ),
            Array( ('name') => "Sunshine Live - trance",   ('url') => "x-sonosapi-stream:s237967?sid=254" ),
            Array( ('name') => "SWR1 BW",                  ('url') => "x-sonosapi-stream:s20291?sid=254" ),
            Array( ('name') => "SWR1 RP",                  ('url') => "x-sonosapi-stream:s1561?sid=254" ),
            Array( ('name') => "SWR3",                     ('url') => "x-sonosapi-stream:s24896?sid=254" ),
            Array( ('name') => "WDR2",                     ('url') => "x-sonosapi-stream:s99166?sid=254" )
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
