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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["group_id"])) {
    $group_id = $_POST['group_id'];

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $electricians = [];
    
    
        // Fetch all electricians without filtering by group_id
        $sql = "SELECT DISTINCT name, phone_number FROM electricians_list where user_login_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
    
        mysqli_stmt_bind_param($stmt, "i", $user_login_id);
  

    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $electricians[] = ["name" => $row["name"], "phone" => $row["phone_number"]];
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
    echo json_encode($electricians);
} else {
    echo json_encode([]);
}
?>
