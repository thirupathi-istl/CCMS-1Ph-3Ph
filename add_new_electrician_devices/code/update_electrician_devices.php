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
    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
    if (!$conn) {
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }
    function sanitize_input($data, $conn) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return mysqli_real_escape_string($conn, $data);
    }
    

    $electrician_name = sanitize_input($_POST['electrician_name'], $conn);
    $electrician_phone = sanitize_input($_POST['electrician_phone'], $conn);
    $group_id = sanitize_input($_POST['group_id'], $conn);
    $device_ids = array_map(function($id) use ($conn) {
        return sanitize_input($id, $conn);
    }, $_POST['device_ids']);

    if (empty($electrician_name) || empty($electrician_phone) || empty($device_ids)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Check if the electrician already exists
    $check_query = "SELECT id FROM electricians_list WHERE phone_number = ? AND name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $electrician_phone, $electrician_name);
    $stmt->execute();
    $stmt->bind_result($electrician_id);
    $stmt->fetch();
    $stmt->close();

    if ($electrician_id) {
        echo json_encode(["status" => "error", "message" => "Electrician already exists."]);
        exit;
    }

    // Fetch group_id for the given device_ids
    $placeholders = implode(',', array_fill(0, count($device_ids), '?'));
    $fetch_group_sql = "SELECT device_group_or_area FROM user_device_group_view WHERE device_id IN ($placeholders) LIMIT 1";
    $stmt = $conn->prepare($fetch_group_sql);
    $stmt->bind_param(str_repeat('s', count($device_ids)), ...$device_ids);
    $stmt->execute();
    $stmt->bind_result($fetched_group_id);
    $stmt->fetch();
    $stmt->close();
    
    $group_area = $fetched_group_id;

    // Insert new electrician into electricians_list
    $insert_query = "INSERT INTO electricians_list (name, phone_number, group_area, user_login_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssss", $electrician_name, $electrician_phone, $group_area, $user_login_id);
    
    if ($stmt->execute()) {
        $electrician_id = $stmt->insert_id;
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add electrician."]);
        exit;
    }
    $stmt->close();

    // Insert into electrician_devices table for each selected device
    $query = "INSERT INTO electrician_devices (electrician_name, phone_number, device_id, group_area, user_login_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    foreach ($device_ids as $device_id) {
        $stmt->bind_param("sssss", $electrician_name, $electrician_phone, $device_id, $group_area, $user_login_id);
        if (!$stmt->execute()) {
            echo json_encode(["status" => "error", "message" => "Failed to assign electrician to device."]);
            exit;
        }
    }
    $stmt->close();
    mysqli_close($conn);

    echo json_encode(["status" => "success", "message" => "Electrician added successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
