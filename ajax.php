<?php
/**
 * AJAX Handler of the lastfm plugin
 * 
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michael Klier <chi@chimeric.de>
 */

if(!count($_POST) && $HTTP_RAW_POST_DATA){
  parse_str($HTTP_RAW_POST_DATA, $_POST);
}
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_PLUGIN.'lastfm/inc/lastfmutils.php');

//close session
session_write_close();

// import variables
$chart = $_REQUEST['chart'];
$user  = $_REQUEST['user'];
$limit = $_REQUEST['limit'];
$dformat = $_REQUEST['dformat'];
$utc_offset = $_REQUEST['utc_offset'];
$cols = $_REQUEST['cols'];
$imgonly = $_REQUEST['imgonly'];

// maybe the whole thing could be done in a cleaner fashion
$chart = preg_replace("/plugin_lastfm_/",'',$chart);

// get chart
lastfm_xhtml($user,$chart,$limit,$dformat,$utc_offset,$cols,$imgonly);

// vim:ts=4:sw=4:et:enc=utf-8:
?>
