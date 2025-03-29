<?php
date_default_timezone_set('Asia/Kolkata');
$live=0;
$data="";
$central_db="";
$conn;
$response="";
$device_db;
$device_id="";
$msgSentConfirm=false;

$ip_address = $_SERVER['REMOTE_ADDR'];
if($ip_address=="::1")
{
	define('HOST','localhost');
	define('USERNAME', 'root');
	define('PASSWORD','123456');
	define('USER_DB', 'new_ccms_user_db');
	define('DB_ALL', 'new_ccms_all');
	//$data="CCMS_41;ALERT;SUPPLY;3;234;235;236;23;24;25;23/07/29 12:23:34";
	//$data="CCMS_3;SUPDATE;";
	
	//$data="CCMS_1;ACK;LOADCLEAR";   //UPDATETIME, LOADCLEAR, MINMAXSET,ONOFFLIGHT ,HYSTVAL, CALIB, SCHEDULE_TIME, LOOP_ON_OFF UPDATETIME ECLR WIFI SERIALNO

	$data="CCMS_1;ACK;UPDATE_TIME";  /////VOLTAGE_LIMIT , CURRENT_LIMIT , UPDATE_TIME
	//$data="CCMS_1;REPORT;SYSTEMSTATUS;1,1,1,1,1,1,0,0,0,0;2024/10/07 17:42:45;";  
	//$data="CCMS_1;ALERT;VOLTAGE;000;258;265;245;25;35;51;2024-10-09 15:45:02";
	$data="CCMS_1;ALERT;CURRENT;010;258;265;245;25;35;51;2024-10-09 15:45:02";
	$data="CCMS_1;ALERT;CONTACTOR;011;258;265;245;25;35;51;2024-10-09 15:45:02";
	$data="CCMS_1;EVENTS;IP_POWER_SMPS;1;4358;2024-10-09 15:45:02";
}
else
{
	define('HOST','95.111.238.141');
	define('USERNAME', 'istlabsonline_db_user');
	define('PASSWORD','istlabsonline_db_pass');
	define('USER_DB', 'ccms_user_details');
	define('DB_ALL', 'ccms_all_devices');	
	$live=1;
	if(isset($_POST['REQ']))
	{
		$data= $_POST['REQ'];
	}
}
$user_db=USER_DB;
$central_db=DB_ALL;

