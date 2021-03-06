<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function monitor_controller()
{
    global $mysqli, $redis, $session, $route, $feed_settings, $user;
    $result = false;
    
    if (!isset($session['read'])) return array('content'=>$result);

    include "Modules/feed/feed_model.php";
    $feed = new Feed($mysqli,$redis,$feed_settings);

    require "Modules/input/input_model.php"; // 295
    $input = new Input($mysqli,$redis, $feed);

    require "Modules/input/process_model.php"; // 886    
	//$process = new Process($mysqli,$input,$feed,$user->get_timezone($session['userid']));
	$process = new Process($mysqli,$input,$feed);
    $process->set_timezone_offset($user->get_timezone($session['userid']));

    include "Modules/monitor/monitor_model.php";
    $monitor = new Monitor($mysqli,$redis,$process);

    if ($route->format == 'html') {
        if ($route->action == "list" && $session['write']) $result = view("Modules/monitor/monitor_view.php",array());
        if ($route->action == "api" && $session['write']) $result = view("Modules/monitor/monitor_api.php",array());
    }

    if ($route->format == 'json') {
    
        if ($route->action == 'set' && $session['write']) 
        {
            $data = explode(",",get('data'));
            for ($i=0; $i<count($data); $i++) $data[$i] = (int) $data[$i];
            $result = $monitor->set($session['userid'],get('monitorid'),get('time'),$data);
        }
        
        if ($route->action == 'multiple' && $session['write']) 
        {
            $data = json_decode(prop('data'));
            $len = count($data);
            
            if ($len>0 && isset($data[$len-1][0]))
            {
                // Sent at mode: input/bulk.json?data=[[45,16,1137],[50,17,1437,3164],[55,19,1412,3077]]&sentat=60
                if (isset($_GET['sentat'])) {
                    $time_ref = time() - (int) $_GET['sentat'];
                }  elseif (isset($_POST['sentat'])) {
                    $time_ref = time() - (int) $_POST['sentat'];
                } 
                // Offset mode: input/bulk.json?data=[[-10,16,1137],[-8,17,1437,3164],[-6,19,1412,3077]]&offset=-10
                elseif (isset($_GET['offset'])) {
                    $time_ref = time() - (int) $_GET['offset'];
                } elseif (isset($_POST['offset'])) {
                    $time_ref = time() - (int) $_POST['offset'];
                }
                // Time mode: input/bulk.json?data=[[-10,16,1137],[-8,17,1437,3164],[-6,19,1412,3077]]&time=1387729425
                elseif (isset($_GET['time'])) {
                    $time_ref = (int) $_GET['time'];
                } elseif (isset($_POST['time'])) {
                    $time_ref = (int) $_POST['time'];
                } 
                // Legacy mode: input/bulk.json?data=[[0,16,1137],[2,17,1437,3164],[4,19,1412,3077]]
                else {
                    $time_ref = time() - (int) $data[$len-1][0];
                }

                foreach ($data as $item)
                {
                    if (count($item)>2)
                    {
                        // check for correct time format
                        $itemtime = (int) $item[0];
                        
                        $time = $time_ref + (int) $itemtime;
                        $monitorid = $item[1];
                        
                        $bytevalues = array();
						$bytevalues[0] = (int)(($time>>0) & 255);
						$bytevalues[1] = (int)(($time>>8) & 255);
						$bytevalues[2] = (int)(($time>>16) & 255);
						$bytevalues[3] = (int)(($time>>24) & 255);
                        for ($i=2; $i<count($item); $i++) $bytevalues[$i+2] = (int) $item[$i];
                        
                        $result = $monitor->set($session['userid'],$monitorid,$time,$bytevalues);
                    }
                }
            }
        }
        
        
        if ($route->action == 'setdecoder' && $session['write']) $result = $monitor->set_decoder($session['userid'],get('monitorid'),get('decoder'));
        if ($route->action == 'getall' && $session['write']) $result = $monitor->get_all($session['userid']);
    }
    
    /*
    
    // Sent at mode: data= [[45,16,1137]] &sentat=60
    // Offset mode:  data= [[-10,16,1137]] &offset=-10
    // Time mode:    data= [[-10,16,1137]] &time=1387729425
    // Legacy mode:  data= [[0,16,1137]]
    
    // Sent at mode: data= [[45,16,1137],[50,17,1437,3164],[55,19,1412,3077]] &sentat=60
    // Offset mode:  data= [[-10,16,1137],[-8,17,1437,3164],[-6,19,1412,3077]] &offset=-10
    // Time mode:    data= [[-10,16,1137],[-8,17,1437,3164],[-6,19,1412,3077]] &time=1387729425
    // Legacy mode:  data= [[0,16,1137],[2,17,1437,3164],[4,19,1412,3077]]
    
    */

    return array('content'=>$result);
}
