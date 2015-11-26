<?php

require dirname(__FILE__) . '/composer_vendor/gapi-google-analytics-php-interface/gapi.class.php';

// ga_email and key.p12 generated via: https://console.developers.google.com

$ga = new gapi("XXXX@developer.gserviceaccount.com'", "key.p12");
$ga->requestAccountData();
foreach($ga->getAccounts() as $result)
{
  echo $result . ' ' . $result->getId() . ' (ga_profile_id:' . $result->getProfileId() . ")<br />";
}
