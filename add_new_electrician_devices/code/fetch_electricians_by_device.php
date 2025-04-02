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
        die(json_encode(["status" => "error", "message" => "Database connection failed."]));
    }

    $group_id = mysqli_real_escape_string($conn, $group_id);

    // Fetch electricians based on group_id or all devices if group_id is "all"
    if ($group_id == "ALL") {
        // Fetch all electricians (ignore group_area)
        $sql_electricians = "SELECT id, electrician_name, phone_number, device_id 
                             FROM electrician_devices
                             WHERE user_login_id = $user_login_id";

        $stmt = mysqli_prepare($conn, $sql_electricians);
        mysqli_stmt_execute($stmt);
        $result_electricians = mysqli_stmt_get_result($stmt);

        $electricians = [];
        while ($row = mysqli_fetch_assoc($result_electricians)) {
            $electricians[] = [
                "id" => $row["id"],
                "name" => $row["electrician_name"],
                "phone" => $row["phone_number"],
                "device_id" => $row["device_id"]
            ];
        }
        mysqli_stmt_close($stmt);

        // Fetch all devices from user_device_list
        $sql_devices = "SELECT Distinct  device_id, c_device_name
        FROM user_device_list
        WHERE device_id NOT IN (
            SELECT device_id
            FROM electrician_devices
            WHERE user_login_id = ?
        )
        GROUP BY device_id, c_device_name";

        $stmt_devices = mysqli_prepare($conn, $sql_devices);

        // Bind parameters to the prepared statement
        mysqli_stmt_bind_param($stmt_devices, "i", $user_login_id);

        // Execute the statement
        mysqli_stmt_execute($stmt_devices);

        // Get the result
        $result_devices = mysqli_stmt_get_result($stmt_devices);

        // Initialize an array to store devices
        $unassigned_devices = [];
        while ($row = mysqli_fetch_assoc($result_devices)) {
            $unassigned_devices[] = [
                "device_id" => $row["device_id"],
                "device_name" => $row["c_device_name"]
            ];
        }

        // Close the statement
        mysqli_stmt_close($stmt_devices);
    } else {
        // Fetch electricians based on specific group_area
        $sql_electricians = "SELECT id, electrician_name, phone_number, device_id 
                             FROM electrician_devices 
                             WHERE group_area = ? AND user_login_id = $user_login_id";

        $stmt = mysqli_prepare($conn, $sql_electricians);
        mysqli_stmt_bind_param($stmt, "s", $group_id);
        mysqli_stmt_execute($stmt);
        $result_electricians = mysqli_stmt_get_result($stmt);

        $electricians = [];
        while ($row = mysqli_fetch_assoc($result_electricians)) {
            $electricians[] = [
                "id" => $row["id"],
                "name" => $row["electrician_name"],
                "phone" => $row["phone_number"],
                "device_id" => $row["device_id"]
            ];
        }
        mysqli_stmt_close($stmt);

        // Fetch devices from user_device_list where device_id is not in electrician_devices for the specific group_area
        $sql_devices = "SELECT DISTINCT device_id, c_device_name 
                FROM user_device_group_view 
                WHERE device_group_or_area = ? AND device_id NOT IN (
                    SELECT device_id 
                    FROM electrician_devices 
                    WHERE group_area = ? AND user_login_id = ?
                )";

        $stmt_devices = mysqli_prepare($conn, $sql_devices);

        // Bind parameters to the prepared statement
        mysqli_stmt_bind_param($stmt_devices, "ssi", $group_id, $group_id, $user_login_id);

        // Execute the statement
        mysqli_stmt_execute($stmt_devices);

        // Get the result
        $result_devices = mysqli_stmt_get_result($stmt_devices);

        // Initialize an array to store devices
        $unassigned_devices = [];
        while ($row = mysqli_fetch_assoc($result_devices)) {
            $unassigned_devices[] = [
                "device_id" => $row["device_id"],
                "device_name" => $row["c_device_name"]
            ];
        }

        // Close the statement
        mysqli_stmt_close($stmt_devices);
    }

    mysqli_close($conn);

    // Return both electricians and unassigned devices
    echo json_encode([
        "electricians" => $electricians,
        "unassigned_devices" => $unassigned_devices
    ]);
} else {
    echo json_encode([]);
}
