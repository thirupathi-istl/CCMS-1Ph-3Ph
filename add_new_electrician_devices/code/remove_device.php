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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["device_id"])) {
    $device_id = $_POST['device_id'];

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $device_id = mysqli_real_escape_string($conn, $device_id);
    $sql = "DELETE FROM electrician_devices WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $device_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Device removed successfully!";
    } else {
        echo "Error removing device.";
    }

    mysqli_close($conn);
} else {
    echo "Invalid request!";
}
?>
