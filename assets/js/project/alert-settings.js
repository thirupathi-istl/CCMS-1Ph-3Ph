let error_message = document.getElementById('error-message');
let error_message_text = document.getElementById('error-message-text');
let success_message = document.getElementById('success-message');
let success_message_text = document.getElementById('success-message-text');

const error_toast= bootstrap.Toast.getOrCreateInstance(error_message);
const success_toast= bootstrap.Toast.getOrCreateInstance(success_message);


let device_id = localStorage.getItem("SELECTED_ID");
if (!device_id) {
	device_id = document.getElementById('device_id').value;
}
//var selected_alert  = document.getElementById('selected_phase_alert').value;

loadUpdatedAlerts(device_id);

let device_id_list=document.getElementById('device_id');
device_id_list.addEventListener('change', function() 
{
	$("#pre-loader").css('display', 'block');
	device_id = document.getElementById('device_id').value;
	loadUpdatedAlerts(device_id);
	refresh_data();
});

setTimeout(refresh_data, 50);
setInterval(refresh_data, 20000);
function refresh_data() {
	if (typeof update_frame_time === "function") {
		device_id = document.getElementById('device_id').value;
		update_frame_time(device_id);
	} 
}


$('#select_all_for_telegram').click(function() {
	if($(this).is(':checked'))
	{
		$('.telegram_group_add option').prop("selected", true)
	}
	else
	{
		$('.telegram_group_add option').prop("selected", false)
	}
		//var count = $(".telegram_group_add :selected").length;        
		//$('#selected_count').text(count);
});

function clear_selection(){
	$('#group_devices option').prop("selected", false);
	
}
function clear_selection_add_devices(){
	
	//document.getElementById('#select_all_for_telegram').checked=false;
	document.getElementById('select_all_for_telegram').checked = false;

	
	$('#add_devices_to_telegram_group option').prop("selected", false);

}


$('#update_device_to_group').click(function(){


	var multipleValues = $( "#add_devices_to_telegram_group" ).val() || [];
	var selected_devices=multipleValues.join( "," );

	var device_id =selected_devices;
	if(selected_devices.length<=0)
	{
		error_message_text.textContent="Please select Devices..";
		error_toast.show();
		document.getElementById("add_devices_to_telegram_group").focus();
		return false;
	}

	var chat_id=$('#update_telegram_groups').val();
	var group_name=$('#update_telegram_groups  option:selected').text();		


	if(chat_id==""||chat_id==null||chat_id==0)
	{
		error_message_text.textContent="Please select Group...";
		error_toast.show();
		document.getElementById("update_telegram_groups").focus();
		return false;
	}

	if (confirm('Are you sure ?')) {
		$("#pre-loader").css('display', 'block');
		$(function(){
			$.ajax({
				type: "POST",
				url: '../settings/code/telegram_group_id_update_to_device.php',  
				traditional : true, 
				dataType: "json", 
				data:{ID:device_id, CHAT_ID:chat_id, GROUP:group_name},

				success: function(response) {
					$("#pre-loader").css('display', 'none');
					if (response.status === 'success') {
						success_message_text.textContent=response.message;
						success_toast.show();
						update_device_of_group();
					} else {
						error_message_text.textContent=response.message;
						error_toast.show();
					}				

				},
				error: function (textStatus, errorThrown) {
					error_message_text.textContent="Error getting the data";
					error_toast.show();
					$("#pre-loader").css('display', 'none');
				},
				failure: function()
				{
					$("#pre-loader").css('display', 'none');
					error_message_text.textContent="Failed to get the dataa";
					error_toast.show();
				}
			});
		});
	}		

});

