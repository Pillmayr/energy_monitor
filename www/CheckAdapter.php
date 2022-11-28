<?php

class CheckAdapter
{
    // Create a function to implement the TP-Link SmartHome encryption
    function tplink_encrypt ($to_encrypt, $key = 171) {

//        $result = "\0\0\0\0";
        $result = pack("N", strlen($to_encrypt));
        foreach (str_split($to_encrypt) as $character) {
            $a = $key ^ ord($character);
            $key = $a;
            $result .= chr($a);
        }

        return $result;
    }

// Create a function to implement the TP-Link SmartHome decryption
    function tplink_decrypt ($to_decrypt, $key = 171) {

        $result = '';
        foreach (str_split($to_decrypt) as $character) {
            $a = $key ^ ord($character);
            $key = ord($character);
            $result .= chr($a);
        }

        $result = preg_replace('/[[:^print:]]/', '', $result);
        $result = '{' . substr($result, 1);
        return $result;
    }

// Create a function to send commands to TP-Link SmartHome devices
    function send_command ($remote, $port, $command) {

        // Encrypt the command in order to send it to the device
        $encrypted_command = $this->tplink_encrypt($command);

        // Create a TCP/IP socket, connect to the device, and send the encrypted command
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>2, "usec"=>0));
        socket_set_option($socket,SOL_SOCKET, SO_SNDTIMEO, array("sec"=>2, "usec"=>0));

        socket_set_nonblock($socket);

        $error = NULL;
        $attempts = 0;
        $timeout = 2000;  // adjust because we sleeping in 1 millisecond increments
        $connected = false;
        while (!($connected = @socket_connect($socket, $remote, $port+0)) && $attempts++ < $timeout) {
            $error = socket_last_error();

            if($error == 56) {
                $connected = true;
                break;
            }

            if ($error != SOCKET_EINPROGRESS && $error != SOCKET_EALREADY) {
                print_r("Error Connecting Socket(" . $error . "): ".socket_strerror($error));
                socket_close($socket);
                return NULL;
            }
            usleep(1000);
        }

        if (!$connected) {
            print_r("Error Connecting Socket: Connect Timed Out After $timeout seconds. ".socket_strerror(socket_last_error()));
            socket_close($socket);
            return NULL;
        }
       
        socket_set_block($socket);


        //($result);

        socket_write($socket, $encrypted_command, strlen($encrypted_command));

        // Decrypt the response, close the socket, and decode the JSON response

        $out = socket_read($socket, 2048);
		
        $final = $this->tplink_decrypt($out);
		/*
        while ($out = socket_read($socket, 2048)) {
            $final = $this->tplink_decrypt($out);
        }
*/
//print_r($final);

        socket_close($socket);

        // Decode the JSON response if there was one, otherwise fail
        if (isset($final)) {
            $final = json_decode($final);
        } else {
            return null;
        }

        // Return the object
        if (is_object($final)) {
            return $final;
        } else {
            return null;
        }
    }

// Create a function to get system information about a TP-Link SmartHome device
    function tplink_get_sysinfo ($host, $port) {

        // Define the simple command to get system info and send it
        $command = '{"system":{"get_sysinfo":null}}';
        $result = $this->send_command($host, $port, $command);

        // Return the object if it worked
        if ( (is_object($result)) && (isset($result->system->get_sysinfo->err_code)) && ($result->system->get_sysinfo->err_code === 0) ) {
            return $result->system->get_sysinfo;
        } else {
            return false;
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle($ip)
    {
        //$this->info("Done");

//        $tpManager = new TPLinkManager(config('TPLink'));
//        $tpDevice = $tpManager->device('lamp')->sendCommand(TPLinkCommand::getTime());

        //$tpDevice = $this->tplink_get_sysinfo($ip, "9999");
        //print_r($tpDevice);

        $tpDevice = $this->send_command($ip, "9999", '{"emeter":{"get_realtime":{}}}');
        if(!$tpDevice instanceof stdClass) {
            return null;
        }

        return [
            'power' => ($tpDevice->emeter->get_realtime->power_mw / 1000),
            'total' => ($tpDevice->emeter->get_realtime->total_wh / 1000)
        ];
    }
}