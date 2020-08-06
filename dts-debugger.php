<?php
/*
Plugin Name: Scrape and Debug
Plugin URI: https://www.missionmike.dev/scrape-and-debug-wordpress-plugin/
Description: Simplify page debugging via Facebook Sharing Debugger, LinkedIn Post Inspector, Google's Structured Data Testing Tool and Rich Results Test, PageSpeed Insights, W3C Validation, and Google AMP Test.
Version: 0.5.4
Author: Michael R. Dinerstein (Mission Mike)
Author URI: https://www.missionmike.dev/
License: GPL2
*/

defined('ABSPATH') or die('No script kiddies please!');

define('DTS_DBGGR_PLUGIN_BASENAME', plugin_basename(__FILE__));

include('include/data.php');

include('include/enqueue.php');

include('include/setup.php');

include('include/settings.php');

include('include/metabox.php');
