<?php
require 'vendor/autoload.php';

Flight::route('/', function(){
    echo 'hello world!';
});

Flight::route('/@device/@rom_type(/@flag:lastest)', function($device, $rom_type, $flag){
    $data = getOTA($device, $rom_type);
    if ($flag == 'lastest') {
        $temp = $data[0];
        unset($data);
        $data []= $temp;
    }
    switch($rom_type) {
        case 'los':
            $result = array_map('formatLOS', $data);
            break;
        case 'aicp':
            $result = array_map('formatAICP', $data);
            break;
        case 'mokee':
            $result = array_map('formatMoKee', $data);
            break;
    }
    if ($flag == 'lastest') $result = $result[0];
    Flight::json($result);
});

Flight::route('*', function(){
    Flight::halt(400, '<h1>Bad Request</h1>');
});

Flight::start();

function getOTA($device, $type) {
    switch($type) {
        case 'los':
            $url = "https://download.lineageos.org/api/v1/$device/nightly/get";
            break;
        case 'aicp':
            $url = "http://updates.aicp-rom.com/update.php?device=$device";
            break;
        case 'mokee':
            $url = 'http://ota.mokeedev.com/full.php';
            $data = "device_name=$device&device_officail=1&device_version=mk";
            break;
        default:
            return 'Unknown Error';
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($type == 'mokee') curl_setopt_array($ch, array(CURLOPT_POST => true,CURLOPT_POSTFIELDS => $data));
    $data = json_decode(curl_exec($ch),true);
    switch($type) {
        case 'los':
            return $data['response'];
            break;
        case 'aicp':
            return $data['updates'];
            break;
        default:
            return $data;
    }
}

function formatLOS($data) {
    $result['name'] = $data['filename'];
    $result['md5'] = '暂无';
    $result['url'] = $data['url'];
    $result['size'] = '暂无';
    $result['log'] = '暂无';
    return $result;
}

function formatAICP($data) {
    $result['name'] = $data['name'];
    $result['md5'] = $data['md5'];
    $result['url'] = $data['url'];
    $result['size'] = $data['size'].' MB';
    $result['log'] = $data['url'] . '.html';
    return $result;
}

function formatMoKee($data) {
    $result['name'] = $data['name'];
    $result['md5'] = $data['md5'];
    $result['url'] = str_replace('dl.php', 'vip-dl.php', $data['rom']);
    $result['size'] = formatBytes($data['length']);
    $result['log'] = $data['log'];
    return $result;
}

function formatBytes($size) { 
    $units = array(' B', ' KB', ' MB', ' GB', ' TB'); 
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024; 
    return round($size, 2).$units[$i]; 
}