<?php
require 'vendor/autoload.php';

Flight::route('/@device/@rom_type(/@flag:lastest)', function($device, $rom_type, $flag){
    if (!in_array($rom_type, array('aicp', 'los', 'mokee'))) Flight::halt(400, Flight::json(array('msg' => "$rom_type: No such ROM")));
    $data = getOTA($device, $rom_type);
    if (array_key_exists('msg', $data)) Flight::halt(400, Flight::json($data));
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
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($type == 'mokee') curl_setopt_array($ch, array(CURLOPT_POST => true,CURLOPT_POSTFIELDS => $data));
    $data = json_decode(curl_exec($ch),true);
    switch($type) {
        case 'los':
            if ($data['response']) return $data['response'];
            break;
        case 'aicp':
            if (!array_key_exists('error', $data)) return $data['updates'];
            break;
        case 'mokee':
            if ($data) return $data;
            break;
        default:
            return array('msg' => 'Unknow Error');
    }
    return array('msg' => "$device: No such device");
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

function getSize($url) {
     $ch = curl_init($url);

     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($ch, CURLOPT_HEADER, TRUE);
     curl_setopt($ch, CURLOPT_NOBODY, TRUE);

     $data = curl_exec($ch);
     $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

     curl_close($ch);
     return formatBytes($size);
}