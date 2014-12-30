<?php

/*
* This file utilizes the Parse PHP SDK in order to take data taken from (or specified in) a database and 
* load it into Parse for use with the iArtView web/smartphone app. It has two main stages. First, a 
* the program connects to the database and selects the desired data, then pulls it from the database. 
* Next, a loop cycles through each individual artpiece database entry and transfers it to Parse using the
* SDK, artPiece by artPiece.
*
* author: John Devivo
* Date: 11/4/2014
*/

//import the autoload.php script
require 'vendor/autoload.php';
 
//retrieve namespaced ParseClient and ParObject Classes
use Parse\ParseClient;
use Parse\ParseObject;
use Parse\ParseFile;

//initialize Parse PHP SDK 
ParseClient::initialize(/* your app id*/, /* your rest key*/, /* your master key*/);

//connect to the database
$db = new mysqli(/*your host*/, /*your username*/, /*your password*/, /*your db name*/);

if($db->connect_errno > 0){
    die('Unable to connect to database [' . $db->connect_error . ']');
}

//prepare the sql request (normally we would secure this request but since this is just 
//an in-house script it should be safe to leave unsanitized)
$sql = <<<SQL
    SELECT *
    FROM `ArtPiece`
SQL;

//if database query fails kill the script
if(!$result = $db->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
}

//Note: The below loop assumes values in the database are named similar to the values 
//in parse. This may not be the case, you may have to adjust these values to interface 
//properly with your database. Also, not every relevant piece of data is added to the 
//parse from the database in this example, just enough to show you how its done (particularly 
//for tricky entries like the image). You will have to loop through and load all 
//needed data into every Parse class from the db (not just ArtPiece).

//loop through the database entries and upload data to parse
while($row = $result->fetch_assoc()){

	//set data that is to be directly translated from the db using the set method
    $object = ParseObject::create("ArtPiece");
	$object->set("piece_name", $row['name']);
	$object->set("height", (int)$row['height']);
	$object->set("width", (int)$row['width']);
	
	//specify path to needed image using database image name
	$pathToImage = "images/".$row['image'];

	//create parse image file
	$file = ParseFile::createFromFile($pathToImage, $row['image']);

	//set image in Parse
	$object->set('image', $file);
	
	//save object
	$object->save();
}