if($data!="")
{
	$array_data = explode(';', $data);
	$device_id=trim(strtoupper($array_data[0]));
	$device_db=strtolower($device_id);
	
	$conn =  mysqli_connect(HOST,USERNAME,PASSWORD);
	if (!$conn) 
	{
		die("DB Connection failed");
	}
	else
	{
		if (trim($array_data[1]) =='ACK')
		{
			$condtion = trim($array_data[2]);

			switch ($condtion)
			{
				case "ONOFFLIGHT":
				clear_ack("ONOFF", "on_off_activities");
				break;

				case "SCHEDULE_TIME" :
				clear_ack("SCHEDULE_TIME", "on_off_modes");
				break;

				case "ON_OFF_MODE" :
				clear_ack("ON_OFF_MODE", "on_off_modes");
				break;

				case "VOLTAGE_LIMIT" :
				clear_ack("VOLTAGE", "limits_voltage");
				break;

				case "CURRENT_LIMIT" :
				clear_ack("CURRENT", "limits_current");
				break;

				case "UPDATE_TIME" :
				clear_ack("FRAME_TIME", "frame_time");
				break;

				case "HYSTVAL" :
				clear_ack("HYSTERESIS", "iot_hysteresis");
				break;

				case "CALIB" :					
				clear_ack("CALIB_VALUES", "iot_calibration_values");
				break;

				case "LOOP_ON_OFF" :					
				clear_ack("LOOP_ON_OFF", "iot_on_off_interval");
				break;

				case "ECLR" :					
				clear_ack("ENERGY_RESET", "iot_reset_energy");
				break;

				case "WIFI" :					
				clear_ack("WIFI_CREDENTIALS", "iot_wifi_credentials");
				break;

				case "SERIALNO" :					
				clear_ack("SERIAL_ID", 'iot_serial_id_change');
				break;

				case "SUPDATE" :						
				clear_ack("SOFTWARE", "");						
				$date=date("Y-m-d H:i:s");
				mysqli_query($conn, "INSERT INTO `$device_db`.`software_update_status` (`status`, `status_code`, `date_time`) VALUES ('$array_data[3]', '0', '$date')");
				break;

				default:

			}

		}
		else if ($array_data[1] =='ALERT')
		{
			//ALERT;VOLTAGE;012;v1;v2;v3;i1;i2;i3;date_time;


			if($array_data[2]=="VOLTAGE")
			{
				interpretPhaseAlertCode($array_data[3],"VOLTAGE", $array_data[4], $array_data[5], $array_data[6], $array_data[7], $array_data[8], $array_data[9], $array_data[10]);
			}
			elseif($array_data[2]=="CURRENT")
			{

				interpretPhaseAlertCode($array_data[3],"CURRENT",$array_data[4], $array_data[5], $array_data[6], $array_data[7], $array_data[8], $array_data[9], $array_data[10]);
			}

			elseif($array_data[2]=="CONTACTOR")
			{

				interpretPhaseAlertCode($array_data[3],"CONTACTOR",$array_data[4], $array_data[5], $array_data[6], $array_data[7], $array_data[8], $array_data[9], $array_data[10]);
			}

		}
		else if ($array_data[1] =='EVENTS')
		{
			$command = $array_data[2];
			switch ($command)
			{
				case "ONOFF":
				onoff_update($conn, $array_data[0], $array_data[3], $array_data[4]);
				break;

				case "IP_POWER_SMPS":
				//EVENTS;IP_POWER_SMPS;1-8;4358;date_time
				ip_power_smps_status_update($conn, $array_data[0], $array_data[3], $array_data[4], $array_data[5]);
				break;

				case "DO":
				$sql_q = "INSERT INTO `$device_db`.`alert_door` (`alert`, `date_time`) VALUES ('Door Open', '$array_data[3]')";
				mysqli_query($conn, $sql_q);
				$device_name = get_name();
				$deviceid_for_msg = check_name($device_name);
				$date_time=$array_data[3];
				$msg = "ID:$deviceid_for_msg, Door Opened, Time :".$date_time;
				sendMessage($msg, "door_alert");
				messageSaveInCentralTable("PANEL DOOR", $deviceid_for_msg, $msg, $date_time);
				messageSaveInDeviceTable("PANEL DOOR", $deviceid_for_msg, $msg, $date_time);
				break;

				case "DC":
				$sql_q = "INSERT INTO `$device_db`.`alert_door` (`alert`, `date_time`) VALUES ('Door Closed', '$array_data[3]')";
				mysqli_query($conn, $sql_q);
				$device_name = get_name();
				$deviceid_for_msg = check_name($device_name);
				$date_time=$array_data[3];
				$msg = "ID:$deviceid_for_msg, Door Closed, Time :".$date_time;
				sendMessage($msg, "door_alert");
				messageSaveInCentralTable("PANEL DOOR", $deviceid_for_msg, $msg, $date_time);
				messageSaveInDeviceTable("PANEL DOOR", $deviceid_for_msg, $msg, $date_time);
				break;

				default:
				$response = "ERROR EVENT";
			}

		}
		else if ($array_data[1] =='SUPDATE')
		{
			$sql="SELECT software FROM software_update `$device_db`.ORDER BY id DESC LIMIT 1" ;
			if (mysqli_query($conn, $sql)) 
			{
				$result = mysqli_query( $conn, $sql);
				if(mysqli_num_rows($result)>0)
				{
					while ($r=  mysqli_fetch_assoc( $result )) 
					{
						$response=$r['software']; 
					}
				}
				else
				{
					$response ="Firmware Not Found";
				}
			} 
		}
		else if ($array_data[1] =='OFFLINE'||$array_data[1] =='DEFAULT')
		{
			$sql="INSERT INTO `saved_settings_on_device` (`frame`) VALUES ( '$data')" ;
			mysqli_query($conn, $sql);
		}
		else if($array_data[1]=='REPORT')
		{
			try 
			{	
				$date_time=date("Y-m-d H:i:s");	
				if($array_data[2]=="SIMCOM_OFF")
				{
					mysqli_query($conn, "INSERT INTO `$device_db`.`sim_module_communication` ( `date_time`, `server_time`) VALUES ( '$array_data[3]', '$date_time');");
				}
				if($array_data[2]=="SYSTEM INFO")
				{

					mysqli_query($conn, "INSERT INTO `$device_db`.system_info ( `info`, `date_time`) VALUES('$array_data[3]', '$date_time') ON DUPLICATE KEY UPDATE info='$array_data[3]', date_time='$date_time'");
				}
				if($array_data[2]=="SIMCOMSTATUS")
				{
					mysqli_query($conn, "INSERT INTO `$device_db`.`simcom_status` (`status`, `status_code`, `date_time`,`server_date_time`) VALUES ('$array_data[3]', '$array_data[4]', '$array_data[5]','$date_time');");
				}
				if($array_data[2]=="SYSTEMSTATUS")
				{
					$array_data[3]=trim($array_data[3]);
					$s_status="";
					$s_id=0;
					$sql="SELECT * FROM `$device_db`.`system_status` ORDER BY id DESC LIMIT 1" ;
					if (mysqli_query($conn, $sql)) 
					{
						$result = mysqli_query( $conn, $sql);
						if(mysqli_num_rows($result)>0)
						{
							$r=  mysqli_fetch_assoc( $result );								
							$s_status=trim($r['status']); 
							$s_id=trim($r['id']); 
						}
					}
					$upd_status_flag=0;
					if($s_status== trim($array_data[3]))
					{
						mysqli_query($conn, "UPDATE `$device_db`.`system_status`  SET `date_time`='$array_data[4]' WHERE id='$s_id'");
					}
					else{
						mysqli_query($conn, "INSERT INTO `$device_db`.`system_status` ( `status`, `date_time`, `prev_date_time` , `server_date_time`) VALUES ( '$array_data[3]', '$array_data[4]', '$array_data[4]', '$date_time');");
						$upd_status_flag=1;
					}
					if($upd_status_flag)
					{
						$old_on_off_status=explode(",", $s_status);
						$new_on_off_status=explode(",", trim($array_data[3]));
						if($old_on_off_status[6]!=$new_on_off_status[6])
						{	
							$deviceid=$array_data[0];
							$device_name=$deviceid;
							$device_name = get_name($deviceid, $conn);

							$deviceid_for_msg="";
							if($deviceid!=$device_name)
							{
								$deviceid_for_msg=$deviceid."(". $device_name.")";
							}
							else
							{
								$deviceid_for_msg=$deviceid;
							}
							if($new_on_off_status[6]==1||$new_on_off_status[6]==3||$new_on_off_status[6]==4||$new_on_off_status[6]==5)
							{
								$on_command="";
								if($new_on_off_status[6]==1)
								{
									$on_command="AUTO";
								}
								else if($new_on_off_status[6]==3)
								{
									$on_command="SERVER";
								}
								else if($new_on_off_status[6]==4)
								{
									$on_command="APP";
								}
								else if($new_on_off_status[6]==5)
								{
									$on_command="MANUAL";
								}
								$msg="ID:$deviceid_for_msg Switched ON($on_command) Lights";
							}
							else
							{
								$msg="ID:$deviceid_for_msg Switched OFF Lights";
							}
							//sendsms($msg, $conn, $array_data[0]);
						}
					}
				}
				else
				{
					$update_command = $array_data[2]; 
					$status = $array_data[3];
					$date_time = $date_time; 
					$query = " INSERT INTO `$device_db`.`device_check_report` (parameter, status, date_time)  VALUES ('$update_command', '$status', '$date_time') ON DUPLICATE KEY UPDATE status = VALUES(status), date_time = VALUES(date_time)";

					mysqli_query($conn, $query);
				}
			} 
			catch (Exception $e) 
			{
			}

		}

		ping_update();

		if($response==null||$response=="")
		{
			$response= device_setting();
		}
		http_response_code(201);
		mysqli_close($conn);
		echo $response;
	}
}
else
{
	echo "000;Posting Error";
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// ping statu Update   /////////////////////////////////////////////////////////

function ping_update()
{
	global $device_id;
	global $conn;
	global $device_db;
	global $central_db;
	$ping_date_time = date("Y-m-d H:i:s");

	try {
		$ping_sql = "INSERT INTO `$central_db`.`live_data_updates` (`device_id`, `ping_time`) VALUES ('$device_id', '$ping_date_time')  ON DUPLICATE KEY UPDATE ping_time='$ping_date_time'";
		mysqli_query($conn, $ping_sql);
	} catch (Exception $e) {

	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////  Phases Alerts   /////////////////////////////////////////////////////////

function interpretPhaseAlertCode($phase_status, $parmater, $vr, $vy, $vb, $ir, $iy, $ib, $date_time) {
	global $device_id;
	global $conn;
	global $device_db;

	$statusMap = [ '0' => 'normal', '1' => 'high', '2' => 'low' ];
	$statusMapContactor = [ '0' => 'Turned ON', '1' => 'Tripped' ];

	$phase_status_msg="";
	if($parmater=="VOLTAGE")
	{
		$r = isset($statusMap[$phase_status[0]]) ? $statusMap[$phase_status[0]] : 'undefined';
		$y = isset($statusMap[$phase_status[1]]) ? $statusMap[$phase_status[1]] : 'undefined';
		$b = isset($statusMap[$phase_status[2]]) ? $statusMap[$phase_status[2]] : 'undefined';
		if($phase_status==="000")
		{
			$return_msg= "Phase voltages are Normal.";
		}
		else
		{
			$phase_status_msg ="Phase voltages: R-phase is $r, Y-phase $y, and B-phase is $b.";
		}

		$r=strtoupper($r);
		$y=strtoupper($y);
		$b=strtoupper($b);
		$sql_store_alert="INSERT INTO `$device_db`.`alert_phases` (`device_id`, `alert_name`, `ph_r`, `ph_y`, `ph_b`, `v_r`, `v_y`, `v_b`, `i_r`, `i_y`, `i_b`, `date_time`) VALUES ('$device_id', '$parmater', '$r', '$y', '$b', '$vr', '$vy', '$vb', '$ir', '$iy', '$ib', '$date_time')";
		if (mysqli_query($conn, $sql_store_alert));

		$phase_status_msg= $phase_status_msg." R=$vr, Y=$vy, Y=$vb, Time: $date_time.";
		$device_name = get_name();
		$deviceid_for_msg = check_name($device_name);		
		$msg = "ID:$deviceid_for_msg, $phase_status_msg";
		sendMessage($msg, "voltage");
		messageSaveInCentralTable($parmater, $deviceid_for_msg, $msg, $date_time);
		messageSaveInDeviceTable($parmater, $deviceid_for_msg, $msg, $date_time);
		
	}
	else if($parmater=="CURRENT")
	{
		$r = isset($statusMap[$phase_status[0]]) ? $statusMap[$phase_status[0]] : 'undefined';
		$y = isset($statusMap[$phase_status[1]]) ? $statusMap[$phase_status[1]] : 'undefined';
		$b = isset($statusMap[$phase_status[2]]) ? $statusMap[$phase_status[2]] : 'undefined';

		if($phase_status==="000")
		{
			$return_msg= "Phase currents are Normal.";
		}
		else
		{
			$phase_status_msg ="Phase currents: R-phase is $r, Y-phase $y, and B-phase is $b.";
		}

		$r=strtoupper($r);
		$y=strtoupper($y);
		$b=strtoupper($b);
		$sql_store_alert="INSERT INTO `$device_db`.`alert_phases` (`device_id`, `alert_name`, `ph_r`, `ph_y`, `ph_b`, `v_r`, `v_y`, `v_b`, `i_r`, `i_y`, `i_b`, `date_time`) VALUES ('$device_id', '$parmater', '$r', '$y', '$b', '$vr', '$vy', '$vb', '$ir', '$iy', '$ib', '$date_time')";
		if (mysqli_query($conn, $sql_store_alert));

		$phase_status_msg= $phase_status_msg." R=$ir, Y=$iy, Y=$ib, Time: $date_time.";
		$device_name = get_name();
		$deviceid_for_msg = check_name($device_name);		
		$msg = "ID:$deviceid_for_msg, $phase_status_msg";
		sendMessage($msg, "overload");
		messageSaveInCentralTable($parmater, $deviceid_for_msg, $msg, $date_time);
		messageSaveInDeviceTable($parmater, $deviceid_for_msg, $msg, $date_time);
	}
	else if($parmater=="CONTACTOR")
	{
		
		$r = isset($statusMapContactor[$phase_status[0]]) ? $statusMapContactor[$phase_status[0]] : 'undefined';
		$y = isset($statusMapContactor[$phase_status[1]]) ? $statusMapContactor[$phase_status[1]] : 'undefined';
		$b = isset($statusMapContactor[$phase_status[2]]) ? $statusMapContactor[$phase_status[2]] : 'undefined';
		if($phase_status==="000")
		{
			$return_msg= "Phase contactors/MCBs are turned ON.";
		}
		else
		{
			$phase_status_msg ="Phase Contactors/MCBs: R-phase is $r, Y-phase $y, and B-phase is $b.";
		}

		$r=strtoupper($r);
		$y=strtoupper($y);
		$b=strtoupper($b);
		$sql_store_alert="INSERT INTO `$device_db`.`alert_phases` (`device_id`, `alert_name`, `ph_r`, `ph_y`, `ph_b`, `v_r`, `v_y`, `v_b`, `i_r`, `i_y`, `i_b`, `date_time`) VALUES ('$device_id', 'CONTACTOR/MCB', '$r', '$y', '$b', '$vr', '$vy', '$vb', '$ir', '$iy', '$ib', '$date_time')";
		if (mysqli_query($conn, $sql_store_alert));

		$phase_status_msg= $phase_status_msg." Time: $date_time.";
		$device_name = get_name();
		$deviceid_for_msg = check_name($device_name);		
		$msg = "ID:$deviceid_for_msg, $phase_status_msg";
		sendMessage($msg, "mcb_contactor_trip");
		messageSaveInCentralTable("CONTACTOR/MCB", $deviceid_for_msg, $msg, $date_time);
		messageSaveInDeviceTable("CONTACTOR/MCB", $deviceid_for_msg, $msg, $date_time);
	}
	
}

/////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////    Get the device name    /////////////////////////////////
function get_name()
{
	global $device_db;
	global $conn;
	$device_name=strtoupper($device_db);
	try {
		$sql = "SELECT user_alternative_name FROM `$device_db`.device_name_update_log ORDER BY id DESC LIMIT 1";
		$stmt_1 = mysqli_prepare($conn, $sql);
		mysqli_stmt_execute($stmt_1);
		mysqli_stmt_bind_result($stmt_1, $user_alternative_name);
		if (mysqli_stmt_fetch($stmt_1)) {
			$device_name = $user_alternative_name;
		} 
		mysqli_stmt_close($stmt_1);
	} catch (Exception $e) {

	}
	return $device_name;

}
function check_name($device_name)
{
	global $device_id;
	$deviceid_for_msg = "";
	if ($device_id!= $device_name && $device_name!="")
	{
		$deviceid_for_msg = $device_name."(".$device_id.")";
	}
	else
	{
		$deviceid_for_msg = $device_id;
	}
	return $deviceid_for_msg ;
}
/////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////    Clear the Settings  //////////////////////////////////////////
function clear_ack($key, $table)
{
	global $conn;
	global $device_db;
	$date_time=date("Y-m-d H:i:s");
	$sql="SELECT * FROM `$device_db`.device_settings WHERE setting_type='$key'" ;
	if (mysqli_query($conn, $sql)) 
	{
		$result = mysqli_query( $conn, $sql);
		if(mysqli_num_rows($result)>0)
		{
			$r= mysqli_fetch_assoc( $result );
			$status =$r['setting_flag'];
		}
	} 
	if($status==2)
	{
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='0' WHERE setting_type='$key'" ;
		mysqli_query($conn, $sql);
		if($table!=""&& $table!=null)
		{
			if($table==="on_off_modes")
			{
				if($key==="SCHEDULE_TIME")
				{
					mysqli_query($conn, "UPDATE `$device_db`.`$table` SET status='Updated', update_data_time='$date_time' WHERE on_off_mode = 'SCHEDULE_TIME' ORDER BY date_time DESC LIMIT 1");
				}
				else
				{
					mysqli_query($conn, "UPDATE `$device_db`.`$table` SET status='Updated', update_data_time='$date_time' WHERE on_off_mode != 'SCHEDULE_TIME' ORDER BY date_time DESC LIMIT 1");
				}
			}
			else
			{

				mysqli_query($conn, "UPDATE `$device_db`.`$table` SET status='Updated', update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
			}
		}
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////    On-Off update  //////////////////////////////////////////

function onoff_update($conn, $deviceid, $status, $date_time)
{

	$device_name = $deviceid;

	$device_name = get_name();
	$deviceid_for_msg = check_name($device_name);
	
	$on_command = "";
	if ($status == 1 || $status == 3 || $status == 4 || $status == 5)
	{

		if ($status == 1)
		{
			$on_command = "ON (Auto)";
		}
		else if ($status == 3)
		{
			$on_command = "ON from SERVER";
		}
		else if ($status == 4)
		{
			$on_command = "ON from WIFI APP";
		}
		else if ($status == 5)
		{
			$on_command = "ON MANUALLY";
		}

		$msg = "ID:$deviceid_for_msg Lights Switched $on_command ";
	}
	else
	{
		$on_command = "OFF";
		$msg = "ID:$deviceid_for_msg Lights Switched OFF";
	}
	/*mysqli
	_query($conn, "INSERT INTO $device_db.`on_off_events_log` (`device_id`, `event`, `date_time`) VALUES ( '$deviceid', '$on_command', '$date_time');");*/
	$msg=$msg.", Time: ". $date_time;

	sendMessage($msg, "on_off");
	messageSaveInCentralTable('ON-OFF', $deviceid_for_msg, $msg, $date_time);
	messageSaveInDeviceTable('ON-OFF', $deviceid_for_msg, $msg, $date_time);
	
}

function messageSaveInCentralTable($paramter, $deviceid_for_msg, $msg, $date_time)
{
	global $device_id;
	global $conn;
	global $central_db;
	try {
		mysqli_query($conn, "INSERT INTO `$central_db`.`alerts_and_updates` (`device_id`, `device_id_name`, `alert_update_name`, `update`, `date_time`) VALUES ( '$device_id', '$deviceid_for_msg', '$paramter', '$msg', '$date_time')");
	} catch (Exception $e) {
		
	}
}
function messageSaveInDeviceTable($paramter, $deviceid_for_msg, $msg, $date_time)
{
	global $device_id;
	global $device_db;
	global $conn;
	global $msgSentConfirm;
	$msg_sent="Not Sent";
	echo $msgSentConfirm;
	if($msgSentConfirm == true)
	{
		$msg_sent="Sent";
	}	
	try {
		mysqli_query($conn, "INSERT INTO `$device_db`.`messges_frame` (`device_id`, `alert_type`, `frame`, `sent_status`, `date_time`) VALUES ( '$device_id', '$paramter', '$msg', '$msg_sent', '$date_time')");
	} catch (Exception $e) {
		
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////    main Function   //////////////////////////////////////////

function device_setting()
{
	global $device_db;
	global $conn;
	global $device_id;
	$response="";
	$length=0;
	$sql="";
	$condtion="NA";
	$date_time=date("Y-m-d H:i:s");	

	$sql="SELECT * FROM (SELECT * FROM `$device_db`.device_settings WHERE setting_type IN ('ONOFF', 'SCHEDULE_TIME', 'ON_OFF_MODE', 'VOLTAGE','CURRENT','FRAME_TIME','HYSTERESIS', 'CALIB_VALUES', 'LOOP_ON_OFF', 'SOFTWARE',  'ID_CHANGE', 'ENERGY_RESET', 'WIFI_CREDENTIALS', 'SERIAL_ID', 'RESET') ORDER BY setting_type DESC) a UNION ALL (SELECT * FROM `$device_db`.device_settings WHERE setting_type='READ_SETTINGS' LIMIT 1)";

	$check=array();
	if (mysqli_query($conn, $sql)) 
	{
		$result = mysqli_query( $conn, $sql);
		if(mysqli_num_rows($result)>0)
		{
			while ($r=  mysqli_fetch_assoc( $result )) 
			{
				$check[]=array('type' => $r['setting_type'],'flag' => $r['setting_flag'] );

			}
		}
		else
		{
			$response ="No data";
		}
	} 
	$values = json_encode($check);
	$set_list= json_decode($values);
	foreach($set_list as $key => $value) {
		$condtion= $value->type;
		$set_flag=  $value->flag;
		if($set_flag==1||$set_flag==2)
		{
			break;
		}
		else
		{
			$condtion="NA";
		}	
	}
	switch ($condtion) 
	{
		case "VOLTAGE":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.limits_voltage ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$l_r=(int)$r['l_r'];
					$l_y=(int)$r['l_y'];
					$l_b=(int)$r['l_b'];
					$u_r=(int)$r['u_r'];
					$u_y=(int)$r['u_y'];
					$u_b=(int)$r['u_b'];
					$Lower_limit=$l_r.";".$l_y.";".$l_b;
					$upper_limit=$u_r.";".$u_y.";".$u_b;
					$data="V_LOWER=".$Lower_limit.";V_UPPER=".$upper_limit.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "CURRENT":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.limits_current ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$i_r=(int)$r['i_r'];
					$i_y=(int)$r['i_y'];
					$i_b=(int)$r['i_b'];
					$upper_limit=$i_r.";".$i_y.";".$i_b;
					$data="I_UPPER=".$upper_limit.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "ONOFF":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.on_off_activities ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.on_off_activities SET status='In-Progress..' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r=  mysqli_fetch_assoc( $result );
					$switch=$r['on_off'].":".$r['time'];
					$data=$switch.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;

		case "ON_OFF_MODE":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.on_off_modes WHERE on_off_mode != 'SCHEDULE_TIME' ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.on_off_modes SET status='In-Progress..' , update_data_time='$date_time' WHERE on_off_mode != 'SCHEDULE_TIME' ORDER BY date_time DESC LIMIT 1");
					$r=  mysqli_fetch_assoc( $result );
					$data="ON_OFF_MODE:".$r['on_off_mode'].";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "SCHEDULE_TIME":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.on_off_schedule_time ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.on_off_modes SET status='In-Progress..' , update_data_time='$date_time' WHERE on_off_mode='SCHEDULE_TIME' ORDER BY date_time DESC LIMIT 1");
					$r=  mysqli_fetch_assoc( $result );
					$check_status=strtoupper($r['status']);
					$scdl_time="";
					$ontime=$r['on_time'];
					$offtime=$r['off_time'];
					$ontime=str_replace(":00","", $ontime);
					$ontime=str_replace(":","","$ontime");
					$offtime=str_replace(":00","", $offtime);
					$offtime=str_replace(":","","$offtime");

					$ontime = str_pad($ontime, 4, '0', STR_PAD_RIGHT);
					$offtime = str_pad($offtime, 4, '0', STR_PAD_RIGHT);

					$scdl_time="SCHDON=".$ontime.";".$offtime.";";

					$data=$scdl_time;
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "FRAME_TIME":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.frame_time ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.frame_time SET status='In-Progress..' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r=  mysqli_fetch_assoc( $result );
					$update_interval=(int)$r['frame_time'];
					$data="SETTIME=".$update_interval.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "HYSTERESIS":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.iot_hysteresis ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.iot_hysteresis SET status='In-Progress..' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r=  mysqli_fetch_assoc( $result );
					$hysteresis=(int)$r['value'];
					$data="HYST=".$hysteresis.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;

		case "LOOP_ON_OFF":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.iot_on_off_interval ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.iot_on_off_interval SET status='In-Progress..' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r=  mysqli_fetch_assoc( $result );
					$val=(int)$r['value'];
					if($val>0)
					{
						$val="1;".$val;
					}
					else
					{
						$val="0;0";
					}

					$data="LOOP_ON_OFF=".$val.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "CALIB_VALUES":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM `$device_db`.iot_calibration_values ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.iot_calibration_values SET status='In-Progress..' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r=  mysqli_fetch_assoc( $result );
					$calib_val=$r['frame'];
					$data="CALIB:".$calib_val;
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;

		case "SOFTWARE":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$data="S_UPDATE;";
			$len =strlen($data);
			$length = str_pad($len, 3, '0', STR_PAD_LEFT);
			$data=$length.";".$data;
			$response= "CCMS=".$device_id.";".$data;					
		}
		break;
		case "RESET":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='0' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			mysqli_query($conn, "UPDATE `$device_db`.iot_device_reset SET status='Updated' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
			$data="RESET=0;";
			$len =strlen($data);
			$length = str_pad($len, 3, '0', STR_PAD_LEFT);
			$data=$length.";".$data;
			$response= "CCMS=".$device_id.";".$data;					
		}
		break;
		case "READ_SETTINGS":
		$sql="UPDATE `$device_db`.device_settings SET setting_flag='0' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$data="READ_SETTINGS;";
			$len =strlen($data);
			$length = str_pad($len, 3, '0', STR_PAD_LEFT);
			$data=$length.";".$data;
			$response= "CCMS=".$device_id.";".$data;
		}
		break;

		case "ID_CHANGE":
		$sql = "UPDATE `$device_db`.device_settings SET setting_flag='0' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM `$device_db`.iot_device_id_change ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.iot_device_id_change SET status='Updated' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r = mysqli_fetch_assoc($result);
					$send_data = $r['new_device_id'];
					$data = "DEVID=" . $send_data.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;

		case "SERIAL_ID":
		$sql = "UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM `$device_db`.iot_serial_id_change ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.iot_serial_id_change SET status='In-Progress..' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r = mysqli_fetch_assoc($result);
					$send_data = $r['serial_id'];
					$data = "SNO=" . $send_data.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;

		case "ENERGY_RESET":
		$sql = "UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM `$device_db`.iot_reset_energy ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.iot_reset_energy SET status='In-Progress..' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r = mysqli_fetch_assoc($result);
					$kwh = $r['kwh'];  
					$kvah = $r['kvah'];
					$data = "ECLR=" . $kwh.";". $kvah.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;


		case "WIFI_CREDENTIALS":
		$sql = "UPDATE `$device_db`.device_settings SET setting_flag='2' WHERE setting_type='$condtion'";
		if (mysqli_query($conn, $sql))
		{
			$set_sql = "SELECT * FROM `$device_db`.iot_wifi_credentials ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql))
			{
				$result = mysqli_query($conn, $set_sql);
				if (mysqli_num_rows($result) > 0)
				{
					mysqli_query($conn, "UPDATE `$device_db`.iot_wifi_credentials SET status='In-Progress..' , update_data_time='$date_time' ORDER BY date_time DESC LIMIT 1");
					$r = mysqli_fetch_assoc($result);
					$access_point_name = $r['ssid'];  
					$pwd = $r['password'];
					$data = "WIFI=" . $access_point_name.";PWD=". $pwd.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response = "CCMS=" . $device_id . ";" . $data;
				}
			}
		}
		break;
		case "NA":
		$response = "No Data";
		break;
		default:
		$response= "No Data";

	}

	return $response;
}
/////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////// INPUT POWER FAIL/ RESUME Alert   //////////////////////////////
function ip_power_smps_status_update($conn, $deviceid, $status, $voltage, $date)
{
	global $device_id;
	global $device_db;
	global $conn;

	date_default_timezone_set('Asia/Kolkata');
	$string = str_replace('/', '-', $date);
	$date_new = date_create($string);
	$date = @date_format($date_new, "Y/m/d H:i:s");

	$BSF_alert = "";

	$device_name = $deviceid;
	$device_name = get_name();
	$deviceid_for_msg = check_name($device_name);

	$send_msg = false;
	$msg = "";
	

	if ($status == "1")
	{
		$send_msg = true;
		$update_sql = "INSERT INTO `$device_db`.`alert_power_supply_check` (`ps_status`, `battery_voltage`, `date_time`) VALUES ('Power Disconnected', '$voltage', '$date');";
		mysqli_query($conn, $update_sql);

		$update_sql = "INSERT INTO `$device_db`.`alert_power_failure` (`device_id`, `status`, `battery_voltage`, `date_time`) VALUES ('$device_id', 'Power Disconnected', '$voltage', '$date');";
		mysqli_query($conn, $update_sql);


		$msg = "ID:" . $deviceid_for_msg . " - Power Failure detected at: " . $date;
	}
	else if ($status == "2")
	{
		$send_msg = true;
		$update_sql = "INSERT INTO `$device_db`.`alert_power_supply_check` (`ps_status`, `battery_voltage`, `date_time`) VALUES ('Power Restored', '$voltage', '$date');";

		$update_sql = "INSERT INTO `$device_db`.`alert_power_failure` (`device_id`, `status`, `battery_voltage`, `date_time`) VALUES ('$device_id', 'Power Restored', '$voltage', '$date');";

		mysqli_query($conn, $update_sql);
		$msg = "ID:" . $deviceid_for_msg . " - Power Restored at: " . $date;
	}

	else if ($status == "3")
	{
		$send_alert_msg = " SMPS-1 Failure (" . $voltage . " mV)";
		$msg = "SMPS-1 Failure detected in Device :" . $deviceid;

		mysqli_query($conn, "INSERT INTO `$device_db`.`smps_status`(`device_id`, `smps`, `smps_status`, `date_time`) VALUES ('$deviceid', 'SMPS_1', 'FAIL', '$date')");
	}
	else if ($status == "4")
	{
		$send_alert_msg = " SMPS-1 Restored (" . $voltage . " mV)";
		$msg = "SMPS-1 Power restored in Device :" . $deviceid;

		mysqli_query($conn, "INSERT INTO `$device_db`.`smps_status`(`device_id`, `smps`, `smps_status`, `date_time`) VALUES ('$deviceid', 'SMPS_1', 'RESTORED', '$date')");
	}
	else if ($status == "5")
	{
		$send_alert_msg = " SMPS-2 Failure (" . $voltage . " mV)";
		$msg = "SMPS-2 Failure detected in Device :" . $deviceid;

		mysqli_query($conn, "INSERT INTO `$device_db`.`smps_status`(`device_id`, `smps`, `smps_status`, `date_time`) VALUES ('$deviceid', 'SMPS_2', 'FAIL', '$date')");
	}
	else if ($status == "6")
	{
		$send_alert_msg = " SMPS-2 Restored (" . $voltage . " mV)";
		$msg = "SMPS-2 Power restored in Device :" . $deviceid;

		mysqli_query($conn, "INSERT INTO `$device_db`.`smps_status`(`device_id`, `smps`, `smps_status`, `date_time`) VALUES ('$deviceid', 'SMPS_2', 'RESTORED', '$date')");
	}
	else if ($status == "7")
	{
		$send_alert_msg = " SMPS-1 & SMPS-2 Failure (" . $voltage . " mV)";
		$msg = "SMPS-1 & SMPS-2 Failure detected in Device :" . $deviceid;

		mysqli_query($conn, "INSERT INTO `$device_db`.`smps_status`(`device_id`, `smps`, `smps_status`, `date_time`) VALUES ('$deviceid', 'SMPS_1', 'FAIL', '$date')");
		mysqli_query($conn, "INSERT INTO `$device_db`.`smps_status`(`device_id`, `smps`, `smps_status`, `date_time`) VALUES ('$deviceid', 'SMPS_2', 'FAIL', '$date')");
	}
	else if ($status == "8")
	{
		$send_alert_msg = " SMPS-1 & SMPS-2 Restored (" . $voltage . " mV)";
		$msg = "SMPS-1 & SMPS-2 Power restored in Device :" . $deviceid;

		mysqli_query($conn, "INSERT INTO `$device_db`.`smps_status`(`device_id`, `smps`, `smps_status`, `date_time`) VALUES ('$deviceid', 'SMPS_1', 'RESTORED', '$date')");
		mysqli_query($conn, "INSERT INTO `$device_db`.`smps_status`(`device_id`, `smps`, `smps_status`, `date_time`) VALUES ('$deviceid', 'SMPS_2', 'RESTORED', '$date')");
	}

	
	if($send_msg == true)
	{ 
		sendMessage($msg, "power_fail");
	}
	messageSaveInCentralTable("SMPS", $deviceid_for_msg, $msg, $date);
	messageSaveInDeviceTable("SMPS", $deviceid_for_msg, $msg, $date);

}

//////////////////////////////////// SMS Sending  message      ///////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////

function sendMessage($msg, $check_alert_status)
{
	global $device_id;
	global $device_db;
	global $conn;
	global $user_db;
	global $msgSentConfirm;
	$msgSentConfirm=false;
	try {
		$check_status=0;
		$sql ="";
		
		$sql = "SELECT $check_alert_status  FROM `$device_db`.notification_updates ORDER BY id DESC LIMIT 1";
		
		$stmt = mysqli_prepare($conn, $sql);
		if ($stmt) {
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $check_status);
			mysqli_stmt_fetch($stmt);
			mysqli_stmt_close($stmt);
		} 

	} catch (Exception $e) {

	}
	if($check_status)
	{
		date_default_timezone_set('Asia/Kolkata');
		$date = date("Y-m-d H:i:s");
		try
		{
			$d_location = "";
			$update_link = "";
			$sql_loc = "SELECT location FROM `$device_db`.`live_data` WHERE location !='000000000,0000000000' AND location !=',' ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $sql_loc))
			{
				$result_loc = mysqli_query($conn, $sql_loc);
				if (mysqli_num_rows($result_loc) > 0)
				{
					$r = mysqli_fetch_assoc($result_loc);
					$d_location = $r['location'];
				}
			}

			$coordinates = $d_location;
			if ($coordinates != "")
			{
				$co_array = explode(',', $coordinates);

				if ($co_array[0] != "" && $co_array[1] != "")
				{
					try
					{
						$coordinate = $co_array[0];
						$array_split = explode('.', $coordinate);
						$deg = (int)($array_split[0] / 100);
						$time = ((float)$coordinate - $deg * 100) / 60;
						$lat = round($deg + $time, 7);

						$coordinate = $co_array[1];
						$array_split = explode('.', $coordinate);
						$deg = (int)($array_split[0] / 100);
						$time = ((float)$coordinate - $deg * 100) / 60;
						$long = round($deg + $time, 7);

						$coordinates = $lat . "," . $long;
					}
					catch(Exception $e)
					{
					}
				}

				if ($GLOBALS['live'] == 1)
				{
					ob_start();
					$url = urlencode('http://maps.google.com/?q=' . $coordinates);
					$json = file_get_contents("https://cutt.ly/api/api.php?key=3fe8e5534ba803b74f26d71a2d6d3edc5823d&short=$url");
					$link = json_decode($json, true);
					$update_link = $link["url"]["shortLink"];
					ob_end_clean();
				}
				else
				{
					$update_link = "http://maps.google.com/?q=" . $coordinates;
				}

				if ($update_link != "")
				{
					$msg = $msg . " GPS: " . $update_link;
				}
				else
				{
					$msg = $msg . " GPS: http://maps.google.com/?q=" . $coordinates;
				}
			}

			$msg = str_replace("&", "and", $msg);
			$msg = str_replace("High", "high", $msg);
			$msg = str_replace("HIGH", "high", $msg);
			$msg = str_replace("Voltage", "voltage", $msg);

			$chat_id = "";
			$sql = "SELECT * FROM `$user_db`.`telegram_groups_new` WHERE id in (SELECT group_id FROM `$user_db`.`telegram_groups_devices` WHERE device_id='$device_id')";
			if (mysqli_query($conn, $sql))
			{
				$result = mysqli_query($conn, $sql);
				if (mysqli_num_rows($result) > 0)
				{
					while ($r = mysqli_fetch_assoc($result))
					{
						$chat_id = $r['chat_id'];
						$token = $r['token'];
						if ($chat_id != "" && $token != "")
						{
							$TG_ALERT_URL = 'https://api.telegram.org/' . $token . '/sendMessage?chat_id=' . $chat_id . '&text=' . $msg;
							file_get_contents($TG_ALERT_URL);
							$msgSentConfirm=true;

						}
					}
				}
			}
		}
		catch(Exception $e)
		{

		}
	}
}


?>
