<?php
require_once('../config/db_config.php');
/* establish DB connection */
$dbConn = getMySQLConnectionInstance();

$action = isset($_GET['action']) ?  $_GET['action'] : null;
$res_arr = array();

if($action == 'saveaddress') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $req_body = file_get_contents("php://input");
        parse_str($req_body, $req_arr);
        // validate form data
        if(validateFormData($req_arr, $res_arr)) {
            if(isset($req_arr['saveType']) && isset($req_arr['userId']) && $req_arr['saveType'] == 'update') {
                // update new address entry
                updateNewAddress($req_arr['userId'], $req_arr, $res_arr);
            } else {
                // create new address entry
                addNewAddress($req_arr, $res_arr);
            }
        }
    } else {
        $res_arr['type'] = 'error';
        $res_arr['message'] = 'Invalid HTTP Method.';
        $res_arr['data'] = null;
    }
} else if($action == 'listaddress') {
    fetchAddressList($res_arr);

} else if($action == 'listcity') {
    $state_id = isset($_GET['stateId']) ? $_GET['stateId'] : 0;
    fetchCityList($res_arr, $state_id);

} else if($action == 'fetchuseraddr') {
    $userId = isset($_GET['userId']) ? $_GET['userId'] : 0;
    fetchAddressList($res_arr, $userId);
}

echo json_encode($res_arr);

/* close DB connection */
mysqli_close($dbConn);
exit;


function validateFormData($req_arr,  &$res_arr) 
{
    $errMsg = null;
    if(trim($req_arr['firstName']) == '') {
        $errMsg = 'First Name is Required';
    } else if(trim($req_arr['lastName']) == '') {
        $errMsg = 'Last Name is Required';
    } else if(trim($req_arr['email']) == '') {
        $errMsg = 'Email is Required';
    } else if( ! filter_var($req_arr['email'], FILTER_VALIDATE_EMAIL)) {
        $errMsg = 'Email is Invalid';
    } else if(trim($req_arr['street']) == '') {
        $errMsg = 'Street is Required';
    } else if(trim($req_arr['city']) == '' || ! is_numeric($req_arr['city'])) {
        $errMsg = 'City is Required';
    } else if(trim($req_arr['zip']) == '') {
        $errMsg = 'ZIP is Required';
    } else if( ! is_numeric($req_arr['zip'])) {
        $errMsg = 'ZIP code is Invalid';
    }
    
    if($errMsg != '') {
        $res_arr['type'] = 'error';
        $res_arr['message'] = $errMsg;
        $res_arr['data'] = null;

        return false;
    } else {
        $res_arr['type'] = 'success';
        $res_arr['message'] = 'Data Saved Successfully.';
        $res_arr['data'] = null;

        return true;
    }
}



function fetchAddressList(&$res_arr, $userId = 0)
{
    global $dbConn;
    $rows_arr = array();
    $sql = "SELECT user_address.*, cities.name as city_name
            FROM user_address
            JOIN cities ON user_address.city_id = cities.id";
    $sql .= $userId > 0 ?  " WHERE user_address.id = $userId" : "";

    if($result = mysqli_query($dbConn, $sql)) {
        while($row = mysqli_fetch_assoc($result))
        {
            $rows_arr[] = $row;
        }

        $res_arr['type'] = 'success';
        $res_arr['message'] = 'Data Saved Successfully.';
        $res_arr['data'] = $rows_arr;
        /* free result set */
        mysqli_free_result($result);
    } else {
        $res_arr['type'] = 'error';
        $res_arr['message'] = 'No Record Found.';
        $res_arr['data'] = null;
    }
}


function addNewAddress($req_arr, &$res_arr)
{
    global $dbConn;
    $rows_arr = array();
    $sql = "INSERT INTO user_address(first_name, last_name, email, street, city_id, zip) 
            VALUES('" . $req_arr['firstName'] . "', '" . $req_arr['lastName'] . "', '". $req_arr['email'] . "' , '" . 
            $req_arr['street'] . "', '". $req_arr['city'] . "', '". $req_arr['zip'] . "')";

    if($result = mysqli_query($dbConn, $sql)) {
        $res_arr['type'] = 'success';
        $res_arr['message'] = 'Data Saved Successfully.';
        $res_arr['data'] = array('addr_id' => mysqli_insert_id($dbConn));
    } else  {
        $res_arr['type'] = 'error';
        $res_arr['message'] = 'Data Can Not be Saved.';
        $res_arr['data'] = null;
    }
}


function updateNewAddress($userId, $req_arr, &$res_arr)
{
    global $dbConn;
    $rows_arr = array();
    $sql = "UPDATE user_address
            SET first_name='".$req_arr['firstName'] ."', last_name='".$req_arr['lastName']."',
            email='".$req_arr['email']."', street='".$req_arr['street']."', city_id=".$req_arr['city'].", 
            zip='".$req_arr['zip']."' WHERE id=".$userId;

    if($result = mysqli_query($dbConn, $sql)) {
        $res_arr['type'] = 'success';
        $res_arr['message'] = 'Data Updated Successfully.';
        $res_arr['data'] = null;
    } else  {
        $res_arr['type'] = 'error';
        $res_arr['message'] = 'Data Can Not be Updated.';
        $res_arr['data'] = null;
    }
}


function fetchCityList(&$res_arr, $state_id = 0)
{
    global $dbConn;
    $rows_arr = array();
    $sql = "SELECT id, name
            FROM cities ";
    $sql .= ($state_id > 0) ? " WHERE state_id = $state_id" : "";
    $sql .= " ORDER BY name";

    if($result = mysqli_query($dbConn, $sql)) {
        while($row = mysqli_fetch_assoc($result))
        {
            $rows_arr[$row['id']] = $row['name'];
        }

        $res_arr['type'] = 'success';
        $res_arr['message'] = 'Cities Found.';
        $res_arr['data'] = $rows_arr;
        /* free result set */
        mysqli_free_result($result);
    } else {
        $res_arr['type'] = 'error';
        $res_arr['message'] = 'Cities Not Found.';
        $res_arr['data'] = null;
    }
}