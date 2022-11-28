<?php

require_once 'CurlReader.php';

class CheckWasserzaehler {

    private $reader;

    public function __construct() {
        $this->reader = new CurlReader();
    }

    public function handle($ip) {

        $response = $this->reader->read(
            sprintf('http://%s/wasserzaehler.html', $ip)
        );

        preg_match('/[0-9]+\.[0-9]+/', $response, $matches);
        if(count($matches) == 1) {
            return $matches[0];
        } else {
            return null;
        }
    }

}