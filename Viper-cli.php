<?php
// Brandon Fiquett
// Viper SmartStart Interface
// Version 1.0
// March 1, 2011
// Revised January 22, 2019
// Mark Caldwell
header('Content-type: text/plain');
if ($argc != 5 ) {
    exit( "Usage: " . $argv[0] . " <username> <password> <vehicle#> <action>or<?> \n" );
}
$smartstart_username = $argv[1];
$smartstart_password = $argv[2];    // sent with https so not disclosed to internet
$smartstart_server = 'colt.calamp-ts.com';
$action = $argv[4];
if ($action == '?') {
    $action = 'READ_CURRENT';    // return all available actions, but don't execute a command
}
$smartstart_username = (isset($_REQUEST['user'])) ? $_REQUEST['user'] : $smartstart_username;
$smartstart_password = (isset($_REQUEST['pass'])) ? $_REQUEST['pass'] : $smartstart_password;
$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : $action;
if (($smartstart_username == '') or ($smartstart_password == '') or ($action == '')){echo "Missing information!\n"; die();}
if(defined('STDIN') ){ 
    echo("Running from CLI\n"); 
	echo "\n";
	echo "Requesting Session ID...\n";
	$sessionID = getSessionID($smartstart_username,$smartstart_password);
	echo "Session ID: " . $sessionID . " \n";
	echo "Getting Vechicle List...\n";
	$vehicles = getVehicles($sessionID);
	echo count($vehicles->Devices) . " vehicle(s) available. \n";

    $vehicleID = getVehicleID($argv[3]);
	echo "Vehicle ID: " . $vehicleID . " \n";
    if ($action == 'READ_CURRENT') {
        print_r(getAvailableActions(0));    // print actions array
    }
//	echo "Enter Command: ";
//	$handle = fopen ("php://stdin","r");
//	$line = fgets($handle);
//	$result = json_decode(sendCommandToVehicle($vehicleID,$sessionID,trim($line)));
    echo "Action: " . $action . " \n";
    $i = 1;
    do {    // attempt to execute successfully with 5 tries and then abort
        $result = json_decode(sendCommandToVehicle($vehicleID,$sessionID,trim($action)));
        if(empty($result)) {
            echo "Timeout Error.  Attempt: " . $i . "\n" ;
            $i = $i + 1;
            sleep(10);
            continue;
        }
        if($result->Return->ResponseSummary->StatusCode == 0){
            echo "Command received successfully.\n";
            break;
        }
    } while ($i <= 5);
}
else{ 
  	$sessionID = getSessionID($smartstart_username,$smartstart_password);
	$vehicles = getVehicles($sessionID);
	$vehicleID = getVehicleID(0);
	//print_r(getAvailableActions(0));
	//print_r($vehicles);
	echo sendCommandToVehicle($vehicleID,$sessionID,$action);
}
function curlGet($url, $referer = null, $headers = null){
	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_1; en-us) AppleWebKit/531.9 (KHTML, like Gecko) Version/4.0.3 Safari/531.9");
    if(!is_null($referer)) curl_setopt($ch, CURLOPT_REFERER, $referer);
   	if(!is_null($headers)) curl_setopt($ch, CURLOPT_HEADER, $headers);
	//curl_setopt($ch, CURLOPT_VERBOSE, true);
	$html = curl_exec($ch);
	if(curl_errno($ch) != 0){
		throw new Exception("Error during GET of '$url': " . curl_error($ch));
	}
	
	return $html;
}
function json_format($json)
{
    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;
    $json_obj = json_decode($json);
    if($json_obj === false)
        return false;
    $json = json_encode($json_obj);
    $len = strlen($json);
    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
                if(!$in_string)
                {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
                if(!$in_string)
                {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                }
            default:
                $new_json .= $char;
                break;                   
        }
    }
    return $new_json;
}
function getSessionID($username,$password){
	global $smartstart_server;
	$html = curlGet("https://$smartstart_server/auth/login/$username/$password","",true);
	preg_match('/^Set-Cookie: kohanasession=(.*?);/m',$html , $m); 
	return $m[1];
}
function getVehicleID($vehicleNum){
	global $vehicles;
	return $vehicles->Devices[$vehicleNum]->DeviceId;	
}
function getAvailableActions($vehicleNum){
	global $vehicles;
	$results = array();
	$actions = $vehicles->Devices[$vehicleNum]->AvailActions;
	//var_dump($actions);
	foreach ($actions as $action) {
		 array_push($results,$action->Name);
	}
	return $results;
}
function getVehicles($sessionID){
	global $smartstart_server;
	$json = curlGet("https://$smartstart_server/device/advancedsearch?sessid=$sessionID");
	//echo json_format($json);
	$result = json_decode($json);
	return $result->Return->Results;	
}
function sendCommandToVehicle($vehicleID,$sessionID,$action){
	global $smartstart_server;
	$json = curlGet("https://$smartstart_server/device/sendcommand/$vehicleID/$action?sessid=$sessionID");
	//echo json_format($json);
	return $json;
}
?>