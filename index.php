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

/*** Start User interest & event interests ****/

$app->get('/getinterest','getInterest');

$app->get('/getEventInterests/:event_id','getEventInterests');

$app->get('/getUserInterests/:user_id','getUserInterests');

// add user interest
$app->post('/addUpdateUserInterests','addUserInterests');

// delete user interest
//$app->get('/deleteUserInterests','deleteUserInterests');

// add event interest
$app->post('/addUpdateEventInterests','addUpdateEventInterests');

/****  End interest section ****/

$app->get("/getProfile/:params+",'getProfile');
$app->get("/getMyEvents/:params+",'getMyEvents');
$app->get("/getEvent/:params+",'getEvent');
$app->get("/getEventUserList/:eventid/:user_id",'getEventUserList');
$app->get("/getEventFeedByEventId/:eventid",'getEventFeedByEventId');
$app->get("/getFollower/:user_id",'getFollower');
$app->get("/getMessages/:user_id",'getMessages');
$app->get("/getFollowing/:user_id",'getFollowing');
$app->get("/searchEventByName/:search/:user_id/:page",'searchEventByName');
$app->get("/searchEventByLocation/:latitude/:longitude/:user_id/:page",'searchEventByLocation');
$app->get("/getNearByUser/:latitude/:longitude",'getNearByUser');
$app->get("/test1","test1");
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
$app->post('/userLocation','userLocation');
$app->post('/updateEvent','updateEvent');
$app->post('/deleteEvent','deleteEvent');
$app->post('/reportEvent','reportEvent');
/*
$app->post("/postStatusOnEvent",'postStatusOnEvent');
*/

function test1()
{

    // $devices = get_user_device_id(372);

    // if($devices != false)
    // {
    //     foreach($devices as $device)
    //     {
    //         if($device['type'] == 0)
    //         {

    //             //iphone notification here
				// send_notification_iphone($device['uid'],'testing');
    //         }
    //         else
    //         {
    //             //android notification here
    //             send_notification_android(array($device['uid']), $message);
    //         }
    //     }
    // }
    $data = array('to'=>'farhan.bashir2002@gmail.com','subject'=>'wasl is good','message'=>'hello farhan');
    sendEmail($data);
}

function get_user_device_id($user_id)
{
    global $db,$app,$response;

    $sql = "SELECT * FROM devices WHERE user_id=$user_id";

    try{
        $stmt   = $db->query($sql);
        $users  = $stmt->fetchAll(PDO::FETCH_NAMED);
        return $users;
    }
    catch(PDOException $e){
        return false;
    }
}



function getInterest()
{
	global $app ,$db, $response;
	$users = array();

    $sql = "SELECT * FROM interests where status=1";

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

function getUserInterests($user_id,$return=false)
{
	global $app ,$db, $response;
	$users = array();

     $sql = "SELECT i.id,i.name,i.status 
			FROM  `user_interests` eui,  `interests` i
			WHERE i.id = eui.interest_id
			AND i.status = 1
			AND eui.user_id =".$user_id;

    try{
        $stmt   = $db->query($sql);
        $userInterest  = $stmt->fetchAll(PDO::FETCH_NAMED);
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";
    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }

	if($return)
		return $userInterest;	
    $response["body"] = $userInterest;

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);


}



