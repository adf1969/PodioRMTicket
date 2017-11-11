<?php

$debug = true;
$debug_file = "./webtest.log";

ini_set('display_errors', 'On');
require __DIR__ . '/../vendor/autoload.php';
//define("REDIRECT_URI", 'http://pubvps.avcorp.biz/podio/avatar_updimg.php');

// -- App Specific IDs / Tokens -- \\
// rmticket-process API Keys
// Generated at: https://podio.com/settings/api
$client_id = "rmticket-process";
$client_secret = "65vwW1gRqqzgRxEPFZqT7WylFR5YnRI0xYTvEN4ZgsuwdKuAwii9IjzMokSjlROs";

// RMTicket AppIDs
// Generated at: https://podio.com/avcorpbiz/project-management/apps/19837746/hooks
// Click on App, then click "wrench", then Developer.
$rmticket_app_id = '19837746';
$rmticket_app_token = 'a70f8a52721a431ab262704e00d55c00';

$username = "andrew@avcorp.biz";
$password = "analq131";

// App Authenticate
require_once('FileSessionManager.class.php');
FileSessionManager::$filename = '../session_data.txt';
Podio::setup($client_id, $client_secret, array(
  "session_manager" => "FileSessionManager"
));

if (!Podio::is_authenticated()) {
  Podio::authenticate_with_app($rmticket_app_id, $rmticket_app_token);
  //Podio::authenticate_with_password($username, $password);  
}

// Authenticated: Start Process
Podio::set_debug(true, 'file');


