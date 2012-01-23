<?php

$user_agent = 'RelationshipsBot/0.1';
//$base = 'http://thedatahub.org/api/rest/';
$base = 'http://test.ckan.net/api/rest/';
$group = 'lodcloud';

function get_datasets($base, $group = null) {
    if ($group) {
        trigger_error("Fetching group '$group'");
        $url = $base . 'group/' . $group;
        $json = get_json($url);
        return $json->packages;
    } else {
        trigger_error("Fetching dataset list");
        $url = $base . 'dataset';
        return get_json($url);
    }
}

function get_links($base, $dataset) {
    trigger_error("Fetching dataset '$dataset'");
    $url = $base . 'dataset/' . $dataset;
    $json = get_json($url);
    $results = array();
    foreach ($json->extras as $key => $value) {
        if (!preg_match('/^links:(.*)/', $key, $match)) continue;
        $results[$match[1]] = (int) $value;
    }
    return $results;
}

function filter_links($dataset, $links, $all_datasets) {
    foreach (array_keys($links) as $target) {
        if (in_array($target, $all_datasets)) continue;
        unset($all_datasets[$target]);
        trigger_error("Unknown target dataset '$target' in '$dataset'", E_USER_WARNING);
    }
    return $links;
}

function save_links($base, $dataset, $links) {
    $linktype = 'depends_on';
    foreach ($links as $target => $count) {
        $data = array('subject' => $dataset, 'object' => $target,
            'type' => $linktype, 'comment' => "Count: $count");
        $url = $base . "dataset/$dataset/relationships";
        post_json($url, $data);
    }
}

function get_json($url) {
    global $user_agent;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        var_dump(curl_error($ch)); die();
    }
    curl_close($ch);
    return json_decode($result);
}

function post_json($url, $data) {
var_dump($url);
    global $user_agent;
    $json = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        var_dump(curl_error($ch)); die();
    }
    curl_close($ch);
    var_dump($json);
    var_dump($result);
    die();
}

function put_json($url, $data) {
    global $user_agent;
    $json = json_encode($data);
    $fp = fopen('php://temp/maxmemory:256000', 'w');
    if (!$fp) {
        die('could not open temp memory data');
    }
    fwrite($fp, $json);
    fseek($fp, 0); 

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($json));
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        var_dump(curl_error($ch)); die();
    }
    curl_close($ch);
    var_dump($json);
    var_dump($result);
    die();
}

function error_handler($level, $message, $file, $line, $context) {
    if ($level === E_USER_NOTICE || $level === E_USER_WARNING) {
        echo $message . "\n";
        return true;
    }
    return false;
}
set_error_handler('error_handler');

$all_datasets = get_datasets($base);
//$lodcloud = get_datasets($base, $group);
$links = get_links($base, 'dbpedia');
$links = filter_links('dbpedia', $links, $all_datasets);
save_links($base, 'dbpedia', $links);
var_dump($links);
