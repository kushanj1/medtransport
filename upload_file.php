<?php

$tripdata_file = $_FILES["tripdata"];
$driverdata_file = $_FILES["driverdata"];

if ($tripdata_file["error"] > 0)
{
	var_dump($tripdata_file["error"]);
}
elseif ($driverdata_file["error"] > 0)
{
	var_dump($driverdata_file["error"]);
}
else
{
/*
	echo "Upload: " . $tripdata_file["name"] . "<br>";
	echo "Type: " . $tripdata_file["type"] . "<br>";
	echo "Size: " . ($tripdata_file["size"] / 1024) . " kB<br>";
	echo "Stored in: " . $tripdata_file["tmp_name"] . "<br/>";
	echo "Upload: " . $driverdata_file["name"] . "<br>";
	echo "Type: " . $driverdata_file["type"] . "<br>";
	echo "Size: " . ($driverdata_file["size"] / 1024) . " kB<br>";
	echo "Stored in: " . $driverdata_file["tmp_name"];
*/

	doStuff($tripdata_file, $driverdata_file);
}

function getDriverData($driverdata_file)
{
	$dr_file = $driverdata_file["tmp_name"];
	$dr_handle = fopen($dr_file, "r");
	$driver_data = array();

	while (($dr_csvdata = fgetcsv($dr_handle)) !== FALSE) 
	{
		if(!isset($driver_data[$dr_csvdata[1]]))
		{
			$driver_data[$dr_csvdata[1]] = array();
		}
		
		$driver_data[$dr_csvdata[1]][] = $dr_csvdata[0];
	}
	fclose($dr_handle);
	return $driver_data;
}

function getDriver($first_name, $last_name, $driver_data)
{
	$full_name = strtolower($first_name) . ' ' . strtolower($last_name);
	foreach($driver_data as $driver => $passengers)
	{
		if(in_array($full_name, array_map('strtolower', $passengers)))
		{
			return $driver;
		}
	}
	return;
}

function createTripLog($tripdata)
{
	$triplog = array();
	foreach($tripdata as $trip)
	{
		if(!empty($trip['driver']))
		{
			$triplog[]  = array(
				ucwords($trip['driver']),
				$trip['full_name'],
				$trip['trip_number'],
			);
		}
	}
	return $triplog;
}

function doStuff($tripdata_file, $driverdata_file)
{

	$file = $tripdata_file["tmp_name"];

	$handle = fopen($file, "r");

	$tripdata = array();

	$driver_data = getDriverData($driverdata_file);

	while (($csvdata = fgetcsv($handle)) !== FALSE) 
	{
		$driver = getDriver($csvdata[1], $csvdata[0], $driver_data);
		$tripdata[] = array(
			'last_name' => $csvdata[0],
			'first_name' => $csvdata[1],
			'phone_number' => $csvdata[2],
			'alt_phone_number' => $csvdata[3],
			'trip_number' => $csvdata[4],
			'appointment_time' => $csvdata[5],
			'trip_status' => $csvdata[6],
			'vehicle_type' => $csvdata[7],
			'trip_cost' => $csvdata[8],
			'pickup_address' => $csvdata[9],
			'pickup_city' => $csvdata[10],
			'pickup_state' => $csvdata[11],
			'pickup_zip_code' => $csvdata[12],
			'delivery_address' => $csvdata[13],
			'delivery_city' => $csvdata[14],
			'delivery_state' => $csvdata[15],
			'delivery_zip_code' => $csvdata[16],
			'delivery_phone_number' => $csvdata[17],
			'driver' => isset($driver) ? $driver : '',
			'full_name' => ucwords(strtolower(($csvdata[1] . ' ' . $csvdata[0]))),
		);
	}


	$assigned = 0;
	foreach($tripdata as $trip)
	{
		if(!empty($trip['driver']))
		{
			$assigned++;
		}
	}

	$triplog = createTripLog($tripdata);

//	echo "<br/>";
//	echo "Total: " . count($tripdata) . "<br/>";
//	echo "Assigned: " . $assigned . "<br/>"; 

	fclose($handle);


	download_send_headers("trip_log_" . date("YmdHis") . ".csv");
	ob_start();
	$fp = fopen('php://output', 'w');

	fputcsv($fp, array('Driver', 'Customer', 'Trip Number'));
	foreach ($triplog as $line) {
	    fputcsv($fp, $line);
	}

	fclose($fp);
	echo ob_get_clean();
}

function download_send_headers($filename) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}
