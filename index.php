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
$app->get("/getMyEvents/:params+",'getMyEvents');
$app->get("/getEventUserList/:eventid",'getEventUserList');
$app->get("/getEventFeedByEventId/:userid/:eventid",'getEventFeedByEventId');
$app->get("/searchEventByName/:search",'searchEventByName');

$app->post('/signup','signup');
$app->post("/createEvent",'createEvent');
$app->post("/joinThisEvent",'joinThisEvent');
$app->post("/checkedInThisEvent",'checkedInThisEvent');
$app->post('/updatePassword','updatePassword');

/*
$app->post('/editProfile','editProfile');
$app->post('/forgotPassword','forgotPassword');


$app->get("/searchEventByLocation/:latitude/:longitude",'searchEventByLocation');




$app->post("/postStatusOnEvent",'postStatusOnEvent');
*/

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
    
    if(userAvailable($username))
    {
        $sql = "INSERT INTO users (first_name,last_name,username,password) 
                values 
                (:first_name,:last_name,:username,:password)";
	
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
    }
    else
    {
        $response["header"]["error"] = 1;
        $response["header"]["message"] = 'User already exist';
    }    
    
    
    
    $response["body"] = $user;
        
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
	
}


function userAvailable($username)
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
			return false;	
		}
		else 
        {
			return true;
		}
	}
	catch(PDOException $e)
	{
		//debug($e->getMessage(),1);
		return false;	
	}
}


function getMyEvents($params)
{
    global $app, $db, $response;
    
    $user_id = $params[0];
    
    $sql = "SELECT e.* FROM events e INNER JOIN user_events ue ON e.id=ue.event_id WHERE ue.user_id=$user_id";
    
    $event_id = "";
    
    if(count($params) > 1)
    {
        $event_id = $params[1];
    }    
    
    if($event_id != "")
    {
        $sql .= " AND e.id=$event_id";
    }    
    
    try{
        $stmt   = $db->query($sql);
        $user_events  = $stmt->fetchAll(PDO::FETCH_NAMED);
        
        if(count($user_events) > 0)
        {    
            $i = 0;
            foreach($user_events as $event)
            {
                $event_users = getUsersList($event['id']);
                if($event_users['header']['error'] == 0)
                {
                    $user_events[$i]['users_list'] = $event_users['header']['body'];
                }    
                $i++;
                
            }
        }    
            
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }



    $response["body"] = $user_events;

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function getUsersList($event_id)
{
    global $app,$db,$response;
    
    $sql = "SELECT u.id,u.first_name,u.last_name,u.username FROM users u INNER JOIN user_events ue ON u.id=ue.user_id WHERE ue.event_id=$event_id";


    try{
        $stmt   = $db->query($sql);
        $users_list  = $stmt->fetchAll(PDO::FETCH_NAMED);
        $response["header"]["error"] = 0;
        $response["header"]["message"] = 'Success';
        $response["header"]["body"] = $users_list;
        
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
        
    }

    return $response;
}    

function getEventUserList($event_id)
{
    global $app,$db,$response;
    
    $users_list = getUsersList($event_id);

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($users_list);
    
    
}    

function getEventFeedByEventId($user_id, $event_id)
{
    global $app, $db, $response;
    
    $sql = "SELECT * FROM event_statuses WHERE event_id=$event_id AND user_id=$user_id";


    try{
        $stmt   = $db->query($sql);
        $user_feeds  = $stmt->fetchAll(PDO::FETCH_NAMED);
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }



    $response["body"] = $user_feeds;

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
    
    
}    


function searchEventByName($search)
{
    global $app, $db, $response;
    
    $search  = $search."*";
    
    $sql = "SELECT * FROM events WHERE MATCH (name, description) AGAINST ('$search' IN BOOLEAN MODE)";

    
    try{
        $stmt   = $db->query($sql);
        $events  = $stmt->fetchAll(PDO::FETCH_NAMED);
        
        if(count($events) > 0)
        {    
            $i = 0;
            foreach($events as $event)
            {
                $event_users = getUsersList($event['id']);
                if($event_users['header']['error'] == 0)
                {
                    $events[$i]['users_list'] = $event_users['header']['body'];
                }    
                $i++;
                
            }
        }
        debug($events,1);
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }



    $response["body"] = $events;

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function searchEventByLocation($latitude, $longitude)
{
    global $app, $db, $response;
    
    
}    

function joinThisEvent()
{
    global $app ,$db, $response;
    $req = $app->request();
    $event_id = $req->params('event_id');
    $user_id = $req->params('user_id');
    
    
    $sql = "SELECT * FROM user_events WHERE user_id=$user_id AND event_id=$event_id"; 
    
    try{
        $stmt   = $db->query($sql);
        $alreadyJoined  = $stmt->fetchColumn();
        
        if($alreadyJoined > 0)
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'Already joined';
        }
        else
        {
            $sql = "INSERT INTO user_events (user_id,event_id,datetime) values (:user_id,:event_id,:datetime)";
            $stmt = $db->prepare($sql);
            $date = date("Y-m-d h:i:s");
            $stmt->bindParam("user_id", $user_id);
            $stmt->bindParam("event_id", $event_id);
            $stmt->bindParam("datetime", $date);
            $stmt->execute();
            $response["header"]["error"] = 0;
            $response["header"]["message"] = "Success";
        }    
        
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }
    
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}    

function createEvent()
{
    global $app ,$db, $response;
    $req = $app->request();
    $name = $req->params('name');
    $description = $req->params('description');
    $address = $req->params('address');
    $start_date = $req->params('start_date');
    $end_date = $req->params('end_date');
    
    
    $sql = "SELECT * FROM events WHERE name='$name'"; 
    
    try{
        $stmt   = $db->query($sql);
        $alreadyJoined  = $stmt->fetchColumn();
        
        if($alreadyJoined > 0)
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'Already exist';
        }
        else
        {
            $sql = "INSERT INTO events (name,description,address,start_date,end_date) values (:name,:description,:address,:start_date,:end_date)";
            $stmt = $db->prepare($sql);
            //$date = date("Y-m-d h:i:s");
            $stmt->bindParam("name", $name);
            $stmt->bindParam("description", $description);
            $stmt->bindParam("address", $address);
            $stmt->bindParam("start_date", $start_date);
            $stmt->bindParam("end_date", $end_date);
            $stmt->execute();
            $response["header"]["error"] = 0;
            $response["header"]["message"] = "Success";
        }    
        
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }
    
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}    


