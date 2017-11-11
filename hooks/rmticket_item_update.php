<?php

// DEBUG SETUP
$debug = true;
$debug_file = "./webhook.log";

ini_set('display_errors', 'On');
require __DIR__ . '/../../vendor/autoload.php';
require_once('../rmticket_util.php');
require_once('../lib/AvcPodioItem.php');

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
require_once('../FileSessionManager.class.php');
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

// Big switch statement to handle the different events
//echo "rmticket_item_update - Starting";
wdebug("rmticket_item_update", "Starting...");

wdebug("POST Var", $_POST);

switch ($_POST['type']) {

  // Validate the webhook. This is a special case where we verify newly created webhooks.
  case 'hook.verify':
    PodioHook::validate($_POST['hook_id'], array('code' => $_POST['code']));
    wdebug("hook.verify", "");
    break;
  
  // An item was created
  case 'item.create':
    $item_id = $_POST['item_id'];
    $item_rev_id = $_POST['item_revision_id'];
    $strout = "item_id =" . $item_id . "\n";
    $strout .= "item_revision_id =" . $item_rev_id;
    wdebug("item.create", $strout);    
    //file_put_contents($file, $string, FILE_APPEND | LOCK_EX);
    break;    

  // An item was updated
  case 'item.update':
    $item_id = $_POST['item_id'];
    $item_rev_id = $_POST['item_revision_id'];
    $strout = "item_id =" . $item_id . "\n";
    $strout .= "item_revision_id =" . $item_rev_id;
    wdebug("item.update", $string);
    write_structure($item_id);
    
    rmt_updated($item_id, $item_rev_id);
    //bd_add_bidder_recs($item_id, $item_rev_id);
    break;    

  // An item was deleted
  case 'item.delete':
    $string = gmdate('Y-m-d H:i:s') . " item.delete webhook received. ";
    $string .= "Post params: ".print_r($_POST, true) . "\n";
    wdebug("item.delete", $string);
    //file_put_contents($file, $string, FILE_APPEND | LOCK_EX);    
    break;

}



/*
 *  
 * FUNCTIONS & METHOD 
 * 
 */

function wdebug($desc, $var) {
  global $debug, $debug_file;
  if ($debug) {
    $str_date = gmdate('Y-m-d H:i:s'). " : ";
    $str_out =  $desc . "\n" . print_r($var, true) . "\n";    
    file_put_contents($debug_file, $str_date . $str_out, FILE_APPEND | LOCK_EX);    
    file_put_contents('php://stderr', $str_out);
  }
}
function vdebug($desc, $var) {
  global $debug, $debug_file;
  if ($debug) {
    $str_date = gmdate('Y-m-d H:i:s'). " : ";
    $str_out =  $desc . "\n" . var_dumpr($var) . "\n";    
    file_put_contents($debug_file, $str_date . $str_out, FILE_APPEND | LOCK_EX);    
    file_put_contents('php://stderr', $str_out);
  }
}
function jdebug($desc, $var) {
  global $debug, $debug_file;
  if ($debug) {
    $str_date = gmdate('Y-m-d H:i:s'). " : ";
    $str_out =  $desc . "\n" . json_encode($var) . "\n";    
    file_put_contents($debug_file, $str_date . $str_out, FILE_APPEND | LOCK_EX);    
    file_put_contents('php://stderr', $str_out);
  }
}

function write_structure($item_id) {
  global $debug;
  $item = PodioItem::get_basic($item_id);
  $str_date = gmdate('Y-m-d H:i:s'). " : \n";
  $str_out = "FULL ITEM\n";
  $str_out .= print_r($item, true) . "\n\n";
  $str_out .= "FIELDS\n";
  $str_out .=  print_r($item->fields, true) . "\n";    
  file_put_contents("./rmticket_struct.txt", $str_date . $str_out, LOCK_EX); // OVERWRITE
}

/*
 * RMTicket - Field external_ids
 * CalcTitle = calctitle-2
 * Title = title
 * Location = location
 * Type = type
 * Responsible = responsible
 * Start Date = start-date
 * Due Date = due-date
 * Move-Out Date = move-out-date
 * Status = status
 * Description = description
 * Project = project
 * Sub Tasks = sub-tasks
 * Action On Save = action-on-save
 * 
 * 
 * 
 */
