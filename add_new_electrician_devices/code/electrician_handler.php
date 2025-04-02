<?php
// Include your database connection here
require_once '../base-path/config-path.php';
require_once BASE_PATH.'config_db/config.php';
require_once BASE_PATH.'session/session-manager.php';
SessionManager::checkSession();
$sessionVars = SessionManager::SessionVariables();
$mobile_no = $sessionVars['mobile_no'];
$user_id = $sessionVars['user_id'];
$role = $sessionVars['role'];
$user_login_id = $sessionVars['user_login_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Fetch devices assigned to electricians from electrician_devices table
    if ($action === 'fetchAssignedDevices') {
        $electricianId = $_POST['electrician_id'];
        fetchAssignedDevices($electricianId);
    }

    // Fetch devices not assigned to electricians from user_device_list table
    if ($action === 'fetchUnassignedDevices') {
        $electricianId = $_POST['electrician_id'];
        fetchUnassignedDevices($electricianId);
    }

    // Remove electrician access from a device
    if ($action === 'removeAccess') {
        $deviceId = $_POST['device_id'];
        $electricianName = $_POST['electrician_name'];
        removeElectricianAccess($deviceId, $electricianName);
    }

    // Update electrician for an unassigned device
    if ($action === 'assignElectrician') {
        $deviceId = $_POST['device_id'];
        $electricianId = $_POST['electrician_id'];
        assignElectrician($deviceId, $electricianId);
    }
}

// Fetch devices already assigned to electricians
function fetchAssignedDevices($electricianId) {
    global $conn;
    $query = "SELECT d.device_id, d.device_name, e.electrician_name ,e.
              FROM electrician_devices e 
              INNER JOIN user_device_list d ON e.device_id = d.device_id 
              WHERE e.electrician_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $electricianId);
    $stmt->execute();
    $result = $stmt->get_result();

    $devices = [];
    while ($row = $result->fetch_assoc()) {
        $devices[] = $row;
    }

    echo json_encode($devices);
}

// Fetch devices not yet assigned to electricians
function fetchUnassignedDevices($electricianId) {
    global $conn;
    $query = "SELECT d.device_id, d.device_name 
              FROM user_device_list d 
              WHERE d.device_id NOT IN (
                SELECT device_id FROM electrician_devices WHERE electrician_id = ?
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $electricianId);
    $stmt->execute();
    $result = $stmt->get_result();

    $unassignedDevices = [];
    while ($row = $result->fetch_assoc()) {
        $unassignedDevices[] = $row;
    }

    echo json_encode($unassignedDevices);
}

// Remove electrician access from the assigned device
function removeElectricianAccess($deviceId, $electricianName) {
    global $conn;
    $query = "DELETE FROM electrician_devices WHERE device_id = ? AND electrician_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $deviceId, $electricianName);

    if ($stmt->execute()) {
        echo "Electrician access removed successfully.";
    } else {
        echo "Error removing electrician access.";
    }
}

// Assign an electrician to an unassigned device
function assignElectrician($deviceId, $electricianId) {
    global $conn;
    $query = "INSERT INTO electrician_devices (device_id, electrician_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $deviceId, $electricianId);

    if ($stmt->execute()) {
        echo "Electrician assigned successfully.";
    } else {
        echo "Error assigning electrician.";
    }
}
?>