function checkedInThisEvent()
{
    global $app ,$db, $response;
    $req = $app->request();
    $event_id = $req->params('event_id');
    $user_id = $req->params('user_id');


    $sql = "SELECT * FROM user_events WHERE user_id=$user_id AND event_id=$event_id"; 

    try{
        $stmt   = $db->query($sql);
        $data  = $stmt->fetch(PDO::FETCH_NAMED);
                
        if(is_array($data) && count($data) > 0)
        {
            if($data['is_checkedIn'] == 1)
            {    
                $response["header"]["error"] = 1;
                $response["header"]["message"] = 'Already checked In';
            }
            else
            {
                $sql = "UPDATE user_events set is_checkedIn=1 WHERE user_id=:user_id AND event_id=:event_id";
                $stmt = $db->prepare($sql);
                
                $stmt->bindParam("user_id", $user_id);
                $stmt->bindParam("event_id", $event_id);
                
                $stmt->execute();
                $response["header"]["error"] = 0;
                $response["header"]["message"] = "Success";
            }    
        }
        else
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'You did not join this event';
        }    

    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}    

function updatePassword()
{
    global $app ,$db, $response;
    $req = $app->request();
    $user_id = $req->params('user_id');
    $old_password = $req->params('old_password');
    $new_password = $req->params('new_password');
    
    $sql = "SELECT * FROM user_events WHERE user_id=$user_id"; 

    try{
        $stmt   = $db->query($sql);
        $data  = $stmt->fetch(PDO::FETCH_NAMED);
                
        if(is_array($data) && count($data) > 0)
        {
            if($data['password'] != MD5($old_password))
            {    
                $response["header"]["error"] = 1;
                $response["header"]["message"] = 'Password do not match';
            }
            else
            {
                $temp_password = MD5($new_password);
                $sql = "UPDATE users set password='$new_password' WHERE user_id=:user_id";
                $stmt = $db->prepare($sql);
                
                $stmt->bindParam("user_id", $user_id);
                
                $stmt->execute();
                $response["header"]["error"] = 0;
                $response["header"]["message"] = "Success";
            }    
        }
        else
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'Some error';
        }    

    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
    
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
