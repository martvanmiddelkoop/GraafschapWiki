<?php

require_once("DataBase.php");

class UsersDatabase extends DataBase
{
    private static $instance;

    public function __construct()
    {
        parent::__construct("localhost", "gcwiki", "root", "");
    }

    public static function getInstance()
    {

        if (!isset(self::$instance))
        {
            self::$instance = new UsersDatabase();
        }

        return self::$instance;
    }

    private function generateHash(string $password)
    {
        $options = array
        (
            'salt' => random_bytes(64),
            'cost' => 12,
        );

        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    private function verify($hash, $password)
    {
        return password_verify($password, $hash);
    }

    public function tryLogin($email, $password)
    {
        $result = $this->runSQL("SELECT password FROM users WHERE email = '$email'");
        if ($result)
        {
            return $this->verify($row = $result->fetch_assoc()['password'], $password);
        }
        else
        {
            return "invalid email";
        }
    }

    public function register($username, $email, $password)
    {
        $result = $this->runSQL("SELECT email, username FROM users WHERE email = '$email' OR username = '$username'");
        if ($result != null)
        {
            $row = $result->fetch_assoc();
            if (!is_null($row['email']) || !is_null($row['username']))
                return "username or email already taken";
        }


        $hash = $this->generateHash($password);
        $result = $this->runSQL("INSERT INTO users(`username`, `email`, `password`) VALUES ('$username', '$email', '$hash')");

        if ($result)
            return true;
        else
            return "kut voor je";
    }
}