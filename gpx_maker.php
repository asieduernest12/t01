<?php

// Report all PHP errors
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

// phpinfo();
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * take coordinates
 * data = map of coordinates to gps
 * data_st = data conver to string
 * write the string to a new file
 * return the link/new file to the new file 
 */

/**
 * 
 *  "utc_timestamp": "2022-04-18T21:13:15.173Z",
 "position": {
 "coords": {
 "longitude": -91.541629,
 "latitude": 41.653193
 }
 }
 */

function parseJsonRide($ride)
{
    $waypoint_gpxs = array_map(function ($waypoint) {
        return wayPointToGpxTrkpt($waypoint->position->coords->latitude,
        $waypoint->position->coords->longitude,
        $waypoint->utc_timestamp);
    }, $ride->waypoints); //get the waypoints as an array


    $waypoints_gpxs_str = implode("", $waypoint_gpxs);

    $trkseg = <<<TRKSEG
    <trkseg>
        $waypoints_gpxs_str
    </trkseg>
TRKSEG;

    $trk = <<<TRK
    <trk>
        <ride id="{$ride->id}"></ride>
        <name>some name</name>
        <type>some type</type>
        {$trkseg}
    </trk>
TRK;
    return $trk;
}



function wayPointToGpxTrkpt($lat, $lon, $time)
{
    print("parsing waypoint");
    return <<<TRKPT
    <trkpt lat="{$lat}" long="{$lon}">
        <time>{$time}</time>
    </trkpt>
TRKPT;
}


$ride_str = <<<RIDE
{
    "id": 1,
    "waypoints": [
        {
            "utc_timestamp": "2022-04-18T21:11:21.486Z",
            "position": {
                "coords": {
                    "longitude": -91.5414511,
                    "latitude": 41.6531178
                }
            }
        },
        {
            "utc_timestamp": "2022-04-18T21:11:22.172Z",
            "position": {
                "coords": {
                    "longitude": -91.5414511,
                    "latitude": 41.6531178
                }
            }
        }
       
        
    ]
}
RIDE;

// print($ride_str);
// $ride = json_decode($ride_str);
// var_dump($ride);
// print(parseJsonRide($ride));



//get data from db
function readData($mysqli, $user_id)
{
    $query_str = "Select from user where id=?;";

    $stmt = mysqli_prepare($mysqli, $query_str);

    //check  if there is an error on each step
    //steps => bind parameters => execute the query 
    if (!mysqli_stmt_bind_param($stmt, 'd', $user_id) || !mysqli_execute($stmt, ['id' => $user_id])) {
        throw new \Exception("Error db");
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new \Exception("Failed to load data");
    }

    //return the result 
    return mysqli_fetch_assoc($result);
}

//write data to  db
function writeData($sqlitedb, $user_id, string $username, string $email, string $ride_history_json, $userExists)
{

    if (!$userExists) {
        if (!strlen($username) || !strlen($email) || !strlen($ride_history_json)) {
            throw new Exception("Insert validation error: check username, email or ride_history_json");
        }

        //   insert new record
        $query_str = "Insert into users (username,email,ride_history) (?,?,?);";
        return runQuery($sqlitedb, $query_str, 'sss', [$username, $email, $ride_history_json], false);
    }
    else {

        if (!strlen($username) || !strlen($ride_history_json)) {
            throw new Exception("Update validation Error: check username or ride_history");
        }
        //update record
        $query_str = "Update users set ride_history = ? where username = ?;";
        return runQuery($sqlitedb, $query_str, $types = "ss", $bindings = [$username, $ride_history_json], $isSelect = false);
    }
}

function userExists($sqlitedb, $username): bool
{
    $query_str = "Select count(*) as 'exists' from user where username=?;";


    return runQuery($sqlitedb, $query_str, 's', [$username], true)[0]['exists'];
}

function runQuery($db, string $query_str, string $types, array $bindings, bool $isSelect)
{
    if (!isset($isSelect)) {
        throw new Exception("Error: query type not specified, must be true or false");
    }


    // $stmt = mysqli_prepare($mysqli, $query_str);
    $stmt = $db->prepare($query_str);


    // //check that types have the same length as bindings
    // if (strlen(explode("", $types)) != count($bindings)) {
    //     throw new Exception("Error: types and bindings length differ");
    // }

    //check  if there is an error on each step
    //steps => bind parameters => execute the query 
    // if (!mysqli_stmt_bind_param($stmt, $types, $bindings) || !mysqli_execute($stmt)) {
    //     throw new Exception("Error db");
    // }
    if (count($bindings) > 0) {
        foreach ($bindings as $key => $binding) {
            $stmt->bindParam($key, $binding);
        }
    }


    //if query is an insert, then return the affected rows count
    if (!$isSelect) {

        return $stmt->execute();
    // return mysqli_stmt_affected_rows($stmt);
    }

    //if the query is for select then get the result
    // $result = mysqli_stmt_get_result($stmt);

    $result = $stmt->execute();

    //fail if there is no ressult
    if (!$result) {
        throw new \Exception("Failed to load data");
    }

    //return the result 
    return $result;
}

function writeGpxToFile()
{

}

function getFileDownload()
{

}

function getSqlite3(): SQLite3
{
    // return mysqli_connect("localhost", "root", "", "rides");
    return new SQLite3('mysqlitedb.db');
}


function testSQLite3($db)
{
    echo "<br> testing sqlite now";
    $db->exec("INSERT INTO foo (id, bar) VALUES (1, 'This is a test')");
    echo "<br> testing sqlite now:complete";

}

echo "testing sqlite ";

testSQLite3(getSqlite3());
writeData(getSqlite3(), null, "new_user", "email@domain.com", "{}", userExists(getSqlite3(), "new_user"));
