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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["electrician_id"])) {
    $electrician_id = $_POST['electrician_id'];

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
    if (!$conn) {
        die(json_encode(["status" => "error", "message" => "Database connection failed."]));
    }

    $electrician_id = mysqli_real_escape_string($conn, $electrician_id);
    $sql = "DELETE FROM electrician_devices WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $electrician_id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Electrician access removed successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to remove electrician."]);
    }

    mysqli_close($conn);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
