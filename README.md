=====
Copyrights
=====

* (c) 2015 by Arnoud van Susteren (arnoud at nospam frontendstudio dot com)
* under a GNU General Public License

* The 'gapi.class.php' libary is taken from: https://github.com/erebusnz/gapi-google-analytics-php-interface.git
* Google Analytic API v3, under the Terms and Conditions of Google

=====
About
=====

 * With the {gapi} UDT you can display Google Analytics data on your CMS Made Simple website.

 * This code is using the Google Analytics PHP Interface v2.0.1:
    https://github.com/erebusnz/gapi-google-analytics-php-interface.git

 * For full Google Analytic API v3 documentation and code examples see:
    https://developers.google.com/analytics/devguides/reporting/core/v3/quickstart/service-php

 * This code implements a filter for the 10 most visited News module articles in the last 30 days,
   you can adapt the filter properties to suit your own needs:
   https://github.com/erebusnz/gapi-google-analytics-php-interface/blob/wiki/UsingFilterControl.md

 * This code has caching support is advisable to set a high cache_time for production websites

=====
Howto
=====

step 1) Download gapi.php and gapi.class.php to $root_path/lib/gapi/
        ! adjust the require on top of gapi.php to point to gapi.class.php

step 2) Register application under Google Cloud Console and generate a key.p12 authentication file

 * Before you can use this code you should register this application via Google Cloud Console
 * Please go to https://github.com/erebusnz/gapi-google-analytics-php-interface
   follow the steps in the bottom of this webpage

   In short:

 * Google Cloud Console (generate a key.p12 file): https://console.developers.google.com/
   - Login to the developer console
   - Create or Open the project, go to APIs & Auth > Credentials
   - Click on Create new Client ID, and select Service account and P12 key.
   - Then click on Create Client ID to download it.
   - key.p12 is used in the get_gapi_data function of this UDT copy it to $root_path/lib/gapi/ to link it correctly

step 3) Login to the /admin area of your website and add a User Defined Tag
        (Extensions -> User Defined Tags) with the name "gapi"

step 4) Add the below code (Which is linking to the gapi.class.php and gapi.php files).

* Edit User Defined Tag
Name: gapi
Code:

## copy/paste start

global $gCms;
$config = cmsms()->GetConfig();

$root_path =  $config['root_path'];

$include_class = "$root_path/lib/gapi/gapi.php";
include($include_class);

## copy/paste end

step 5) Call the gapi UDT by using the correct parameters:

 * ga_profile_id (number:required): Google Analytics ProfileId
 * ga_email (string:required): Google Analytics Email
 * max_results (number:optional): Number of page results to fetch
 * cache_time (number:optional): Number of seconds to cache the Google Analytics .json file
 * nocache (int:optional): nocache on or off, '0|1'
 * cache_path (string:optional): where to store the cache file relatively to $config['root_path']

** Example 1: (default)

{gapi ga_profile_id='XXXprofile_id' ga_email='XXXX@developer.gserviceaccount.com'}

or

{gapi ga_profile_id='XXXprofile_id' ga_email='XXXX@developer.gserviceaccount.com' max_results='10' nocache='0' cache_time='14400' cache_path='tmp/cache'}

** Example 2: (non-default)

{gapi ga_profile_id='XXXprofile_id' ga_email='XXXX@developer.gserviceaccount.com' max_results='5' nocache='0' cache_time='600' cache_path='tmp'}

step 6) Print out the Smarty data

 * results, totalResults, pageViews, visits, startDate, endDate

 == copy/paste start

<pre>
  {$gapi|@print_r}

  {if isset($gapi[0]->visits) }

  {foreach from=$gapi->results item='item' name='loop'}
    pagePath: {$item->pagePath}
    pageTitle: {$item->pageTitle}
    pageId: {$item->pageId}
    pageViews: {$item->pageViews}
    visits: {$item->visits}
  {/foreach}

  {*
    An example on how to integrate Google Analytics Data with CMSMS modules
    Don't do intensive operations in foreach loops!
  *}

  {*news action='detail' articleid=$item->pageId detailtemplate='gapi'*}

  totalResults: {$gapi->totalResults}
  pageViews: {$gapi->pageViews}
  visits: {$gapi->visits}
  startDate: {$gapi->startDate}
  endDate: {$gapi->endDate}

  {else}
     Gapi data unavailable!
  {/if}
</pre>

== copy/paste end