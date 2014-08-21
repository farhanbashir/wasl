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
$app->get("/getProfile/:username",'getProfile');
$app->get("/getMyEvents/:params+",'getMyEvents');
$app->get("/getEvent/:params+",'getEvent');
$app->get("/getEventUserList/:eventid/:user_id",'getEventUserList');
$app->get("/getEventFeedByEventId/:eventid",'getEventFeedByEventId');
$app->get("/getFollower/:user_id",'getFollower');
$app->get("/getMessages/:user_id",'getMessages');
$app->get("/getFollowing/:user_id",'getFollowing');
$app->get("/searchEventByName/:search",'searchEventByName');
$app->get("/searchEventByLocation/:latitude/:longitude",'searchEventByLocation');
$app->get("/test","test");
$app->get("/verify/:email/:code",'verify');
$app->get("/getMyNotifications/:user_id",'getMyNotifications');

$app->post('/signup','signup');
$app->post("/login",'login');
$app->post("/createEvent",'createEvent');
$app->post("/joinThisEvent",'joinThisEvent');
$app->post("/checkedInThisEvent",'checkedInThisEvent');
$app->post('/updatePassword','updatePassword');
$app->post('/shareStatus','shareStatus');
$app->post('/followUser','followUser');
$app->post('/sendMessage','sendMessage');
$app->post('/imgSave','imgSave');
$app->post('/editProfile','editProfile');
$app->post('/forgotPassword','forgotPassword');
$app->post('/shareCard','shareCard');

/*
$app->post("/postStatusOnEvent",'postStatusOnEvent');
*/

function test()
{
    
	$data = array("from"=>372,"to"=>373,"message"=>"aby o");
    //insertNotification($data);
}

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

function send_notification_android($registatoin_ids, $message) {
echo "here";die;
        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';

        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );
		
		//print_r($fields);die;
        $headers = array(
            'Authorization: key=' . self::GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);
        //echo $result;
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

