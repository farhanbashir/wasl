<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
error_reporting(E_ALL); 
 
require "config.php"; 
require "functions.php"; 
require "NotORM.php";

$pdo = new PDO("mysql:host=".$config["db"]["db_host"].";dbname=".$config["db"]["db_name"], $config["db"]["db_user"], $config["db"]["db_password"]);
$db = new NotORM($pdo);

require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Slim\Slim(array("MODE" => "development"));

$response = array();

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */

// GET route
$app->get(
    '/',
    function () {
        
        echo "<h1>It's a Slim World</h1>";
    }
);

$app->get("/users", function () use ($app, $db, $response) {
    $users = array();
    foreach ($db->users() as $user) {
        $users[]  = array(
            "id" => $user["id"],
            "first_name" => $user["first_name"],
            "last_name" => $user["last_name"]

        );
    }
    
    $response["header"]["error"] = 0;
    $response["header"]["message"] = "Success";
    $response["body"] = $users;
    
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);
});

$app->get("/getInfo/:username",function($username) use ($app,$db){
    $info = array();
    $user = $db->users->where("username = ?",$username);
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($user);

});

$app->get("/authenticate/:username/:password",function($username,$password) use ($app,$db,$response){
    $data = array();
    

    if($user = $db->users->where("username",$username)->fetch())
    {
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Success";
        $data["info"] = $user;
            
    }
    else
    {
        $response["header"]["error"] = 0;
        $response["header"]["message"] = "Fail";
    }        
    
    
    $response["header"]["error"] = 0;
    $response["header"]["message"] = "Success";
    $response["body"] = $data;
    
    
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($response);

});



// POST route
$app->post(
    '/post',
    function () {
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
