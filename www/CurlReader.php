<?php

class CurlReader {

    public function read($url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_TIMEOUT,1000);
        $response = curl_exec($ch);
        curl_close($ch);
        unset($ch);

        return $response;
    }

}