$('#save_chat_id').click(function(){
	var chat_id=$('#chat_id').val();
	var group_name=$('#telegram_group_name').val();

	if(chat_id==""||chat_id==null)
	{
		error_message_text.textContent="Enter Chat ID...";
		error_toast.show();

		document.getElementById("chat_id").focus();
		return false;
	}

	if(group_name==""||group_name==null)
	{
		error_message_text.textContent="Enter Group Name...";
		error_toast.show();

		document.getElementById("telegram_group_name").focus();
		return false;
	}

	if (confirm('Are you sure you want to Update ?')) {
		$("#pre-loader").css('display', 'block');
		$(function(){
			$.ajax({
				type: "POST",
				url: '../settings/code/telegram_chat_id_update.php',
				traditional : true, 
				dataType:'json',
				data:{CHAT_ID:chat_id, GROUP_NAME:group_name, SAVE:"SAVE"},

				success: function(response) {
					$("#pre-loader").css('display', 'none');
					if (response.status === 'success') {
						success_message_text.textContent=response.message;
						success_toast.show();
						
						$('#chat_id').val("");
						$('#telegram_group_name').val("");
					} else {
						error_message_text.textContent=response.message;
						error_toast.show();
					}
					
					

				},
				error: function (textStatus, errorThrown) {
					error_message_text.textContent="Error getting the data";
					error_toast.show();
					$("#pre-loader").css('display', 'none');
				},
				failure: function()
				{
					error_message_text.textContent="Failed to get the dataa";
					error_toast.show();
					$("#pre-loader").css('display', 'none');
				}
			});
		});
	}		

});

$('#test_chat_id').click(function(){
	var chat_id=$('#chat_id').val();
	var group_name=$('#telegram_group_name').val();

	if(chat_id==""||chat_id==null)
	{
		error_message_text.textContent="Enter Chat ID...";
		error_toast.show();

		document.getElementById("chat_id").focus();
		return false;
	}		
	$("#pre-loader").css('display', 'block');
	$(function(){
		$.ajax({
			type: "POST",
			url: '../settings/code/telegram_chat_id_update.php',
			traditional : true, 
			dataType:'json',
			data:{CHAT_ID:chat_id, GROUP_NAME:group_name, CHECK:"TEST"},

			success: function(response) {
				$("#pre-loader").css('display', 'none');
				if (response.status === 'success') {
					success_message_text.textContent=response.message;
					success_toast.show();
				} else {
					error_message_text.textContent=response.message;
					error_toast.show();
				}
				

				
			},
			error: function (textStatus, errorThrown) {
				error_message_text.textContent="Error getting the data";
				error_toast.show();
				$("#pre-loader").css('display', 'none');

			},
			failure: function()
			{
				error_message_text.textContent="Failed to get the data";
				error_toast.show();
				$("#pre-loader").css('display', 'none');

			}
		});
	});

});
$("#updated_telegram_groups").change(function () {
	$("#pre-loader").css('display', 'block');
	update_device_of_group();
});

function update_device_of_group(){
	var selected_group=$('#updated_telegram_groups').val();
	if(selected_group!="")
	{
		$('#group_devices').empty();
		$(function(){
			$.ajax({
				type: "POST",
				url: '../settings/code/telegram_group_devices.php',
				traditional : true, 
				data:{GROUP_ID:selected_group},
				dataType: "json", 
				success: function(data) {

					if(Object.keys(data).length)
					{
						for(var i=0; i<Object.keys(data).length; i++)
						{
							$('#group_devices').append('<option value="'+data[i].device_id+'">'+data[i].device_name+'</>');
						}
					}
					$("#pre-loader").css('display', 'none');
				},
				error: function (textStatus, errorThrown) {
					$('#group_devices').empty();
					error_message_text.textContent="Error getting the data";
					error_toast.show();
					$("#pre-loader").css('display', 'none');
				},
				failure: function()
				{
					$('#group_devices').empty();
					error_message_text.textContent="Failed to get the dataa";
					error_toast.show();
					$("#pre-loader").css('display', 'none');
				}
			});
		});

	}
	else
	{
		$('#group_devices').empty();
	}
}

