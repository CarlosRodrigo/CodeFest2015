<?php
require 'include/Database.php';
require 'include/DbHandler.php';
require 'libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = null;

$app->get('/hello/:name', function ($name) {

    $dbUserName = 'mjohnst4_admin';
    $whichPass = 'a';
    $dbName = 'MJOHNST4_skibum';
    // $database = new Database($dbUserName,$whichPass,$dbName); 
    // $sql = 'SELECT * FROM tblLog WHERE pmkLogId = :pmkLogId';
    // $results = $database->select($sql,array(':pmkLogId' => $name));
    $database = new Database($dbUserName,$whichPass,$dbName); 
    $email = "jackb@skibumm.com";
    $sql = "SELECT user_password FROM users WHERE user_email = ?";
    $result = $database->select($sql, array($email));
    print_r($result[0]);
    // $password_hash = $result[0]['user_password'];

    // if ($result != null) {
    //     print($password_hash);
    // } else {

    //     print("false");
    // }

	echo "Hello, $name";
    
});

/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('email', 'password', 'firstname', 'lastname', 'gender'));

    //reading post params
    $email = $app->request()->post('email');
    $password = $app->request()->post('password');
    $firstname = $app->request()->post('firstname');
    $lastname = $app->request()->post('lastname');
    $phone1 = $app->request()->post('phone1');
    $phone2 = $app->request()->post('phone2');
    $bio = $app->request()->post('bio');
    $pic = $app->request()->post('pic');
    $gender = $app->request()->post('gender');
    $response = array();
    $db = new DbHandler();

    // check for correct email and password
    if ($db->createUser($firstname,$email,$password,$phone1,$phone2,$bio,$lastname,$pic,$gender)) {

        $response["error"] = false;
        $response['message'] = "User was created.";

    } else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = "User was not created.";
    }

    echoRespnse("200", $response);
});

/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
    verifyRequiredParams(array('email', 'password'));

    // reading post params
    $email = $app->request()->post('email');
    $password = $app->request()->post('password');
    $response = array();
    $db = new DbHandler();
    // check for correct email and password
    if ($db->checkLogin($email, $password)) {
        // get the user by email
        $user = $db->getUserByEmail($email);

        if ($user != null) {
            $response["error"] = false;
            $response['first_name'] = $user['user_firstname'];
            $response['last_name'] = $user['user_lastname'];
            $response['email'] = $user['user_email'];
            $response['userId'] = $user['user_id'];
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
    } else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'Login failed. Incorrect credentials';
    }

    echoRespnse("200", $response);
});

$app->get('/profile/:id', function($id) {

    // reading post params
    $response = array();
    $db = new DbHandler();
    
    // get the user by email
    $user = $db->getUserById($id);

    if ($user != null) {
        $response["error"] = false;
        $response['first_name'] = $user['user_firstname'];
        $response['last_name'] = $user['user_lastname'];
        $response['email'] = $user['user_email'];
    }

    echoRespnse("200", $response);
});


// Get all available rides
$app->get('/rides/:mountain&:orderby', function ($mountain, $orderby) {

    $db = new DbHandler();
    $rides = $db->getRides($mountain, intval($orderby));  

    $rides_output = array();

    for($i = 0; $i < sizeof($rides); $i++) {

        $ride = $rides[$i];

        $rides_output['rides'][$i] = array(
            "id" => $ride['rides_id'],
            "name" => $ride['user_firstname'],
            "seats" => $ride['seats'],
            "departure_time" => $ride['departure_time'],
            "meeting_place" => $ride['rides_meetingPlace'],
            "requestsCompensation" => $ride['requestsCompensation']
        );
    }

    echoRespnse("200", $rides_output);
});


/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/rides', function() use ($app) {
    verifyRequiredParams(array('userId', 'mountainId', 'seats', 'departureTime', 'meetingPlace'));

    $user_id = $app->request->post('userId');
    $mountain_id = $app->request->post('mountainId');
    $seats = $app->request->post('seats');
    $departureTime = $app->request->post('departureTime');
    $meetingPlace = $app->request->post('meetingPlace');

    $response = array();

    $db = new DbHandler();

    // creating new ride
    $ride = $db->createRide($user_id, $mountain_id, $seats, $departureTime, $meetingPlace);

    if ($ride != null) {
        $response["error"] = false;
        $response["message"] = "Ride created successfully";
    } else {
        $response["error"] = true;
        $response["message"] = "Failed to create ride. Please try again";
    }
    echoRespnse(201, $response);
});


/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}
 
/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}
 
/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
}


$app->run();
