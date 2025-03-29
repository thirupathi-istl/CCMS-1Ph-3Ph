<?php
date_default_timezone_set('Asia/Kolkata');
$live=0;
$response="";

$ip_address = $_SERVER['REMOTE_ADDR'];
if($ip_address=="::1")
{
	define('HOST','localhost');
	define('USERNAME', 'root');
	define('PASSWORD','123456');
	define('DB1', 'ccms_userdb');

	//$data="CCMS_41;ALERT;SUPPLY;3;234;235;236;23;24;25;23/07/29 12:23:34";
	//$data="CCMS_3;SUPDATE;";
	
	$data="CCMS_41;ACK;LOADCLEAR";   //UPDATETIME, LOADCLEAR, MINMAXSET,ONOFFLIGHT ,HYSTVAL, CALIB, SCHEDULE_TIME, LOOP_ON_OFF UPDATETIME ECLR WIFI SERIALNO
	//$data="CCMS_41;"; 
}
else
{
	define('HOST','95.111.238.141');
	define('USERNAME', 'istlabsonline_db_user');
	define('PASSWORD','istlabsonline_db_pass');
	define('DB1', 'ccms_userdb');
	define('DB2','notifications_db');
	$live=1;

}
if(isset($_POST['CCMS']))
{
	$data= $_POST['CCMS'];

	if($data!="")
	{
		$array_data = explode(';', $data);
		if($live==1)
		{
			update($array_data[0]);
        
       }
		$db=trim(strtolower($array_data[0]));
		define('DB',$db);
		$conn =  mysqli_connect(HOST,USERNAME,PASSWORD,DB);
		if (!$conn) 
		{
			die("Connection failed");
		}
		else
		{
			/*if(trim($array_data[0])=="BMC_CCMS_58")
			{
				store_data($data, $conn);
			}*/
			update_ping($array_data[0],$conn);
			if(count($array_data)<3)
			{
				$response= mainfunction($data, $conn);
			}
			else
			{
				if (trim($array_data[1]) =='ACK')
				{

					$condtion = $array_data[2];

					switch ($condtion)
					{
						case "ONOFFLIGHT":
						clear_ack("ONOFF", $conn);
						break;

						case "SCHEDULE_TIME" :
						clear_ack("SCHEDULE_TIME", $conn);
						break;

						case "MINMAXSET" :
						clear_ack("VOLTAGE", $conn);
						break;

						case "LOADCLEAR" :
						clear_ack("CURRENT", $conn);
						break;

						case "SUPDATE" :						
						clear_ack("SOFTWARE", $conn);						
						$date=date("Y-m-d H:i:s");
						mysqli_query($conn, "INSERT INTO `software_update_status` ( `status_1`, `status_2`, `date_time`) VALUES ('$array_data[3]', '0', '$date')");
						break;

						case "UPDATETIME" :
						clear_ack("FRAME_TIME", $conn);
						break;

						case "HYSTVAL" :
						clear_ack("HYSTERESIS", $conn);
						break;

						case "CALIB" :					
						clear_ack("CALIB_VALUES", $conn);
						break;

						case "LOOP_ON_OFF" :					
						clear_ack("LOOP_ON_OFF", $conn);
						break;

						case "ECLR" :					
						clear_ack("ENERGY_RESET", $conn);
						break;

						case "WIFI" :					
						clear_ack("WIFI_CREDENTIALS", $conn);
						break;

						case "SERIALNO" :					
						clear_ack("SERIAL_ID", $conn);

						default:
						$response= mainfunction($data, $conn);
					}
					
				}
				else if ($array_data[1] =='ALERT')
				{
					$phase = $array_data[3];
					switch ($phase) {
						case "000":
						alert_fun($array_data[0],$array_data[2],"Resumed", "1", $conn, "0",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "001":
						alert_fun($array_data[0],$array_data[2],"B", "R & Y", $conn, "1",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "010":
						alert_fun($array_data[0],$array_data[2],"Y" ,"R & B", $conn , "1",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "011":
						alert_fun($array_data[0],$array_data[2],"Y & B", "R", $conn, "1",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "100":
						alert_fun($array_data[0],$array_data[2],"R","Y & B", $conn, "1",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "101":
						alert_fun($array_data[0],$array_data[2],"R & B", "Y", $conn, "1",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "110":
						alert_fun($array_data[0],$array_data[2],"R & Y", "B", $conn, "1",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "111":
						alert_fun($array_data[0],$array_data[2],"R, Y & B", "0", $conn, "1",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "2":
						supply_alert($array_data[0],$array_data[2],"", $conn, "1",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "3":
						supply_alert($array_data[0],$array_data[2],"", $conn, "0",$array_data[4],$array_data[5],$array_data[6],$array_data[7],$array_data[8],$array_data[9],$array_data[10]);
						break;
						case "4":							
						power_supply_alert($array_data[0], $conn, $array_data[4],$array_data[5], $array_data[6]);
						break;
						default:
						$response="ERROR";
					}
				}
            	else if ($array_data[1] =='EVENTS')
				{
					if ($array_data[2] == "DO")
					{
						$sql_q = "INSERT INTO `alert_door` (`alert`, `date_time`) VALUES ('Door Open', '$array_data[3]')";
						mysqli_query($conn, $sql_q);

					}
					else if ($array_data[2] == "DC")
					{
						$sql_q = "INSERT INTO `alert_door` (`alert`, `date_time`) VALUES ('Door Closed', '$array_data[3]')";
						mysqli_query($conn, $sql_q);
					}

				}
				else if ($array_data[1] =='SUPDATE')
				{
					$response="";
					$sql="SELECT software FROM software_update ORDER BY id DESC LIMIT 1" ;
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
							$response ="000;No data";
						}
					} 
				}
				else if ($array_data[1] =='OFFLINE'||$array_data[1] =='DEFAULT')
				{
					$sql="INSERT INTO `loaded_settings` ( `frame`) VALUES ( '$data')" ;
					mysqli_query($conn, $sql);				
					
				}
				else if($array_data[1]=='REPORT')
				{
					try 
					{	
						
						$date_time=date("Y-m-d H:i:s");					
						$command=$array_data[2];
						if(mysqli_query( $conn, "SELECT * FROM device_check_report WHERE field ='$command'"))
						{
							$exist_row = mysqli_query( $conn, "SELECT * FROM device_check_report WHERE field ='$command'");
							if( mysqli_num_rows($exist_row) > 0) 
							{
								mysqli_query( $conn,"UPDATE device_check_report SET status='$array_data[3]', date_time = '$date_time' WHERE field = '$command' ");
							}
							else
							{
								mysqli_query( $conn, "INSERT INTO `device_check_report` ( `field`, `status`, `date_time`) VALUES ( '$array_data[2]', '$array_data[3]', '$date_time')");
							}
							if($array_data[2]=="SIMCOM_OFF")
							{
								mysqli_query($conn, "INSERT INTO `sim_module_communication` ( `date_time`, `server_time`) VALUES ( '$array_data[3]', '$date_time');");
							}
							if($array_data[2]=="SYSTEM INFO")
							{
								
								mysqli_query($conn, "INSERT INTO system_info ( `info`, `date_time`) VALUES('$array_data[3]', '$date_time') ON DUPLICATE KEY UPDATE info='$array_data[3]', date_time='$date_time'");
							}
							if($array_data[2]=="SIMCOMSTATUS")
							{
								mysqli_query($conn, "INSERT INTO `simcom_status` (`status`, `status_code`, `date_time`,`server_date_time`) VALUES ('$array_data[3]', '$array_data[4]', '$array_data[5]','$date_time');");
							}
							if($array_data[2]=="SYSTEMSTATUS")
							{
								$array_data[3]=trim($array_data[3]);
								$s_status="";
								$s_id=0;
								$sql="SELECT * FROM system_status ORDER BY id DESC LIMIT 1" ;
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
									mysqli_query($conn, "UPDATE `system_status`  SET `date_time`='$array_data[4]' WHERE id='$s_id'");
								}
								else{
									mysqli_query($conn, "INSERT INTO `system_status` ( `status`, `date_time`, `prev_date_time` , `server_date_time`) VALUES ( '$array_data[3]', '$array_data[4]', '$array_data[4]', '$date_time');");
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
										sendsms($msg, $conn, $array_data[0]);
									}
								}
							}
						}
						else
						{
							$result = mysqli_query($conn, "SHOW TABLES LIKE 'device_check_report'");
							if(mysqli_num_rows($result)<=0) 						
							{
								$sql = "CREATE TABLE `device_check_report` (`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `field` varchar(255) NOT NULL, `status` varchar(255) NOT NULL, `date_time` datetime NOT NULL ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
								mysqli_query($conn, $sql);
								mysqli_query( $conn, "INSERT INTO `device_check_report` ( `field`, `status`, `date_time`) VALUES ( '$array_data[2]', '$array_data[3]', '$date_time')");
							}
						}
					} 
					catch (Exception $e) 
					{
					}
					
				}
				else
				{
					$response= '000;No data';
				}
			}
			http_response_code(201);

			if($response==null||$response=="")
			{
				$response= mainfunction($data, $conn);
			}
			mysqli_close($conn);
			echo $response;
		}
	}
	else
	{
		echo "000;No data ";
	}
}

////////////////////////////////////    main Function   //////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////
function mainfunction($data, $conn)
{

	$array_data = explode(';', $data); 
	$response="";
	$length=0;
	$sql="";
	$condtion="NA";
	$device_id=$array_data[0];

	$sql="SELECT *from (SELECT * FROM device_settings WHERE setting_type IN ('ONOFF','VOLTAGE','CURRENT','FRAME_TIME','HYSTERESIS', 'CALIB_VALUES', 'SCHEDULE_TIME','LOOP_ON_OFF', 'SOFTWARE',  'ID_CHANGE', 'ENERGY_RESET', 'WIFI_CREDENTIALS', 'SERIAL_ID', 'RESET') ORDER BY setting_type DESC) a UNION ALL (SELECT * FROM device_settings WHERE setting_type='DEFAULT' LIMIT 1)";

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
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM limit_voltage ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$min_1=(int)$r['min_1'];
					$min_2=(int)$r['min_2'];
					$min_3=(int)$r['min_3'];
					$max_1=(int)$r['max_1'];
					$max_2=(int)$r['max_2'];
					$max_3=(int)$r['max_3'];
					$MIN=$min_1.";".$min_2.";".$min_3;
					$MAX=$max_1.";".$max_2.";".$max_3;
					$data="MIN=".$MIN.";MAX=".$MAX.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "CURRENT":
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM limit_current ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$max_1=(int)$r['max_1'];
					$max_2=(int)$r['max_2'];
					$max_3=(int)$r['max_3'];
					$MAX=$max_1.";".$max_2.";".$max_3;
					$data="LMA=".$MAX.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "ONOFF":
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM on_off_activity ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$switch=$r['on_off'];
					$data=$switch.";";
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "FRAME_TIME":
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM update_time_interval ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$update_interval=(int)$r['time'];
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
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM hysteresis_history ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$hysteresis=(int)$r['set_value'];
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
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM on_off_interval_history ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$val=(int)$r['set_value'];
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
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM calib_settings ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
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
		case "SCHEDULE_TIME":
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$set_sql="SELECT * FROM schedule_time ORDER BY id DESC LIMIT 1";
			if (mysqli_query($conn, $set_sql)) 
			{
				$result = mysqli_query( $conn, $set_sql);
				if(mysqli_num_rows($result)>0)
				{
					$r=  mysqli_fetch_assoc( $result );
					$check_status=strtoupper($r['status']);
					$scdl_time="";
					if($check_status=="ENABLED")
					{
						$ontime=$r['on_time'];
						$offtime=$r['off_time'];
						$ontime=str_replace(":00","", $ontime);
						$ontime=str_replace(":","","$ontime");
						$offtime=str_replace(":00","", $offtime);
						$offtime=str_replace(":","","$offtime");

						$ontime = str_pad($ontime, 4, '0', STR_PAD_RIGHT);
						$offtime = str_pad($offtime, 4, '0', STR_PAD_RIGHT);

						$scdl_time="SCHDON=".$ontime.";".$offtime.";";
					}
					else
					{
						$scdl_time="SCHDOFF;";
					}
					$data=$scdl_time;
					$len =strlen($data);
					$length = str_pad($len, 3, '0', STR_PAD_LEFT);
					$data=$length.";".$data;
					$response= "CCMS=".$device_id.";".$data;
				}
			}
		}
		break;
		case "SOFTWARE":
		$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
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
		$sql="UPDATE device_settings SET setting_flag='0' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$data="RESET=0;";
			$len =strlen($data);
			$length = str_pad($len, 3, '0', STR_PAD_LEFT);
			$data=$length.";".$data;
			$response= "CCMS=".$device_id.";".$data;					
		}
		break;
		case "DEFAULT":
				//$sql="UPDATE device_settings SET setting_flag='2' WHERE setting_type='$condtion'" ;
		$sql="UPDATE device_settings SET setting_flag='0' WHERE setting_type='$condtion'" ;
		if (mysqli_query($conn, $sql)) 
		{
			$data="DEFAULT;";
			$len =strlen($data);
			$length = str_pad($len, 3, '0', STR_PAD_LEFT);
			$data=$length.";".$data;
			$response= "CCMS=".$device_id.";".$data;
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
function clear_ack($key, $conn)
{

	$sql="SELECT * FROM device_settings WHERE setting_type='$key' ORDER BY id ASC LIMIT 1" ;
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
		$sql="UPDATE device_settings SET setting_flag='0' WHERE setting_type='$key'" ;
		mysqli_query($conn, $sql);

	}

}
function alert_fun($deviceid, $type, $phase, $normal_phases, $conn, $status,$v1,$v2,$v3,$c1,$c2,$c3,$date)
{
	$string = str_replace('/', '-', $date);
	$date_new=date_create($string);
	$date =@date_format($date_new,"Y/m/d H:i:s");
	if( !strlen($date))
	{

		$date=date("Y-m-d H:i:s");
	}
	$date_sms =date_format(date_create($date),"H:i:s d/m/Y ");
	$response="";
	$device_name = $deviceid;
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

	$enable_disable = 0;

	$sql = "SELECT * FROM `user_notification_settings` WHERE alert_type='VOLTAGE' ORDER BY id DESC LIMIT 1";

	if (mysqli_query($conn, $sql))
	{

		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0)
		{
			$r = mysqli_fetch_assoc($result);
			$enable_disable = $r['alert_en_dis'];

		}
	}
	switch ($type) 
	{
		case "PHASEFAIL":
		if($normal_phases=="1")
		{
			$phase="All Phases Normal";
		}
		else if($normal_phases=="0")
		{
			$phase=$phase;
		}
		else
		{
			$phase=$phase ." (".$normal_phases." Normal)";
		}

		$sql="INSERT INTO `phase_alerts` (`device_id`, `status`, `phases`, `v_ph1`, `v_ph2`, `v_ph3`, `i_ph1`, `i_ph2`, `i_ph3`, `date_time`) VALUES ( '$deviceid', '$type', '$phase', '$v1', '$v2', '$v3', '$c1', '$c2', '$c3', '$date')" ;
		mysqli_query($conn, $sql);
		if($status==1)
		{
			$msg = "ID:".$deviceid_for_msg." Power failure in ".$phase." Voltages(V): R=".$v1.", Y=".$v2.", Y=".$v3." TIME:".$date_sms.""; 
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid);  
			}
		}
		else
		{
			$msg = "ID:".$deviceid_for_msg." Power Resumed in all phases Voltages(V): R=".$v1.", Y=".$v2.", B=".$v3." TIME:".$date_sms.""; 
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid); 
			}
		}
		break;
		case "OVERLOAD":
		if($normal_phases=="1")
		{

			$phase="ALL Phases Normal";
		}
		else if($normal_phases=="0")
		{
			$phase=$phase;
		}
		else
		{
			$phase=$phase ." (".$normal_phases." Normal)";
		}


		$enable_disable = 0;

		$sql = "SELECT * FROM `user_notification_settings` WHERE alert_type='OVERLOAD' ORDER BY id DESC LIMIT 1";

		if (mysqli_query($conn, $sql))
		{

			$result = mysqli_query($conn, $sql);
			if (mysqli_num_rows($result) > 0)
			{
				$r = mysqli_fetch_assoc($result);
				$enable_disable = $r['alert_en_dis'];

			}
		}

		$sql="INSERT INTO `phase_alerts` (`device_id`, `status`, `phases`, `v_ph1`, `v_ph2`, `v_ph3`, `i_ph1`, `i_ph2`, `i_ph3`, `date_time`) VALUES ( '$deviceid', '$type', '$phase', '$v1', '$v2', '$v3', '$c1', '$c2', '$c3', '$date')" ;
		mysqli_query($conn, $sql); 
		if($status==1)
		{
			$msg = "ID:".$deviceid_for_msg."  Overload = ".$phase." Current(A): R=".$c1.", Y=".$c2.", B=".$c3." TIME:".$date_sms.""; 
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid); 
			}
		}
		else
		{
			$msg = "ID:".$deviceid_for_msg." Phases Load Normal Current(A): R=".$c1.", Y=".$c2.", B=".$c3." TIME:".$date_sms."";   
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid);
			}
		}
		break;

		case "HIGH":
		if($normal_phases=="1")
		{
			$phase="All Phases Normal";
		}
		else if($normal_phases=="0")
		{
			$phase=$phase;
		}
		else
		{
			$phase=$phase ." (".$normal_phases." Normal)";
		}
		$sql="INSERT INTO `phase_alerts` (`device_id`, `status`, `phases`, `v_ph1`, `v_ph2`, `v_ph3`, `i_ph1`, `i_ph2`, `i_ph3`, `date_time`) VALUES ( '$deviceid', '$type Voltage', '$phase', '$v1', '$v2', '$v3', '$c1', '$c2', '$c3', '$date')" ;
		mysqli_query($conn, $sql);
		if($status==1)
		{
			$msg = "ID:".$deviceid_for_msg."  High Voltage= ".$phase." Voltages(V): R=".$v1.", Y=".$v2.", B=".$v3." TIME:".$date_sms."";   
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid);
			}
		}
		else
		{
			$msg = "ID:".$deviceid_for_msg." Phases Voltage Normal Voltages(V): R=".$v1.", Y=".$v2.", B=".$v3." TIME:".$date_sms."";   
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid);
			}
		}
		break;

		case "LOW":
		if($normal_phases=="1")
		{
			$phase="ALL Phases Normal";
		}
		else if($normal_phases=="0")
		{
			$phase=$phase;
		}
		else
		{
			$phase=$phase ." (".$normal_phases." Normal)";
		}
		$sql="INSERT INTO `phase_alerts` (`device_id`, `status`, `phases`, `v_ph1`, `v_ph2`, `v_ph3`, `i_ph1`, `i_ph2`, `i_ph3`, `date_time`) VALUES ( '$deviceid', '$type Voltage', '$phase', '$v1', '$v2', '$v3', '$c1', '$c2', '$c3', '$date')" ;
		mysqli_query($conn, $sql);
		if($status==1)
		{
			$msg = "ID:".$deviceid_for_msg." Low Voltage = ".$phase." Voltages(V): R=".$v1.", Y=".$v2.", B=".$v3." TIME:".$date_sms."";   
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid);
			}
		}
		else
		{
			$msg = "ID:".$deviceid_for_msg." Phases Voltage Normal Voltages(V): R=".$v1.", Y=".$v2.", B=".$v3." TIME:".$date_sms."";   
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid);
			}
		}
		break;

		case "MCB":
		if($normal_phases=="1")
		{
			$phase="R, Y & B";
		}
		else if($normal_phases=="0")
		{
			$phase="R, Y & B";
		}
		else
		{
			$phase=$phase ." (".$normal_phases." Phase(s) ON)";

		}
		if($status==1)
		{
			$sql="INSERT INTO `phase_alerts` (`device_id`, `status`, `phases`, `v_ph1`, `v_ph2`, `v_ph3`, `i_ph1`, `i_ph2`, `i_ph3`, `date_time`) VALUES ( '$deviceid', 'MCB/Contactor OFF', '$phase', '$v1', '$v2', '$v3', '$c1', '$c2', '$c3', '$date')" ;
			mysqli_query($conn, $sql); 
			$msg = "ID:".$deviceid_for_msg." MCB/Contactor Turned OFF in ".$phase." TIME:".$date_sms."";   
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid);
			}
		}
		else
		{
			$sql="INSERT INTO `phase_alerts` (`device_id`, `status`, `phases`, `v_ph1`, `v_ph2`, `v_ph3`, `i_ph1`, `i_ph2`, `i_ph3`, `date_time`) VALUES ( '$deviceid', 'MCB/Contactor ON', '$phase', '$v1', '$v2', '$v3', '$c1', '$c2', '$c3', '$date')" ;
			mysqli_query($conn, $sql);
			$msg = "ID:".$deviceid_for_msg." MCB/Contactor Turned ON in ".$phase." TIME:".$date_sms."";   
			if($enable_disable==1)
			{
				sendsms($msg, $conn, $deviceid);
			}
		}
		break;
		default:
		return "ERROR";
	}
}
	/////////////////////////////////////Input Supply(Modules) Alert Failure Alert ////////////////////////////
