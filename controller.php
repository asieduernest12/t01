<?php

// Report all PHP errors
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

include_once('parser.php');
include_once('db.php');


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


// testParser();

function testController()
{

    echo "testing sqlite ";


    writeData(getSqlite3(), null, "new_user", "email@domain.com", "{}", userExists(getSqlite3(), "new_user"));


    $result = readData(getSqlite3(), null);
    $res_json = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        array_push($res_json, $row);
    }

    echo json_encode($res_json);
}


function handle()
{


    $action = $_GET['action'];
    $json_data = json_decode(file_get_contents("php://input"));

    switch ($action) {
        case 'upsertride':


            upsertRide($json_data->rides, $json_data->username, $json_data->email);

            $json = getRides($user_id = null);

            echo  toJson($json);

            break;
        case 'deleteride':
            deleteRide($_GET['ride_id']);
            break;

        case 'getrides':

            $json = getRides($user_id = null);

            if (!isset($_GET['transform']) && $_GET['transform'] !== true) {
                echo  toJson($json);
                break;
            }
            

            $arr_res = array_map(function ($ride) {

                return parseJsonRide($ride);
            }, json_decode($json[0]['ride_history']));

            $output= wrapAsGpx(implode("", $arr_res));

            sendDownloadable('gpxdata.xml',$output);

            break;
        default:
            print('Error: operation unspecified');
            throw new Exception("Error: no action specified");
    }
}

function getRides($userid)
{

    return fetchAllAssoc(readData(getSqlite3(), null));
}

function upsertRide(array $ride, $username, $email)
{

    writeData(getSqlite3(), null, $username, $email, toJson($ride), userExists(getSqlite3(), $username));
}

function deleteRide($ride_id)
{
    print("delete ride $ride_id");
}

function toJson($data): String
{
    return json_encode($data);
}


function sendDownloadable(String $filename, $contents){

    header("Content-Disposition:attachment;filename={$filename}");

    print($contents);
}

handle();
