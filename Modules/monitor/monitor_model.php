<?php

/*
     All Emoncms code is released under the GNU Affero General Public License.
     See COPYRIGHT.txt and LICENSE.txt.

     ---------------------------------------------------------------------
     Emoncms - open source energy visualisation
     Part of the OpenEnergyMonitor project:
     http://openenergymonitor.org

*/

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

class Monitor
{
    private $mysqli;
    private $redis;
    private $process;
    private $log;

    public function __construct($mysqli,$redis,$process)
    {
        $this->mysqli = $mysqli;
        $this->redis = $redis;
        $this->process = $process;
        $this->log = new EmonLogger(__FILE__);
    }

    public function set($userid,$monitorid,$time,$data)
    {
        $this->log->info("Monitor:set userid=$userid monitorid=$monitorid time=$time data=".implode(",",$data));
        // Input sanitisation
        $userid = (int) $userid;
        $monitorid = (int) $monitorid;
        $time = (int) $time;
        
        $data = implode(",",$data);

        // Load the user's monitors object
        if (!$time) $time = time();

        if ($this->redis) {
            $monitors = json_decode($this->redis->get("monitors:$userid"));
            if ($monitors==NULL) $monitors = $this->get_mysql($userid);
        } else {
            $monitors = $this->get_mysql($userid);
        }

        // Either update or insert the monitor that's just been recieved        
        if ($monitors==false) $monitors = new stdClass();
        
        if (!isset($monitors->$monitorid)) $monitors->$monitorid = new stdClass();
        
        $monitors->$monitorid->data = $data;
        $monitors->$monitorid->time = $time;
        
        if ($this->redis) {
            $this->redis->set("monitors:$userid",json_encode($monitors));
        } else {
            $this->set_mysql($userid,$monitors);
        }

        $this->process($userid,$monitors,$monitorid,$time,$data);

        return true;
    }

    public function set_decoder($userid,$monitorid,$decoder)
    {
        // Input sanitisation 
        $userid = (int) $userid;
        $monitorid = (int) $monitorid;
        $decoder_in = json_decode($decoder);
        if (!$decoder_in) return false;
        
        $decoder = new stdClass();
        $decoder->name = preg_replace('/[^\w\s-:()]/','',$decoder_in->name);
        $decoder->updateinterval = (int) $decoder_in->updateinterval;
        
        $decoder->variables = array();
        // Ensure each variable is defined with the allowed fields and correct types
        foreach ($decoder_in->variables as $variable)
        {
          $var = new stdClass();
          $var->name = preg_replace('/[^\w\s-:]/','',$variable->name);
          if (isset($variable->type)) $var->type = (int) $variable->type;
          if (isset($variable->scale)) $var->scale = (float) $variable->scale;
          if (isset($variable->units)) $var->units = preg_replace('/[^\w\s-°]/','',$variable->units);
          if (isset($variable->processlist)) {
              $var->processlist = preg_replace('/[^\d-:,.]/','',$variable->processlist);
          }
          $decoder->variables[] = $var;
        }

        // Load full monitors definition from redis or mysql
        if ($this->redis) {
            $monitors = json_decode($this->redis->get("monitors:$userid"));
        } else {
            $monitors = $this->get_mysql($userid);
        }

        // Set the decoder part of the monitor definition 
        if ($monitors!=NULL && isset($monitors->$monitorid)) 
        {
            $monitors->$monitorid->decoder = $decoder;
            if ($this->redis) $this->redis->set("monitors:$userid",json_encode($monitors));
            $this->set_mysql($userid,$monitors);
        }

        return true;
    }

    public function get_all($userid)
    {
        $userid = (int) $userid;
        if ($this->redis) {
            $monitors = $this->redis->get("monitors:$userid");
            if ($monitors) {
                return json_decode($monitors);
            } else {
                $monitors = $this->get_mysql($userid);
                $this->redis->set("monitors:$userid",json_encode($monitors));
                return $monitors;
            }
            
        } else {
            return $this->get_mysql($userid);
        }
    }

	//----------------------------------------------------------------------------------------------
    public function process($userid,$monitors,$monitorid,$time,$data)
    {    
        $bytes = explode(',',$data);
        $pos = 0;
        
        if (isset($monitors->$monitorid->decoder) && sizeof($monitors->$monitorid->decoder->variables)>0)
        {
            foreach($monitors->$monitorid->decoder->variables as $variable)
            {
                $value = null; 
                
                // Byte value
                if ($variable->type==0)
                {
                    if (!isset($bytes[$pos])) break;
                    $value = (int) $bytes[$pos];
                    $pos += 1;
                }

                // signed integer
                if ($variable->type==1)
                {
                    if (!isset($bytes[$pos+1])) break;
                    $value = (int) $bytes[$pos] + (int) $bytes[$pos+1]*256;
                    if ($value>32768) $value += -65536;  
                    $pos += 2;
                }

                // unsigned long
                if ($variable->type==2 || $variable->type==5)
                {
                    if (!isset($bytes[$pos+3])) break;
                    $value = (int) $bytes[$pos] + (int) $bytes[$pos+1]*256 + (int) $bytes[$pos+2]*65536 + (int) $bytes[$pos+3]*16777216;
                    //if ($value>32768) $value += -65536;  
					if($variable->type==5)
					{
						$v = unpack('f', pack('L', $value));
						$value = $v[1];
					}
                    $pos += 4;
                }
				
				// signed integer BE
                if ($variable->type==3)
                {
                    if (!isset($bytes[$pos])) break;
                    $value = (int) $bytes[$pos]*256 + (int) $bytes[$pos+1];
                    if ($value>32768) $value += -65536;  
                    $pos += 2;
                }

                // unsigned long BE
                if ($variable->type==4 || $variable->type==6)
                {
                    if (!isset($bytes[$pos])) break;
                    $value = (int) $bytes[$pos+3] + (int) $bytes[$pos+2]*256 + (int) $bytes[$pos+1]*65536 + (int) $bytes[$pos+0]*16777216;
                    //if ($value>32768) $value += -65536;  
					if($variable->type==6)
					{
						$v = unpack('f', pack('L', $value));
						$value = $v[1];
					}					
                    $pos += 4;
                }

                if (isset($variable->scale)) $value *= $variable->scale;

                if (isset($variable->processlist) && $variable->processlist!='') $this->process->input($time,$value,$variable->processlist);
            }
        }
    }
    
    public function set_mysql($userid,$data)
    {
        $json = json_encode($data);
        $result = $this->mysqli->query("SELECT `userid` FROM monitor WHERE `userid`='$userid'");
        if ($result->num_rows) {
            $this->mysqli->query("UPDATE monitor SET `data`='$json' WHERE `userid`='$userid'");
        } else {
            $this->mysqli->query("INSERT INTO monitor (`userid`,`data`) VALUES ('$userid','$json')");
        }
    }
    
    public function get_mysql($userid)
    {
        $result = $this->mysqli->query("SELECT `data` FROM monitor WHERE `userid`='$userid'");
        if ($row = $result->fetch_array()) {
          return json_decode($row['data']);
        } else {
          return false;
        }
        
    }

}
