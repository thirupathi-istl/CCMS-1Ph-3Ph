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
// Establish database connection
$conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

// Fetch electricians from the database
$sql = "SELECT id, name, phone_number FROM electricians_list where user_login_id =$user_login_id  ORDER BY name ASC";
$result = mysqli_query($conn, $sql);

$electricians = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $electricians[] = [
            "id" => $row["id"],
            "name" => $row["name"],
            "phone" => $row["phone_number"]
        ];
    }
    echo json_encode(["status" => "success", "data" => $electricians]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to fetch electricians."]);
}

// Close the database connection
mysqli_close($conn);
?>
