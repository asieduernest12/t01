<?php
//get data from db
function readData(SQLite3 $sqlitedb, $user_id)
{
    $query_str = "Select * from users";

    if (isset($user_id)) {
        $query_str .= ' where id=?';
    }

    // $stmt = mysqli_prepare($mysqli, $query_str);
    $stmt = $sqlitedb->prepare($query_str);

    //check  if there is an error on each step
    // //steps => bind parameters => execute the query 
    // if (!mysqli_stmt_bind_param($stmt, 'd', $user_id) || !mysqli_execute($stmt, ['id' => $user_id])) {
    //     throw new \Exception("Error db");
    // }

    if (!$stmt) {
        throw new Exception("Error: failed to prepare stmt", 1);
    }

    $result = $stmt->execute();

    if (!$result) {
        throw new \Exception("Failed to load data");
    }

    //return the result 
    return $result;
}

//write data to  db
function writeData($sqlitedb, $user_id, string $username, string $email, string $ride_history_json, $userExists)
{

    if (!$userExists) {
        if (!strlen($username) || !strlen($email) || !strlen($ride_history_json)) {
            throw new Exception("Insert validation error: check username, email or ride_history_json");
        }

        //   insert new record
        $query_str = "Insert into users (username,email,ride_history) values (:username,:email,:ride_history);";
        return runQuery($sqlitedb, $query_str, $type = 'sss', $bindings = [':username' => $username, ':email' => $email, ':ride_history' => $ride_history_json], false);
    } else {

        if (!strlen($username) || !strlen($ride_history_json)) {
            throw new Exception("Update validation Error: check username or ride_history");
        }
        //update record
        $query_str = "Update users set ride_history = :ride_history where username = :username;";
        return runQuery($sqlitedb, $query_str, $types = "ss", $bindings = [':username' => $username,  ':ride_history' => $ride_history_json], $isSelect = false);
    }
}

function userExists($sqlitedb, $username, $debug = false): bool
{
    $query_str = "Select count(*) as 'exists' from users where username=:username;";

    $result = runQuery($sqlitedb, $query_str, 's', ['username' => $username], true);

    if (!$result) {
        throw new Exception("Error Processing Request", 1);
    }

    $res = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($debug == true) {
        print_r($res);
    }

    return  $res['exists'];
}

function  runQuery(SQLite3 $db, string $query_str, string $types, array $bindings, bool $isSelect, $debug = false)
{
    if (!isset($isSelect)) {
        throw new Exception("Error: query type not specified, must be true or false");
    }


    // $stmt = mysqli_prepare($mysqli, $query_str);
    $stmt = $db->prepare($query_str);

    if (!$stmt) {
        echo "Error in fetch " . $db->lastErrorMsg();
        throw new Exception('Error: failed to prepare $stmt');
    }


    if (count($bindings) > 0) {

        foreach ($bindings as $key => $binding) {
            if ($debug == true) {
                print_r([$key, $binding]);
            }
            $stmt->bindValue($key, $binding);
        }
    }


    //if query is an insert, then return the affected rows count
    if (!$isSelect) {

        return $stmt->execute();
    }



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


function prepareDB()
{
    return <<<QUERY

    -- COMMENT 'containes rides info as json' CHECK (json_valid(`ride_history`)),
        
        CREATE TABLE IF NOT EXISTS users (
        `id` int(11) PRIMARY KEY ,
        `ride_history` longtext DEFAULT NULL,
        `username` text DEFAULT NULL,
        `email` text DEFAULT NULL
        
        ) ;
QUERY;
}


function getSqlite3(): SQLite3
{

    try {
        $db = new SQLite3('mysqlitedb.db');

        runQuery($db, prepareDB(), '', [], false);

        return $db;
    } catch (\Throwable $th) {
        throw $th;
    }
}


function fetchAllAssoc(SQLite3Result $result)
{
    $raw_arr = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        array_push($raw_arr, $row);
    }

    return $raw_arr;
}
