<?php
function getMySQLConnectionInstance()
{
    // Database settings is set here
    define('DB_HOST', "127.0.0.1");
    define('DB_USERNAME', "root");
    define('DB_PASSWORD', "");
    define('DB_DATABSE', "addressbook");

    $conInstance = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABSE);
    /* check connection */
    if ( ! $conInstance) {
        echo "Error: Unable to connect to MySQL. " . 
        "ErrorNo : " . mysqli_connect_errno() . ". " .
        "Error : " . mysqli_connect_error();
        die;
    }

    return$conInstance;
}