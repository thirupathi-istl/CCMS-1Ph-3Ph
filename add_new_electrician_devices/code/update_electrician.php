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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["device_id"], $_POST["new_electrician_id"], $_POST["group_id"])) {
    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
    
    if (!$conn) {
        die(json_encode(["status" => "error", "message" => "Database connection failed."]));
    }

    $device_id = $_POST["device_id"];
    $new_electrician_id = $_POST["new_electrician_id"];
    $group_id = $_POST["group_id"];

    // Fetch new electrician details
    $fetch_sql = "SELECT name, phone_number FROM electricians_list WHERE id = ? and user_login_id = ?";
    $stmt = $conn->prepare($fetch_sql);
    $stmt->bind_param("ii", $new_electrician_id, $user_login_id);
    $stmt->execute();
    $stmt->bind_result($electrician_name, $electrician_phone);
    $stmt->fetch();
    $stmt->close();

    if ($electrician_name && $electrician_phone) {
        // Fetch group_id or device_group_or_area before updating or inserting
        $fetch_group_sql = "SELECT device_group_or_area FROM user_device_group_view WHERE device_id = ? LIMIT 1";
        $stmt = $conn->prepare($fetch_group_sql);
        $stmt->bind_param("s", $device_id);
        $stmt->execute();
        $stmt->bind_result($fetched_group_id);
        $stmt->fetch();
        $stmt->close();

        // Use the fetched group_id as group_area in both the update and insert operations
        $group_area = $fetched_group_id;

        // Check if the device already has an associated electrician
        $check_sql = "SELECT id FROM electrician_devices WHERE device_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $device_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update the existing record with the fetched group_area
            $update_sql = "UPDATE electrician_devices SET electrician_name = ?, phone_number = ?, group_area = ? WHERE device_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssss", $electrician_name, $electrician_phone, $group_area, $device_id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Electrician updated successfully."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update electrician."]);
            }
        } else {
            // Insert a new record if no matching device_id is found
            $insert_sql = "INSERT INTO electrician_devices (device_id, electrician_name, phone_number, group_area,user_login_id) VALUES (?, ?, ?, ?,?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssss", $device_id, $electrician_name, $electrician_phone, $group_area,$user_login_id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Electrician added successfully."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to add electrician."]);
            }
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Electrician details not found."]);
    }

    mysqli_close($conn);
}
?>
