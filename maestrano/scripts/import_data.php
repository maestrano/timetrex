<?php

// Fetches all the updates from Connec! since last synchronization timestamp

if(!Maestrano::param('connec.enabled')) { return false; }

set_time_limit(0);

// Last update timestamp
$timestamp = lastDataUpdateTimestamp();
$date = date('c', $timestamp);
$current_timestamp = round(microtime(true));

error_log("Fetching data updates since $date");

// Fetch updates
$client = new Maestrano_Connec_Client();
$subscriptions = Maestrano::param('webhook.connec.subscriptions');
foreach ($subscriptions as $entity => $enabled) {
  if(!$enabled) { continue; }
  
  // Fetch first page of entities since last update timestamp
  $params = array("\$filter" => "updated_at gte '$date'");
  $result = fetchData($client, $entity, $params);
  // Fetch next pages
  while(array_key_exists('pagination', $result) && array_key_exists('next', $result['pagination'])) {
    $result = fetchData($client, $result['pagination']['next']);
  }
}

// Set update timestamp
setLastDataUpdateTimestamp($current_timestamp);

// Fetches and import data from specified entity
function fetchData($client, $entity, $params=array()) {
  $msg = $client->get($entity, $params);
  $code = $msg['code'];
  $body = $msg['body'];

  if($code != 200) {
    error_log("Cannot fetch connec entities=$entity, code=$code, body=$body");
    return array();
  } else {
    error_log("Received entities=$entity, code=$code");
    $result = json_decode($body, true);

    // Dynamically find mappers and map entities
    foreach(BaseMapper::getMappers() as $mapperClass) {
      if (class_exists($mapperClass)) {
        $test_class = new ReflectionClass($mapperClass);
        if($test_class->isAbstract()) { continue; }

        $mapper = new $mapperClass();
        $mapper->persistAll($result[$mapper->getConnecResourceName()]);
      }
    }

    return $result;
  }
}