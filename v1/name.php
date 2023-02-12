<?php

include_once ('./auth.php');
include_once ('./userdb.php');
require "./vendor/autoload.php";
use \Firebase\JWT\JWT;

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

    //Get jwt parameters
    $issuer = getenv('ISSUER');
    $secretKey = getenv('SECRET');

    //get authorization header
    $headers = getallheaders();

    if(!isset($headers['Authorization']))
        throw new Exception("No authorization header available.");

    $authHeader = $headers["Authorization"];
    $jwt = null;

    $arr = explode(" ", $authHeader);
    $jwt = $arr[1];

    //check authentication
    $errorCode = Auth::CheckAccess($secretKey, $jwt);
    $errorMessage = Auth::GetLastErrorMessage();  
    if($errorCode == Auth::ERROR_CODE_OK)
    {
        //decode input and write it to data object
        $input = file_get_contents("php://input");
        $data = json_decode($input);
        $decoded = json_decode(Auth::GetLastDecoded());
        $user = $database->GetUserById($decoded->id);
        
        http_response_code(200);
        echo json_encode(array(
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            ));
    }
    else
    {  
        //check if error code appeared due to expired token
        if($errorCode == Auth::ERROR_ACCESS_DENIED)
        {
            http_response_code(401);
            echo json_encode(array(
                "error" => $errorMessage
            ));
        }
        else
        {
            http_response_code(400);
            echo json_encode(array(
                "error" => $errorMessage
            ));
        }  
    }
}
catch(Exception $e)
{
    //return error
    http_response_code(400);
    echo json_encode(array(
        "error" => $e->getMessage()
    ));
}
?>