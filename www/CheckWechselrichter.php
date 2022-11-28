<?php

require_once 'CurlReader.php';

class CheckWechselrichter {

    private $reader;

    public function __construct() {
        $this->reader = new CurlReader();
    }

    public function handle($ip) {

        $response = $this->reader->read(
            sprintf('http://%s//solar_api/v1/GetPowerFlowRealtimeData.fcgi', $ip)
        );

        if($response) {
            $response = json_decode($response, true);
            if(isset($response['Body']['Data']['Inverters'][1])) {
                $inverterData = $response['Body']['Data']['Inverters'][1];

                return [
                    'Total' => round($inverterData['E_Total'] / 1000, 2 ),
                    'Year'  => round($inverterData['E_Year'] / 1000, 2 ),
                    'Day'   => round($inverterData['E_Day'] / 1000, 2),
                    'Peak'  => $inverterData['P']
                ];
            }


        }

        return null;
    }

}