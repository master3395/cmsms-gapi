<?php

# (c) 2015 by Arnoud van Susteren (arnoud@frontendstudio.com)
# under a GNU General Public License

# README for documentation and howto!

###############################################################################
# dependencies
###############################################################################

if(!is_object(cmsms())) exit;

$config     = cmsms()->GetConfig();
$sub_smarty = cmsms()->GetSmarty();

require dirname(__FILE__) . '/composer_vendor/gapi-google-analytics-php-interface/gapi.class.php';

###############################################################################
# config
###############################################################################

$debug                = 0;

$gapi                 = new stdClass();
$gapi->dimensions     = array('pagePath', 'pageTitle');
$gapi->metrics        = array('pageviews', 'visits');
$gapi->sort_metric    = '-visits';
$gapi->filter         = 'pagePath =~ ^/news/ && pagePath != /news/ && visits > 1';
$gapi->start_date     = date('Y-m-d', strtotime("-30 days"));
$gapi->end_date       = date('Y-m-d', strtotime("-1 days"));
$gapi->start_index    = 1;

###############################################################################
# params
###############################################################################

$gapi->ga_profile_id  = is_int($params['ga_profile_id']) ? $params['ga_profile_id'] : '';
$gapi->ga_email       = isset($params['ga_email']) ? $params['ga_email'] : '';
$gapi->max_results    = isset($params['max_results']) ? $params['max_results'] : 10;

$nocache              = is_int($params['nocache']) ? $params['nocache'] : 0;
$cache_time           = isset($params['cache_time']) ? $params['cache_time'] : 600;
$cache_path           = isset($params['cache_path']) ? $params['cache_path'] : $config['root_path'].DIRECTORY_SEPARATOR.'tmp/cache';
$cache_filename       = isset($params['cache_filename']) ? $params['cache_filename'] : 'gapi';

$cache_file           = $cache_path. '/'. $cache_filename.'.json';

if ($gapi->ga_profile_id == '' || $gapi->ga_email == '') {
  if ($debug) {
    print "ga_profile_id and ga_email are required!\n";
  }
  return;
}

###############################################################################
# program
###############################################################################

$gapi_data = '';

if ($debug) {
  print $cache_file . "\n";
}

if (file_exists($cache_file)) {
  $fh = fopen($cache_file, 'r');
  $written_time = trim(fgets($fh));

  $cache_time_string = '-'. $cache_time . ' seconds';

  if ($written_time > strtotime($cache_time_string) && $nocache == 0) {
    if ($debug) { print "read cache file \n"; }
    $json = fread($fh, filesize($cache_file));
    fclose($fh);
  } else {
    // unlink cache_file
    fclose($fh);
    unlink($cache_file);

    if ($debug) { print "get_gapi_data after cache is expired or cache is disabled \n"; }
    $gapi_data = get_gapi_data($gapi);
    $json = json_encode($gapi_data);

    $fh = fopen($cache_file, 'w+');
    fwrite($fh, time() . "\n");
    fwrite($fh, $json);
    fclose($fh);
  }

} else {
  if ($debug) { print "get_gapi_data cache file does not exist \n"; }
  $gapi_data = get_gapi_data($gapi);
  $json = json_encode($gapi_data);

  $fh = fopen($cache_file, 'w+');
  fwrite($fh, time() . "\n");
  fwrite($fh, $json);
  fclose($fh);
}

$php_data = json_decode($json);

if ($debug) {
  print_r($php_data);
}

$sub_smarty->assign('gapi', $php_data);

return;

###############################################################################
# subroutines
###############################################################################

function get_gapi_data($gapi) {

  $ga = new gapi($gapi->ga_email, "../../key.p12");

  $ga->requestReportData($gapi->ga_profile_id, $gapi->dimensions, $gapi->metrics, $gapi->sort_metric, $gapi->filter, $gapi->start_date, $gapi->end_date, $gapi->start_index, $gapi->max_results);

  $gapi_data  = new stdClass();
  $results    = array();

  foreach($ga->getResults() as $result) {
    $obj              = new stdClass();
    $obj->pagePath    = $result->getpagePath();
    $obj->pageTitle   = $result->getpageTitle();
    $resultList       = explode('/', $result->getpagePath());
    $obj->pageId      = $resultList[2];
    $obj->pageViews   = $result->getPageviews();
    $obj->visits      = $result->getVisits();
    $results[]        = $obj;
  }

  $gapi_data->results = $results;
  $gapi_data->totalResults = $ga->getTotalResults();
  $gapi_data->pageViews = $ga->getPageviews();
  $gapi_data->visits = $ga->getVisits();
  $gapi_data->startDate = $ga->getStartDate();
  $gapi_data->endDate = $ga->getEndDate();

  return $gapi_data;

}

###############################################################################

#
# EOF
#
?>
