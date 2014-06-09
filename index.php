<?php

error_reporting(E_ALL); 
 
require "config.php"; 
require "functions.php"; 
//require "NotORM.php";

$db = new PDO("mysql:host=".$config["db"]["db_host"].";dbname=".$config["db"]["db_name"], $config["db"]["db_user"], $config["db"]["db_password"]);
//$db = new NotORM($pdo);

require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();


$app = new \Slim\Slim(array("MODE" => "development"));

$response = array();

$app->get('/users','getUsers');
$app->get("/login/:username/:password/:type",'login');
$app->get("/getProfile/:username",'getProfile');
$app->post('/signup','signup');

$app->post('/editProfile','editProfile');
$app->post('/updatePassword','updatePassword');
$app->post('/forgotPassword','forgotPassword');
$app->get("/getMyEvents/:user_id/:event_id",'getMyEvents');
$app->get("/searchEventByName/:search",'searchEventByName');
$app->get("/searchEventByLocation/:latitude/:longitude",'searchEventByLocation');
$app->get("/getEventFeedByEventId/:user_id/:event_id",'getEventFeedByEventId');
$app->get("/getEventUserList/:event_id",'getEventUserList');
$app->post("/joinThisEvent",'joinThisEvent');
$app->post("/checkedInThisEvent",'checkedInThisEvent');
$app->post("/postStatusOnEvent",'postStatusOnEvent');


function getUsers()
{
	global $app ,$db, $response;
	$users = array();
    
    $sql = "SELECT * FROM users";
    
    try{
        $stmt   = $db->query($sql);
        $users  = $stmt->fetchAll(PDO::FETCH_NAMED);
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }
    
    
    
    $response["body"] = $users;
    
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
	
	
}


function getProfile($username){
	global $app,$db;
    $info = array();
    
    $sql = "SELECT * FROM users where username=:username ";
    try{
        $stmt = $db->prepare($sql);  
        $stmt->bindParam("username", $username);
        $stmt->execute();
        //$stmt   = $db->query($sql);
        $info  = $stmt->fetch(PDO::FETCH_NAMED);
        
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";
        
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }    
    
    $response["body"] = $info;
        
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);

}

function login($username,$password, $type){
    global $app, $db, $response;	
    $data = array();
    

    $sql = "SELECT * FROM users where username=:username ";
    try{
        $stmt = $db->prepare($sql);  
        $stmt->bindParam("username", $username);
        $stmt->execute();
        //$stmt   = $db->query($sql);
        $data  = $stmt->fetch(PDO::FETCH_NAMED);
        
        if(count($data))
        {
        	if($type == 0)
			{
	            if($data["password"] == MD5($password))
	            {
	                $response["header"]["error"] = 0;
	                $response["header"]["message"] = "Success";
	            }
	            else
	            {
	                $data = array();
	                $response["header"]["error"] = 1;
	                $response["header"]["message"] = "Username or password incorrect";    
	            }
			}
			else {
				$response["header"]["error"] = 0;
	            $response["header"]["message"] = "Success";
			}	    
        }
        else
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = "No user found";
        }
        
        
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }    
    
    $response["body"] = $data;
        
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);

}

function signup() {
	global $app, $db, $response;
	$user = array("user_id"=>0);
	
	$req = $app->request(); // Getting parameter with names
    $first_name = $req->params('first_name'); // Getting parameter with names
    $last_name = $req->params('last_name'); // Getting parameter with names
    $username = $req->params('username'); // Getting parameter with names
    $password = md5($req->params('password')); // Getting parameter with names
    //debug(checkUser($username),1);
    
    $sql = "INSERT INTO users (first_name,last_name,username,password) values (:first_name,:last_name,:username,:password)";
	
	try{
		$stmt = $db->prepare($sql);  
        $stmt->bindParam("first_name", $first_name);
		$stmt->bindParam("last_name", $last_name);
		$stmt->bindParam("username", $username);
		$stmt->bindParam("password", $password);
        $stmt->execute();
		
		$user["user_id"] = $db->lastInsertId();
        
		$response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";
		
	}
	catch(PDOException $e)
	{
		$response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
	}
    
    $response["body"] = $user;
        
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
	
}


function checkUser($username)
{
	global $db;
	$sql = "SELECT * FROM users WHERE username=:username limit 1";
	
	try{
		$stmt = $db->prepare($sql);  
        $stmt->bindParam("username", $username);
		$result = $stmt->execute();
		$info  = $stmt->fetch(PDO::FETCH_NAMED);
		
		if(count($info) > 0)
		{
			debug(count($info));
			return false;	
		}
		else {debug("true");
			return true;
		}
	}
	catch(PDOException $e)
	{
		//debug($e->getMessage(),1);
		return false;	
	}
}


function getMyEvents($user_id, $event_id = "")
{
    global $app, $db, $response;
}

function searchEventByName($search)
{
    global $app, $db, $response;
}

function searchEventByLocation($latitude, $longitude)
{
    global $app, $db, $response;
}    


// POST route
$app->post(
    '/post',
    function () {
    	
		$req = $app->request(); // Getting parameter with names
    $paramName = $req->params('name'); // Getting parameter with names
    $paramEmail = $req->params('email'); // Getting parameter with names
		
        echo 'This is a POST route';
    }
);

// PUT route
$app->put(
    '/put',
    function () {
        echo 'This is a PUT route';
    }
);

// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});

// DELETE route
$app->delete(
    '/delete',
    function () {
        echo 'This is a DELETE route';
    }
);

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