$("#remove_device_from_group").click(function () {


	var selected_group=$('#updated_telegram_groups').val();
	if(selected_group=="")
	{
		error_message_text.textContent="Please select telegram group...";
		error_toast.show();

		document.getElementById("updated_telegram_groups").focus();
		return false;

	}

	var multipleValues = $( "#group_devices" ).val() || [];
	var selected_devices=multipleValues.join( "," );

	var device_id =selected_devices;
	if(selected_devices.length<=0)
	{
		error_message_text.textContent="Please select Devices...";
		error_toast.show();
		document.getElementById("group_devices").focus();
		return false;
	}


	if (confirm('Are you sure ?')) {
		$("#pre-loader").css('display', 'block');
		$(function(){
			$.ajax({
				type: "POST",
				url: '../settings/code/telegram_group_remove_from_device.php',
				traditional : true, 
				dataType: 'json',
				data:{ID:device_id, GROUP_ID:selected_group},

				success: function(response) {
					$("#pre-loader").css('display', 'none');
					if (response.status === 'success') {
						success_message_text.textContent=response.message;
						success_toast.show();
						update_device_of_group();
					} else {
						error_message_text.textContent=response.message;
						error_toast.show();
					}
				},
				error: function (textStatus, errorThrown) {
					error_message_text.textContent="Error getting the data";
					error_toast.show();
					$("#pre-loader").css('display', 'none');
				},
				failure: function()
				{
					error_message_text.textContent="Failed to get the dataa";
					error_toast.show();
					$("#pre-loader").css('display', 'none');
				}
			});
		});
	}

});

$("#remove_devices-tab").click(function () {

	$('#group_devices').empty();
	$('#updated_telegram_groups option').prop("selected", false);
});

function loadUpdatedAlerts(device_id){

	$("#pre-loader").css('display', 'block');
	$(function(){
		$.ajax({
			type: "POST",
			url: '../settings/code/fetch-updated-notification-settings.php',
			traditional : true, 
			dataType: 'json', 
			data:{D_ID:device_id},

			success: function(response_data) {
				$("#pre-loader").css('display', 'none');				
				if(response_data.status=="success")
				{					
					$('#voltage').prop('checked', response_data.data.voltage == 1);
					$('#overload').prop('checked', response_data.data.overload == 1);
					$('#power_fail').prop('checked', response_data.data.power_fail == 1);
					$('#on_off').prop('checked', response_data.data.on_off == 1);
					$('#mcb_contactor_trip').prop('checked', response_data.data.mcb_contactor_trip == 1);
					$('#door_alert').prop('checked', response_data.data.door_alert == 1);
				}	
				else
				{
					error_message_text.textContent=response_data.message;
					error_toast.show();

					$('#voltage').prop('checked', false);
					$('#overload').prop('checked', false);
					$('#power_fail').prop('checked', false);
					$('#on_off').prop('checked', false);
					$('#mcb_contactor_trip').prop('checked', false);
					$('#door_alert').prop('checked', false);
				}					

			},
			error: function (textStatus, errorThrown) {
				error_message_text.textContent="Error getting the data";
				error_toast.show();
				$("#pre-loader").css('display', 'none');
			},
			failure: function()
			{
				error_message_text.textContent="Failed to get the data";
				error_toast.show();
				$("#pre-loader").css('display', 'none');
			}
		});
	});
}

function updateSelectedAlerts()
{

	let selectedNotification = {};

    // Collect all permissions and whether they are checked or not
	document.querySelectorAll('#notifications-form input[type="checkbox"]').forEach(function(checkbox) {
		selectedNotification[checkbox.id] = checkbox.checked ? 1 : 0;
	});
	var multipleValues = $( "#multi_selection_device_id" ).val() || [];
	var selected_devices=multipleValues.join( "," );
	var device_id =selected_devices;
	
	if(selected_devices==""||selected_devices==null)
	{
		device_id = document.getElementById('device_id').value;
		if(device_id==""||device_id==null)
		{
			error_message_text.textContent="Please Select the Device Id";
			error_toast.show();
			return false;
		}
	}
	console.log(selectedNotification)
	if (confirm('Are you sure you want to update the selected notifications?')) {
		$("#pre-loader").css('display', 'block');
		$.ajax({
        url: '../settings/code/notifications-save.php',  // Adjust the path to the actual PHP script
        type: 'POST',
        data: { D_ID: device_id, parameters: selectedNotification },
        dataType: 'json',
        success: function(response) {
        	$("#pre-loader").css('display', 'none'); 
        	if (response.status === 'success') {
        		success_message_text.textContent=response.message;
        		success_toast.show();
        	} else {
        		
        		error_message_text.textContent=response.message;
        		error_toast.show();
        	}
        },
        error: function() {
        	$("#pre-loader").css('display', 'none'); 
        	error_message_text.textContent="Failed to update Notification settings.";
        	error_toast.show();
        }
    });
	}
}
