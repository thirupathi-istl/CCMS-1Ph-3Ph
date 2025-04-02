 

 /* function initMap() {

  	var options = {
  		zoom: 8,
  		center: { lat: 17.8850, lng: 78.8867 }
  	};

  	var map = new google.maps.Map(document.getElementById('map'), options);
  	var marker = new google.maps.Marker({
  		position: { lat: 17.8850, lng: 76.4867 },
  		map: map
  	});
  }*/

var loc_lat="17.890307";
var loc_long="79.863593";
var zoom_level=7;
var user_map="";
var group="";
var modal_event=0;

let group_list_map = document.getElementById('group-list');
var group_name=localStorage.getItem("GroupNameValue")
if(group_name==""||group_name==null)
{
	group_name= group_list_map.value;
}
gps_initMaps(group_name);
group_list_map.addEventListener('change', function() {
	group_name = group_list_map.value;
	gps_initMaps(group_name);
});

function gps_initMaps(group_name) {

	$("#loader").css('display', 'block');
	$(function () {
		$.ajax({
			type: "POST",
			url: '../devices/code/gis-locations.php',
			traditional : true, 
			data:{GROUP_ID:group_name},
			dataType: "json", 
			success:  function(data){
				$("#loader").css('display', 'none');
				on_success(data);
			},
			failure: function (response) {
				alert(response.responseText);
			},
			error: function (response) {
				alert(response.responseText);
			}
		});
	});
}
function on_success(data)
{
	var json = data;
	locations = [];

	var subinfoWindow= new google.maps.InfoWindow();

	for (var i = 0; i < json.length; i++)
	{
		locations.push([ json[i].va, json[i].l1, json[i].l2, json[i].icon, json[i].id]);
	}
	if(group_name=="ALL")
	{
		zoom_level=5;
	}
	else
	{
		var i=0;
		loc_lat=0;
		loc_long= 0;
		for (i = 0; i < locations.length; i++) 
		{  
			var lat=Number(locations[i][1])
			var long=Number(locations[i][2])
			if((lat!=0)||(long!=0))
			{

				loc_lat=locations[0][1];
				loc_long= locations[0][2];
			}

		}
		zoom_level=12;
	}

	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: zoom_level,
		center: new google.maps.LatLng(loc_lat, loc_long),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		gestureHandling: 'cooperative'
	});
	var infowindow = new google.maps.InfoWindow();
	var marker, i;
	var markers = [];
	var image="";
	var image_red = 'https://maps.gstatic.com/mapfiles/ms2/micons/red-dot.png';
	var image_green = 'https://maps.gstatic.com/mapfiles/ms2/micons/green-dot.png';
	var image_yellow = 'https://maps.gstatic.com/mapfiles/ms2/micons/yellow-dot.png';
	var image_blue = 'https://maps.gstatic.com/mapfiles/ms2/micons/blue-dot.png';
	var image_orange = 'https://maps.gstatic.com/mapfiles/ms2/micons/orange-dot.png';
	var image_purple = 'https://maps.gstatic.com/mapfiles/ms2/micons/purple-dot.png';

	for (i = 0; i < locations.length; i++) 
	{  
		image="";

		if(locations[i][3]=="1")
		{
			image=image_green;
		}
		else if(locations[i][3]=="2")
		{
			image=image_yellow;
		}
		else if(locations[i][3]=="3")
		{
			image=image_blue;
		}
		else if(locations[i][3]=="4")
		{
			image=image_purple;
		}
		else
		{
			image=image_red;
		}

		marker = new google.maps.Marker({
			position: new google.maps.LatLng(locations[i][1], locations[i][2]),
			map: map,
			icon: image
		});
		google.maps.event.addListener(marker, 'click', (function (marker, i) {
			return function () {
				infowindow.setContent(locations[i][0]);
				infowindow.open(map, marker);
				if (subinfoWindow) {
					subinfoWindow.close();
				}
			};
		})(marker, i));
		google.maps.event.addListener(map, 'click', function (event) {
			if (infowindow) {
				infowindow.close();
			}
			if (subinfoWindow) {
				subinfoWindow.close();
			}
		});

		markers.push(marker);
	}


	function populateDropdown() {
		const dropdown = document.getElementById('locationsDropdown');

     // $("#locationsDropdown option").remove();
		$("#locationsDropdown").empty();


		const option = document.createElement('option');
		option.value = ""; 
		option.textContent = "Find Device Location"; 
		dropdown.appendChild(option);

		locations.forEach((location, index) => {
			const option = document.createElement('option');
			option.value = index.toString(); 
			option.textContent = location[4]; 

			dropdown.appendChild(option);
		});

		dropdown.addEventListener('change', function () {
			const selectedIndex = parseInt(this.value, 10);
			if (!isNaN(selectedIndex)) {
				highlightMarker(selectedIndex);
			}

		});
	}
	function highlightMarker(index) {
		markers.forEach((marker, i) => {
			if (i === index) {
				marker.setAnimation(google.maps.Animation.BOUNCE); 
				map.setCenter(marker.getPosition());
				map.setZoom(16);

          //////////////////////////
				infowindow.setContent(locations[i][0]);
				infowindow.open(map, marker);
				if (subinfoWindow) {
					subinfoWindow.close();
				}
          //////////////////////////
				setTimeout(function () {
					marker.setAnimation(null);
				}, 2000);
			} else {
				marker.setAnimation(null);
			}
		});
	}

	populateDropdown();
}



