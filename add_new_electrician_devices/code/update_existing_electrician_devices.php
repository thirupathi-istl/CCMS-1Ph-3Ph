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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["device_ids"])) {
    $electrician_name = $_POST['electrician_name'];
    $electrician_phone = $_POST['electrician_phone'];
    $group_id = $_POST['group_id'];
    $device_ids = $_POST['device_ids']; // This will be an array

    if (empty($electrician_name) || empty($electrician_phone) || empty($device_ids)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
    if (!$conn) {
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }

    // Fetch group_id for the given device_ids
    $device_ids_placeholder = implode(',', array_fill(0, count($device_ids), '?'));
    $fetch_group_sql = "SELECT device_group_or_area FROM user_device_group_view WHERE device_id IN ($device_ids_placeholder) LIMIT 1";
    $stmt = $conn->prepare($fetch_group_sql);
    
    // Bind parameters dynamically for the device_ids
    $stmt->bind_param(str_repeat('s', count($device_ids)), ...$device_ids);
    $stmt->execute();
    $stmt->bind_result($fetched_group_id);
    $stmt->fetch();
    $stmt->close();

    $group_area = $fetched_group_id; // Use fetched group_id for group_area

    // Check if the electrician already exists
    $electrician_phone = mysqli_real_escape_string($conn, $electrician_phone);
    $electrician_name = mysqli_real_escape_string($conn, $electrician_name);

    $check_query = "SELECT id FROM electricians_list WHERE phone_number = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $electrician_phone);
    $stmt->execute();
    $stmt->bind_result($electrician_id);
    $stmt->fetch();
    $stmt->close();

    if (!$electrician_id) {
        // Electrician doesn't exist, insert into electricians table
        $insert_query = "INSERT INTO electricians_list (name, phone_number, group_area, user_login_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $electrician_name, $electrician_phone, $group_area, $user_login_id);

        if ($stmt->execute()) {
            $electrician_id = $stmt->insert_id; // Get the newly inserted ID
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add electrician."]);
            exit;
        }
        $stmt->close();
    }

    // Insert into electrician_devices table for each selected device
    foreach ($device_ids as $device_id) {
        $device_id = mysqli_real_escape_string($conn, $device_id);
        $query = "INSERT INTO electrician_devices (electrician_name, phone_number, device_id, group_area, user_login_id) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $electrician_name, $electrician_phone, $device_id, $group_area, $user_login_id);

        if (!$stmt->execute()) {
            echo json_encode(["status" => "error", "message" => "Failed to assign electrician to device."]);
            exit;
        }
        $stmt->close();
    }

    mysqli_close($conn);
    echo json_encode(["status" => "success", "message" => "Electrician added successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