function getEventInterests($event_id,$return=0)
{
	global $app ,$db, $response;
	$users = array();

    $sql = "SELECT i.id,i.name FROM `event_interests` eui , `interests` i where i.id = eui.interest_id and eui.event_id = ".$event_id." and i.status=1 ";
    
    try{
        $stmt   = $db->query($sql);
        $eventInterests  = $stmt->fetchAll(PDO::FETCH_NAMED);
        if($return == 1){
        
        	return $eventInterests;
        }
        
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


function addUserInterests()
{
    global $app ,$db, $response;
    $req = $app->request();
    
    $user_id = $req->params('user_id');
    $interests = json_decode($req->params('interest_ids'));
		
    $interest_ids = "";
    try{
           
           	foreach($interests as $interest_id){
    
 			  $interest_ids = $interest_ids.$interest_id.",";

     		  $sql = "SELECT * FROM user_interests WHERE user_id=$user_id AND interest_id=$interest_id";
	   	      $stmt   = $db->query($sql);
        	  $alreadyInterested  = $stmt->fetchColumn();

			  if($alreadyInterested == 0){

				$sql = "INSERT INTO user_interests (user_id,interest_id,datetime) values (:user_id,:interest_id,:datetime)";
				$stmt = $db->prepare($sql);
				$date = date("Y-m-d h:i:s");
				$stmt->bindParam("user_id", $user_id);
				$stmt->bindParam("interest_id", $interest_id);
				$stmt->bindParam("datetime", $date);
				$stmt->execute();

				}
            
            }

			$sql = "DELETE FROM user_interests WHERE user_id=$user_id AND INTEREST_ID NOT IN (".rtrim($interest_ids,",").")";
			$stmt   = $db->query($sql);
			$stmt->execute();
  			/*          
            $sql = "SELECT * FROM events WHERE id=$event_id";
            $stmt   = $db->query($sql);
            $event  = $stmt->fetch(PDO::FETCH_NAMED);

            $sql = "SELECT * FROM users WHERE id=$user_id";
            $stmt   = $db->query($sql);
            $user  = $stmt->fetch(PDO::FETCH_NAMED);
			*/	
 				
            $response["header"]["error"] = 0;
            $response["header"]["message"] = "You have successfully updated your interests.";
        

    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function addUpdateEventInterests($user_id,$event_id,$interest_ids,$app,$db,$response)
{
    
    //global $app ,$db, $response;
    $req = $app->request();
    
    $date = date("Y-m-d h:i:s");
    $interests = json_decode($interest_ids);
			
    $interest_ids = "";
    
    try{
           
           	foreach($interests as $interest_id){

			  $interest_ids = $interest_ids.$interest_id.",";
    
     		  $sql = "SELECT * FROM event_interests WHERE user_id=$user_id AND event_id = $event_id and interest_id=$interest_id";
	   	      $stmt   = $db->query($sql);
        	  $alreadyInterested  = $stmt->fetchColumn();

			  if($alreadyInterested == 0){

				$sql = "INSERT INTO event_interests (user_id,interest_id,event_id,datetime) values (:user_id,:interest_id,:event_id,:datetime)";
				$stmt = $db->prepare($sql);
				$stmt->bindParam("user_id", $user_id);
				$stmt->bindParam("event_id", $event_id);
				$stmt->bindParam("interest_id", $interest_id);
				$stmt->bindParam("datetime", $date);

				$stmt->execute();

				}
            
            }

			$sql = "DELETE FROM event_interests WHERE user_id=$user_id AND event_id = $event_id AND INTEREST_ID NOT IN (".rtrim($interest_ids,",").")";
			$stmt   = $db->query($sql);
			$stmt->execute();

 			return;	
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



function getUsers()
{
	global $app ,$db, $response;
	$users = array();

    $sql = "SELECT * FROM users where is_active=1";

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


    function send_notification_iphone($deviceToken, $message, $sound='default')
    {

        //$deviceToken = '7229e0f7cc34bd639a31e81802def2c02945b0a89d01ce52c7528f8671ef8f32';

        // Put your private key's passphrase here:
        //$passphrase = 'developmentc2gapns';

        global $config;
        $passphrase = $config['PASS_PHRASE'];

        $remote_url = $config['REMOTE_SOCKET_APPLE'];

        // Put your alert message here:
        //$message = 'Helo this is first message.';

        ////////////////////////////////////////////////////////////////////////////////

        $ctx = stream_context_create();

        //stream_context_set_option($ctx, 'ssl', 'local_cert', 'apns-dev.pem');
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        //stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client(
                                   $remote_url, $err,
                                   $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

        //echo 'Connected to APNS' . PHP_EOL;

        // Create the payload body
        $body['aps'] = array(
                             'alert' => $message,
                             'sound' => 'default',
                             'ji' =>88
                             );




        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

      /*  if (!$result)
            echo 'Message not delivered' . PHP_EOL;
        else
            echo 'Message successfully delivered' . PHP_EOL;
        */
        // Close the connection to the server
        fclose($fp);


    }



function old_send_notification_iphone($deviceToken, $message, $sound='default')
{
	global $config;
	$socketClient = "";

	/*
	init work
	*/
	$certificateFilename ="ck.pem";
	//$certificateFilename =env('DOCUMENT_ROOT').''. Router::url('/') . "app/Lib/PushNotification/apns-dist.pem";

	//echo $certificateFilename;
	//exit;

	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'local_cert', $certificateFilename);
	stream_context_set_option($ctx, 'ssl', 'passphrase', $config['PASS_PHRASE']);

	// Open a connection to the APNS server
	$fp = stream_socket_client($config['REMOTE_SOCKET_APPLE'], $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

	if (!$fp)
	{
		$fp = (array("code"=>1, "message"=>"Failed to connect: $err $errstr" . PHP_EOL));
	}

	/*
	init work
	*/


	if (is_array($fp)) {
		//CakeLog::write('debug', 'Couldn\'t connect to socket client' . PHP_EOL . print_r($socketClient));
		return ;
	}

	// Create the payload body
	$body['aps'] = array(
		'alert' => $message,
		'sound' => $sound
	);

	// Encode the payload as JSON
	$payload = json_encode($body);

	// Build the binary notification
	$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

	// Send it to the server
	$result = fwrite($socketClient, $msg, 8192);
	fclose($socketClient);
	//self::abort($socketClient);

	if (!$result) {
		//CakeLog::write('debug', 'Error Code 2: Message not delivered' . PHP_EOL);
		return 2;
	}
	else {
		//CakeLog::write('debug', 'Message successfully delivered' . PHP_EOL);
		return 0;
	}
}

function send_notification_android($registatoin_ids, $message) {
global $config;
        // Set POST variables
        $url = $config["REMOTE_SOCKET_GOOGLE"];

        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );

		//print_r($fields);die;
        $headers = array(
            'Authorization: key=' . $config['google_key'],
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

function getProfile($params){
	global $app,$db;
    $info = array();
    $user_id = "";

    $target_id = $params[0];

    if(count($params) > 1)
    {
        $user_id = $params[1];
    }

//    $sql = "SELECT (select count(*) from user_events where user_id=u.id and is_checkedIn=1) as checkins,u.* FROM users u where u.username=:username ";
    $sql = "SELECT
            (select count(*) from user_events where user_id=u.id and is_checkedIn=1) as checkins,
            (select count(*) from followers where user_id=u.id) as follower,
            (select count(*) from followers where follower_id=u.id) as following,
            u.* FROM users u where u.id=:id and u.is_active=1";
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":id", $target_id);
        $stmt->execute();
        //$stmt   = $db->query($sql);
        $info  = $stmt->fetch(PDO::FETCH_NAMED);

		$user_interests = getUserInterests($target_id,true);
		
        if(is_array($info))
        {
            if($user_id != "")
            {
                $following = getFollowingInternal($user_id);
                if(count($following) > 0)
                {
                    foreach($following as $follow)
                    {
                        if($follow['id'] == $target_id)
                        {
                            $info['is_followed'] = true;
                            break;
                        }
                    }

                    if(!isset($info['is_followed']))
                    {
                        $info['is_followed'] = false;
                    }
                }
                else
                {
                    $info['is_followed'] = false;
                }

                $followers = getFollowerInternal($user_id);
                if(count($followers) > 0)
                {
                    foreach($followers as $follow)
                    {
                        if($follow['id'] == $target_id)
                        {
                            $info['is_follower'] = true;
                            break;
                        }
                    }

                    if(!isset($info['is_follower']))
                    {
                        $info['is_follower'] = false;
                    }
                }
                else
                {
                    $info['is_follower'] = false;
                }

            }

			if(count($user_interests) > 0 )
				$info['interests'] = $user_interests;
			else
				$info['interests'] = array();
						
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
            u.* FROM users u where u.linkedin_id=:linkedin_id and u.is_active=1";

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
            u.* FROM users u where u.username=:username and u.is_active=1";

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

    $devices = get_user_device_id($to);

    if($devices != false)
    {
        foreach($devices as $device)
        {
            if($device['type'] == 0)
            {
                //iphone notification here
				send_notification_iphone($device['uid'],$message);
            }
            else
            {
                //android notification here
                send_notification_android(array($device['uid']), $message);
            }
        }
    }



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
	                sendEmail($email_data );

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

    	 if($user_image){
   	 	 	 $userImageText = "user_image=:user_image,";
    	 }else{
	    	 $userImageText = "";
    	 }

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
                ".$userImageText."
                modified=:modified
                WHERE id=:user_id";

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
                if($user_image){
                	$stmt->bindParam(":user_image", $user_image);
                }
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
		$sql = "SELECT * FROM users WHERE username=:username and is_active=1 limit 1";
	}
	else
	{
		$sql = "SELECT * FROM users WHERE linkedin_id=:username and is_active=1 limit 1";
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

    $sql = "select u.user_image as image,u.first_name as from_name,n.* from notifications n
            inner join users u on u.id = n.from
            where `to` = $user_id and u.is_active=1";

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

    $sql = "SELECT e.* FROM events e INNER JOIN user_events ue ON e.id=ue.event_id WHERE ue.user_id=$user_id and e.is_active=1";

    $event_id = "";
    $rec_limit = 10;
    $limit = "";

    if(count($params) > 1)
    {
        if($params[1] != 0)
        {
            $event_id = $params[1];
            $sql .= " AND e.id=$event_id";
        }
        elseif(isset($params[2]) && $params[2] > 0)
        {
            $page = ($params[2] == 1) ? 0 : (($params[2] -1) * $rec_limit);
            $sql .= " LIMIT $page,$rec_limit";
        }

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
                $follower_users = getFollowerInternal($user_id);
				$eventInterests = getEventInterests($event['id'],1);
                $users_list = getIsFollowed($users_list, $following_users);

                $users_list = getIsFollower($users_list, $following_users);

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
				
				if(count($eventInterests)>0)
                {
                    $user_events[$i]['interests'] = $eventInterests;
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

    $sql = "SELECT e.* FROM events e WHERE e.id=$event_id and e.is_active=1";

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
                    $users_list = getIsFollowed($users_list, $following_users);

                    $users_list = getIsFollower($users_list, $following_users);

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

	 $sql = "SELECT u.id,u.first_name,u.last_name,u.username,u.user_image,u.designation,ue.is_checkedIn FROM users u INNER JOIN user_events ue ON u.id=ue.user_id WHERE ue.event_id=$event_id and u.is_active=1";
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

    $sql = "SELECT u.id,u.first_name,u.last_name,u.username,ue.is_checkedIn,u.user_image FROM users u INNER JOIN user_events ue ON u.id=ue.user_id WHERE ue.event_id=$event_id and u.is_active=1";


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
            where user_id=$user_id and u.is_active=1";


    try{
        $stmt   = $db->query($sql);
        $users_list  = $stmt->fetchAll(PDO::FETCH_NAMED);

        $response["header"]["error"] = 0;
        $response["header"]["message"] = 'Success';
        $response["body"] = $users_list;

    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();

    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function getFollowerInternal($user_id)
{
    global $app,$db,$response;

    $sql = "select u.* from followers f
            inner join users u on u.id=f.follower_id
            where user_id=$user_id and u.is_active=1";


    try{
        $stmt   = $db->query($sql);
        $users_list  = $stmt->fetchAll(PDO::FETCH_NAMED);

        return $users_list;

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

    $sql = "select m.*,u.first_name,u.last_name,u.username,u.designation,u.user_image as image from messages m
            inner join users u on u.id=m.from
            where m.to=$user_id and u.is_active=1";


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
            where follower_id=$user_id and u.is_active=1";

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
            where follower_id=$user_id and u.is_active=1";


    try{
        $stmt   = $db->query($sql);
        $users_list  = $stmt->fetchAll(PDO::FETCH_NAMED);

        $response["header"]["error"] = 0;
        $response["header"]["message"] = 'Success';
        $response["body"] = $users_list;

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

    $users_list = getIsFollowed($users_list, $following_users);

    $users_list = getIsFollower($users_list, $following_users);



	$app->response()->header("Content-Type", "application/json");
    echo json_encode($users_list);


}

function getIsFollowed($users_list, $following_users)
{
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

    return $users_list;
}

function getIsFollower($users_list, $follower_users)
{
    if(count($follower_users) > 0)
    {
        $follower_users_ids = array();
        foreach($follower_users as $follower_user)
        {
            $follower_users_ids[] = $follower_user['id'];
        }

        foreach($users_list as $key=>$val)
        {
            if(in_array($users_list[$key]['id'],$follower_users_ids))
            {
                $users_list[$key]['is_follower'] = true;
            }
            else
            {
                $users_list[$key]['is_follower'] = false;
            }
        }
    }
    else
    {
        foreach($users_list as $key=>$val)
        {
            $users_list[$key]['is_follower'] = false;
        }
    }

    return $users_list;
}


function getEventFeedByEventId( $event_id)
{
    global $app, $db, $response;

//    $sql = "SELECT * FROM event_statuses WHERE event_id=$event_id";// AND user_id=$user_id
    $sql = "SELECT e.name, es.*,u.user_image,u.username,u.first_name, u.last_name FROM event_statuses es
                inner join users u on u.id=es.user_id
                inner join events e on es.event_id=e.id
                 WHERE es.event_id=$event_id and u.is_active=1 and e.is_active=1";


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


function searchEventByName($search,$user_id,$page)
{
    global $app, $db, $response;

    $search  = $search."*";
    $rec_limit = 10;
    $start = ($page > 1) ? (($page -1) * $rec_limit) : 0;


    $sql = "SELECT * FROM events WHERE MATCH (name, description) AGAINST ('$search' IN BOOLEAN MODE) and is_active=1 LIMIT $start,$rec_limit";


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

                $event_users = getIsFollowed($event_users, $following_users);

                $event_users = getIsFollower($event_users, $following_users);

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

function IsUserNearbyEvent($latitude,$longitude,$event_id)
{
    global $db;
    $sql = "SELECT
            ( 6371 *
            ACOS(
             COS( RADIANS($latitude) )
             * COS( RADIANS( latitude ) )
             * COS( RADIANS( longitude ) - RADIANS($longitude) )
             + SIN( RADIANS($latitude) ) * SIN( RADIANS( latitude ) ) )
              ) AS distance
            FROM `events`
            WHERE is_active=1
            AND id=$event_id
            LIMIT 1";

        try{

        $stmt   = $db->query($sql);
        $event  = $stmt->fetch(PDO::FETCH_NAMED);

        if(is_array($event) && $event['distance'] < 1)
        {
            return true;
        }
        else
        {
            return false;
        }

    }
    catch(PDOException $e){
        return false;
    }
}

function searchEventByLocation($latitude,$longitude,$user_id,$page)
{
    global $app, $db, $response;

    $rec_limit = 10;
    $start = ($page > 1) ? (($page -1) * $rec_limit) : 0;

    $sql = "SELECT *,
            ( 6371 *
            ACOS(
             COS( RADIANS($latitude) )
             * COS( RADIANS( latitude ) )
             * COS( RADIANS( longitude ) - RADIANS($longitude) )
             + SIN( RADIANS($latitude) ) * SIN( RADIANS( latitude ) ) )
              ) AS distance
            FROM `events`
			WHERE is_active=1
            AND if(end_date != '',end_date,start_date) >=now()
            HAVING distance < 250
            ORDER BY distance LIMIT $start,$rec_limit";


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

                $event_users = getIsFollowed($event_users, $following_users);

                $event_users = getIsFollower($event_users, $following_users);

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

function getNearByUser($latitude,$longitude)
{
    global $app, $db, $response;


    $sql = "SELECT ul.*,u.*,
            ( 6371 *
            ACOS(
             COS( RADIANS($latitude) )
             * COS( RADIANS( ul.latitude ) )
             * COS( RADIANS( ul.longitude ) - RADIANS($longitude) )
             + SIN( RADIANS($latitude) ) * SIN( RADIANS( ul.latitude ) ) )
              ) AS distance
            FROM `user_locations` ul
            inner join users u on ul.user_id=u.id
			WHERE u.is_active=1
            HAVING distance < 25
            ORDER BY distance";


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

            $sql = "SELECT * FROM users WHERE id=$user_id";
            $stmt   = $db->query($sql);
            $user  = $stmt->fetch(PDO::FETCH_NAMED);

            $name = ucfirst($user['first_name'].' '.$user['last_name']);
            $message = $name." joined the event.";

            $notification_data = array("from"=>$user_id,"to"=>$event['user_id'] ,"message"=> $message,"event_id"=>$event_id);
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
    $interest_ids = $req->params('interests');
    
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
                $stmt->bindParam(":start_date", $start_date);
                $stmt->bindParam(":end_date", $end_date);
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
                
                if($interest_ids)
       		         addUpdateEventInterests($user_id,$event_id,$interest_ids,$app,$db,$response);

                $response["header"]["error"] = 0;
                $response["header"]["message"] = "Event posted successfully.";
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

function updateEvent()
{
    global $app ,$db, $response;
    $req = $app->request();
    $event_id = $req->params('event_id');
    $name = $req->params('name');
    $description = $req->params('description');
    $address = $req->params('address');
    $start_date = $req->params('start_date');
    $end_date = $req->params('end_date');
    $latitude = $req->params('latitude');
    $longitude = $req->params('longitude');
    $user_id = $req->params('user_id');
    $image = '';

    $sql = "SELECT * FROM events WHERE id=$event_id";
    $stmt   = $db->query($sql);
    $alreadyExist  = $stmt->fetch(PDO::FETCH_NAMED);//$stmt->fetchColumn();

    if(is_array($alreadyExist))
    {
        if($alreadyExist['user_id'] != $user_id)
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'You are not allowed to update the event';
        }
        else
        {
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

                        $sql = "UPDATE events set name=:name,
                                description=:description,
                                address=:address,
                                start_date=:start_date,
                                end_date=:end_date,
                                image=:image,
                                latitude=:latitude,
                                longitude=:longitude
                                WHERE id=:event_id
                                ";

                        $stmt = $db->prepare($sql);

                        //$created_date = date("Y-m-d h:i:s");

                        $stmt->bindParam(":name", $name);
                        $stmt->bindParam(":description", $description);
                        $stmt->bindParam(":address", $address);
                        $stmt->bindParam(":start_date", $start_date);
                        $stmt->bindParam(":end_date", $end_date);
                        $stmt->bindParam(":image", $image);
                        $stmt->bindParam(":latitude", $latitude);
                        $stmt->bindParam(":longitude", $longitude);
                        $stmt->bindParam(":event_id", $event_id);
                        //$stmt->bindParam(":user_id", $user_id,PDO::PARAM_INT);
                        //$st->bindValue( ":art", $art, PDO::PARAM_INT );

                        $stmt->execute();

                        $response["header"]["error"] = 0;
                        $response["header"]["message"] = "Updated successfully.";


                }
                catch(PDOException $e){
                    $response["header"]["error"] = 1;
                    $response["header"]["message"] = $e->getMessage();
                }
            }

        }
    }
    else
    {
        $response["header"]["error"] = 1;
        $response["header"]["message"] = 'Event do not exist';
    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function deleteEvent()
{
    global $app ,$db, $response;
    $req = $app->request();
    $event_id = $req->params('event_id');
    $user_id = $req->params('user_id');

    $sql = "SELECT * FROM events WHERE id=$event_id";
    $stmt   = $db->query($sql);
    $alreadyExist  = $stmt->fetch(PDO::FETCH_NAMED);//$stmt->fetchColumn();

    if(is_array($alreadyExist))
    {
        if($alreadyExist['user_id'] != $user_id)
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'You are not allowed to delete the event';
        }
        else
        {
            try{

                    $sql = "UPDATE events set is_active=:is_active
                            WHERE id=:event_id
                            ";

                    $stmt = $db->prepare($sql);

                    $is_active = 0;

                    $stmt->bindParam(":is_active", $is_active,PDO::PARAM_INT);
                    $stmt->bindParam(":event_id", $event_id);

                    $stmt->execute();
                    //debug($stmt->debugDumpParams(),1);

                    $response["header"]["error"] = 0;
                    $response["header"]["message"] = "Success";


            }
            catch(PDOException $e){
                $response["header"]["error"] = 1;
                $response["header"]["message"] = $e->getMessage();
            }
        }
    }
    else
    {
        $response["header"]["error"] = 1;
        $response["header"]["message"] = 'Event do not exist';
    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
}

function reportEvent()
{
    global $app ,$db, $response;
    $req = $app->request();
    $event_id = $req->params('event_id');
    $user_id = $req->params('user_id');
    $report_text = $req->params('report_text');


    $sql = "SELECT * FROM events WHERE id=$event_id";

    try{
        $stmt   = $db->query($sql);
        $event_exist  = $stmt->fetch(PDO::FETCH_NAMED);

        if(is_array($event_exist))
        {
            $sql = "INSERT INTO reports (user_id,event_id,text) values (:user_id,:event_id,:report_text)";
            $stmt = $db->prepare($sql);

            $stmt->bindParam("user_id", $user_id);
            $stmt->bindParam("event_id", $event_id);
            $stmt->bindParam("report_text", $report_text);
            $stmt->execute();

            $response["header"]["error"] = 0;
            $response["header"]["message"] = "Successfully submitted";

        }
        else
        {
            $response["header"]["error"] = 1;
            $response["header"]["message"] = 'Event do not exist';
        }

    }
    catch(PDOException $e){
        $response["header"]["error"] = 1;
        $response["header"]["message"] = $e->getMessage();
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
    $datetime = $req->params('datetime');


    try{
        $sql = "INSERT INTO event_statuses (user_id,event_id,status,datetime) values (:user_id,:event_id,:status,:datetime)";
            $stmt = $db->prepare($sql);
            //$date = date("Y-m-d h:i:s");
            $stmt->bindParam("user_id", $user_id);
            $stmt->bindParam("event_id", $event_id);
            $stmt->bindParam("status", $status);
            $stmt->bindParam("datetime", $datetime);
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

function userLocation()
{
    global $app, $db, $response;
    $req = $app->request();
    $user_id = $req->params('user_id');
    $latitude = $req->params('latitude');
    $longitude = $req->params('longitude');

    $sql = "select count(*) from user_locations where user_id=:user_id";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $present = $stmt->fetchColumn();

    if($present != false)
    {
        //update

        $sql = "UPDATE user_locations set latitude='$latitude',longitude='$longitude' WHERE user_id=:user_id";

        $stmt = $db->prepare($sql);

        $stmt->bindParam(":user_id", $user_id);

        $stmt->execute();
    }
    else
    {

     //insert
     try{
        $sql = "insert into user_locations (user_id,latitude,longitude) values (:user_id,:latitude,:longitude)";

        $stmt = $db->prepare($sql);

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":latitude", $latitude);
        $stmt->bindParam(":longitude", $longitude);

        $stmt->execute();
     }
     catch(PDOException $e){
            $response["header"]["error"] = 1;
            $response["header"]["message"] = $e->getMessage();
        }
    }

    $response["header"]["error"] = 0;
    $response["header"]["message"] = "Success";

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

            $sql = "SELECT * FROM users WHERE id=$from";
            $stmt   = $db->query($sql);
            $user  = $stmt->fetch(PDO::FETCH_NAMED);

            $name = ucfirst($user['first_name'].' '.$user['last_name']);
            $message = $name." share business card with you.";

            $notification_data = array("from"=>$from,"to"=>$t ,"message"=>$message,"event_id"=>0);
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
    $latitude = $req->params('latitude');
    $longitude = $req->params('longitude');


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
                if(IsUserNearbyEvent($latitude, $longitude, $event_id))
                {
                    $sql = "UPDATE user_events set is_checkedIn=1 WHERE user_id=:user_id AND event_id=:event_id";
                    $stmt = $db->prepare($sql);

                    $stmt->bindParam("user_id", $user_id);
                    $stmt->bindParam("event_id", $event_id);

                    $stmt->execute();
                    $response["header"]["error"] = 0;
                    $response["header"]["message"] = "Success";
                }
                else
                {
                    $response["header"]["error"] = 1;
                    $response["header"]["message"] = 'You can not check in this event!';
                }

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

            $sql = "SELECT * FROM users WHERE id=$follower_id";
            $stmt   = $db->query($sql);
            $user  = $stmt->fetch(PDO::FETCH_NAMED);

            $name = ucfirst($user['first_name'].' '.$user['last_name']);
            $message = $name." is following you.";

            $notification_data = array("from"=>$follower_id,"to"=>$user_id ,"message"=>$message,"event_id"=>$event_id);
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
    $headers = "From: support@waslevents.com" . "\r\n";

    mail($to, $subject, $message,$headers);
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
