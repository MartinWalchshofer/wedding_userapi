<?php

include_once (__DIR__ . './userdb.php');
include_once (__DIR__ . './token.php');
include_once (__DIR__ . './classes.php');

require (__DIR__ . './vendor/autoload.php');
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

try
{
    //get authorization header
    $headers = getallheaders();

    //flutter converts headers to lowercase headers / accept both
    $ucHeaderSet = isset($headers['Authorization']);
    $lcHeaderSet = isset($headers['authorization']);
    $authHeader = null;
    if($ucHeaderSet)
        $authHeader = $headers["Authorization"];
    if($lcHeaderSet)
        $authHeader = $headers["authorization"];
    if($authHeader == null)
        throw new Exception("No authorization header available.");

    $jwt = null;

    $arr = explode(" ", $authHeader);
    $jwt = $arr[1];
    if($jwt)
    {
        try 
        {
            //check if token is in database
            $database = new UserDatabase();
            $token = $database->GetRefreshToken($jwt);

            //verify that tokens are equal
            if (strcmp($token->refreshtoken, $jwt) !== 0)
            throw new Exception('Acquired token not equal to database token.');

            //delete token from database
            $database->DeleteRefreshToken($token->refreshtoken);
        }
        catch (Exception $e)
        {
            //DO NOTHING
        }

        http_response_code(200);
        echo json_encode(
            array(
                "message" => "Logout succeeded.",
            )); 
    }
    else
    {
        http_response_code(401);
        echo json_encode(array("error" => "Logout failed. No token provided."));
    }
}
catch(Exception $e)
{
    http_response_code(401);
    echo json_encode(array("error" => "Logout failed. " . $e->getMessage()));
}

?>