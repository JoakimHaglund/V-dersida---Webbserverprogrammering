<?php
include ('class/Template.php');
include ('class/Forecast.php');
include ('class/DB.php');

//lite variabler med info som behöves senare 
$appName = 'SchoolProject'; 
$appVersion = '0.3';
$devEmail = 'anon824697@gmail.com';
$url = "http://$_SERVER[HTTP_HOST]/index.php";
//skapar instanser av klasserna 
$fcast = new Forecast($appName, $appVersion, $devEmail);
$db = DB::getInstance("forecast");
$site = new Template();

//variabler till html-template som behöver starta tomma
$locationName = '';
$tableStr = '';
//kollar att formuläret skickats och har "location"
if (isset($_POST['location'])){
	$location = $_POST['location'];
	//Hämtar väderlek för den valda platsen
	$returnData= $fcast->getLocationForecast($location);
	//hämtar lägden på listan -1 pga platsdatan ($returnData['location']) 
	$count = count($returnData) -1;
	//lägger in platsen i databasen
	$db->insertLocation($returnData);
	//lägger in all data i databasen
	for($i=0; $i < $count; $i++){
		$db->insertData($returnData[$i], $returnData['location']['areaName']);
	}
	//hämtar alla data för den valda platsen från databasen
	$data = $db->getData(rawurlencode($location));
	//skapar en sträng för html tabbelen med väderdata
	$tableStr =	'<table id="forecast">
	<tr>
		<th>date and time</th>
		<th>Temp</th>
		<th>Humidity</th>
		<th>windSpeed</th>
	</tr>';
	$count = count($data);
	for($i=0; $i < $count; $i++){
		$tableStr .= '<tr>';
		$dateParts = preg_split('{T|Z}',$data[$i]['timeStamp']);
		$tableStr .= '<td>' . $dateParts[0] . ' at ' . $dateParts[1] . '</td>';
		$tableStr .= '<td>' . $data[$i]["temp"] . '</td>';
		$tableStr .= '<td>' . $data[$i]["humidity"] . '</td>';
		$tableStr .= '<td>' . $data[$i]['windSpeed'] . '</td>';
		$tableStr .= '</tr>';
	} 
	$tableStr .=	'</table>';
	$locationName = ucfirst(mb_strtolower($location, 'UTF-8'));
}
//skapar ett formulär för att söka väderlek
$form = '<form action="' . $url . '" method="post">
							<input type="text" name="location" placeholder="location">
							<input type="submit" value="Submit">
					</form>';
//hämtar html-template
$site->load("html/template.html");
//sätter byter ut nycklar mot värden för html-templaten
$site->set('title', 'Vädersida - projektarbete');
$site->set('lastUpdate', '');
$site->set('nav', '');
$site->set('message', '');
$site->set('form', $form);
$site->set('contact', $devEmail);
$site->set('content', $tableStr);
$site->set('location', $locationName);
//skriver ut sidan
echo $site->render();

?>