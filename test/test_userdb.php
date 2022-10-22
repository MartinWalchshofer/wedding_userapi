<?php

include_once (__DIR__ . './../v1/userdb.php');

$username = 'Tschini';
$first_name = 'Martin';
$last_name = 'Walchshofer';
$email = 'walchshofer@gtec.at';
$pw = 'asd123';

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