function supply_alert($deviceid, $type, $phase, $conn, $status,$v1,$v2,$v3,$c1,$c2,$c3,$date)
{
	$string = str_replace('/', '-', $date);
	$date_new=date_create($string);
	$date =@date_format($date_new,"Y/m/d H:i:s");
	if( !strlen($date))
	{

		$date=date("Y-m-d H:i:s");
	}
	$msg="";
	$store_msg="";
	$date_sms =date_format(date_create($date),"H:i:s d/m/Y ");
	if($status==1)
	{
		$msg="ID:".$deviceid." Input Power Supply OFF TIME:".$date_sms;
		$store_msg="POWER SUPPLY OFF";
	}
	else if($status==0)
	{
		$msg="ID:".$deviceid." Input Power Supply ON TIME:".$date_sms;
		$store_msg="POWER SUPPLY ON";
	}

	$date=date("Y-m-d H:i:s");
	$sql="INSERT INTO `power_supply_alert` (`device_id`, `mobile_number`, `message`, `date_time`) VALUES ( '$deviceid','NA','$store_msg','$date')";

	mysqli_query($conn, $sql);

	sendsms($msg, $conn, $deviceid );
}

///////////////////////////////////// Electric Power Alert  ////////////////////////////

function power_supply_alert($deviceid, $conn, $status, $volts , $date)
{

	$string = str_replace('/', '-', $date);
	$date_new=date_create($string);
	$date =@date_format($date_new,"Y/m/d H:i:s");
	$BSF_alert="";
	$pf_status="";
	$ps_query = mysqli_query( $conn, "SELECT * FROM `alert_power_supply_check` ORDER BY id DESC lIMIT 1");
	if( mysqli_num_rows($ps_query) > 0) 
	{
		$r=  mysqli_fetch_assoc( $ps_query );
		$pf_status=$r['ps_status']; 
	}
	$device_name=$deviceid;
	$device_name = get_name($deviceid, $conn);
	$deviceid_for_msg="";
	$update_sql="";
	if($deviceid!=$device_name)
	{
		$deviceid_for_msg=$deviceid."(". $device_name.")";
	}
	else
	{
		$deviceid_for_msg=$deviceid;
	}
	if($status=="2")
	{ 
		$BSF_alert="Power Supply ON (".$volts." mV)";
		if($pf_status==="OFF")
		{
			mysqli_query($conn, "INSERT INTO `alert_power_supply` (`device_id`, `mobile_number`, `message`, `date_time`) VALUES ('$deviceid', '', '".$BSF_alert."', '".$date."')");
		}

		$update_sql=	"INSERT INTO `alert_power_supply_check` ( `ps_status`, `battery_voltage`, `date_time`) VALUES ( 'ON', '$volts', '$date');";

		$date_msg=date("H:i:s d-m-Y", strtotime($date));
		$msg = "ID:".$deviceid_for_msg." Power Resumed TIME:".$date_msg."";   
		sendsms($msg, $conn, $deviceid);
	}
	else if($status=="3")
	{
		$BSF_alert="Power Supply OFF(".$volts." mV)";
		$SUBJECT="Power Supply Alert of ".$deviceid;
		$emial_send= "swamy@istlabs.in,sampath@istlabs.in";
			//$emial_send= "thirupathi818@gmail.com";
		$msg="The 12V Power Supply has been failed in the Device ID = ".$deviceid;
			//send_email_alert($emial_send, $msg, $SUBJECT);
		if($pf_status==="ON")
		{
			mysqli_query($conn, "INSERT INTO `alert_power_supply` (`device_id`, `mobile_number`, `message`, `date_time`) VALUES ('$deviceid', '', '".$BSF_alert."', '".$date."')");
		}
		$update_sql="INSERT INTO `alert_power_supply_check` ( `ps_status`, `battery_voltage`, `date_time`) VALUES ( 'OFF', '$volts', '$date');";
		$date_msg=date("H:i:s d-m-Y", strtotime($date));
		$msg = "ID:".$deviceid_for_msg." Power Failure TIME:".$date_msg."";   
		sendsms($msg, $conn, $deviceid);
	}
	mysqli_query($conn,$update_sql);
}

