<?php

include_once (__DIR__ . './dotenv.php');
include_once (__DIR__ . './classes.php');

class UserDatabase{

    //table containing user names
    private const USERS_TABLENAME = 'users';

    //table containing tokens
    private const TOKENS_TABLENAME = 'tokens';

    //database access data
    private string $db_host;
    private string $db_name;
    private string $db_user;
    private string $db_password;

    //The database connection handle
    private $connection = null;

    //flag indicating whether connection was established or not.
    private bool $connected = false;

    //flag indicating whether the .env file could be loaded or not
    private bool $loadedEnv;

    /**
     * Initializes a new instance of UserDatabase
     * Loads and parses .env file stored in config folder
     */
    public function __construct()
    {
        $this->loadedEnv = false;
        try
        {
            //load .env file
            $dotenv = new DotEnv(__DIR__ . './config/.env');
            $dotenv->load();

            $this->db_host = getenv('DBUSR_HOST');
            $this->db_name = getenv('DBUSR_NAME');
            $this->db_user = getenv('DBUSR_USER');
            $this->db_password = getenv('DBUSR_PASSWORD');

            $this->loadedEnv = true;
        }
        catch(Exception $e)
        {
            $this->loadedEnv = false;
        }
    }

    /**
     * Connects to the database. 
     */
    private function Connect() : void
    {
        $this->connected = false;

        try
        {
            if(!$this->loadedEnv)
                throw new Exception('Failed to load .env file.');

            $this->connection = null;
            $this->connection = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name, $this->db_user, $this->db_password);
            $this->connected = true;
        }
        catch(Exception $e)
        {
            $this->connected = false;
        }

