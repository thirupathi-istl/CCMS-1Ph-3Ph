<?php
require_once 'config-path.php';
require_once BASE_PATH . 'config_db/config.php';
require_once BASE_PATH . 'session/session-manager.php';
SessionManager::checkSession();
$sessionVars = SessionManager::SessionVariables();

$mobile_no = $sessionVars['mobile_no'];
$user_id = $sessionVars['user_id'];
$role = $sessionVars['role'];
$user_login_id = $sessionVars['user_login_id'];
$user_name = $sessionVars['user_name'];
$user_email = $sessionVars['user_email'];
$permission_check = 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
    <title>Add New Electrician / Assign Devices</title>
    <?php
    include(BASE_PATH . "assets/html/start-page.php");
    ?>
    <div class="d-flex flex-column flex-shrink-0 p-3 main-content ">
        <div class="container-fluid">
            <div class="row d-flex align-items-center">
                <div class="col-12 p-0">
                    <p class="m-0 p-0"><span class="text-body-tertiary">Pages / </span><span>Add New Electrician / Assign Devices</span></p>
                </div>
            </div>
            <?php
            // include(BASE_PATH . "dropdown-selection/group-device-list.php");
            include(BASE_PATH . "dropdown-selection/device-list.php");
            ?>
            <div class="row">
                <!-- Add New Electrician -->
                <div class="col-md-6">
                    <div class="card mt-3 h-100 d-flex flex-column">
                        <div class="card-header bg-primary bg-opacity-25 fw-bold">
                            <span class="me-2">Add New Electrician</span>
                            <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="Info" data-bs-content="Add New Electrician">
                                <i class="bi bi-info-circle"></i>
                            </a>
                        </div>
                        <div class="card-body flex-grow-1">
                            <form class="col-md-12" id="new-Electrician-data" method="post">
                                <div class="pb-2">
                                    <label for="Electrician-name" class="form-label">Electrician Name</label>
                                    <input type="text" class="form-control" id="Electrician-name" name="Electrician-name" placeholder="Enter Electrician Name" required>

                                    <label for="Electrician-phone" class="form-label mt-2">Phone Number</label>
                                    <input type="tel" class="form-control" id="Electrician-phone" name="Electrician-phone" placeholder="Enter Phone Number" pattern="[0-9]{10}" maxlength="10" required>
                                </div>

                                <div class="mb-2 ms-2">
                                    <div class="col-sm-12">
                                        <div class="custom-control custom-checkbox pl-3">
                                            <input type="checkbox" id="select_all" style="width: auto; margin-top:10px" />
                                            <label class="small"> Select All</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 text-right d-flex align-items-center ">
                                        <div class="col-12">
                                            <select id="multi_selection_device_id" class="multi_selection_device_id col-12" multiple size="30" style="max-height: 250px;">

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="custom-control custom-checkbox pl-3">
                                            <span>Selected : <b><span id="selected_count">0</span></b> </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2" id="response-message"></div>

                                <div class="d-flex justify-content-center align-items-center mt-2">
                                    <button type="button" class="btn btn-primary" onclick="submitElectricianForm()">Add</button>
                                </div>
                            </form>

                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div class="w-100 text-center">
                                <div class="mt-1 text-start">
                                    <p class="text-danger">* To Add New Electrician details </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Assign Devices -->
                <div class="col-md-6">
                    <div class="card mt-3 h-100 d-flex flex-column">
                        <div class="card-header bg-primary bg-opacity-25 fw-bold">
                            <span class="me-2">Assign Devices</span>
                            <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="Info" data-bs-content="Add New Devices">
                                <i class="bi bi-info-circle"></i>
                            </a>
                        </div>
                        <div class="card-body flex-grow-1">
                            <div class="row mt-2 d-flex justify-content-center">
                                <!-- <div class="col-sm-8">
                                    <div class="mb-2 ms-2">
                                        <label for="add_user" class="form-label">Select Electrician</label>
                                        <select class="form-select" id="electricion_list">
                                        </select>
                                    </div>

                                    <div class="mb-2 ms-2" id="electrician_devices">
                                    </div>
                                </div> -->
                                <!-- <div class="col-sm-8">
                                    <div class="mb-2 ms-2">
                                        <label for="device_id" class="form-label">Select Device</label>
                                        <?php
                                        include("../dropdown-selection/device_selection.php");
                                        ?>

                                    </div>

                                    <div class="mb-2 ms-2" id="electrician_Names">
                                    </div>
                                </div> -->

                                <div class="col-12"> <!-- Changed from col-sm-8 to col-12 for full width -->
                                    <div class="table-responsive w-100"> <!-- Ensures the table takes full width -->
                                        <table class="table table-bordered w-100" id="electricianTable"> <!-- Added w-100 to table -->
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Device ID</th>
                                                    <th>Electrician Name</th>
                                                    <th>Phone</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic Content Will Be Inserted Here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <!-- <div class="container">
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary mb-2" onclick="assignDevicesToElectrician()">Assign Devices</button>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
    </div>
    </main>

    <?php
    include(BASE_PATH . "/devices/html/group-creation.php");
    include(BASE_PATH . "/add_new_electrician_devices.php/html/update_electrician.php");

    ?>


    <script src="<?php echo BASE_PATH; ?>js_modal_scripts/popover.js"></script>

    <script src="<?php echo BASE_PATH; ?>assets/js/sidebar-menu.js"></script>
    <!-- <script src="<?php echo BASE_PATH; ?>assets/js/project/group_list_update.js"></script> -->
    <script src="<?php echo BASE_PATH; ?>assets/js/project/add_new_electrician_details.js"></script>

    <script src="<?php echo BASE_PATH; ?>json-data/json-data.js"></script>


    <?php
    include(BASE_PATH . "assets/html/body-end.php");
    include(BASE_PATH . "assets/html/html-end.php");
    ?>