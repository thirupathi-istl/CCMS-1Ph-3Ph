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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["group_id"])) {
    $group_id = $_POST['group_id'];

    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DB_USER);
    if (!$conn) {
        die(json_encode(["status" => "error", "message" => "Database connection failed."]));
    }

    $group_id = mysqli_real_escape_string($conn, $group_id);

    $electricians = [];
    $unassigned_devices = [];

    if ($group_id === "ALL") {
        // Fetch all electricians
        $sql_electricians = "SELECT id, electrician_name, phone_number, device_id 
                             FROM electrician_devices 
                             WHERE user_login_id = ?";
        $stmt = mysqli_prepare($conn, $sql_electricians);
        mysqli_stmt_bind_param($stmt, "i", $user_login_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $electricians[] = [
                "id" => $row["id"],
                "name" => $row["electrician_name"],
                "phone" => $row["phone_number"],
                "device_id" => $row["device_id"]
            ];
        }
        mysqli_stmt_close($stmt);

        // Fetch unassigned devices
        $sql_devices = "SELECT DISTINCT device_id, c_device_name 
                        FROM user_device_list 
                        WHERE device_id NOT IN (
                            SELECT device_id FROM electrician_devices WHERE user_login_id = ?
                        )
                        GROUP BY device_id, c_device_name";
        $stmt = mysqli_prepare($conn, $sql_devices);
        mysqli_stmt_bind_param($stmt, "i", $user_login_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $unassigned_devices[] = [
                "device_id" => $row["device_id"],
                "device_name" => $row["c_device_name"]
            ];
        }
        mysqli_stmt_close($stmt);
    } else {
        // Fetch electricians for specific group_area
        $sql_electricians = "SELECT id, electrician_name, phone_number, device_id 
                             FROM electrician_devices 
                             WHERE group_area = ? AND user_login_id = ?";
        $stmt = mysqli_prepare($conn, $sql_electricians);
        mysqli_stmt_bind_param($stmt, "si", $group_id, $user_login_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $electricians[] = [
                "id" => $row["id"],
                "name" => $row["electrician_name"],
                "phone" => $row["phone_number"],
                "device_id" => $row["device_id"]
            ];
        }
        mysqli_stmt_close($stmt);

        // Fetch unassigned devices for that group
        $sql_devices = "SELECT DISTINCT device_id, c_device_name 
                        FROM user_device_group_view 
                        WHERE device_group_or_area = ? AND device_id NOT IN (
                            SELECT device_id 
                            FROM electrician_devices 
                            WHERE group_area = ? AND user_login_id = ?
                        )";
        $stmt = mysqli_prepare($conn, $sql_devices);
        mysqli_stmt_bind_param($stmt, "ssi", $group_id, $group_id, $user_login_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $unassigned_devices[] = [
                "device_id" => $row["device_id"],
                "device_name" => $row["c_device_name"]
            ];
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);

    echo json_encode([
        "electricians" => $electricians,
        "unassigned_devices" => $unassigned_devices
    ]);
} else {
    echo json_encode([]);
}
?>