function login(){
    global $app, $db, $response;
    
    $req = $app->request(); // Getting parameter with names
    $type = $req->params('type'); // Getting parameter with names
    $device_id = $req->params('device_id'); // Getting parameter with names
    $device_type = $req->params('device_type'); // Getting parameter with names
    //$data = array();
    
    if($type == 1)
    {
        $linkedin_id = $req->params('linkedin_id');
        $token = $req->params('token');
        $sql = "SELECT 
            (select count(*) from user_events where user_id=u.id and is_checkedIn=1) as checkins,
            (select count(*) from followers where user_id=u.id) as follower,
            (select count(*) from followers where follower_id=u.id) as following,
            u.* FROM users u where u.linkedin_id=:linkedin_id";
        
        try{
            $stmt = $db->prepare($sql);  
            $stmt->bindParam("linkedin_id", $linkedin_id);
            $stmt->execute();
            //$stmt   = $db->query($sql);
            $data  = $stmt->fetch(PDO::FETCH_NAMED);
            
            if(is_array($data) && count($data))
            {
                
                    $sql = "select count(*) from devices where user_id=:user_id and type=:device_type";

                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(":user_id", $data['id']);
                    $stmt->bindParam(":device_type", $device_type);
                    $stmt->execute();

                    $present = $stmt->fetchColumn();



                    if($present != false)
                    {
                        //update

                        $sql = "UPDATE devices set uid='$device_id' WHERE user_id=:user_id and type=:device_type";

                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(":user_id", $data['id']);
                        $stmt->bindParam(":device_type", $device_type);

                        $stmt->execute();    
                    }
                    else
                    {
                        //insert
                        $sql = "insert into devices (user_id,uid,`type`) values (:user_id,:device_id,:device_type)";

                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(":user_id", $data['id']);
                        $stmt->bindParam(":device_type", $device_type);
                        $stmt->bindParam(":device_id", $device_id);

                        $stmt->execute();    

                    }   
                    $response["header"]["error"] = 0;
                    $response["header"]["message"] = "Success";
                
            }
            else
            {
                $response["header"]["error"] = 1;
                $response["header"]["message"] = "User is not signed up";
            }


        }
        catch(PDOException $e){
            $response["header"]["error"] = 1;
            $response["header"]["message"] = $e->getMessage();
        }
        
    }
    else
    {
        $username = $req->params('username'); // Getting parameter with names
        $password = $req->params('password'); // Getting parameter with names
        
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

            if(is_array($data) && count($data))
            {
                if($data["password"] == MD5($password) && $data['verified'] == 1)
                {
                    $sql = "select count(*) from devices where user_id=:user_id and type=:device_type";

                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(":user_id", $data['id']);
                    $stmt->bindParam(":device_type", $device_type);
                    $stmt->execute();

                    $present = $stmt->fetchColumn();



                    if($present != false)
                    {
                        //update

                        $sql = "UPDATE devices set uid='$device_id' WHERE user_id=:user_id and type=:device_type";

                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(":user_id", $data['id']);
                        $stmt->bindParam(":device_type", $device_type);

                        $stmt->execute();    
                    }
                    else
                    {
                        //insert
                        $sql = "insert into devices (user_id,uid,`type`) values (:user_id,:device_id,:device_type)";

                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(":user_id", $data['id']);
                        $stmt->bindParam(":device_type", $device_type);
                        $stmt->bindParam(":device_id", $device_id);

                        $stmt->execute();    

                    }   
                    $response["header"]["error"] = 0;
                    $response["header"]["message"] = "Success";
                }
                elseif($data["password"] == MD5($password) && $data['verified'] == 0)
                {
                    $response["header"]["error"] = 1;
                    $response["header"]["message"] = "Email address not verified";
                }    
                else
                {
                    $data = array();
                    $response["header"]["error"] = 1;
                    $response["header"]["message"] = "Username or password incorrect";    
                }
                	    
            }
            else
            {
                $response["header"]["error"] = 1;
                $response["header"]["message"] = "User is not signed up";
            }


        }
        catch(PDOException $e){
            $response["header"]["error"] = 1;
            $response["header"]["message"] = $e->getMessage();
        }
        
    }    
    
    $response["body"] = $data;
        
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);

}

function insertNotification($data)
{
    global $db;
    
    $from = (isset($data["from"])) ? $data["from"] : 0;
    $to = (isset($data["to"])) ? $data["to"] : 0;
    $message = $data["message"];
    $datetime = date("Y-m-d h:i:s");
    $event_id = $data["event_id"];
    
    $sql = "INSERT INTO notifications (`from`,`to`,`message`,`datetime`,event_id) VALUES (:from,:to,:message,:datetime,:event_id)";
    
    $stmt = $db->prepare($sql);  
    $stmt->bindParam(":from", $from);
    $stmt->bindParam(":to", $to);
    $stmt->bindParam(":message", $message);
    $stmt->bindParam(":datetime", $datetime);
    $stmt->bindParam(":event_id", $event_id);
    $stmt->execute();
    
}    

function rand_string( $length ) {

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars),0,$length);

}

