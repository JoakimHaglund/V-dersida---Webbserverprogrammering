<?php
class Forecast
{
	private 	$_coordinates = array('lon' => NULL, 'lat' => NULL, 'areaName' => NULL);
	private		$_appDetails = array(
		'appName' => NULL, 
		'appVersion' => NULL, 
		'devEmail' => NULL
		);
	private 	$_geocodeURL = 'http://open.mapquestapi.com/geocoding/v1/address?key=[key]&location=[loc]';
	private 	$_forecastURL = 'https://api.met.no/weatherapi/locationforecast/2.0/compact.json?lat=[lat]&lon=[lon]';
	protected $_jsonData;
	protected $_mapQuestKey = 'GuWycxcrSy9spmAOVToLekwWPXTaU0uZ';
	
	public function __construct($appName, $appVersion ,$devEmail){
		$this->_appDetails['appName'] = $appName;
		$this->_appDetails['appVersion'] = $appVersion;
		$this->_appDetails['devEmail'] = $devEmail;
	}
	//Kontrollerar att inga värden saknas
	private function arrayIsNotNull($array)
	{
		foreach ($array as $value){
			if ($value == NULL){
				return false;
			}
		}
		return true;
	}
	//Kontrollerar att inga värden saknas i urler
	private function noMissingValuesURL($url){
		if (preg_match('#\[|\]#', $url)){
			return false;
		}
		else {
			return true;
		}
	}
	//Byter ut nycklar mot värden i urler 
	private function setURL($URL, $replace, $values) 
	{
		//kontrollerar att inget är helt galet.
		if ($URL == null){
			throw new Exception('setURL() needs a URL-string to function');
		}
		if (!is_array($replace)){
			throw new Exception('The $replace var needs to be an array in setURL()');
		}
		if (!is_array($values)){
			throw new Exception('The $values var needs to be an array in setURL()');
		}
		//kollar att båda arrayerna har lika många värden
		if (count($replace) != count($values)){
			throw new Exception('The $replace and $values arrays needs to have the 
			same lenght in setURL()');
		}
		//byter ut nycklar mot värden
		foreach ($replace as $key => $value){
			$URL = str_replace("[$value]", "$values[$key]" ,$URL);
		}
		return $URL;	
	}
	//Variant av setURL() som istället använder en accsociativ lista
	private function setURLAssoc($URL, $array)
	{
		$keyArray 	= array();
		$valueArray = array();
		//kollar om något är fel
		if ($URL == null){
			throw new Exception('setURLAssoc() needs a URL-string to function');
		}
		if (!is_array($array)){
			throw new Exception('The $array var needs to be an array in setURLAssoc()');
		}
		//delar upp listan i 2 listor
		foreach ($array as $key => $value){
			$keyArray[] 	= $key;
			$valueArray[] = $value;
		}
		//skickar url och listorna till setURL så den kan göra resten av proceduren
		$URL = $this->setURL($URL, $keyArray, $valueArray);
		return $URL;
	}
	//hämtar kordinaterna från open geocoding API
	private function callGeocodeAPI($area)
	{ 
		$ch = curl_init();  
		$area = ucfirst(strtolower($area));
		//urlenkodar $area eller avbryter om det inte går
		if (!$location = curl_escape($ch, $area)){
			return false;
		}
		//byter ut nycklar till värden i urlen till geokods-apin
		$this->_geocodeURL = $this->setURLAssoc($this->_geocodeURL, 
			array('key' => $this->_mapQuestKey ,'loc' => $location)
			);
		//sätter option så man får resultatet som en sträng
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		//sätter url
		curl_setopt($ch, CURLOPT_URL, $this->_geocodeURL); 
		//utför curl förfrågan och fångar resultatet i $jsondata
		$jsondata = curl_exec($ch);
		//dekodar json-datan till en php array
		$reslut = json_decode($jsondata, true);
		//sätter kordinater om resultatet finns
		if (isset($reslut['results'][0]['locations'][0]['latLng'])){
			$this->_coordinates['lat'] = $reslut['results'][0]['locations'][0]['latLng']['lat'];
			$this->_coordinates['lon'] = $reslut['results'][0]['locations'][0]['latLng']['lng'];
			$this->_coordinates['areaName'] = $location;
			if ($this->arrayIsNotNull($this->_coordinates)){
				return true;
			}
			else {
				throw new Exception('Could not set cordinates in callGeocodeAPI()');
			}
		}
		else {
			return false;
		}
	}
	//Hämtar väderdatan
	private function callForecastAPI()
	{
		$ch = curl_init(); 
		//sätter option så man får resultatet som en sträng		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//byter ut nycklar mot värden 
		$this->_forecastURL = $this->setURLAssoc($this->_forecastURL, $this->_coordinates);
		//kontrollerar att inga värden saknas
		if ($this->noMissingValuesURL($this->_forecastURL)){
			curl_setopt($ch, CURLOPT_URL, $this->_forecastURL);
		}
		else {
			throw new Exception('Missing values for API call in callForecastAPI()');
		}
		if ($this->arrayIsNotNull($this->_appDetails)){
			$user = $this->_appDetails['appName'] . '/' . 
							$this->_appDetails['appVersion'] . '/' . $this->_appDetails['devEmail'];
			//Ändrar useragent till värdena som angavs i konstruktorn.
			//Detta görs för att följa ToS hos Yr.no 
			curl_setopt($ch, CURLOPT_USERAGENT, $user);
		}
		else {
			throw new Exception('Missing application details in callForecastAPI()');
		}
		//utför curl förfrågan och fångar resultatet 
		$jsondata = curl_exec($ch);
		//retunerar datan som en php array
		return json_decode($jsondata, true);
	}
	//Skapar en lite mer lättanvänd lista med datan.
	//Är förmodligen en anledning till att webbplatsen är lite slö
	private function handleForecastData($data)
	{
		$returnData = array();
		$count = count($data['properties']['timeseries']);
		//loppar igenom listan
		for($i=0; $i < $count; $i++){
			$time = $data['properties']['timeseries'][$i]['time'];
			$time = str_replace('Z' ,'', $time);
			//delar upp tid och datum
			$setTime = preg_split('{T}' ,$time);
			//delar upp timme minut och sekund
			$tmpTime = preg_split('{:}', $setTime[1]);
			//omvandlar värdet till en int
			$tmpTime[0] = intval($tmpTime[0]);
			//Kollar att tidskillnaden är 1 timme
			//Är skillnaden större så vill vi inte ha datan
			if (!isset($lastTime) || $tmpTime[0] == $lastTime + 1){
				$lastTime = $tmpTime[0];
				if ($lastTime == 23){
					$lastTime = -1;
				}
				//sätter datan i den nya listan 
				$returnData[$i]['date']['date'] = $setTime[0];
				$returnData[$i]['date']['time'] = $setTime[1];
				foreach ($data['properties']['timeseries']
									[$i]['data']['instant']['details'] as $key => $value)
				{
					$returnData[$i]['data'][$key] = $value;
				}	
			}
			else {
				break;
			}
		}
		return $returnData;
	}
	//funktion för att direkt hämta datan från ett platsnamn
	public function getLocationForecast($location)
	{	
		//hämtar kordinaterna för platsen 
		if ($this->callGeocodeAPI($location)){
			//hämtar datan från yr och omvandlar den direkt
			$data = $this->handleForecastData($this->callForecastAPI());
			//sätter kordinater och platsnamn
			$data['location'] = $this->_coordinates;
			return $data;
		}
		else{
			return false;
		}
	}

}
?>