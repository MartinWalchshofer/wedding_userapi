<?php

include_once ('./classes.php');
require ('./vendor/autoload.php');
//include_once (__DIR__ . './classes.php');
//require (__DIR__ . './vendor/autoload.php');
use \Firebase\JWT\JWT;

class Token
{
    public static function CreateAccessToken(User $user, string $issuer, string $audience, string $secretKey) : string
    {
        //prepare token
        $secret_key = $secretKey;
        $issuer_claim = $issuer;
        $audience_claim = $audience;
        $issuedat_claim = time();
        $notbefore_claim = $issuedat_claim; //not valid before in seconds
        $expire_claim = $issuedat_claim + 60; // expire time in seconds
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $user->iduser,
                "username" => $user->user_name,
                "firstname" => $user->first_name,
                "lastname" => $user->last_name,
                "email" => $user->email
        ));

        //creawte jwt token
        $jwt = JWT::encode($token, $secret_key);
        return $jwt;
    }

    public static function CreateRefreshToken($user, string $issuer, string $audience, string $secretKey) : string
    {
        //prepare token
        $secret_key = $secretKey;
        $issuer_claim = $issuer;
        $audience_claim = $audience;
        $issuedat_claim = time();
        $notbefore_claim = $issuedat_claim; //not valid before in seconds
        $expire_claim = $issuedat_claim + (60 * 24 * 365); // expire time in seconds
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $user->iduser,
                "username" => $user->user_name,
                "firstname" => $user->first_name,
                "lastname" => $user->last_name,
                "email" => $user->email
        ));

        //creawte jwt token
        $jwt = JWT::encode($token, $secret_key);
        return $jwt;
    }
}

?>