<?php

include 'setenvs.php';
include 'CheckAdapter.php';
include 'CheckWasserzaehler.php';
include 'CheckWechselrichter.php';
include 'CheckSmartmeter.php';
include 'ScrapeFile.php';
include 'CheckTapo.php';

$tpLink = new CheckAdapter();
$wasserzaehler = new CheckWasserzaehler();
$wechselrichter = new CheckWechselrichter();
$scapeFile = new ScrapeFile();
$smartmeter = new CheckSmartmeter();
$tapo = new CheckTapo();

$devicesFile = null;
if(is_file("/etc/devices.ini")) {
    $devicesFile = "/etc/devices.ini";
} else if(is_file("/var/www/devices.ini")) {
    $devicesFile = "/var/www/devices.ini";
} else if(is_file("../devices.ini")) {
    $devicesFile = "../devices.ini";
} else {
    die(1);
}

$devices = parse_ini_file($devicesFile, true);

foreach($devices as $device) {
    switch($device['type']) {
        case 'wechselrichter':
            $data = $wechselrichter->handle($device['host']);
            if(is_array($data)) {
                $scapeFile->gauge("fronius_year", $data['Year'], ["name" => $device['name']]);
                $scapeFile->gauge("fronius_day", $data['Day'], ["name" => $device['name']]);
                $scapeFile->gauge("fronius_peak", $data['Peak'], ["name" => $device['name']]);
                $scapeFile->counter("fronius_total", $data['Total'], ["name" => $device['name']]);
            }
            break;

        case 'wasser':
            $data = $wasserzaehler->handle($device['host']);
            if(is_numeric($data)) {
                $scapeFile->counter("watermeter", $data, ["name" => $device['name']]);
            }
            break;

        case 'tp_link':
            $data = $tpLink->handle($device['host']);
            if(is_array($data)) {
                $scapeFile->gauge("tplink_power", $data['power'], ["name" => $device['name']]);
                $scapeFile->counter("tplink_total", $data['total'], ["name" => $device['name']]);
            }
            break;

        case 'smartmeter':
            $data = $smartmeter->handle($device['host'], $device['port'], $device['file']);
            if(is_array($data) && count($data) > 0) {
                $scapeFile->counter("smartmeter_wirkenergie_in", $data['Wirkenergie+'], ["name" => $device['name']]);
                $scapeFile->counter("smartmeter_wirkenergie_out", $data['Wirkenergie-'], ["name" => $device['name']]);
                $scapeFile->gauge("smartmeter_leistung_in", $data['Momentanleistung+'], ["name" => $device['name']]);
                $scapeFile->gauge("smartmeter_leistung_out", $data['Momentanleistung-'], ["name" => $device['name']]);
                $scapeFile->gauge("smartmeter_leistungsfaktor", $data['Leistungsfaktor'], ["name" => $device['name']]);
                $scapeFile->gauge("smartmeter_momentanleistung", $data['Momentanleistung'], ["name" => $device['name']]);
            }
            break;
			
		case 'tapo':
			$data = $tapo->handle($device['host'], $device['login'], $device['password']);
            if(is_array($data)) {
                $scapeFile->gauge("tplink_power", $data['power'], ["name" => $device['name']]);
                $scapeFile->counter("tplink_total", $data['total'], ["name" => $device['name']]);
            }
			break;
    }
}

file_put_contents("/tmp/energy_scrape_file", $scapeFile->printFile());