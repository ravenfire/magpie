<?php

require "vendor/autoload.php";

// "symfony/console": "^5.4", 
// "symfony/dotenv": "^5.4",
// "illuminate/database": "^8.76",
// "monolog/monolog": "^2.3",
// "symfony/monolog-bridge": "^5.4",
// "ext-json": "*",
// "guzzlehttp/guzzle": "^7.4"; Helps make calls to the API sources

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

$client = new Client([
    'base_uri' => 'https://www.boardgamegeek.com/xmlapi/',
    'timeout' => 90.0, //increased from 2.0
]);

$alphabet = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

function xmlToJson($body)
{
    $xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($xml);
    return json_decode($json, true);
}

function boardgameKeyExists($games)
{
    return array_key_exists("boardgame", $games);
}

function attributesKeyExists($games)
{
// Result if true (1 result):
// array(2) {
//    ["@attributes"]=>
//    array(1) {
//        ["termsofuse"]=>
//        string(43) "https://boardgamegeek.com/xmlapi/termsofuse"
//    }
//    ["boardgame"]=>
//    array(19) {
//        ["@attributes"]=>
//        array(1) {
//            ["objectid"]=>
//            string(5) "32897"
//
// Result if false (result > 1):
// array(2) {
//    ["@attributes"]=>
//    array(1) {
//        ["termsofuse"]=>
//        string(43) "https://boardgamegeek.com/xmlapi/termsofuse"
//    }
//    ["boardgame"]=>
//    array(6) {
//        [0]=>
//        array(3) {
//            ["@attributes"]=>
//            array(1) {
//                ["objectid"]=>
//                string(3) "749"
//            }

    return array_key_exists("@attributes", $games);
}

function objectidKeyExists($games)
{
    return array_key_exists("objectid", $games);
}

$search = [];
foreach($alphabet as $letter)
{
    for($i = 0; $i < count($alphabet); $i++)
    {
        array_push($search, $letter . $alphabet[$i]);
    }
}

$gameIdArr = [];
// for($i=0; $i<1; $i++)
for($i=0; $i<count($search); $i++)
{
    //BGG caps results at 5000
    $response = $client->request("GET", 'search', [
        "query" => ['search' => $search[$i]]
    ]);

    echo "\n";
    echo $response->getStatusCode();
    echo "\n";

    $body = $response->getBody()->getContents();

    $games = xmlToJson($body);

    if(boardgameKeyExists($games))
    {
//      There are different nesting levels
        if(attributesKeyExists($games["boardgame"]))
        {
            if(objectidKeyExists($games["boardgame"]["@attributes"]) and !in_array($games["boardgame"]["@attributes"]["objectid"], $gameIdArr))
            {
                $gameIdArr[] = $games["boardgame"]["@attributes"]["objectid"] . "?comments=1" . "&stats=1";
            }
        }
        else
        {
            foreach ($games["boardgame"] as $game)
            {
                if(objectidKeyExists($game["@attributes"]) and !in_array($game["@attributes"]["objectid"], $gameIdArr))
                {
                    $gameIdArr[] = $game["@attributes"]["objectid"] . "?comments=1" . "&stats=1";
                }
            }
        }
    }
//     var_dump($gameIdArr);
//    die();
    // var_dump(count($gameIdArr));
    // var_dump($search[$i]);
}

//cycle through all ids
while(count($gameIdArr) > 0)
{
    $count = 0;
    count($gameIdArr) > 30 ? $count = 30 : $count = count($gameIdArr);
    $batch = array_splice($gameIdArr, count($gameIdArr) - 30);

    $batch = implode(",", $batch);

    //search by ID
    $response = $client->request("GET", 'boardgame/' . $batch);

    echo "\n";
    echo $response->getStatusCode();
    echo "\n";

    $body = $response->getBody()->getContents();

    $xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($xml);
    $gamesById = json_decode($json, true);

    var_dump($gamesById);
    var_dump(count($gameIdArr));
}








// echo "\n\n ----------------------------\n\n";
// $client = new Client([
//     'base_uri' => "http://dog.ceo/api/",
//     'timeout' => 2.0,
// ]);

// $response = $client->request("GET", "breeds/list/all");

// // var_dump($response);
// echo "\n\n ----------------------------\n\n";

// echo $response->getStatusCode();
// echo "\n";
// echo $response->getBody();
// echo "\n";

// $body = $response->getBody();
// $breeds = json_decode($body, true);

// foreach($breeds as $breed)
// {
//     var_dump($breed);
// }




