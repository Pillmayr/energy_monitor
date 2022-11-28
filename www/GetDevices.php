<?php

class GetDevices {

    public static function get()
    {
        $devices = [];
        $prefix = getenv('DEVICES_PREFIX');

        if(empty($prefix)) {
            return $devices;
        }

        $i = 0;
        while(true) {
            $name = getenv($prefix . '.' . $i . '.NAME');
            $ip   = getenv($prefix . '.' . $i . '.IP');

            if(!empty($name) && !empty($ip)) {
                array_push($devices, ['ip' => $ip, 'name' => $name]);
            } else {
                break;
            }
            $i++;
        }

        return $devices;
    }

}