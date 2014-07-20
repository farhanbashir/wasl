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
$app->get("/getEvent/:event_id",'getEvent');
$app->get("/getEventUserList/:eventid",'getEventUserList');
$app->get("/getEventFeedByEventId/:eventid",'getEventFeedByEventId');
$app->get("/getFollower/:user_id",'getFollower');
$app->get("/getMessages/:user_id",'getMessages');
$app->get("/getFollowing/:user_id",'getFollowing');
$app->get("/searchEventByName/:search",'searchEventByName');
$app->get("/searchEventByLocation/:latitude/:longitude",'searchEventByLocation');


$app->post('/signup','signup');
$app->post("/createEvent",'createEvent');
$app->post("/joinThisEvent",'joinThisEvent');
$app->post("/checkedInThisEvent",'checkedInThisEvent');
$app->post('/updatePassword','updatePassword');
$app->post('/shareStatus','shareStatus');
$app->post('/followUser','followUser');
$app->post('/sendMessage','sendMessage');
$app->post('/imgSave','imgSave');
$app->post('/editProfile','editProfile');

/*

$app->post('/forgotPassword','forgotPassword');







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
    
//    $sql = "SELECT (select count(*) from user_events where user_id=u.id and is_checkedIn=1) as checkins,u.* FROM users u where u.username=:username ";
    $sql = "SELECT 
            (select count(*) from user_events where user_id=u.id and is_checkedIn=1) as checkins,
            (select count(*) from followers where user_id=u.id) as follower,
            (select count(*) from followers where follower_id=u.id) as following,
            u.* FROM users u where u.username=:username";
    try{
        $stmt = $db->prepare($sql);  
        $stmt->bindParam("username", $username);
        $stmt->execute();
        //$stmt   = $db->query($sql);
        $info  = $stmt->fetch(PDO::FETCH_NAMED);
        
        if(is_array($info))
        {
            $response["header"]["error"] = 0;
            $response["header"]["message"] = "Success";
            $response["body"] = $info;    
        }    
        else
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = "User not exist";
        }    
        
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }    
    
    
        
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);

}

function login($username,$password, $type){
    global $app, $db, $response;	
    $data = array();
    
    //$sql = "SELECT * FROM users where username=:username ";
    $sql = "SELECT 
            (select count(*) from user_events where user_id=u.id and is_checkedIn=1) as checkins,
            (select count(*) from followers where user_id=u.id) as follower,
            (select count(*) from followers where follower_id=u.id) as following,
            u.* FROM users u where u.username=:username";
    
    
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
    $company_email= $req->params('company_email');
    $company_name= $req->params('company_name');
    $dob= $req->params('dob');
    $designation= $req->params('designation');
    $phone= $req->params('phone');
    $office_no= $req->params('office_no');
    $user_image = '';
    
    if(userAvailable($username))
    {
        $sql = "INSERT INTO users (first_name,last_name,username,password,company_email,date_of_birth,designation,phone,office_no,company_name,user_image) 
                values 
                (:first_name,:last_name,:username,:password,:company_email,:date_of_birth,:designation,:phone,:office_no,:company_name,:user_image)";
	
        if(isset($_FILES['file']))
        {
            $uploaddir = 'images/';
            $file = basename($_FILES['file']['name']);
            
            $uploadfile = $uploaddir . $file;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                $user_image = $uploadfile;
                $path = substr($_SERVER['REQUEST_URI'],0,stripos($_SERVER['REQUEST_URI'], "index.php"));
                $user_image = $_SERVER['SERVER_NAME'].$path.$user_image;    
            } else {
                $response["header"]["error"] = 1;
                $response["header"]["message"] = 'Some error';
            }
        }
        
        if(count($response) == 0)
        {
            try{
                $stmt = $db->prepare($sql);  
                $stmt->bindParam(":first_name", $first_name);
                $stmt->bindParam(":last_name", $last_name);
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":password", $password);
                $stmt->bindParam(":company_email", $company_email);
                $stmt->bindParam(":date_of_birth", $dob);
                $stmt->bindParam(":designation", $designation);
                $stmt->bindParam(":phone", $phone);
                $stmt->bindParam(":office_no", $office_no);
                $stmt->bindParam(":company_name", $company_name);
                $stmt->bindParam(":user_image", $user_image);
                $stmt->execute();

                $user["user_id"] = $db->lastInsertId();
                $response["body"] = $user;
                $response["header"]["error"] = 0;
                $response["header"]["message"] = "Success";

            }
            catch(PDOException $e)
            {
                $response["header"]["error"] = 1;
                $response["header"]["message"] = $e->getMessage();
            }
        }    
    }
    else
    {
        $response["header"]["error"] = 1;
        $response["header"]["message"] = 'User already exist';
    }    
    
        
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
	
}

function editProfile() {
	global $app, $db, $response;
	
	$req = $app->request(); // Getting parameter with names
    $first_name = $req->params('first_name'); // Getting parameter with names
    $last_name = $req->params('last_name'); // Getting parameter with names
    $company_email= $req->params('company_email');
    $username= $req->params('username');
    $company_name= $req->params('company_name');
    $dob= $req->params('dob');
    $designation= $req->params('designation');
    $phone= $req->params('phone');
    $office_no= $req->params('office_no');
    $user_id = $req->params('user_id');
    $user_image = '';
    
    if(!userAvailable($username))
    {
        $sql = "UPDATE users SET 
                first_name=:first_name,
                last_name=:last_name,
                company_email=:company_email,
                date_of_birth=:date_of_birth,
                designation=:designation,
                phone=:phone,
                office_no=:office_no,
                company_email=:company_email,
                user_image=:user_image,
                modified=:modified
                WHERE id=:user_id";
       
        if(isset($_FILES['file']))
        {
            $uploaddir = 'images/';
            $file = basename($_FILES['file']['name']);
            
            $uploadfile = $uploaddir . $file;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                $user_image = $uploadfile;
                $path = substr($_SERVER['REQUEST_URI'],0,stripos($_SERVER['REQUEST_URI'], "index.php"));
                $user_image = $_SERVER['SERVER_NAME'].$path.$user_image;    
            } else {
                $response["header"]["error"] = 1;
                $response["header"]["message"] = 'Some error';
            }
        }
        
        if(count($response) == 0)
        {
            try{
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $db->prepare($sql);  
                $datetime = date("Y-m-d h:i:s");
                $stmt->bindParam(":first_name", $first_name);
                $stmt->bindParam(":last_name", $last_name);
                $stmt->bindParam(":company_email", $company_email);
                $stmt->bindParam(":date_of_birth", $dob);
                $stmt->bindParam(":designation", $designation);
                $stmt->bindParam(":phone", $phone);
                $stmt->bindParam(":office_no", $office_no);
                $stmt->bindParam(":company_name", $company_name);
                $stmt->bindParam(":user_image", $user_image);
                $stmt->bindParam(":user_id",$user_id);
                $stmt->bindParam(":modified",$datetime);
                $stmt->execute();
                
                $response["header"]["error"] = 0;
                $response["header"]["message"] = "Success";

            }
            catch(PDOException $e)
            {
                $response["header"]["error"] = 1;
                $response["header"]["message"] = $e->getMessage();
            }
        }    
    }
    else
    {
        $response["header"]["error"] = 1;
        $response["header"]["message"] = 'User not exist';
    }    
    
        
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
	
}

function parms($string,$data) {
    $indexed=$data==array_values($data);
    foreach($data as $k=>$v) {
        if(is_string($v)) $v="'$v'";
        if($indexed) $string=preg_replace('/\?/',$v,$string,1);
        else $string=str_replace(":$k",$v,$string);
    }
    return $string;
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
	   
		if($stmt->rowCount() > 0)
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

function imgSave()
{
    global $db, $app, $response;
    
    if(!isset($_FILES['file']))
    {
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }
    else
    {
        $uploaddir = 'images/';
        $file = basename($_FILES['file']['name']);
        $uploadfile = $uploaddir . $file;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $response["header"]["error"] = 0;
            $response["header"]["message"] = $uploadfile;
        } else {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'Some error';
        }
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
               	 // $event_users = getUsersList($event['id']);
                  $users_list = getUserListArray($event['id']);  
                  
                /*
                  
                  $event_id = $event['id'];
                	  
                  $sql = "SELECT u.id,u.first_name,u.last_name,u.username,u.user_image,ue.is_checkedIn FROM users u INNER JOIN user_events ue ON u.id=ue.user_id WHERE ue.event_id=$event_id";
			      $stmt = $db->query($sql);
        		  $users_list = $stmt->fetchAll(PDO::FETCH_NAMED);
               
                */	

                if(count($users_list)>0)
                {
                    $user_events[$i]['users_list'] = $users_list;
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

function getEvent($event_id)
{
    global $app, $db, $response;
    
    $sql = "SELECT e.* FROM events e WHERE e.id=$event_id";
    
    try{
        $stmt   = $db->query($sql);
        $events  = $stmt->fetchAll(PDO::FETCH_NAMED);
        
        if(count($events) > 0)
        {    
            $i = 0;
            foreach($events as $event)
            {
                  $users_list = getUserListArray($event['id']);  
                  
    
                if(count($users_list)>0)
                {
                    $events[$i]['users_list'] = $users_list;
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



    $response["body"] = $events;

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}


function getUserListArray($event_id){
  try{
	
	 global $app,$db,$response;

	 $sql = "SELECT u.id,u.first_name,u.last_name,u.username,u.user_image,u.designation,ue.is_checkedIn FROM users u INNER JOIN user_events ue ON u.id=ue.user_id WHERE ue.event_id=$event_id";
	 $stmt = $db->query($sql);
     $users_list = $stmt->fetchAll(PDO::FETCH_NAMED);
     return $users_list;
    
    }catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
        
    }
        		  
}


function getUsersList($event_id)
{
    global $app,$db,$response;
    
    $sql = "SELECT u.id,u.first_name,u.last_name,u.username,ue.is_checkedIn,u.user_image FROM users u INNER JOIN user_events ue ON u.id=ue.user_id WHERE ue.event_id=$event_id";


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

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}    

function getFollower($user_id)
{
    global $app,$db,$response;
    
    $sql = "select u.* from followers f
            inner join users u on u.id=f.follower_id
            where user_id=$user_id";


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

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function getMessages($user_id)
{
    global $app,$db,$response;
    
    $sql = "select m.*,u.first_name,u.last_name,u.username,u.designation from messages m 
            inner join users u on u.id=m.from 
            where m.to=$user_id";


    try{
        $stmt   = $db->query($sql);
        $messages  = $stmt->fetchAll(PDO::FETCH_NAMED);
       
        $response["header"]["error"] = 0;
        $response["header"]["message"] = 'Success';
        $response["header"]["body"] = $messages;
        
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
        
    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function getFollowing($user_id)
{
    global $app,$db,$response;
    
    $sql = "select u.* from followers f
            inner join users u on u.id=f.user_id
            where follower_id=$user_id";


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

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}    



function getEventUserList($event_id)
{
    global $app,$db,$response;
    
    $users_list = getUserListArray($event_id);
	$app->response()->header("Content-Type", "application/json");
    echo json_encode($users_list);
    
    
}    

function getEventFeedByEventId( $event_id)
{
    global $app, $db, $response;
    
//    $sql = "SELECT * FROM event_statuses WHERE event_id=$event_id";// AND user_id=$user_id
    $sql = "SELECT e.name, es.*,u.user_image,u.username,u.first_name, u.last_name FROM event_statuses es
                inner join users u on u.id=es.user_id 
                inner join events e on es.event_id=e.id 
                 WHERE es.event_id=$event_id";


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
                $event_users = getUserListArray($event['id']);
                if(count($event_users) > 0)
                {
                    $events[$i]['users_list'] = $event_users;
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



    $response["body"] = $events;

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function searchEventByLocation($latitude,$longitude)
{
    global $app, $db, $response;
    
    
    $sql = "SELECT *,
            ( 6371 * 
            ACOS( 
             COS( RADIANS($latitude) )
             * COS( RADIANS( latitude ) ) 
             * COS( RADIANS( longitude ) - RADIANS($longitude) ) 
             + SIN( RADIANS($latitude) ) * SIN( RADIANS( latitude ) ) )
              ) AS distance
            FROM `events` 
            HAVING distance < 1 
            ORDER BY distance";

    
    try{
        $stmt   = $db->query($sql);
        $events  = $stmt->fetchAll(PDO::FETCH_NAMED);
        
        if(count($events) > 0)
        {    
            $i = 0;
            foreach($events as $event)
            {
                $event_users = getUserListArray($event['id']);
                
                if(count($event_users) > 0)
                {
                    $events[$i]['users_list'] = $event_users;
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



    $response["body"] = $events;

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
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
    $latitude = $req->params('latitude');
    $longitude = $req->params('longitude');
    $user_id = $req->params('user_id');
    $image = '';
    
    if(isset($_FILES['file']))
    {
        $uploaddir = 'images/';
        $file = basename($_FILES['file']['name']);
        $uploadfile = $uploaddir . $file;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $image = $uploadfile;
            $path = substr($_SERVER['REQUEST_URI'],0,stripos($_SERVER['REQUEST_URI'], "index.php"));
            $image = $_SERVER['SERVER_NAME'].$path.$user_image;    
        } else {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'Some error';
        }
    }    
    
    if(count($response) == 0)
    {
        try{
            $sql = "SELECT * FROM events WHERE name='$name'"; 
            $stmt   = $db->query($sql);
            $alreadyExist  = $stmt->fetchColumn();

            if($alreadyExist > 0)
            {
                $response["header"]["error"] = 1;
                $response["header"]["message"] = 'Already exist';
            }
            else
            {
                $sql = "INSERT INTO events (name,description,address,start_date,end_date,image,created_date,latitude,longitude,user_id) values ( :name, :description, :address, :start_date, :end_date, :image, :created_date, :latitude, :longitude, :user_id)";
                $stmt = $db->prepare($sql);
                
                $created_date = date("Y-m-d h:i:s");
                
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":description", $description);
                $stmt->bindParam(":address", $address);
                $stmt->bindParam(":start_date", $created_date);
                $stmt->bindParam(":end_date", $created_date);
                $stmt->bindParam(":created_date", $created_date);
                $stmt->bindParam(":image", $image);
                $stmt->bindParam(":latitude", $latitude);
                $stmt->bindParam(":longitude", $longitude);
                $stmt->bindParam(":user_id", $user_id,PDO::PARAM_INT);
                //$st->bindValue( ":art", $art, PDO::PARAM_INT );

                $stmt->execute();
                
                $event_id = $db->lastInsertId();
                //autojoin
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
    }    
    
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}    

function shareStatus()
{
    global $app ,$db, $response;
    $req = $app->request();
    $event_id = $req->params('event_id');
    $user_id = $req->params('user_id');
    $status = $req->params('status');
    
    
    try{
        $sql = "INSERT INTO event_statuses (user_id,event_id,status,datetime) values (:user_id,:event_id,:status,:datetime)";
            $stmt = $db->prepare($sql);
            $date = date("Y-m-d h:i:s");
            $stmt->bindParam("user_id", $user_id);
            $stmt->bindParam("event_id", $event_id);
            $stmt->bindParam("status", $status);
            $stmt->bindParam("datetime", $date);
            $stmt->execute();
            $response["header"]["error"] = 0;
            $response["header"]["message"] = "Success";    
        
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

function followUser()
{
    global $app ,$db, $response;
    $req = $app->request();
    $follower_id = $req->params('follower_id');
    $user_id = $req->params('user_id');
    $event_id = $req->params('event_id');


    $sql = "SELECT * FROM followers WHERE user_id=$user_id AND follower_id=$follower_id"; 

    try{
        $stmt   = $db->query($sql);
        $data  = $stmt->fetchColumn();
        
        if($data == false)
        {
            $sql = "INSERT INTO followers (user_id,follower_id,datetime,event_id) values (:user_id,:follower_id,:datetime,:event_id)";
            $stmt = $db->prepare($sql);
            $date = date("Y-m-d h:i:s");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":follower_id", $follower_id);
            $stmt->bindParam(":datetime", $date);
            $stmt->bindParam(":event_id", $event_id);
            $stmt->execute();
            $response["header"]["error"] = 0;
            $response["header"]["message"] = "Success";        
        }
        else
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'Already following';
        }    

    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}    

function sendMessage()
{
    global $app ,$db, $response;
    $req = $app->request();
    $message = $req->params('message');
    $from = $req->params('from');
    $to = $req->params('to');


    try{
        
        $sql = "INSERT INTO messages (message,`from`,`to`,`datetime`) values (:message,:from,:to,:datetime)";
        $stmt = $db->prepare($sql);
        $date = date("Y-m-d h:i:s");
        $stmt->bindParam(":message", $message);
        $stmt->bindParam(":from", $from);
        $stmt->bindParam(":to", $to);
        $stmt->bindParam(":datetime", $date);
        $stmt->execute();
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";        
            

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
    
    $sql = "SELECT * FROM users WHERE id=:id"; 

    try{
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();
 
        $data = (array)$stmt->fetchObject();                
 		
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
                //echo $new_password;
                $sql = "UPDATE users set password='$temp_password' WHERE id=:id";
                
                $stmt = $db->prepare($sql);
                
                $stmt->bindParam(":id", $user_id);
                
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
