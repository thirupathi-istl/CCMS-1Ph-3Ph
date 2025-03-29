<?php

$device_id = "my name";

if ($device_id != "") {
	echo $device_id;

	echo "<br>";

	my_fun();
}

function my_fun() {
    global $device_id;  // Declare the global variable
    echo "=======================" . "<br>";
    echo $device_id;
}

?>