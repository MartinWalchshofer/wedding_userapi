<?php

include_once ('./userdb.php');

//CORSE compatibility
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: POST, OPTIONS");
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    header("Access-Control-Allow-Origin: *");
    exit(0);
}
else
{
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
}

$conn = null;
try {
    //initialize database class and get connection
    $database = new UserDatabase();
    
    //decode input and write it to data object
    $input = file_get_contents("php://input");
    $data = json_decode($input);

    //check if all required fields exist and are set
    if(!isset($data) || empty($data))
        throw new Exception('No input data available.');

    if( !property_exists ( $data, 'user_name') || 
        !property_exists ( $data, 'first_name')|| 
        !property_exists ( $data, 'last_name')|| 
        !property_exists ( $data, 'email')|| 
        !property_exists ( $data, 'password'))
        throw new Exception('Required property does not exist.');

    if( !isset ( $data->user_name))
        throw new Exception('No user name set.');

    if( !isset ( $data->first_name))
        throw new Exception('No first name set.');

    if( !isset ( $data->last_name))
        throw new Exception('No last name set.');

    if( !isset ( $data->email))
        throw new Exception('No email set.');

    if( !isset ( $data->password))
        throw new Exception('No password set.');

    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email not valid.');
    }

    //Add user to database
    $database->AddUser($data->user_name, $data->first_name, $data->last_name, $data->email, $data->password);

    //TODO: SEND VALIDATION MAIL

    //form response
    http_response_code(200);
    echo json_encode(array("message" => $data->user_name . " was successfully registered."));
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("error" => "Registration failed. " . $e->getMessage()));
}
?>