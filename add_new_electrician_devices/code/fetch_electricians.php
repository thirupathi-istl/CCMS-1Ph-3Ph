<?php
require_once '../../base-path/config-path.php';
require_once BASE_PATH_1 . 'config_db/config.php';
require_once BASE_PATH_1 . 'session/session-manager.php';

SessionManager::checkSession();
$sessionVars = SessionManager::SessionVariables();
$mobile_no = $sessionVars['mobile_no'];
$user_id = $sessionVars['user_id'];
$role = $sessionVars['role'];
$user_login_id = $sessionVars['user_login_id'];
$user_devices = "";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["group_id"])) {
    $group_id = $_POST['group_id'];
    include_once(BASE_PATH_1 . "common-files/selecting_group_device.php");

    if ($user_devices != "") {
        $user_devices = substr($user_devices, 0, -1);
    }

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
    if (!$conn) {
        die(json_encode(["status" => "error", "message" => "Connection failed: " . mysqli_connect_error()]));
    }

    $electricians = [];
    $temp_data = [];

    $sql = "SELECT DISTINCT electrician_name, phone_number FROM electrician_devices WHERE device_id IN ($user_devices)";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $temp_data[] = [
                'name' => $row['electrician_name'],
                'phone' => $row['phone_number']
            ];
        }
    }

    // If we have electricians from electrician_devices, use their name and phone to fetch from electricians_list
    foreach ($temp_data as $data) {
        $name = mysqli_real_escape_string($conn, $data['name']);
        $phone = mysqli_real_escape_string($conn, $data['phone']);

        $query = "SELECT id, name, phone_number FROM electricians_list WHERE name = '$name' AND phone_number = '$phone' LIMIT 1";
        $res = mysqli_query($conn, $query);
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $electricians[] = [
                "id" => $row["id"],
                "name" => $row["name"],
                "phone" => $row["phone_number"]
            ];
        }
    }

    mysqli_close($conn);
    echo json_encode($electricians);
} else {
    echo json_encode([]);
}
?>