//////////////////////////////////// SMS Sending  message      ///////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////

function sendsms($msg, $conn, $device_id)
{

	date_default_timezone_set('Asia/Kolkata');
	$date = date("Y-m-d H:i:s");

        /////////////////////////////////////////////////////////////
	try
	{

		$d_location = "";
		$update_link = "";

		$sql_loc = "SELECT  location FROM frame_data where location !='000000000,0000000000' AND location !=',' ORDER BY id DESC LIMIT 1";
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

	}
	catch(Exception $e)
	{

	}

        /////////////////////////////////////////////////////
	$sql = "INSERT INTO `message` (`device_id`, `mobile_number`, `message`, `date_time`) VALUES ( '$device_id','--','$msg','$date')";
	if (mysqli_query($conn, $sql))
	{
		$msg = str_replace("&", "and", $msg);
		$msg = str_replace("High", "high", $msg);
		$msg = str_replace("HIGH", "high", $msg);
		$msg = str_replace("Voltage", "voltage", $msg);

		$chat_id = "";
		$conn1 = mysqli_connect(HOST, USERNAME, PASSWORD, DB1);
		if (!$conn1)
		{
			die("Connection failed");
		}
		else
		{
			$sql = "SELECT * FROM `telegram_groups_new` WHERE id in (SELECT group_id FROM `telegram_groups_devices` WHERE device_id='$device_id')";
			if (mysqli_query($conn1, $sql))
			{

				$result = mysqli_query($conn1, $sql);
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
						}

					}
				}
			}

			mysqli_close($conn1);
		}

	}

}


