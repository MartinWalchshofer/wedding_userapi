<?php

require "./vendor/autoload.php";
use \Firebase\JWT\JWT;

class Auth
{
    //error codes
    const ERROR_CODE_OK = 0;
    const ERROR_ACCESS_DENIED = 1;
    const ERROR_CODE_INVALID_REQUEST = 2;

    private static $lastMessage = "";

    //returns the last error message
    public static function GetLastErrorMessage() : string
    {
        return self::$lastMessage;
    }

    public static function CheckAccess($secret_key) : int
    {
        $errorCode = self::ERROR_ACCESS_DENIED;
        $lastMessage = "";
        try
        {
            //get authorization header
            $headers = getallheaders();

            if(!isset($headers['Authorization']))
                throw new Exception("No authorization header available.");

            $authHeader = $headers["Authorization"];
            $jwt = null;

            $arr = explode(" ", $authHeader);
            $jwt = $arr[1];
            if($jwt)
            {
                try 
                {
                    //decode jwt
                    $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

                    //TODO VALIDATE TOKEN

                    //TODO
                    //----------
                    //store user / get last user function
                    //----------

                    $errorCode = self::ERROR_CODE_OK;
                    self::$lastMessage = "Access granted.";      
                }
                catch (Exception $e)
                {
                    //TODO CHECK IF EXPIRED, NEW ERROR CODE

                    $errorCode = self::ERROR_ACCESS_DENIED;
                    self::$lastMessage = $e->getMessage();
                }
            }
            else
            {
                $errorCode = self::ERROR_CODE_INVALID_REQUEST;
                self::$lastMessage = "Invalid request";
            }
        }
        catch (Exception $e)
        {
            $errorCode = self::ERROR_CODE_INVALID_REQUEST;
            self::$lastMessage = $e->getMessage();
        }

        return $errorCode;
    }
}
?>