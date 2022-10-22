<?php

include_once ('./userdb.php');
include_once ('./token.php');
include_once ('./classes.php');
include_once ('./dotenv.php');

require ('./vendor/autoload.php');
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
    //get parameters
    $data = json_decode(file_get_contents("php://input"));
    $app = $data->application;
    $device = $data->device;

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
            //load .env file
            $dotenv = new DotEnv('./config/.env');
            $dotenv->load();

            //Get jwt parameters
            $issuer = getenv('ISSUER');
            $secretKey = getenv('SECRET');

            //check if token is valid
            $decoded = JWT::decode($jwt, $secretKey, array('HS256'));

            //check if token is in database
            $database = new UserDatabase();
            $token = $database->GetRefreshToken($jwt);

            //verify that tokens are equal
            if (strcmp($token->refreshtoken, $jwt) !== 0)
                throw new Exception('Acquired token not equal to database token.');

            //combine app + devicename for audience
            $audience = $app . '_' . $device;
            if (strcmp($decoded->aud, $audience) !== 0)
                throw new Exception('Device or app do not match.');

            //get user
            $iduser = $decoded->data->id;
            $user = $database->GetUserById($iduser);

            //delete token from database
            $database->DeleteRefreshToken($token->refreshtoken);

            //create tokens
            $jwtAccess = Token::CreateAccessToken($user, $issuer, $audience, $secretKey);
            $jwtRefresh = Token::CreateRefreshToken($user, $issuer, $audience, $secretKey);

            //store new refresh token in database
            $database->StoreRefreshToken($user, $jwtRefresh);
            
            //successfully created an issued new token
            http_response_code(200);
            echo json_encode(
                array(
                    "message" => "Successful login.",
                    "token" => $jwtAccess,
                    "refresh_token" => $jwtRefresh,
                ));     
        }
        catch (Exception $e)
        {
            http_response_code(401);
            echo json_encode(array("error" => "Invalid token provided. " . $e->getMessage()));
        }
    }
    else
    {
        http_response_code(401);
        echo json_encode(array("error" => "Refresh failed. No token provided."));
    }
}
catch(Exception $e)
{
    http_response_code(401);
    echo json_encode(array("error" => "Refresh failed. " . $e->getMessage()));
}

?>