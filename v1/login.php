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
    $user = $data->user;
    $password = $data->password;
    $app = $data->application;
    $device = $data->device;
    
    //Connect to database
    $database = new UserDatabase();
    $conn = $database->GetConnection();

    //SQL query - get user
    $table_name = 'users';
    $query = "SELECT * FROM " . $table_name . " WHERE user_name = '" . $user . "'";
    $stmt = $conn->prepare( $query );
    $stmt->execute();
    $num = $stmt->rowCount();

    if($num > 0){
        //parse database response
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row['iduser'];
        $username = $row['user_name'];
        $firstname = $row['first_name'];
        $lastname = $row['last_name'];
        $email = $row['email'];
        $password2 = $row['password'];

        //check password
        if(password_verify($password, $password2))
        {
            try
            {
                //TODO: CHECK IF APP IS IN APPS DATABASE

                //create user
                $user = new User();
                $user->iduser = $id;
                $user->user_name = $username ;
                $user->first_name = $firstname;
                $user->last_name = $lastname;
                $user->email = $email;

                //load .env file
                $dotenv = new DotEnv('./config/.env');
                $dotenv->load();
        
                //Get jwt parameters
                $issuer = getenv('ISSUER');
                $secretKey = getenv('SECRET');

                //combine app + devicename for audience
                $audience = $app . '_' . $device;
                
                $jwtRefresh = null;
                $foundTokenForExistingAudience = false;
                try
                {
                    //check if user already logged in on this device
                    //return existing refresh token from database in this case
                    $tokens = $database->GetRefreshTokens($user);                   
                    for($i=0; $i < count($tokens); $i++)
                    {
                        $decoded = JWT::decode($tokens[$i]->refreshtoken, $secretKey, array('HS256'));
                        if(strcmp($decoded->aud, $audience == 0))
                        {
                            $jwtRefresh = $tokens[$i]->refreshtoken;
                            $foundTokenForExistingAudience = true;
                            break;
                        }
                    }
                }
                catch(Exception $e)
                {
                    $jwtRefresh = null;
                    $foundTokenForExistingAudience = false;
                }

                //create tokens
                $jwtAccess = Token::CreateAccessToken($user, $issuer, $audience , $secretKey);
                
                if(!$foundTokenForExistingAudience)
                {
                    //create and store refresh token in database
                    $jwtRefresh = Token::CreateRefreshToken($user, $issuer, $audience , $secretKey);
                    $database->StoreRefreshToken($user, $jwtRefresh);
                }
                    
                http_response_code(200);
                echo json_encode(
                    array(
                        "message" => "Successful login.",
                        "token" => $jwtAccess,
                        "refresh_token" => $jwtRefresh,
                    ));
            }
            catch(Exception $e)
            {
                http_response_code(400);
                echo json_encode(array(
                    "error" => "Unknown error.", 
                    "message" => $e->getMessage()));
            }
        }
        else{
            http_response_code(401);
            echo json_encode(array("error" => "Login failed. Invalid password"));
        }
    }
    else{

        http_response_code(401);
        echo json_encode(array("error" => "Login failed. User not found."));
    }
}
catch(Exception $e)
{
    http_response_code(400);
    echo json_encode(array("error" => "Login failed. " . $e->getMessage()));
}
?>