function update_ping($deviceid, $conn)
{
	try{

		$date_time=date("Y-m-d H:i:s");

		mysqli_query($conn,"INSERT INTO communication_check ( `device_id`, `date_time`) VALUES('$deviceid', '$date_time') ON DUPLICATE KEY UPDATE device_id='$deviceid', date_time='$date_time'");



	}catch(exeption $e)
	{
	}

}

function update($deviceid){

	$date_time=date("Y-m-d H:i:s");
	$time=date("H:i:s");
	$date= date("Y-m-d ");
	try{
		$log_conn =  mysqli_connect(HOST,USERNAME,PASSWORD,DB2);
		if (!$log_conn) 
		{
			die("Connection failed: ");
		}
		else
		{
			mysqli_query($log_conn,"INSERT INTO devices ( `device`, `status`) VALUES('$deviceid', '1') ON DUPLICATE KEY UPDATE device='$deviceid', status='1'");

			$sql="INSERT INTO device_logs ( `device_id`, `time`, `date`, `date_time`, `notification_flag`) VALUES('$deviceid', '$time', '$date', '$date_time', '0') ON DUPLICATE KEY UPDATE device_id='$deviceid', `time`='$time', `date`='$date', `date_time` = '$date_time', `notification_flag`='0'";
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
	try {

		$date=date("Y-m-d H:i:s");
		mysqli_query( $conn, "INSERT INTO `check_main_frame` ( `frame_type`,`frame`, `date_time`) VALUES ('REQUEST','".$data."', '".$date."')");
	} catch (Exception $e) {
	}
}

function get_name($device_name, $conn)
{

	$sql = "SELECT * FROM `device_name` ORDER BY id DESC lIMIT 1";
	if (mysqli_query($conn, $sql))
	{

		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0)
		{
			while ($r = mysqli_fetch_assoc($result))
			{
				$device_name = $r['device_name'];
			}
		}
	}
	return $device_name;

}

?>
