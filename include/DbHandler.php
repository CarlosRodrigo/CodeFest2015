<?php

define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_ALREADY_EXISTED', 2);
 
class DbHandler {
 
    private $conn;
 
    function __construct() {
        require_once 'Database.php';
        // opening db connection
        $dbUserName = 'mjohnst4_admin';
        $whichPass = 'a';
        $dbName = 'MJOHNST4_skibum';
        
		$this->conn = new Database($dbUserName,$whichPass,$dbName); 
    }

    public function createUser($name, $email, $password) {
        

    }

    public function checkLogin($email, $password) {
        // fetching user by email
        $sql = "SELECT user_password FROM users WHERE user_email = ?";
        $result = $this->conn->select($sql, array($email));
        $password_hash = $result[0]['user_password'];

        if ($result != null) {
            return $password_hash == $password;
        } else {
 
            return false;
        }
    }

    public function getUserByEmail($email) {
        $sql = "SELECT user_firstname, user_lastname, user_email FROM users WHERE user_email = ?";
        $result = $this->conn->select($sql, array($email));
        
        if ($result != null) {
            $user = $result[0];
            return $user;
        } else {
            return null;
        }
    }

}
 