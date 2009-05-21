<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'action.php');

class action_plugin_lastfm extends DokuWiki_Action_Plugin{

    function getInfo() {
        return array(
                'author' => 'Michael Klier',
                'email'  => 'chi@chimeric.de',
                'date'   => @file_get_contents(DOKU_PLUGIN.'lastfm/VERSION'),
                'name'   => 'LastFM Plugin (action component)',
                'desc'   => 'Display Last.fm stats on a wiki page',
                'url'    => 'http://dokuwiki.org/plugin:lastfm',
                );
    }

    function register(&$contr) {
        $contr->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call', array());
    }

    /**
     * Preview Comments
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function handle_ajax_call(&$event, $params) {
        if($event->data != 'plugin_lastfm') return;
        $event->preventDefault();
        $event->stopPropagation();

        require_once(DOKU_PLUGIN.'lastfm/inc/lastfmutils.php');

        // import variables
        $chart   = $_REQUEST['plugin_lastfm_chart'];
        $user    = $_REQUEST['plugin_lastfm_user'];
        $limit   = $_REQUEST['plugin_lastfm_limit'];
        $cols    = $_REQUEST['plugin_lastfm_cols'];
        $imgonly = $_REQUEST['plugin_lastfm_imgonly'];

        $dformat    = $this->getConf('dformat');
        $utc_offset = $this->getconf('utc_offset');

        // maybe the whole thing could be done in a cleaner fashion
        $chart = preg_replace("/plugin_lastfm_/",'',$chart);

        // get chart
        lastfm_xhtml($user, $chart, $limit, $dformat, $utc_offset, $cols, $imgonly);
    }
}
  
// vim:ts=4:sw=4:et:enc=utf-8:
