$(document).ready(function () {
    let group_name = localStorage.getItem("GroupNameValue") || "ALL";
    $("#pre-loader").css('display', 'block');
    fetchDeviceList(group_name);
    fetchDeviceList1(group_name);

    fetchElectrician_details(group_name);
    fetchElectricians(group_name);
    let group_list = document.getElementById('group-list');

    group_list.addEventListener('change', function () {
        let group_name = group_list.value;
        if (group_name !== "" && group_name !== null) {
            // console.log(group_name);
            $("#pre-loader").css('display', 'block');
            $("#response-message").html(""); // Clear the response message
            fetchDeviceList(group_name);
            fetchDeviceList1(group_name);

            fetchElectrician_details(group_name);
            fetchElectricians(group_name);
        }
    });
});


function fetchDeviceList(group_name) {
    $.ajax({
        type: "POST",
        url: '../add_new_electrician_devices/code/fetch_multiple_devices.php',
        data: { GROUP_ID: group_name },
        dataType: "json",
        success: function (data) {
            let selectElement = $("#multi_selection_device_id");
            selectElement.empty();
            if (Array.isArray(data)) {
                data.forEach(device => {
                    selectElement.append(`<option value="${device.D_ID}">${device.D_NAME}</option>`);
                });
                $("#selected_count").text("0");
            } else {
                console.error("Invalid data format received.");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error: ", status, error);
        }
    });
}

function fetchDeviceList1(group_name) {
    $.ajax({
        type: "POST",
        url: '../add_new_electrician_devices/code/fetch_multiple_devices.php',
        data: { GROUP_ID: group_name },
        dataType: "json",
        success: function (data) {
            let selectElement = $("#multi_selection_device_id1");
            selectElement.empty();
            if (Array.isArray(data)) {
                data.forEach(device => {
                    selectElement.append(`<option value="${device.D_ID}">${device.D_NAME}</option>`);
                });
                $("#selected_count").text("0");
            } else {
                console.error("Invalid data format received.");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error: ", status, error);
        }
    });
}


setInterval(refresh_data, 20000);
function refresh_data() {
    let group_name = document.getElementById('group-list').value;
    if (group_name !== "" && group_name !== null) {
        fetchDeviceList(group_name);
        fetchDeviceList1(group_name);
        fetchElectrician_details(group_name)
        fetchElectricians(group_name);
    }
}

function submitElectricianForm1() {
    let selectedElectrician = $("#electrician_list").val();
    let selectedDevices = $("#multi_selection_device_id1").val() || [];
    let group_name = document.getElementById('group-list').value;

    // if (group_name === "ALL") {
    //     $("#response-message-new").html('<div class="text-danger">Please select devices from a specific group.</div>');
    //     return;
    // }

    if (!selectedElectrician || selectedDevices.length === 0) {
        $("#response-message-new").html('<div class="text-danger">Please select an electrician and devices.</div>');
        return;
    }

    // Retrieve phone number from the selected option's data attribute
    let electricianPhone = $("#electrician_list option:selected").data("phone");

    $.ajax({
        type: "POST",
        url: "../add_new_electrician_devices/code/update_existing_electrician_devices.php",
        data: {
            electrician_name: selectedElectrician,
            electrician_phone: electricianPhone,
            group_id: group_name,
            device_ids: selectedDevices
        },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                $("#response-message-new").html('<div class="text-success">Electrician and devices assigned successfully.</div>');
                // Reset the form or update the UI as needed
                $("#selected_count1").text("0");
                // Optionally, refresh data or clear selection
                refresh_data();
            } else {
                $("#response-message-new").html('<div class="text-danger">' + response.message + '</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            $("#response-message-new").html('<div class="text-danger">An error occurred. Please try again.</div>');
        }
    });
}

function submitElectricianForm() {
    let electricianName = $("#Electrician-name").val().trim();
    let electricianPhone = $("#Electrician-phone").val().trim();
    let selectedDevices = $("#multi_selection_device_id").val() || [];
    let group_name = document.getElementById('group-list').value;

    // if (group_name === "ALL") {
    //     $("#response-message").html('<div class="text-danger">Please select devices from a specific group.</div>');
    //     return;
    // }

    if (electricianName === "" || electricianPhone === "" || selectedDevices.length === 0) {
        $("#response-message").html('<div class="text-danger">All fields are required.</div>');
        return;
    }

    $.ajax({
        type: "POST",
        url: "../add_new_electrician_devices/code/update_electrician_devices.php",
        data: {
            electrician_name: electricianName,
            electrician_phone: electricianPhone,
            group_id: group_name,
            device_ids: selectedDevices
        },
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                $("#response-message").html('<div class="text-success">Electrician and devices added successfully.</div>');
                $("#new-Electrician-data")[0].reset();
                $("#selected_count").text("0");
                // $("#selected_count1").text("0");
                refresh_data();

            } else {
                $("#response-message").html('<div class="text-danger">' + response.message + '</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            $("#response-message").html('<div class="text-danger">An error occurred. Please try again.</div>');
        }
    });
}


function fetchElectrician_details(group_name) {
    $.ajax({
        type: "POST",
        url: "../add_new_electrician_devices/code/fetch_electricians.php",
        data: { group_id: group_name },
        dataType: "json",
        success: function (data) {
            let selectElement = $("#electrician_list");
            selectElement.empty();
            selectElement.append('<option value="">Select Electrician</option>');

            if (Array.isArray(data)) {
                data.forEach(electrician => {
                    // Add name and phone number as data attributes
                    selectElement.append(
                        `<option value="${electrician.name}" 
                                data-phone="${electrician.phone}">
                            ${electrician.name} (${electrician.phone})
                        </option>`
                    );
                });
            } else {
                console.error("Invalid data format received.");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error fetching electricians:", error);
        }
    });
}

$("#electrician_list").change(function () {
    let electrician_name = $(this).val();
    if (electrician_name) {
        fetchElectricianDevices(electrician_name);
    }
});


function fetchElectricianDevices(electrician_name) {
    $.ajax({
        type: "POST",
        url: "../add_new_electrician_devices/code/fetch_electrician_devices.php",
        data: { electrician_name: electrician_name },
        dataType: "json",
        success: function (data) {
            let tableContent = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Device ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            if (Array.isArray(data)) {
                data.forEach(device => {
                    tableContent += `
                        <tr>
                            <td>${device.device_id}</td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="removeDevice(${device.id})">Remove</button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tableContent += `<tr><td colspan="2">No devices found.</td></tr>`;
            }
            tableContent += `</tbody></table>`;
            $("#electrician_devices").html(tableContent);
        },
        error: function (xhr, status, error) {
            console.error("Error fetching devices:", error);
        }
    });
}

function removeDevice(device_id) {
    if (confirm("Are you sure you want to remove this device?")) {
        $.ajax({
            type: "POST",
            url: "../add_new_electrician_devices/code/remove_device.php",
            data: { device_id: device_id },
            success: function (response) {
                alert(response);
                $("#electricion_list").change();
            },
            error: function (xhr, status, error) {
                console.error("Error removing device:", error);
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const selectAllCheckbox = document.getElementById("select_all1");
    const deviceSelect = document.getElementById("multi_selection_device_id1");
    const selectedCountSpan = document.getElementById("selected_count1");

    // Function to update the selected count
    function updateSelectedCount() {
        const selectedOptions = Array.from(deviceSelect.options).filter(option => option.selected);
        selectedCountSpan.textContent = selectedOptions.length;

        // If all options are selected manually, check the select all box
        selectAllCheckbox.checked = selectedOptions.length === deviceSelect.options.length;
    }

    // Event listener for Select All checkbox
    selectAllCheckbox.addEventListener("change", function () {
        const options = deviceSelect.options;
        for (let i = 0; i < options.length; i++) {
            options[i].selected = this.checked;
        }
        updateSelectedCount();
    });

    // Event listener for manual selection (ensuring multiple selection works)
    deviceSelect.addEventListener("change", function () {
        updateSelectedCount();
    });

    // Allow Ctrl + Click or Shift + Click for multiple selections
    deviceSelect.addEventListener("mousedown", function (e) {
        e.preventDefault(); // Prevent default selection behavior
        const option = e.target;

        if (option.tagName === "OPTION") {
            option.selected = !option.selected; // Toggle selection state
            updateSelectedCount();
        }
    });
});

// document.addEventListener("DOMContentLoaded", function () {
//     function fetchElectricians(deviceId) {
//         if (deviceId) {
//             fetch("../add_new_electrician_devices/code/fetch_electricians_by_device.php", {
//                 method: "POST",
//                 headers: { "Content-Type": "application/x-www-form-urlencoded" },
//                 body: `device_id=${deviceId}`
//             })
//                 .then(response => response.json())
//                 .then(data => updateElectricianTable(data))
//                 .catch(error => console.error("Error fetching electricians:", error));
//         } else {
//             document.getElementById("electrician_Names").innerHTML = "";
//         }
//     }

//     function updateElectricianTable(data) {
//         let tableHTML = `<table class="table table-bordered">
//             <thead>
//                 <tr>
//                     <th>Electrician Name</th>
//                     <th>Phone</th>
//                     <th>Actions</th>
//                 </tr>
//             </thead>
//             <tbody>`;

//         if (Array.isArray(data) && data.length > 0) {
//             data.forEach(electrician => {
//                 tableHTML += `<tr>
//                     <td>${electrician.name}</td>
//                     <td>${electrician.phone}</td>
//                     <td>
//                         <button class="btn btn-danger btn-sm remove-access" data-id="${electrician.id}">Remove Access</button>
//                         <button class="btn btn-primary btn-sm edit-electrician" data-id="${electrician.id}" data-bs-toggle="modal" data-bs-target="#editElectricianModal">Edit</button>
//                     </td>
//                 </tr>`;
//             });
//         } else {
//             tableHTML += `<tr><td colspan="3" class="text-center">No electricians assigned to this device.</td></tr>`;
//             tableHTML += `<tr>
//                 <td colspan="3" class="text-center">
//                     <button class="btn btn-primary btn-sm edit-electrician" data-id="new" data-bs-toggle="modal" data-bs-target="#editElectricianModal">Edit / Add Electrician</button>
//                 </td>
//             </tr>`;
//         }

//         tableHTML += `</tbody></table>`;
//         document.getElementById("electrician_Names").innerHTML = tableHTML;
//     }

//     function removeElectricianAccess(electricianId) {
//         if (confirm("Are you sure you want to remove access for this electrician?")) {
//             fetch("../add_new_electrician_devices/code/remove_electrician_access.php", {
//                 method: "POST",
//                 headers: { "Content-Type": "application/x-www-form-urlencoded" },
//                 body: `electrician_id=${electricianId}`
//             })
//                 .then(response => response.json())
//                 .then(data => {
//                     alert(data.message);
//                     fetchElectricians(document.getElementById("device_id").value);
//                 })
//                 .catch(error => console.error("Error removing electrician:", error));
//         }
//     }

//     function loadAvailableElectricians() {
//         fetch("../add_new_electrician_devices/code/fetch_all_electricians.php")
//             .then(response => response.json())
//             .then(response => {
//                 let dropdown = document.getElementById("electricianDropdown");
//                 dropdown.innerHTML = '<option value="">Select an Electrician</option>';

//                 if (response.status === "success" && Array.isArray(response.data) && response.data.length > 0) {
//                     response.data.forEach(electrician => {
//                         let option = document.createElement("option");
//                         option.value = electrician.id;
//                         option.textContent = `${electrician.name} (${electrician.phone})`;
//                         dropdown.appendChild(option);
//                     });
//                 } else {
//                     dropdown.innerHTML = '<option value="">No electricians available</option>';
//                 }
//             })
//             .catch(error => {
//                 console.error("Error loading electricians:", error);
//                 document.getElementById("electricianDropdown").innerHTML = '<option value="">Error loading electricians</option>';
//             });
//     }

//     document.addEventListener("click", function (event) {
//         if (event.target.classList.contains("edit-electrician")) {
//             let electricianId = event.target.dataset.id;
//             document.getElementById("editElectricianId").value = electricianId;
//             loadAvailableElectricians();
//         }
//         if (event.target.classList.contains("remove-access")) {
//             let electricianId = event.target.dataset.id;
//             removeElectricianAccess(electricianId);
//         }
//     });

//     document.getElementById("updateElectrician").addEventListener("click", function () {
//         let deviceId = document.getElementById("device_id").value;
//         let newElectricianId = document.getElementById("electricianDropdown").value;
//         let groupName = document.getElementById("group-list").value;

//         if (!deviceId || !newElectricianId) {
//             alert("Please select an electrician and device.");
//             return;
//         }

//         if (!confirm("Are you sure you want to update the electrician?")) {
//             return;
//         }

//         fetch("../add_new_electrician_devices/code/update_electrician.php", {
//             method: "POST",
//             headers: { "Content-Type": "application/x-www-form-urlencoded" },
//             body: `device_id=${deviceId}&new_electrician_id=${newElectricianId}&group_id=${groupName}`
//         })
//             .then(response => response.json())
//             .then(data => {
//                 alert(data.message);
//                 if (data.status === "success") {
//                     fetchElectricians(deviceId);
//                     new bootstrap.Modal(document.getElementById("editElectricianModal")).hide();
//                 }
//             })
//             .catch(error => {
//                 console.error("Error updating electrician:", error);
//                 alert("Failed to update electrician. Please try again.");
//             });
//     });

//     document.getElementById("device_id").addEventListener("change", function () {
//         fetchElectricians(this.value);
//     });

//     let defaultDeviceId = document.getElementById("device_id").value;
//     if (defaultDeviceId) {
//         fetchElectricians(defaultDeviceId);
//     }
// });


function fetchElectricians(group_id) {
    
    // let group_id = document.getElementById('group-list').value; // Correct variable name

    if (group_id) {
        $.ajax({
            type: "POST",
            url: "../add_new_electrician_devices/code/fetch_electricians_by_device.php",
            data: { group_id: group_id },
            dataType: "json",
            success: function (data) {
                updateElectricianTable(data); // Correct function call
            },
            error: function (xhr, status, error) {
                console.error("Error fetching electricians:", error);
            }
        });
    } else {
        // document.getElementById("electrician_Names").innerHTML = "";
    }
}

function updateElectricianTable(data) {
    let tableHTML = `<table class="table table-bordered">
            <thead>
                <tr>
                    <th>Device ID</th>
                    <th>Electrician Name</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>`;

    // Create an array of device_ids from electricians to filter unassigned devices
    let electricianDeviceIds = data.electricians.map(electrician => electrician.device_id);

    // Render electricians table
    if (Array.isArray(data.electricians) && data.electricians.length > 0) {
        data.electricians.forEach(electrician => {
            tableHTML += `<tr>
                    <td>${electrician.device_id}</td>
                    <td>${electrician.name}</td>
                    <td>${electrician.phone}</td>
                    <td>
                        <!-- Responsive buttons -->
                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                            <button class="btn btn-danger btn-sm w-100 w-sm-auto remove-access" onclick="removeElectricianAccess(${electrician.id})">Remove Access</button>
                            <button class="btn btn-primary btn-sm w-100 w-sm-auto edit-electrician" 
                                    data-id="${electrician.device_id}" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editElectricianModal">
                                Edit
                            </button>
                        </div>
                    </td>
                </tr>`;
        });
    } else {
        // tableHTML += `<tr><td colspan="4" class="text-center">No electricians assigned to this group.</td></tr>`;
    }

    // Filter unassigned devices to exclude those that already have an electrician
    // if (Array.isArray(data.unassigned_devices) && data.unassigned_devices.length > 0) {
    //     // Filter out devices that are already in the electricians list
    //     let filteredUnassignedDevices = data.unassigned_devices.filter(device => !electricianDeviceIds.includes(device.device_id));

    //     // Render unassigned devices if there are any left after filtering
    //     if (filteredUnassignedDevices.length > 0) {
    //         filteredUnassignedDevices.forEach(device => {
    //             tableHTML += `<tr>
    //                     <td>${device.device_id}</td>
    //                     <td></td> <!-- Empty name for unassigned devices -->
    //                     <td></td> <!-- Empty phone for unassigned devices -->
    //                     <td>
    //                         <button class="btn btn-success btn-sm w-100 w-sm-auto edit-electrician" data-id="${device.device_id}" data-bs-toggle="modal" data-bs-target="#editElectricianModal">Add Electrician</button>
    //                     </td>
    //                 </tr>`;
    //         });
    //     } else {
    //         tableHTML += `<tr><td colspan="4" class="text-center">No unassigned devices available.</td></tr>`;
    //     }
    // }

    tableHTML += `</tbody></table>`;
    document.getElementById("electricianTable").innerHTML = tableHTML;
}




function removeElectricianAccess(electricianId) {
    // console.log(electricianId);
    if (confirm("Are you sure you want to remove access for this electrician?")) {
        fetch("../add_new_electrician_devices/code/remove_electrician_access.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `electrician_id=${electricianId}`
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                var group_id = document.getElementById('group-list').value;

                fetchElectricians(group_id);
            })
            .catch(error => console.error("Error removing electrician:", error));
    }
}

function loadAvailableElectricians() {
    fetch("../add_new_electrician_devices/code/fetch_all_electricians.php")
        .then(response => response.json())
        .then(response => {
            let dropdown = document.getElementById("electricianDropdown");
            dropdown.innerHTML = '<option value="">Select an Electrician</option>';

            if (response.status === "success" && Array.isArray(response.data) && response.data.length > 0) {
                response.data.forEach(electrician => {
                    let option = document.createElement("option");
                    option.value = electrician.id;
                    option.textContent = `${electrician.name} (${electrician.phone})`;
                    dropdown.appendChild(option);
                });
            } else {
                dropdown.innerHTML = '<option value="">No electricians available</option>';
            }
        })
        .catch(error => {
            console.error("Error loading electricians:", error);
            document.getElementById("electricianDropdown").innerHTML = '<option value="">Error loading electricians</option>';
        });
}

document.addEventListener("click", function (event) {
    if (event.target.classList.contains("edit-electrician")) {
        let deviceId = event.target.dataset.id; // Get device_id from the button
        document.getElementById("editElectricianId").value = deviceId; // Store device_id in hidden input
        document.getElementById("deviceIdDisplay").innerText = deviceId; // Show device_id in the modal (optional)

        loadAvailableElectricians(); // Call to load electricians for selection
    }
});


document.getElementById("updateElectrician").addEventListener("click", function () {
    let deviceId = document.getElementById("editElectricianId").value; // Get device_id from hidden input
    let newElectricianId = document.getElementById("electricianDropdown").value;
    let groupName = document.getElementById("group-list").value;

    if (!deviceId || !newElectricianId) {
        alert("Please select an electrician and device.");
        return;
    }

    if (!confirm("Are you sure you want to update the electrician?")) {
        return;
    }

    fetch("../add_new_electrician_devices/code/update_electrician.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `device_id=${deviceId}&new_electrician_id=${newElectricianId}&group_id=${groupName}`
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                var group_id = document.getElementById('group-list').value;
                fetchElectricians(group_id);
                // Show success message or alert here
                // alert("Electrician details updated successfully.");
            }
        })
        .catch(error => {
            console.error("Error updating electrician:", error);
            alert("Failed to update electrician. Please try again.");
        });
});


// let group_list = document.getElementById('group-list');

// group_list.addEventListener('change', function () {
//     var group_id = document.getElementById('group-list').value;

//     fetchElectricians(group_id);
// });
// fetchElectricians();

// let defaultDeviceId = document.getElementById("device_id").value;
// if (defaultDeviceId) {
//     fetchElectricians(defaultDeviceId);
// }
document.addEventListener("DOMContentLoaded", function () {
    var group_id = document.getElementById('group-list').value;

    fetchElectricians(group_id);
});
// Function to filter the table based on search input
// Function to filter the table based on the search input
function filterTable() {
    let searchTerm = document.getElementById("searchBar").value.toLowerCase(); // Get the search term
    let table = document.getElementById("electricianTable");
    let rows = table.getElementsByTagName("tr");

    // Loop through all table rows and hide those that don't match the search term
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
        let cells = rows[i].getElementsByTagName("td");
        let deviceID = cells[0].textContent.toLowerCase();
        let electricianName = cells[1].textContent.toLowerCase();

        // Check if the search term matches either the device ID or the electrician name
        if (
            deviceID.indexOf(searchTerm) > -1 ||  // If search term matches device ID
            electricianName.indexOf(searchTerm) > -1 // If search term matches electrician name
        ) {
            rows[i].style.display = ""; // Show the row
        } else {
            rows[i].style.display = "none"; // Hide the row
        }
    }
}
