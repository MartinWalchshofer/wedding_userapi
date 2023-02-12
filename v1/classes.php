<?php

class User 
{
    public int $iduser;
    public string $user_name;
    public string $first_name;
    public string $last_name;
    public string $email;
}

class UserToken 
{
    public string $idtoken;
    public int $iduser;
    public string $refreshtoken;
}

class Response 
{
    public int $iduser;
    public string $name;
}

?>