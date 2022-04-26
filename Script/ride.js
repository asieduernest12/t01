let WAY_POINT_TIME_INTERVAL = 1_000;
// ride codes here
let _interval;

/**@typ Map<string,Ride> */
let _rides;

/**
 * @typedef {{id:string, waypoints: WayPoint[]}} Ride
 * @typedef {{utc_timestamp: Date, position: {coords:{latitude:number, longitude:number}}}} WayPoint
 *
 *
 */

/**
 * @description create a ride and pass it to the wayPoint recorderd with an x interval
 */
function startRide() {
	if (_interval) throw alert("Interval is engaged, end the current ride and try again");

	showPathElement(false);

	if (!_rides) _rides = new Map();

	let ride = { id: _rides.size + 1, waypoints: [] };

	_rides.set(ride.id, ride);

	_interval = WayPointRecorder(ride);
}

function WayPointRecorder(/**@type Ride */ ride) {
	return setInterval(() => {
		console.log("new way point recorded");

		let way_point_indicator = document.querySelector("input.waypoint_indicator");

		if (way_point_indicator) way_point_indicator.value = ride.waypoints.length;
		//record waypoints
		navigator.geolocation.getCurrentPosition(recordLocationAndTimeStamp(ride), gpsError);

		updateTimeUi(ride);
	}, WAY_POINT_TIME_INTERVAL);
}

function updateTimeUi(/**@type Ride */ ride) {
	if (ride.waypoints.length < 2) return console.log("there must be a minimum of two waypoint records");

	let _time = dateFns.differenceInMinutes(new Date(ride.waypoints.at(-1).utc_timestamp), new Date(ride.waypoints[0].utc_timestamp));

	document.querySelector(".time_value").innerHTML = _time;
}

let gpsError = (error) => {
	throw alert("failed to record position");
};

function recordLocationAndTimeStamp(ride) {
	return (position) =>
		ride.waypoints.push({ utc_timestamp: new Date().toISOString(), position: { coords: { longitude: position.coords.longitude, latitude: position.coords.latitude } } });
}

function endRide() {
	if (!_interval) throw alert("ride is present");

	clearInterval(_interval);

	_interval = null;

	const lastride = _rides.get(_rides.size);
	document.querySelector("#waypoints_json").value = JSON.stringify(lastride);

	showRoute(lastride); //show the route for the last ride
}

function showRoute(/**@type Ride */ past_ride) {
	if (!past_ride) throw alert("Error: past_ride is invalid");

	try {
		showPathElement(true);
		console.log("attempting to show ride path");
		let path_container = document.getElementById("map-path");

		if (!path_container) throw alert("Error: path_container not found");

		const flightPlanCoordinates = past_ride.waypoints.map(({ position }) => ({ lat: position.coords.latitude, lng: position.coords.longitude }));

		const map = new google.maps.Map(path_container, {
			zoom: 15,
			center: flightPlanCoordinates[0],
			mapTypeId: "terrain",
		});

		const flightPath = new google.maps.Polyline({
			path: flightPlanCoordinates,
			geodesic: true,
			strokeColor: "#FF0000",
			strokeOpacity: 1.0,
			strokeWeight: 2,
		});

		flightPath.setMap(map);
	} catch (error) {
		console.log(error);
	}
}

function showPathElement(show = false) {
	document.querySelector("#map-container-google-1").style.display = show ? "none" : "initial";

	document.querySelector("#map-path").style.display = show ? "initial" : "none";
}

function clearTextArea(event) {
	event.preventDefault();
	document.querySelector("textarea").value = "";
}

function handleSubmit(event) {
	event.preventDefault();

	console.log(event.target);

	//send the recorded ride info back to the server
	fetch("controller.php?action=upsertride", {
		headers: {
			"Content-Type": "application/json",
		},
		method: "POST",
		body:JSON.stringify( { data: event.target.elements.data}) ,
	})
		.then((res) => res.json())
		.then(console.log)
		.catch(console.err);
}

function loadHistory(user_id) {}
