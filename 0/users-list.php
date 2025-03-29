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
  <title>Add New User</title>  
  
  <?php
  include(BASE_PATH."assets/html/start-page.php");
  ?>
  <div class="d-flex flex-column flex-shrink-0 p-3 main-content ">
    <div class="container-fluid">
        <div class="row d-flex align-items-center">
            <div class="col-12 p-0">
                <p class="m-0 p-0"><span class="text-body-tertiary">Pages / </span><span>Add New User</span></p>
            </div>
        </div>
        <div class="row">                 
            <div class="container mt-2 p-0">
                <div class="row justify-content-end align-items-center mt-2 ">
                    <div class="col-auto mb-2 p-0">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search..."  id="searchInput">
                            <button class="btn btn-primary" type="button" onclick="search_users()">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-auto mb-2 ms-2">
                        <button type="button" class="btn btn-primary w-md-auto" onclick="addUser()">Add User</button>
                    </div>
                </div>
            </div>
            <div class="col-12 p-0">
                <div class="table-responsive rounded mt-2 border">
                    <table class="table table-striped styled-table table-sticky-header table-type-1 w-100 text-center resulttable" id="user_list_table">
                        
                     
                    </table>
                </div>

                <div class="pagination-wrapper mt-2">
                    <div class="row">
                        <div class="col">
                            <div class="row g-2 align-items-center d-flex">
                                <div class="col-auto">
                                    <label for="items-per-page" class="form-label">Items per page:</label>
                                </div>
                                <div class="col-auto">
                                    <select id="items-per-page" class="form-select">
                                        <option value="10">10</option>
                                        <option value="20" selected>20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="100">200</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="col">
                            <div class="pagination-container">
                                <nav>
                                    <ul class="pagination justify-content-end " id="pagination">
                                    </ul>
                                </nav>
                            </div>
                        </div>
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
include(BASE_PATH."account/html/create-new-user.php");
include(BASE_PATH."account/html/user-managing-devices.php");
include(BASE_PATH."account/html/update-button.php");   
include(BASE_PATH."account/html/add-to-group.php");
include(BASE_PATH."account/html/edit-user-details.php");
include(BASE_PATH."account/html/permissions.php");  
include(BASE_PATH."account/html/menu-permissions.php");  
include(BASE_PATH."account/html/account-action.php");  
?>

<script src="<?php echo BASE_PATH;?>assets/js/sidebar-menu.js"></script>
<!-- <script src="<?php echo BASE_PATH;?>js_modal_scripts/searchbar.js"></script> -->
<script src="<?php echo BASE_PATH; ?>js_modal_scripts/popover.js"></script>

<script src="<?php echo BASE_PATH;?>assets/js/project/user-list.js"></script>
<?php
include(BASE_PATH."assets/html/body-end.php"); 
include(BASE_PATH."assets/html/html-end.php");

?>