function signup() {
	global $app, $db, $response;
	$user = array();
	
	$req = $app->request(); // Getting parameter with names
	$type = $req->params('type'); // Getting parameter with names
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
    
	if($type == 0)
	{
	    if(userAvailable($username,$type))
	    {
	        $sql = "INSERT INTO users (first_name,last_name,username,password,company_email,date_of_birth,designation,phone,office_no,company_name,user_image) 
	                values 
	                (:first_name,:last_name,:username,:password,:company_email,:date_of_birth,:designation,:phone,:office_no,:company_name,:user_image)";
		
	        if(isset($_FILES['file']))
	        {
	            $uploaddir = 'images/';
	            $file = basename($_FILES['file']['name']);
	            $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
				
	            $uploadfile = $uploaddir . $file;
	            
	            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
	                $user_image = $uploadfile;
	                $path = substr($_SERVER['REQUEST_URI'],0,stripos($_SERVER['REQUEST_URI'], "index.php"));
	                $user_image = $protocol.$_SERVER['SERVER_NAME'].$path.$user_image;    
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
	                
	                $email_data = array('to'=>$username,'subject'=>'WASL - Please verify your email', 'message'=>'Your verification code is '.substr($password,0,6));
	                //sendEmail($email_data );
	                
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
	}	
	else 
	{
		//linkedin
		$linkedin_id = $req->params('linkedin_id');
		$token = $req->params('token');
		
		if(userAvailable($linkedin_id, $type))
		{
            $sql = "INSERT INTO users (first_name,last_name,username,password,company_email,date_of_birth,designation,phone,office_no,company_name,user_image,linkedin_id,token,type,verified) 
	                values 
	                (:first_name,:last_name,:username,:password,:company_email,:date_of_birth,:designation,:phone,:office_no,:company_name,:user_image,:linkedin_id,:token,:type,:verified)";
					
            if(isset($_FILES['file']))
            {
                $uploaddir = 'images/';
                $file = basename($_FILES['file']['name']);
                $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

                $uploadfile = $uploaddir . $file;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                    $user_image = $uploadfile;
                    $path = substr($_SERVER['REQUEST_URI'],0,stripos($_SERVER['REQUEST_URI'], "index.php"));
                    $user_image = $protocol.$_SERVER['SERVER_NAME'].$path.$user_image;    
                } else {
                    $response["header"]["error"] = 1;
                    $response["header"]["message"] = 'Some error';
                }
            }
            
			try{
					$verified = 1;
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
	                $stmt->bindParam(":linkedin_id", $linkedin_id);
					$stmt->bindParam(":token", $token);
	                $stmt->bindParam(":type", $type);
	                $stmt->bindParam(":verified", $verified);
                    
					
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
		else
		{
			$response["header"]["error"] = 1;
	        $response["header"]["message"] = 'User already exist';
		}
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
	$type = $req->params('type');
	$user_image = '';
    
    if($type == 1)
	{
		$linkedin_id = $req->params('linkedin_id');
		$token = $req->params('token');
		$useravailable = userAvailable($linkedin_id, $type);
	}
	else
	{
		$useravailable = userAvailable($username, $type);	
	}
	
    if(!$useravailable)
    {
        $sql = "UPDATE users SET 
                first_name=:first_name,
                last_name=:last_name,
                company_email=:company_email,
                company_name=:company_name,
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
            $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
			
            $uploadfile = $uploaddir . $file;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                $user_image = $uploadfile;
                $path = substr($_SERVER['REQUEST_URI'],0,stripos($_SERVER['REQUEST_URI'], "index.php"));
                $user_image = $protocol.$_SERVER['SERVER_NAME'].$path.$user_image;    
            } else {
                $response["header"]["error"] = 1;
                $response["header"]["message"] = 'Some error';
            }
        }
        
        if(count($response) == 0)
        {
            try{
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);
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
                $stmt->execute() ;
               
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

function userAvailable($username,$type)
{
	global $db;
	if($type == 0)
	{
		$sql = "SELECT * FROM users WHERE username=:username limit 1";	
	}
	else
	{
		$sql = "SELECT * FROM users WHERE linkedin_id=:username limit 1";
	}	
	
	
	try{
		$stmt = $db->prepare($sql);  
        $stmt->bindParam(":username", $username);
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


function getMyNotifications($user_id)
{
    global $app,$db,$response;
    
    $sql = "select u.first_name as from_name,n.* from notifications n 
            inner join users u on u.id = n.from 
            where `to` = $user_id ";
    
    $stmt   = $db->query($sql);
    $notifications  = $stmt->fetchAll(PDO::FETCH_NAMED);
    
    $response["header"]["error"] = 0;
    $response["header"]["message"] = "Success";
    
    $response["body"] = $notifications;

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
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
                $following_users = getFollowingInternal($user_id);

                if(count($following_users) > 0)
                {
                    $following_users_ids = array();
                    foreach($following_users as $following_user)
                    {
                        $following_users_ids[] = $following_user['id'];
                    }    

                    foreach($users_list as $key=>$val)
                    {
                        if(in_array($users_list[$key]['id'],$following_users_ids))
                        {
                            $users_list[$key]['is_followed'] = true;
                        }
                        else
                        {
                            $users_list[$key]['is_followed'] = false;
                        }    
                    }    
                }
                else
                {
                    foreach($users_list as $key=>$val)
                    {
                        $users_list[$key]['is_followed'] = false;
                    }
                }
                  
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

function getEvent($params)
{
    global $app, $db, $response;
    
    $event_id = $params[0];
    
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
                
                if(count($params) > 1)
                {
                    $user_id = $params[1];
                    $following_users = getFollowingInternal($user_id);
                    
                    if(count($following_users) > 0)
                    {
                        $following_users_ids = array();
                        foreach($following_users as $following_user)
                        {
                            $following_users_ids[] = $following_user['id'];
                        }    
                        
                        foreach($users_list as $key=>$val)
                        {
                            if(in_array($users_list[$key]['id'],$following_users_ids))
                            {
                                $users_list[$key]['is_followed'] = true;
                            }
                            else
                            {
                                $users_list[$key]['is_followed'] = false;
                            }    
                        }    
                    }
                    else
                    {
                        foreach($users_list as $key=>$val)
                        {
                            $users_list[$key]['is_followed'] = false;
                        }
                    }    
                    
                }    
                
    
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

function getFollowingInternal($user_id)
{
    global $app,$db,$response;

    $sql = "select u.* from followers f
            inner join users u on u.id=f.user_id
            where follower_id=$user_id";

    $user_list = array();
    try{
        $stmt   = $db->query($sql);
        $users_list  = $stmt->fetchAll(PDO::FETCH_NAMED);

        return $users_list;

    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
        return false;
    }

    
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



function getEventUserList($event_id,$user_id)
{
    global $app,$db,$response;
    
    $users_list = getUserListArray($event_id);
    
    $following_users = getFollowingInternal($user_id);

    if(count($following_users) > 0)
    {
        $following_users_ids = array();
        foreach($following_users as $following_user)
        {
            $following_users_ids[] = $following_user['id'];
        }    

        foreach($users_list as $key=>$val)
        {
            if(in_array($users_list[$key]['id'],$following_users_ids))
            {
                $users_list[$key]['is_followed'] = true;
            }
            else
            {
                $users_list[$key]['is_followed'] = false;
            }    
        }    
    }
    else
    {
        foreach($users_list as $key=>$val)
        {
            $users_list[$key]['is_followed'] = false;
        }
    }
    
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


function searchEventByName($search,$user_id)
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
                
                $following_users = getFollowingInternal($user_id);

                if(count($following_users) > 0)
                {
                    $following_users_ids = array();
                    foreach($following_users as $following_user)
                    {
                        $following_users_ids[] = $following_user['id'];
                    }    

                    foreach($users_list as $key=>$val)
                    {
                        if(in_array($users_list[$key]['id'],$following_users_ids))
                        {
                            $users_list[$key]['is_followed'] = true;
                        }
                        else
                        {
                            $users_list[$key]['is_followed'] = false;
                        }    
                    }    
                }
                else
                {
                    foreach($users_list as $key=>$val)
                    {
                        $users_list[$key]['is_followed'] = false;
                    }
                }
                
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

function searchEventByLocation($latitude,$longitude,$user_id)
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
            HAVING distance < 25 
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
                
                $following_users = getFollowingInternal($user_id);

                if(count($following_users) > 0)
                {
                    $following_users_ids = array();
                    foreach($following_users as $following_user)
                    {
                        $following_users_ids[] = $following_user['id'];
                    }    

                    foreach($users_list as $key=>$val)
                    {
                        if(in_array($users_list[$key]['id'],$following_users_ids))
                        {
                            $users_list[$key]['is_followed'] = true;
                        }
                        else
                        {
                            $users_list[$key]['is_followed'] = false;
                        }    
                    }    
                }
                else
                {
                    foreach($users_list as $key=>$val)
                    {
                        $users_list[$key]['is_followed'] = false;
                    }
                }
                
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
    
            $sql = "SELECT * FROM events WHERE id=$event_id";
            $stmt   = $db->query($sql);
            $event  = $stmt->fetch(PDO::FETCH_NAMED);
            
            $notification_data = array("from"=>$user_id,"to"=>$event['user_id'] ,"message"=>"joined the event","event_id"=>$event_id);
            insertNotification($notification_data);
            
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
		$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
		
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $image = $uploadfile;
            $path = substr($_SERVER['REQUEST_URI'],0,stripos($_SERVER['REQUEST_URI'], "index.php"));
            $image = $protocol.$_SERVER['SERVER_NAME'].$path.$image;
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

function shareCard()
{
    global $app ,$db, $response;
    $req = $app->request();
    $from = $req->params('from');
    $to = json_decode($req->params('to'),true);
    

    foreach($to as $t)
    {    
        try{
            $sql = "INSERT INTO business_card (`from`,`to`,datetime) values (:from,:to,:datetime)";
            $stmt = $db->prepare($sql);
            $date = date("Y-m-d h:i:s");
            $stmt->bindParam("from", $from);
            $stmt->bindParam("to", $t);
            $stmt->bindParam("datetime", $date);
            $stmt->execute();
            
            $notification_data = array("from"=>$from,"to"=>$t ,"message"=>"share business card with you","event_id"=>0);
            insertNotification($notification_data);
            
            $response["header"]["error"] = 0;
            $response["header"]["message"] = "Success";    

        }
        catch(PDOException $e){
            $response["header"]["error"] = 1;
            $response["header"]["message"] = $e->getMessage();
        }
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
            
            $notification_data = array("from"=>$follower_id,"to"=>$user_id ,"message"=>" is following you.","event_id"=>$event_id);
            insertNotification($notification_data);
            
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

function sendEmail($data)
{
    $to = $data['to'];
    $subject = $data['subject'];
    $message = $data['message'];
    
    mail($to, $subject, $message);
}

function verify($email,$code)
{
    global $app ,$db, $response;
    
    
    $sql = "SELECT * FROM users WHERE username=:email AND SUBSTRING(password from 1 for 6)=:password"; 

    try{

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $code);
        $stmt->execute();

        $data = $stmt->fetchColumn();                
        
        if($data != false)
        {

            $sql = "UPDATE users set verified=1 WHERE username=:username";

            $stmt = $db->prepare($sql);

            $stmt->bindParam(":username", $email);

            $stmt->execute();
            $response["header"]["error"] = 0;
            $response["header"]["message"] = "Success";
                
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

function forgotPassword()
{
    global $app ,$db, $response;
    $req = $app->request();
    $username = $req->params('username');
    
    $sql = "SELECT count(*) FROM users WHERE username=:username"; 

    try{
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
 
        $result = $stmt->fetchColumn();                
 		
 	    if($result > 0)
        {
        	
            
                $temp_password = rand_string(8);
                $md5 = md5($temp_password);
                //echo $new_password;
                $sql = "UPDATE users set password='$md5' WHERE username=:username";
                
                $stmt = $db->prepare($sql);
                
                $stmt->bindParam(":username", $username);
                
                $stmt->execute();
                
				//email work here
				$subject = 'WASL - Your password has been changed successfully';
                $message = 'Your temporary password is '.$temp_password;
                $email = array('to'=>$username,'subject'=>$subject, 'message'=>$message);
				sendEmail($email);
				
                $response["header"]["error"] = 0;
                $response["header"]["message"] = "Success";
                
        }
        else
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'Invalid Username';
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
