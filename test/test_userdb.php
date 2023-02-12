<?php

include_once (__DIR__ . './../v1/userdb.php');
include_once (__DIR__ . './../v1/auth.php');
include_once (__DIR__ . './../v1/token.php');
include_once (__DIR__ . './../v1/classes.php');
include_once (__DIR__ . './../v1/dotenv.php');

$username = 'MartinW';
$first_name = 'Martin';
$last_name = 'Walchshofer';
$email = 'mwalchshofer@gmx.net';
$pw = 'asd123';

//test add response
try
{
    //create user
    $user = new User();
    $user->iduser = "1";
    $user->user_name = "MM";
    $user->first_name = "Max";
    $user->last_name = "Musterman";
    $user->email = "max@mustermann.net";

    $database = new UserDatabase();
    $database->AddResponse($user, "Yes", 2, 1, "none");
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}


//test token
try
{
    //create user
    $user = new User();
    $user->iduser = "1";
    $user->user_name = "MM";
    $user->first_name = "Max";
    $user->last_name = "Musterman";
    $user->email = "max@mustermann.net";

    //load .env file
    $dotenv = new DotEnv(__DIR__ . './../v1/config/.env');
    $dotenv->load();

    //Get jwt parameters
    $issuer = getenv('ISSUER');
    $secretKey = getenv('SECRET');

    //combine app + devicename for audience
    $audience = "test" . '_' . "test";

    $jwtAccess = Token::CreateAccessToken($user, $issuer, $audience , $secretKey);
    $errorCode = Auth::CheckAccess($secretKey, $jwtAccess);
    $errorMessage = Auth::GetLastErrorMessage();  
    $decoded = json_decode(Auth::GetLastDecoded());
    //echo $decoded;

    echo $decoded->id;
    echo $decoded["id"];
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test add user
try
{
    $database = new UserDatabase();
    $database->AddUser($username , $first_name, $last_name, $email, $pw);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test get user by email
try
{
    $user = $database->GetUserByEmail($email);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test get user by email
try
{
    $user = $database->GetUserById($user->iduser);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test delete user
try
{
    $database->DeleteUserByEmail($user->email);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test add user
try
{
    $database = new UserDatabase();
    $database->AddUser($username , $first_name, $last_name, $email, $pw);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test get user by email
try
{
    $user = $database->GetUserByEmail($email);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test delete user
try
{
    $database->DeleteUserById($user->iduser);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test add user
try
{
    $database = new UserDatabase();
    $database->AddUser($username , $first_name, $last_name, $email, $pw);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test get user by email
try
{
    $user = $database->GetUserByEmail($email);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}

//test delete user
try
{
    $database->DeleteUser($user);
    echo 'success';
}
catch(Exception $e)
{
    echo $e->getMessage();
}
?>