function rmt_updated($item_id, $item_rev_id) {
  // Get the Item
  $rmt_item = PodioItem::get_basic($item_id);
  $rmt_item_diff = PodioItemDiff::get_for($item_id, $item_rev_id - 1, $item_rev_id );
  //wdebug("RMTicket", $rmt_item->fields);
  wdebug("RMTicket-DIFF", $rmt_item_diff);
  
  // Update CalcTitle
  //TODO Only update if location or title changes value
  $rmt_item->fields['calctitle-2']->values = 
      $rmt_item->fields['location']->values . ": " .
      $rmt_item->fields['title']->values;
  
  wdebug ("Location->field_id", p_get_field_id($rmt_item, "location"));
  wdebug ("Location: field value changed", p_field_value_changed($rmt_item, $rmt_item_diff, "location"));
//  foreach ($rmt_item_diff as $rid) {
//    wdebug("RID", $rid->fields['location']);
//  }
  
  
  // Save the Item
  $rmt_item->save(array('hook' => false)); // do NOT execute Hooks on Save
}


function p_get_field_id($item, $ext_id) {
  return $item->fields[$ext_id]->field_id;  
}

function p_field_value_changed($item, $item_diff, $ext_id) {
  $field_id = p_get_field_id($item, $ext_id);
  
  return $item_diff->fields;
  
//  foreach ($item_diff as $f) {
//    if ($f["field_id"] == $field_id) {
//      return true;
//    }
//  }
//  return false;
}


function var_dumpr( $object=null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    return $contents;
}

function dump_debug($input, $collapse=false) {
    $recursive = function($data, $level=0) use (&$recursive, $collapse) {
        global $argv;

        $isTerminal = isset($argv);

        if (!$isTerminal && $level == 0 && !defined("DUMP_DEBUG_SCRIPT")) {
            define("DUMP_DEBUG_SCRIPT", true);

            echo '<script language="Javascript">function toggleDisplay(id) {';
            echo 'var state = document.getElementById("container"+id).style.display;';
            echo 'document.getElementById("container"+id).style.display = state == "inline" ? "none" : "inline";';
            echo 'document.getElementById("plus"+id).style.display = state == "inline" ? "inline" : "none";';
            echo '}</script>'."\n";
        }

        $type = !is_string($data) && is_callable($data) ? "Callable" : ucfirst(gettype($data));
        $type_data = null;
        $type_color = null;
        $type_length = null;

        switch ($type) {
            case "String": 
                $type_color = "green";
                $type_length = strlen($data);
                $type_data = "\"" . htmlentities($data) . "\""; break;

            case "Double": 
            case "Float": 
                $type = "Float";
                $type_color = "#0099c5";
                $type_length = strlen($data);
                $type_data = htmlentities($data); break;

            case "Integer": 
                $type_color = "red";
                $type_length = strlen($data);
                $type_data = htmlentities($data); break;

            case "Boolean": 
                $type_color = "#92008d";
                $type_length = strlen($data);
                $type_data = $data ? "TRUE" : "FALSE"; break;

            case "NULL": 
                $type_length = 0; break;

            case "Array": 
                $type_length = count($data);
        }

        if (in_array($type, array("Object", "Array"))) {
            $notEmpty = false;

            foreach($data as $key => $value) {
                if (!$notEmpty) {
                    $notEmpty = true;

                    if ($isTerminal) {
                        echo $type . ($type_length !== null ? "(" . $type_length . ")" : "")."\n";

                    } else {
                        $id = substr(md5(rand().":".$key.":".$level), 0, 8);

                        echo "<a href=\"javascript:toggleDisplay('". $id ."');\" style=\"text-decoration:none\">";
                        echo "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>";
                        echo "</a>";
                        echo "<span id=\"plus". $id ."\" style=\"display: " . ($collapse ? "inline" : "none") . ";\">&nbsp;&#10549;</span>";
                        echo "<div id=\"container". $id ."\" style=\"display: " . ($collapse ? "" : "inline") . ";\">";
                        echo "<br />";
                    }

                    for ($i=0; $i <= $level; $i++) {
                        echo $isTerminal ? "|    " : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    }

                    echo $isTerminal ? "\n" : "<br />";
                }

                for ($i=0; $i <= $level; $i++) {
                    echo $isTerminal ? "|    " : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                }

                echo $isTerminal ? "[" . $key . "] => " : "<span style='color:black'>[" . $key . "]&nbsp;=>&nbsp;</span>";

                call_user_func($recursive, $value, $level+1);
            }

            if ($notEmpty) {
                for ($i=0; $i <= $level; $i++) {
                    echo $isTerminal ? "|    " : "<span style='color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                }

                if (!$isTerminal) {
                    echo "</div>";
                }

            } else {
                echo $isTerminal ? 
                        $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " : 
                        "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";
            }

        } else {
            echo $isTerminal ? 
                    $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " : 
                    "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";

            if ($type_data != null) {
                echo $isTerminal ? $type_data : "<span style='color:" . $type_color . "'>" . $type_data . "</span>";
            }
        }

        echo $isTerminal ? "\n" : "<br />";
    };

    call_user_func($recursive, $input);
}