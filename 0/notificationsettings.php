<?php
require_once 'config-path.php';
require_once '../session/session-manager.php';
require_once BASE_PATH . 'config_db/config.php';
require_once BASE_PATH . 'session/session-manager.php';
SessionManager::checkSession();
$sessionVars = SessionManager::SessionVariables();
$role = $sessionVars['role'];
$user_login_id = $sessionVars['user_login_id'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <title>Dashboard</title>

    <?php
    include(BASE_PATH . "assets/html/start-page.php");
    ?>
    <div class="d-flex flex-column flex-shrink-0 p-3 main-content ">
        <div class="container-fluid">
            <div class="row d-flex align-items-center">
                <div class="col-12 p-0">
                    <p class="m-0 p-0"><span class="text-body-tertiary">Pages / </span><span>Alert Settings</span></p>
                </div>
            </div>
            <?php
            include(BASE_PATH . "dropdown-selection/group-device-list.php");
            ?>

            <div class="row d-flex justify-content-center">
                <div class="col-lg-8 col-xl-6 p-0 m-0">
                    <div class="col-12 rounded mt-2 p-0 ">
                        <div class="card">
                            <div class="d-flex justify-content-between align-items-center p-1">
                                <button type="button" class="btn btn-primary btn-sm" id="add_devices_to_dp_selection" data-bs-toggle="modal" data-bs-target="#group_device_multiselection">Multiple Device Selection</button>

                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    Telegram Group Update
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 rounded mt-2 p-0 ">
                        <div class="card">
                            <div class="card-header bg-primary bg-opacity-25 fw-bold">
                                Alerts
                                <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="Info"
                                    data-bs-content="Enable the Types of Alerts to be received"> <i class="bi bi-info-circle"></i> </a>

                            </div>
                            <div class="card-body ">
                                <form id="notifications-form">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-check-label" for="voltage">Voltage</label>
                                        <div class="form-check form-switch ms-auto">
                                            <input class="form-check-input pointer" type="checkbox" name="notifications" id="voltage" data-permission="voltage" value="voltage">
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-check-label" for="overload">Overload/Current</label>
                                        <div class="form-check form-switch ms-auto">
                                            <input class="form-check-input pointer" type="checkbox" name="notifications" id="overload" data-permission="overload" value="overload">
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-check-label" for="power_fail">Input Power Fail</label>
                                        <div class="form-check form-switch ms-auto">
                                            <input class="form-check-input pointer" type="checkbox" name="notifications" id="power_fail" data-permission="power_fail" value="power_fail">

                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-check-label" for="on_off">On/Off</label>
                                        <div class="form-check form-switch ms-auto">
                                            <input class="form-check-input pointer" type="checkbox" name="notifications" id="on_off" data-permission="on_off" value="on_off">

                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-check-label" for="mcb_contactor_trip">MCB/Contactor</label>
                                        <div class="form-check form-switch ms-auto">
                                            <input class="form-check-input pointer" type="checkbox" name="notifications" id="mcb_contactor_trip" data-permission="mcb_contactor_trip" value="mcb_contactor_trip">

                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-check-label" for="door_alert">Panel Door</label>
                                        <div class="form-check form-switch ms-auto">
                                            <input class="form-check-input pointer" type="checkbox" name="notifications" id="door_alert" data-permission="door_alert" value="door_alert">

                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-center align-items-center mt-2">
                                        <button type="button" class="btn btn-primary" onclick="updateSelectedAlerts()">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <?php
    include(BASE_PATH . "settings/html/telegram-integration.php");
    include(BASE_PATH . "dropdown-selection/multiple-group-device_selection.php");
    ?>
    <script src="<?php echo BASE_PATH; ?>assets/js/sidebar-menu.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/project/alert-settings.js"></script>
    <script src="<?php echo BASE_PATH; ?>js_modal_scripts/popover.js"></script>

    <?php
    include(BASE_PATH . "assets/html/body-end.php");
    include(BASE_PATH . "assets/html/html-end.php");
    ?>