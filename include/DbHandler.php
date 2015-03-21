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
        // $database = new Database($dbUserName,$whichPass,$dbName); 
        // $sql = 'SELECT * FROM tblLog WHERE pmkLogId = :pmkLogId';
        // $results = $database->select($sql,array(':pmkLogId' => $name));
		$this->conn = new Database($dbUserName,$whichPass,$dbName); 
    }

    public function createUser($name, $email, $password) {
        

    }

}
 