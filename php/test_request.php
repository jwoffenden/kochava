<?php
/*
 * JMW: test_endpoint.php - not production code. This is a simple 1-off
 * request to the system to make sure that postbacks make it all the way
 * through the pipeline. It can be accessed from a browser or scripted
 * via the command line.
 *
 * It is not recommended that this test be used
 * for profiling or performance measurement, especially if it's being
 * run from the same system as the server.
 * */

///////////////////
// Configuration //
///////////////////
$arguments = '{
      "endpoint":{
        "method":"GET",
        "url":"http://localhost/test_endpoint.php?title={mascot}&image={location}&foo={bar}"
      },
      "data":[
        {
          "mascot":"Gopher",
          "location":"https://blog.golang.org/gopher/gopher.png"
        }
      ]
    }';

$serviceUrl = 'http://localhost/service.php?json_data=' . urlencode($arguments);
$timeoutInSeconds = 60;


/////////////////////
// Procedural code //
/////////////////////
$curl_connection = curl_init($serviceUrl);

curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, $timeoutInSeconds);
curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);


$curlresult = curl_exec($curl_connection);

echo "Output from server: " . $curlresult . "<br/>\n";
echo "Response code: " . curl_getinfo($curl_connection, CURLINFO_HTTP_CODE) . "<br/>\n";

curl_close($curl_connection);
?>