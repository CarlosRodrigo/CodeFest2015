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

    public function createUser($firstname,$email,$password,$phone1,$phone2,$bio,$lastname,$pic,$gender) {
        // fetching user by email
        $sql= "INSERT INTO `users` (user_firstname,user_email, user_password, user_phone1,user_phone2,user_bio,user_lastname,user_profilepic,user_gender) VALUES (?,?,?,?,?,?,?,?,?)";
        $result = $this->conn->insert($sql, array($firstname,$email,$password,$phone1,$phone2,$bio,$lastname,$pic,$gender));

        if ($result != null) {
            return true;
        } else {
 
            return false;
        }

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
    
    public function getAllRides() {
        $rides_query = "SELECT 
                    rides_id, rides_meetingPlace, rides_seats AS seats, departure_time, user_firstname 
                FROM 
                    rides, users, mountains 
                WHERE 
                    user_id=driver_id AND mountain_id=mountains_id 
                ORDER BY 
                    departure_time";                    

        return $this->conn->select($rides_query, array());
    }

    public function createRide($user_id, $mountains_id, $seats, $departureTime, $meetingPlace) {
        $sql = "INSERT INTO rides(driver_id, mountains_id, rides_seats, rides_meetingPlace, departure_time) VALUES(?,?,?,?,?)";
        $result = $this->conn->insert($sql, array($user_id, $mountains_id, $seats, $departureTime, $meetingPlace));
 
        if ($result != null) {
            // task row created
            // now assign the task to user
            $ride_id = $this->conn->lastInsert();
            $res = $this->createUserTask($user_id, $ride_id);
            if ($res) {
                // task created successfully
                return $ride_id;
            } else {
                // task failed to create
                return null;
            }
        } else {
            // task failed to create
            return null;
        }
    }

}
 
