<?php
date_default_timezone_set('Asia/Kolkata');
$live = 0;
$response = "";
$data ="";
$db_all="";
$ip_address = $_SERVER['REMOTE_ADDR'];
if ($ip_address == "::1")
{
	define('HOST', 'localhost');
	define('USERNAME', 'root');
	define('PASSWORD', '123456');
	$db_all="new_ccms_all";
	$date_time = date("Y/m/d H:i:s");
	
	$data ="CCMS_2;2.2;240.25;245.05;235.66;0.1;0.11;0.05;9.62;101.79;117.86;33270.45;12.16;108.91;127.42;35520.15;0;0;0;1.51;5.36;37;46.83;21015.83;50;50;50;1;1;1;1;0;0;0;0;0;0;0;0;0;1725.0397,07828.8843;4098/31;".$date_time.";58278";
}
else
{
	$live = 1;
	define('HOST', '95.111.238.141');
	define('USERNAME', 'istlabsonline_db_user');
	define('PASSWORD', 'istlabsonline_db_pass');
	define('DB2', 'notifications_db');
	$db_all="ccms_all_devices";
	if (isset($_POST['CCMS']))
	{
		$data = $_POST['CCMS'];
	}
}
if ($data != "")
{
    $crc_string = strrchr($data, ';'); // get the crc word from data
    $crc_compare = trim($crc_string, ";"); // getting crc value from the data
    $crc_len = strlen($crc_string) - 1; // getting the length of the crc word
    $data_crc = substr($data, 0, -$crc_len); // removing last crc word
    $data_crc = "CCMS=" . $data_crc;
    $for_array = substr($data, 0, -strlen($crc_string)); // removing array crc from data
    $array_data = explode(';', $for_array); //converting data to array
    $crc = 0xFFFF;
    for ($i = 0;$i < strlen($data_crc);$i++)
    {
    	$crc ^= ord($data_crc[$i]);
    	for ($j = 8;$j != 0;$j--)
    	{
    		if (($crc & 0x0001) != 0)
    		{
    			$crc >>= 1;
    			$crc ^= 0xA001;
    		}
    		else $crc >>= 1;
    	}
    }

    $db =trim(strtolower($array_data[0]));
    date_default_timezone_set("Asia/Kolkata");
    $server_date_time = date("Y-m-d H:i:s");

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD);
    if (!$conn)
    {
    	die("DB Connection failed");
    }
    else
    {
    	if ($crc == $crc_compare)
    	{

			////////////////////////////////////    GPS Manual   //////////////////////////////////////////////////
    		try {
    			$gps_coordinates="";
    			$status_flag=0;
    			$gps_sql = "SELECT  lat_long_ddm_format, update_status FROM `$db`.`coordinates_list` ORDER by id DESC limit 1";
    			if (mysqli_query($conn, $gps_sql)) 
    			{
    				$result_gps = mysqli_query($conn, $gps_sql);
    				if (mysqli_num_rows($result_gps) > 0) 
    				{
    					$r = mysqli_fetch_assoc($result_gps);
    					$status_flag = $r['update_status'];
    					$gps_coordinates = $r['lat_long_ddm_format'];
    				}
    			}
    			if($status_flag==1)
    			{
    				if($gps_coordinates!="")
    				{
    					$array_data[count($array_data) - 3] = $gps_coordinates;

    				}
    			}
    		} catch (Exception $e) {

    		}
    		///////////////////////////////////////////////////////////////////////////////////////////////////////

    		try {   		
    			$insertdata = "";
    			for ($i = 0;$i < count($array_data);$i++)
    			{
    				if($i!=1)
    				{
    					$insertdata = $insertdata . "'" . $array_data[$i] . "',";
    				}
    			}
    			$insertdata = $insertdata . "'" . $server_date_time . "'";
    			$sql = "";
    			$sql = "INSERT IGNORE INTO `$db`.`live_data` (`device_id`, `voltage_ph1`, `voltage_ph2`, `voltage_ph3`, `current_ph1`, `current_ph2`, `current_ph3`, `energy_kwh_ph1`, `energy_kwh_ph2`, `energy_kwh_ph3`, `energy_kwh_total`, `energy_kvah_ph1`, `energy_kvah_ph2`, `energy_kvah_ph3`, `energy_kvah_total`, `lag_kvarh_ph1`, `lag_kvarh_ph2`, `lag_kvarh_ph3`, `lag_kvarh_total`, `lead_kvarh_ph1`, `lead_kvarh_ph2`, `lead_kvarh_ph3`, `lead_kvarh_total`, `frequency_ph1`, `frequency_ph2`, `frequency_ph3`, `powerfactor_ph1`, `powerfactor_ph2`, `powerfactor_ph3`, `on_off_status`, `contactor_status`, `kw_1`, `kw_2`, `kw_3`, `kw_total`, `kva_1`, `kva_2`, `kva_3`, `kva_total`, `location`, `signal_level`, `date_time`, `server_date_time`) VALUES (" . $insertdata . ")";
    			if (mysqli_query($conn, $sql))
    			{

    				$insertdata = $insertdata . ",'" . $server_date_time . "'";
    				$sql = "INSERT INTO `$db_all`.`live_data_updates` 
    				(`device_id`, `voltage_ph1`, `voltage_ph2`, `voltage_ph3`, `current_ph1`, `current_ph2`, `current_ph3`, `energy_kwh_ph1`, `energy_kwh_ph2`, `energy_kwh_ph3`, `energy_kwh_total`, `energy_kvah_ph1`, `energy_kvah_ph2`, `energy_kvah_ph3`, `energy_kvah_total`, `lag_kvarh_ph1`, `lag_kvarh_ph2`, `lag_kvarh_ph3`, `lag_kvarh_total`, `lead_kvarh_ph1`, `lead_kvarh_ph2`, `lead_kvarh_ph3`, `lead_kvarh_total`, `frequency_ph1`, `frequency_ph2`, `frequency_ph3`, `powerfactor_ph1`, `powerfactor_ph2`, `powerfactor_ph3`, `on_off_status`, `contactor_status`, `kw_1`, `kw_2`, `kw_3`, `kw_total`, `kva_1`, `kva_2`, `kva_3`, `kva_total`, `location`, `signal_level`, `date_time`, `server_date_time`, `ping_time`) VALUES (" . $insertdata . ")  ON DUPLICATE KEY UPDATE `voltage_ph1` = VALUES(`voltage_ph1`), `voltage_ph2` = VALUES(`voltage_ph2`), `voltage_ph3` = VALUES(`voltage_ph3`), `current_ph1` = VALUES(`current_ph1`), `current_ph2` = VALUES(`current_ph2`), `current_ph3` = VALUES(`current_ph3`), `energy_kwh_ph1` = VALUES(`energy_kwh_ph1`), `energy_kwh_ph2` = VALUES(`energy_kwh_ph2`), `energy_kwh_ph3` = VALUES(`energy_kwh_ph3`), `energy_kwh_total` = VALUES(`energy_kwh_total`), `energy_kvah_ph1` = VALUES(`energy_kvah_ph1`), `energy_kvah_ph2` = VALUES(`energy_kvah_ph2`), `energy_kvah_ph3` = VALUES(`energy_kvah_ph3`), `energy_kvah_total` = VALUES(`energy_kvah_total`), `lag_kvarh_ph1` = VALUES(`lag_kvarh_ph1`), `lag_kvarh_ph2` = VALUES(`lag_kvarh_ph2`), `lag_kvarh_ph3` = VALUES(`lag_kvarh_ph3`), `lag_kvarh_total` = VALUES(`lag_kvarh_total`), `lead_kvarh_ph1` = VALUES(`lead_kvarh_ph1`), `lead_kvarh_ph2` = VALUES(`lead_kvarh_ph2`), `lead_kvarh_ph3` = VALUES(`lead_kvarh_ph3`), `lead_kvarh_total` = VALUES(`lead_kvarh_total`), `frequency_ph1` = VALUES(`frequency_ph1`), `frequency_ph2` = VALUES(`frequency_ph2`), `frequency_ph3` = VALUES(`frequency_ph3`), `powerfactor_ph1` = VALUES(`powerfactor_ph1`), `powerfactor_ph2` = VALUES(`powerfactor_ph2`), `powerfactor_ph3` = VALUES(`powerfactor_ph3`), `on_off_status` = VALUES(`on_off_status`), `contactor_status` = VALUES(`contactor_status`), `kw_1` = VALUES(`kw_1`), `kw_2` = VALUES(`kw_2`), `kw_3` = VALUES(`kw_3`), `kw_total` = VALUES(`kw_total`), `kva_1` = VALUES(`kva_1`), `kva_2` = VALUES(`kva_2`), `kva_3` = VALUES(`kva_3`), `kva_total` = VALUES(`kva_total`), `location` = VALUES(`location`), `signal_level` = VALUES(`signal_level`), `date_time` = VALUES(`date_time`), `server_date_time` = VALUES(`server_date_time`), `ping_time` = VALUES(`ping_time`)";
    				mysqli_query($conn, $sql);
    				http_response_code(202);
    			//$response = mainfunction($array_data[0], $conn);
    				echo "Saved";
    			}
    			else
    			{
    			//$response = mainfunction($array_data[0]);
    				echo $response;
    				echo "frame-FAIL";
    			}
    		} catch (Exception $e) {
    			echo "Data-Error";
    		}
    	}
    	else
    	{
    		http_response_code(202);
    		echo "CRC-FAIL";
    	}
    }
    mysqli_close($conn);
}
else
{
	echo "000;No data";
}

?>
