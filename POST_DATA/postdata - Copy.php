<?php
date_default_timezone_set('Asia/Kolkata');
$live = 0;
$response = "";
$data ="";
$ip_address = $_SERVER['REMOTE_ADDR'];
if ($ip_address == "::1")
{
	define('HOST', 'localhost');
	define('USERNAME', 'root');
	define('PASSWORD', '123456');

	$date_time = date("Y/m/d H:i:s");
	$data = "CCMS_3;000.0;001.04;001.04;001.06;000.005;000.000;000.000;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00000000.0;00.0;00.0;00.0;0.000;-0.001;-0.001;0;0000.0000;0000.0000;0000.0000;0000.0000;0000.0001;0000.0000;0000.0000;0000.0001;000000000,0000000000;27/4300;$date_time;58278";
}
else
{
	$live = 1;
	define('HOST', '95.111.238.141');
	define('USERNAME', 'istlabsonline_db_user');
	define('PASSWORD', 'istlabsonline_db_pass');
	define('DB2', 'notifications_db');
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

    define('DB', $db);
    date_default_timezone_set("Asia/Kolkata");
    $server_time = date("Y-m-d H:i:s");

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB);
    if (!$conn)
    {
    	die("Connection failed: " . mysqli_connect_error());
    }
    else
    {

    	/*if(trim($array_data[0])=="BMC_CCMS_81")
    	{
    		store_data($data, $conn );
        	//http_response_code(202);
    	}*/

    	if ($live == 1)
    	{
    		update($array_data[0]);

    	}
        /////////////////////////////  IOT Communication break check ////////////////////////////////
    	try
    	{
    		date_default_timezone_set("Asia/Kolkata");
    		$server_time = date("Y-m-d H:i:s");
    		$frame_date_time = $array_data[count($array_data) - 1];
    		$ping_date_time = $server_time;
    		$prev_frame_date_time = "";
    		$device_break_down = 0;
    		$pres_frame_date = "";
    		try
    		{
    			$sql_frame_time = "SELECT Date_Time FROM frame_data WHERE Date_Time NOT LIKE '%0000-00-00 00:00:00%'  ORDER BY Date_Time DESC LIMIT 1";
    			if (mysqli_query($conn, $sql_frame_time))
    			{
    				$result_frame_time = mysqli_query($conn, $sql_frame_time);
    				$count_frame_time = mysqli_num_rows($result_frame_time);
    				if ($count_frame_time > 0)
    				{
    					$r_fr = mysqli_fetch_assoc($result_frame_time);
    					$prev_frame_date_time = $r_fr['Date_Time'];
    				}
    			}
    			if ($prev_frame_date_time != "")
    			{
    				$string_date = str_replace('/', '-', $array_data[count($array_data) - 1]);

                    // $date_new_string = date_create($string_date);
                    // $pres_frame_date = date("Y-m-d H:i:s", $date_new_string);
    				$date = new DateTime($string_date);
    				$pres_frame_date = $date->format('Y-m-d H:i:s');

    				$current_time = date("Y-m-d H:i:s", strtotime($server_time) + 300);
    				$aa = strtotime($pres_frame_date);
    				$bb = strtotime($current_time);
    				$cc = strtotime($prev_frame_date_time);

    				if ($aa < $bb && $aa > $cc)
    				{
    					$aa = (int)($aa / 60);
    					$cc = (int)($cc / 60);
    					$device_break_down = $aa - $cc;
    					if ($device_break_down <= 2)
    					{
    						$device_break_down = 0;
    					}
    				}
    			}
    		}
    		catch(Exception $e)
    		{
    		}
    		$sql = "SELECT id, ping_date_time FROM device_communication_break ORDER BY id DESC LIMIT 1";
    		if (mysqli_query($conn, $sql))
    		{
    			$result = mysqli_query($conn, $sql);
    			$count = mysqli_num_rows($result);
    			if ($count >= 1)
    			{
    				$r = mysqli_fetch_assoc($result);
    				$date_time = $r['ping_date_time'];
    				$s_id = $r['id'];
                    //$main_min= strtotime($frame_date_time);
    				$server_min = (int)(strtotime($server_time) / 60);
    				$old_server_min = (int)(strtotime($date_time) / 60);
    				$ping_min = (int)(strtotime($ping_date_time) / 60);
    				$server_diff = $server_min - $old_server_min;
    				$ping_diff = $server_min - $ping_min;

    				/////////////////////////////////// frame Update time  ////////////////////////////////
    				$frame_update_time=60;
    				$sql_frame_time = "SELECT * FROM `update_time_interval` ORDER BY id DESC LIMIT 1";
    				if (mysqli_query($conn, $sql_frame_time))
    				{
    					$result_frame_time = mysqli_query($conn, $sql_frame_time);
    					if (mysqli_num_rows($result_frame_time)> 0)
    					{
    						$r_f_time = mysqli_fetch_assoc($result_frame_time);
    						$frame_update_time = $r_f_time['time'];
    					}
    				}
    				$frame_update_time=30;
    				if($frame_update_time>60)
    				{
    					$frame_update_time=$frame_update_time+29;
    					$frame_update_time=round($frame_update_time/60);
    				}
    				else
    				{
    					$frame_update_time=1;
    				}

    				$frame_update_time=$frame_update_time+3;
    				$down_time_frame=$frame_update_time-1;

    				///////////////////////////////////////////////////////////////////////////////////////////////////////

    				if ($server_diff > $frame_update_time)
    				{
    					mysqli_query($conn, "INSERT INTO `device_communication_break` ( `previous_server_date_time`, `present_server_date_time`, `frame_date_time`, `ping_date_time`, `break_time`,`device_break_down_time`) VALUES ( '$date_time', '$server_time', '$frame_date_time','$server_time' , '$server_diff','$device_break_down');");

    				}
    				else if ($device_break_down > $down_time_frame && $server_diff < $frame_update_time)
    				{

    					$pre_d_time = 0;
    					$_update_s_no = 0;
    					$sql_prev_time_to_update = "SELECT `device_break_down_time`, `id` FROM `device_communication_break`  WHERE present_server_date_time >='$pres_frame_date' or present_server_date_time <=(SELECT present_server_date_time FROM `device_communication_break`  WHERE present_server_date_time <='$pres_frame_date' ORDER BY `id` DESC limit 1) limit 1";

    					if (mysqli_query($conn, $sql_prev_time_to_update))
    					{
    						$result_down_time_update = mysqli_query($conn, $sql_prev_time_to_update);
    						$down_time_update_time = mysqli_num_rows($result_down_time_update);
    						if ($down_time_update_time > 0)
    						{
    							$r_update = mysqli_fetch_assoc($result_down_time_update);

    							$_update_s_no = $r_update["id"];
    							$pre_d_time = $r_update["device_break_down_time"];
    						}

    					}
    					$device_break_down = $device_break_down + $pre_d_time;

    					$up_sql = "UPDATE `device_communication_break` SET `device_break_down_time` = '$device_break_down' WHERE `id`='$_update_s_no' ";
    					mysqli_query($conn, $up_sql);
    				}
    				else
    				{
    					mysqli_query($conn, "UPDATE device_communication_break SET ping_date_time ='$server_time' WHERE id='$s_id'");
    				}

    			}
    			else
    			{
    				$ins_sql = "INSERT INTO `device_communication_break` (`previous_server_date_time`, `present_server_date_time`, `frame_date_time`, `ping_date_time`, `break_time`, `device_break_down_time`) VALUES ( '$server_time', '$server_time', '$frame_date_time','$server_time' , '0', '0');";
    				mysqli_query($conn, $ins_sql);
    			}
    		}
    	}
    	catch(Exception $e)
    	{
    	}
        ///////////////////////////////////////////////////////////////////////////////////////////////////////
    	update_ping($array_data[0], $conn);
    	if ($crc == $crc_compare)
    	{
            //update($array_data[0]);
    		$insertdata = "";
    		for ($i = 0;$i < count($array_data);$i++)
    		{
    			if ($array_data[0] == "CCMS_101" && $i == (count($array_data) - 3))
    			{
    				$array_data[$i] = "1724.49687,7828.31645";
    			}
    			if ($array_data[0] == "CCMS_102" && $i == (count($array_data) - 3))
    			{
    				$array_data[$i] = "1724.5155,7828.29263";
    			}
    			if ($array_data[0] == "BMC_CCMS_50" && $i == (count($array_data) - 3))
    			{
    				$array_data[$i] = "2315.70966,7725.19068";
    			}
    			if ($array_data[0] == "BMC_CCMS_122" && $i == (count($array_data) - 3))
    			{
    				$array_data[$i] = "2316.47146,7728.30812";
    			}



    			$insertdata = $insertdata . "'" . $array_data[$i] . "',";

                /*if(count($array_data)==42&& $i==42-12)
                {
                $insertdata=$insertdata."'0',";
            }*/
        }

        $server_date_time = date("Y-m-d H:i:s");
        $insertdata = $insertdata . "'" . $server_date_time . "'";

        $sql = "";
        $sql = "INSERT IGNORE INTO `ccms_data_live` (`device_id`,  `board_temperature`, `voltage_ph1`, `voltage_ph2`, `voltage_ph3`, `current_ph1`, `current_ph2`, `current_ph3`, `energy_kwh_ph1`, `energy_kwh_ph2`, `energy_kwh_ph3`, `energy_kwh_total`, `energy_kvah_ph1`, `energy_kvah_ph2`, `energy_kvah_ph3`, `energy_kvah_total`, `lag_kvarh_ph1`, `lag_kvarh_ph2`, `lag_kvarh_ph3`, `lag_kvarh_total`, `lead_kvarh_ph1`, `lead_kvarh_ph2`, `lead_kvarh_ph3`, `lead_kvarh_total`, `frequency_ph1`, `frequency_ph2`, `frequency_ph3`, `powerfactor_ph1`, `powerfactor_ph2`, `powerfactor_ph3`, `on_off_status`, `contactor_status`, `kw_1`, `kw_2`, `kw_3`, `kw_total`, `kva_1`, `kva_2`, `kva_3`, `kva_total`, `location`, `signal_level`, `Date_Time`, `server_date_time`) VALUES  (" . $insertdata . ")";

        if (mysqli_query($conn, $sql))
        {
        	http_response_code(202);
        	$response = mainfunction($array_data[0], $conn);
        	echo $response;
        }
        else
        {
        	$response = mainfunction($array_data[0]);
        	echo $response;
        }
    }
    else
    {
    	http_response_code(202);
    	$response = mainfunction($array_data[0], $conn);
    	echo $response;
    }
}
mysqli_close($conn);
}
else
{
	echo "000;No data";
}
function mainfunction($data, $conn)
{
	$array_data = explode(';', $data);
	$response = "";
	$length = 0;
	$sql = "";
	$condtion = "NA";
	$device_id = $array_data[0];

	$sql = "SELECT *from (SELECT * FROM device_settings WHERE setting_type IN ('ONOFF','VOLTAGE','CURRENT','FRAME_TIME','HYSTERESIS', 'CALIB_VALUES', 'SCHEDULE_TIME','LOOP_ON_OFF', 'SOFTWARE',  'ID_CHANGE', 'ENERGY_RESET', 'WIFI_CREDENTIALS', 'SERIAL_ID', 'RESET') ORDER BY setting_type DESC) a UNION ALL (SELECT * FROM device_settings WHERE setting_type='DEFAULT' LIMIT 1)";

	$check = array();
	if (mysqli_query($conn, $sql))
	{
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0)
		{
			while ($r = mysqli_fetch_assoc($result))
			{
				$check[] = array(
					'type' => $r['setting_type'],
					'flag' => $r['setting_flag']
				);

			}
		}
		else
		{
			$response = "No data";
		}
	}
	$values = json_encode($check);
	$set_list = json_decode($values);
	foreach ($set_list as $key => $value)
	{
		$condtion = $value->type;
		$set_flag = $value->flag;
		if ($set_flag == 1 || $set_flag == 2)
		{
			break;
		}
		else
		{
			$condtion = "NA";
		}
	}
	switch ($condtion)
	{
		case "VOLTAGE":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM limit_voltage ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$min_1 = (int)$r['min_1'];
					$min_2 = (int)$r['min_2'];
					$min_3 = (int)$r['min_3'];
					$max_1 = (int)$r['max_1'];
					$max_2 = (int)$r['max_2'];
					$max_3 = (int)$r['max_3'];
					$MIN = $min_1 . ";" . $min_2 . ";" . $min_3;
					$MAX = $max_1 . ";" . $max_2 . ";" . $max_3;
					$data = "MIN=" . $MIN . ";MAX=" . $MAX . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "CURRENT":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM limit_current ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$max_1 = (int)$r['max_1'];
					$max_2 = (int)$r['max_2'];
					$max_3 = (int)$r['max_3'];
					$MAX = $max_1 . ";" . $max_2 . ";" . $max_3;
					$data = "LMA=" . $MAX . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "ONOFF":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM on_off_activity ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$switch = $r['on_off'];
					$data = $switch . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "FRAME_TIME":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM update_time_interval ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$update_interval = (int)$r['time'];
					$data = "SETTIME=" . $update_interval . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "HYSTERESIS":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM hysteresis_history ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$hysteresis = (int)$r['set_value'];
					$data = "HYST=" . $hysteresis . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;

		case "LOOP_ON_OFF":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM on_off_interval_history ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$val = (int)$r['set_value'];
					if ($val > 0)
					{
						$val = "1;" . $val;
					}
					else
					{
						$val = "0;0";
					}

					$data = "LOOP_ON_OFF=" . $val . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "CALIB_VALUES":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM calib_settings ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$calib_val = $r['frame'];
					$data = "CALIB:" . $calib_val;
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "SCHEDULE_TIME":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM schedule_time ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$check_status = strtoupper($r['status']);
					$scdl_time = "";
					if ($check_status == "ENABLED")
					{
						$ontime = $r['on_time'];
						$offtime = $r['off_time'];
						$ontime = str_replace(":00", "", $ontime);
						$ontime = str_replace(":", "", "$ontime");
						$offtime = str_replace(":00", "", $offtime);
						$offtime = str_replace(":", "", "$offtime");

						$ontime = str_pad($ontime, 4, '0', STR_PAD_RIGHT);
						$offtime = str_pad($offtime, 4, '0', STR_PAD_RIGHT);

						$scdl_time = "SCHDON=" . $ontime . ";" . $offtime . ";";
					}
					else
					{
						$scdl_time = "SCHDOFF;";
					}
					$data = $scdl_time;
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "SOFTWARE":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$data = "S_UPDATE;";
			$len = strlen($data);
			$length = str_pad($len, 3, '0', STR_PAD_LEFT);
			$data = $length . ";" . $data;
			$response = "CCMS=" . $device_id . ";" . $data;
		}
		break;
		case "RESET":
		$sql = "UPDATE device_settings SET setting_flag='0' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$data = "RESET=0;";
			$len = strlen($data);
			$length = str_pad($len, 3, '0', STR_PAD_LEFT);
			$data = $length . ";" . $data;
			$response = "CCMS=" . $device_id . ";" . $data;
		}
		break;
		case "DEFAULT":
            //$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		$sql = "UPDATE device_settings SET setting_flag='0' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$data = "DEFAULT;";
			$len = strlen($data);
			$length = str_pad($len, 3, '0', STR_PAD_LEFT);
			$data = $length . ";" . $data;
			$response = "CCMS=" . $device_id . ";" . $data;
		}
		break;

		case "ID_CHANGE":
		$sql = "UPDATE device_settings SET setting_flag='0' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM history_device_id_change ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$send_data = $r['new_id'];
					$data = "DEVID=" . $send_data . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;

		case "SERIAL_ID":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM history_serial_id_change ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$send_data = $r['serial_id'];
					$data = "SNO=" . $send_data . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;

		case "ENERGY_RESET":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM history_energy_reset ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$kwh = $r['kwh'];
					$kvah = $r['kvah'];
					$data = "ECLR=" . $kwh . ";" . $kvah . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;

		case "WIFI_CREDENTIALS":
		$sql = "UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM history_wifi_credentials ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					$r = mysqli_fetch_assoc($result);
					$access_point_name = $r['access_point_name'];
					$pwd = $r['password'];
					$data = "WIFI=" . $access_point_name . ";PWD=" . $pwd . ";";
					$len = strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data = $length . ";" . $data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "NA":
		$response = "No Data";
		break;
		default:
		$response = "No Data";
	}
	return $response;
}
function update_ping($deviceid, $conn)
{
	try
	{
		$date_time = date("Y-m-d H:i:s");
		mysqli_query($conn, "INSERT INTO communication_check ( `device_id`, `date_time`) VALUES('$deviceid', '$date_time') ON DUPLICATE KEY UPDATE device_id='$deviceid', date_time='$date_time'");
	}
	catch(exeption $e)
	{
	}
}
function update($deviceid)
{

	$date_time = date("Y-m-d H:i:s");
	$time = date("H:i:s");
	$date = date("Y-m-d ");
	try
	{
		$log_conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB2);
		if (!$log_conn)
		{
			die("Connection failed: ");
		}
		else
		{

			mysqli_query($log_conn, "INSERT INTO devices ( `device`, `status`) VALUES('$deviceid', '1') ON DUPLICATE KEY UPDATE device='$deviceid', status='1'");

			$sql = "INSERT INTO device_logs ( `device_id`, `time`, `date`, `date_time`, `notification_flag`) VALUES('$deviceid', '$time', '$date', '$date_time', '0') ON DUPLICATE KEY UPDATE device_id='$deviceid', `time`='$time', `date`='$date', `date_time` = '$date_time', `notification_flag`='0'";
			mysqli_query($log_conn, $sql);

			mysqli_close($log_conn);
		}
	}
	catch(exeption $e)
	{
	}
}
function store_data($data, $conn)
{
	try
	{

		date_default_timezone_set('Asia/Kolkata');
		$date = date("Y-m-d H:i:s");
		mysqli_query($conn, "INSERT INTO `check_main_frame` ( `frame_type`,`frame`, `date_time`) VALUES ('MAIN','$data', '$date')");
	}
	catch(Exception $e)
	{

	}
}

?>
