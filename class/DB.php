<?php
class DB extends PDO
{
	private static $instance = null;
	public static function getInstance($dbname)
  {
    if (self::$instance == null)
    {
      self::$instance = new DB($dbname);
    }
 
    return self::$instance;
  }
	private function __construct($dbname)
	{
		try {
			parent::__construct("mysql:host=localhost;dbname=$dbname;charset=utf8", "root", "");
		} 
		catch (Exception $e) {
			echo "<pre>" . print_r($e, 1) . "</pre>";
		}
	}
	//Lägger till en plats i databasen
	public function insertLocation($data)
	{
		//query som ignorerar ogiltiga värden 
		$query = "INSERT IGNORE INTO location(locationName, lat, lng)
		VALUES (:name, :lat, :long)";
		//lista med värden
		$values = array(
				':name' => $data['location']['areaName'] ,
				':lat'  => $data['location']['lat'] ,
				':long' => $data['location']['lon'] 
				);
		//förbered query
		$sth = $this->prepare($query);
		//utför query
		if ($sth->execute($values)) {
			return true;
		} else {
			//Går något fel skriver vi ut ett felmedellande
			echo "<pre>" . print_r($sth->errorInfo(), 1) . "</pre>";
			return false;
		}
	}
	//Lägger till datan i databasen
	public function insertData($data, $area)
	{
		//query för att hämta plats id:et
		$query = "SELECT locationName, id FROM location WHERE locationName=
		'" . $area . "'";
		//fortsätter om vi fick platsnamn och id
		if ($result = $this->query($query)) {
			//lägger id:et i en variabel
			$id = $result->fetchColumn(1);
			//query som ignorerar ogiltiga värden
			$query = "INSERT IGNORE INTO data(
					locRef, pressure, temp, cloudArea, 
					humidity, windDirection, windSpeed, timeStamp
					) VALUES (
					:locRef, :pressure, :temp, :cloudArea, 
					:humidity, :windDirection, :windSpeed, :timeStamp)";
			//lista med värden
			$values = array(
					':locRef' => $id ,
					':pressure' =>$data['data']['air_pressure_at_sea_level'] ,
					':temp' =>$data['data']['air_temperature'] ,
					':cloudArea' =>$data['data']['cloud_area_fraction'] ,
					':humidity' =>$data['data']['relative_humidity'] ,
					':windDirection' =>$data['data']['wind_from_direction'] ,
					':windSpeed' =>$data['data']['wind_speed'],
					':timeStamp' =>$data['date']['date'] . 'T' . $data['date']['time'] . 'Z' . $id
					);
			//förbered query
			$sth = $this->prepare($query);
			//utför query
			if ($sth->execute($values)) {
				return true;
			} else {
				//Går något fel skriver vi ut ett felmedellande
				echo "<pre>" . print_r($sth->errorInfo(), 1) . "</pre>";
				return false;
			}
		}
	}
	//Hämtar datan från databasen
	public function getData($locationName)
	{
		//query för att hämta datan
		$query = "SELECT
				location.locationName,
				data.temp,
				data.humidity,
				data.windSpeed,
				data.timeStamp
				FROM location
				INNER JOIN data
				ON location.id = data.locRef
				WHERE location.locationName = '$locationName'
				ORDER BY data.timestamp";
		//om vi kunde hämta datan så retuneras den som en accosiativ lista 
		if ($result = $this->query($query)) {
			return $data = $result->fetchAll(PDO::FETCH_ASSOC);
		} else {
			echo "<pre>" . print_r($this->errorInfo(), 1) . "</pre>";
		}
	}
}
?>