        $this->connected;
    }

    /**
     * Returns the database connection handle
     */
    public function GetConnection() : object
    {
        //try to connect
        if($this->connected != true)
            $this->Connect();

        // check if connection attempt was succesful
        if($this->connected != true)
        {
            $this->connected = false;
            $this->connection = null;
            throw new Exception('Could not connect to database');
        }
        
        //return database handle
        return $this->connection;
    }

    /**
     * Checks if user with defined email adress exists. True if user exists. False if user doesn't exist.
     */
    public function UserEmailExists(string $email) : bool
    {
        $res = false;
        try
        {
            //get connection handle
            $conn = $this->GetConnection();

            $table_name = self::USERS_TABLENAME;
            $query = "SELECT iduser FROM " . $table_name . " WHERE email = '" . $email . "'";
            $stmt = $conn->prepare( $query );
            $stmt->execute();
            $num = $stmt->rowCount();
    
            if($num > 0)
                $res = true;
        }
        catch(Exception $ex)
        {
            $res = false;
        }
        return $res;
    }

    /**
     * Checks if user with defined id exists. True if user exists. False if user doesn't exist.
     */
    public function UserIDExists(int $id) : bool
    {
        $res = false;
        try
        {
            //get connection handle
            $conn = $this->GetConnection();

            $table_name = self::USERS_TABLENAME;
            $query = "SELECT iduser FROM " . $table_name . " WHERE iduser = '" . $id . "'";
            $stmt = $conn->prepare( $query );
            $stmt->execute();
            $num = $stmt->rowCount();
    
            if($num > 0)
                $res = true;         
        }
        catch(Exception $ex)
        {
            $res = false;
        }
        return $res;
    }

    /**
     * Gets user by the id. Returns User object or throws exception if the user can't be acquired.
     */
    public function GetUserById(int $id) : User
    {
        $user = null;
        try
        {
            //get connection handle
            $conn = $this->GetConnection();

            $table_name = self::USERS_TABLENAME;
            $query = "SELECT iduser, user_name, first_name, last_name, email FROM " . $table_name . " WHERE iduser = '" . $id . "'";
            $stmt = $conn->prepare( $query );
            $stmt->execute();
            $num = $stmt->rowCount();
    
            if($num == 0)
                throw new Exception('User id ' . $id . ' not found.');

            if($num > 1)
                throw new Exception('User id ' . $id . ' duplicate found.');

            //fetch to user
            $users = $stmt->fetchAll(PDO::FETCH_CLASS, "User");
            $user = $users[0];
        }
        catch(Exception $ex)
        {
            throw $ex;
        }

        if($user == null)
            throw new Exception('Could not find user with id ' . $id);

        return $user;
    }

    /**
     * Gets user by the email. Returns User object or throws exception if the user can't be acquired.
     */
    public function GetUserByEmail(string $email) : User
    {
        $user = null;
        try
        {
            //get connection handle
            $conn = $this->GetConnection();

            $table_name = self::USERS_TABLENAME;
            $query = "SELECT iduser, user_name, first_name, last_name, email FROM " . $table_name . " WHERE email = '" . $email . "'";
            $stmt = $conn->prepare( $query );
            $stmt->execute();
            $num = $stmt->rowCount();
    
            if($num == 0)
                throw new Exception('User email ' . $email . ' not found.');

            if($num > 1)
                throw new Exception('User email ' . $email . ' duplicate found.');

            //fetch to user
            $users = $stmt->fetchAll(PDO::FETCH_CLASS, "User");
            $user = $users[0];
        }
        catch(Exception $ex)
        {
            throw $ex;
        }

        if($user == null)
            throw new Exception('Could not find user with ' . $email);

        return $user;
    }

    /**
     * Deletes user by email. Throws exception if the user can't be deleted.
     */
    public function DeleteUserByEmail(string $email) : void
    {
        //get connection handle
        $conn = $this->GetConnection();

        $table_name = self::USERS_TABLENAME;
        $query = "DELETE FROM " . $table_name . " WHERE email = '" . $email . "'";
        $stmt = $conn->prepare( $query );
        $stmt->execute();

        if(!$stmt->execute())
            throw new Exception('Could not delete user with email ' . $email);
    }

    /**
    * Deletes user by id. Throws exception if the user can't be deleted.
    */
    public function DeleteUserById(string $id) : void
    {
        //get connection handle
        $conn = $this->GetConnection();

        $table_name = self::USERS_TABLENAME;
        $query = "DELETE FROM " . $table_name . " WHERE iduser = '" . $id . "'";
        $stmt = $conn->prepare( $query );
        $stmt->execute();

        if(!$stmt->execute())
            throw new Exception('Could not delete user with id ' . $id);
    }

    /**
    * Deletes user by User object. Throws exception if the user can't be deleted.
    */
    public function DeleteUser(User $user) : void
    {
        $this->DeleteUserById($user->iduser);
    }

    /**
    * Adds a user to the database.
    */
    public function AddUser(string $user_name, string $first_name, string $last_name, string $email, string $password) : void
    {
        try
        {
            if($this->UserEmailExists($email))
                throw new Exception($email . ' is already in use.');

            //get connection handle
            $conn = $this->GetConnection();

            //form sql command for inserting data
            $table_name = self::USERS_TABLENAME;

            $query = "INSERT INTO " . $table_name . "
                            SET user_name = :username,
                                first_name = :firstname,
                                last_name = :lastname,
                                email = :email,
                                password = :password,
                                validated = 0";
            
            $stmt = $conn->prepare($query);

            //add data to sql command
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bindParam(':username', $user_name);
            $stmt->bindParam(':firstname', $first_name);
            $stmt->bindParam(':lastname', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $passwordHash);

            //execute sql statement
            if(!$stmt->execute())
                throw new Exception('Could not register ' . $userName);
        }
        catch(Exception $ex)
        {
            throw $ex;
        }
    }

    public function GetRefreshToken(string $token) : UserToken
    {
        $tokenAcquired = null;
        try
        {
            //get connection handle
            $conn = $this->GetConnection();

            $table_name = self::TOKENS_TABLENAME;
            $query = "SELECT idtoken, iduser, refreshtoken FROM " . $table_name . " WHERE refreshtoken = '" . $token . "'";
            $stmt = $conn->prepare( $query );
            $stmt->execute();
            $num = $stmt->rowCount();
    
            if($num == 0)
                throw new Exception('No token found.');

            if($num > 1)
                throw new Exception('Multiple tokens found.');

            //fetch to user
            $tokenAcquired = $stmt->fetchAll(PDO::FETCH_CLASS, "UserToken");
            $tokenAcquired = $tokenAcquired[0];
        }
        catch(Exception $ex)
        {
            throw $ex;
        }

        if($tokenAcquired == null)
            throw new Exception('Could not find token.');

        return $tokenAcquired;
    }
    
    public function GetRefreshTokens(User $user)
    {
        $tokensAcquired = null;
        try
        {
            //get connection handle
            $conn = $this->GetConnection();

            $table_name = self::TOKENS_TABLENAME;
            $query = "SELECT idtoken, iduser, refreshtoken FROM " . $table_name . " WHERE iduser = '" . $user->iduser . "'";
            $stmt = $conn->prepare( $query );
            $stmt->execute();
            $num = $stmt->rowCount();
    
            if($num == 0)
                throw new Exception('No tokens found for this user.');

            //fetch to user
            $tokensAcquired = $stmt->fetchAll(PDO::FETCH_CLASS, "UserToken");
        }
        catch(Exception $ex)
        {
            throw $ex;
        }

        if($tokensAcquired == null)
            throw new Exception('Could not find tokens for this user.');

        return $tokensAcquired;
    }

    public function DeleteRefreshToken(string $token) : void
    {
        //get connection handle
        $conn = $this->GetConnection();

        $table_name = self::TOKENS_TABLENAME;
        $query = "DELETE FROM " . $table_name . " WHERE refreshtoken = '" . $token . "'";
        $stmt = $conn->prepare( $query );
        $stmt->execute();

        if(!$stmt->execute())
            throw new Exception('Could not delete token');
    }

    public function DeleteRefreshTokens(User $user) : void
    {
        //get connection handle
        $conn = $this->GetConnection();

        $table_name = self::TOKENS_TABLENAME;
        $query = "DELETE FROM " . $table_name . " WHERE iduser = '" . $user->id . "'";
        $stmt = $conn->prepare( $query );
        $stmt->execute();

        if(!$stmt->execute())
            throw new Exception('Could not delete tokens from user with id ' . $user->id);
    }

    public function StoreRefreshToken(User $user, string $token) : void
    {
        try
        {
            //check if valid user id is provided
            if(!$this->UserIDExists($user->iduser))
                throw new Exception('Unknown user.');

            //get connection handle
            $conn = $this->GetConnection();

            //form sql command for inserting data
            $table_name = self::TOKENS_TABLENAME;

            $query = "INSERT INTO " . $table_name . "(
                iduser, 
                refreshtoken) 
                VALUES (
                    :token,
                    :iduser)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':token', $user->iduser);
            $stmt->bindParam(':iduser', $token);

            //execute sql statement
            if(!$stmt->execute())
                throw new Exception('Could not store refresh token');
        }
        catch(Exception $ex)
        {
            throw $ex;
        }
    }
}
?>