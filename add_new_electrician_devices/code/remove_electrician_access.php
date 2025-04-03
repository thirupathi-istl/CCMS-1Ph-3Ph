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

$conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["electrician_id"])) {
        // Single deletion (existing logic)
        $electrician_id = mysqli_real_escape_string($conn, $_POST["electrician_id"]);
        $sql = "DELETE FROM electrician_devices WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $electrician_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["status" => "success", "message" => "Electrician access removed successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to remove electrician."]);
        }
    } elseif (isset($_POST["electrician_ids"])) {
        // Multiple deletions
        $electrician_ids = json_decode($_POST["electrician_ids"], true); // Decode JSON string

        if (!is_array($electrician_ids) || count($electrician_ids) === 0) {
            echo json_encode(["status" => "error", "message" => "No valid electrician IDs provided."]);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($electrician_ids), '?'));
        $sql = "DELETE FROM electrician_devices WHERE id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);

        $types = str_repeat("i", count($electrician_ids));
        mysqli_stmt_bind_param($stmt, $types, ...$electrician_ids);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["status" => "success", "message" => "Selected electricians removed successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to remove selected electricians."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

mysqli_close($conn);
?>
