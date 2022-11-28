<?php

require_once 'CurlReader.php';

class CheckSmartmeter {

    private $reader;

    public function __construct() {
        $this->reader = new CurlReader();
    }

    public function handle($ip, $port, $file) {

        $response = $this->reader->read(
            sprintf('http://%s:%d/%s', $ip, $port, $file)
        );

        if(!empty($response)) {

            $sanitized = [];

            $cnt = preg_match_all("/^([^:]+)\:\s*([\-0-9\.]+)$/im", $response, $matches);
            if($cnt > 0) {
                foreach($matches[1] as $idx => $name) {
                    $sanitized[$name] = floatval($matches[2][$idx]);
                }
            }
            
            return $sanitized;
        }

        return null;
    }

}