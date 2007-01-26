<?php
/**
 * DokuWiki Syntax Plugin LastFm
 *
 * Shows various statistics from the last.fm service for a given user.
 *
 * Syntax:  {{lastfm>[username]?[keyword] [keyword] [keyword]}}
 *
 *   [username] - a valid last.fm username
 *   [keyword]  - a space separated list of the following keywords:
 *                 - topartists
 *                 - topalbums
 *                 - toptracks
 *                 - tags
 *                 - friends
 *                 - neighbours
 *                 - recenttracks
 *                 - artistchart
 *                 - albumchart
 *                 - trackchart 
 * 
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Michael Klier <chi@chimeric.de>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DW_LF')) define('DW_LF',"\n");
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_lastfm extends DokuWiki_Syntax_Plugin {

    // UTC Offset
    var $_utc_offset;

    // Date format
    var $_dformat;

    /**
     * General Info
     */
    function getInfo(){
        return array(
            'author' => 'Michael Klier (chi)',
            'email'  => 'chi@chimeric.de',
            'date'   => '2006-12-07',
            'name'   => 'LastFm',
            'desc'   => 'Displays lastfm statistics for a given user',
            'url'    => 'http://www.chimeric.de/projects/dokuwiki/plugin/lastfm'
        );
    }

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     */
    function getType()  { return 'substition'; }
    function getPType() { return 'block'; }
    function getSort()  { return 312; }
    
    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{lastfm>[a-zA-Z0-9.\-_]*\?.*?}}',$mode,'plugin_lastfm');
    }

    /**
     * Handler to prepare matched data for the rendering process
     */
    function handle($match, $state, $pos, &$handler){

        $data = array();

        $match = substr($match,9,-2);

        list($user,$params) = explode('?',$match);

        $data['user']   = $user;

        // parse params
        if(preg_match('/\btopartists\b/',$params))   $data['params'][] = 'topartists';
        if(preg_match('/\btopalbums\b/',$params))    $data['params'][] = 'topalbums';
        if(preg_match('/\btoptracks\b/',$params))    $data['params'][] = 'toptracks';
        if(preg_match('/\btags\b/',$params))         $data['params'][] = 'tags';
        if(preg_match('/\bfriends\b/',$params))      $data['params'][] = 'friends';
        if(preg_match('/\bneighbours\b/',$params))   $data['params'][] = 'neighbours';
        if(preg_match('/\brecenttracks\b/',$params)) $data['params'][] = 'recenttracks';
        if(preg_match('/\bartistchart\b/',$params))  $data['params'][] = 'weeklyartistchart';
        if(preg_match('/\balbumchart\b/',$params))   $data['params'][] = 'weeklyalbumchart';
        if(preg_match('/\btrackchart\b/',$params))   $data['params'][] = 'weeklytrackchart';

        if(preg_match('/\bL=([0-9]{1,2})\b/',$params,$match)) {
            $data['limit'] = $match[1];
        } else {
            $data['limit'] = 10;
        }

        return ($data);
    }

    /**
     * Handles the actual output creation.
     */
    function render($mode, &$renderer, $data) {
        global $ID;
        global $lang;

        if($mode == 'xhtml'){
            // disable caching
            $renderer->info['cache'] = false;

            $renderer->doc .= '<div id="plugin_lastfm">' . DW_LF;
            foreach($data['params'] as $param) {
                $renderer->doc .= '  <span class="plugin_lastfm_charttype">' . $this->getLang($param) . '</span>' . DW_LF;
                $renderer->doc .= '  <div class="plugin_lastfm_chart" id="plugin_lastfm_' . $param . '"></div>' . DW_LF;
            }
            $renderer->doc .= '</div>' . DW_LF;

            // print javascript
            $renderer->doc .= '<script type="text/javascript" language="javascript">' . DW_LF;
            $renderer->doc .= 'var plugin_lastfm_user = "' . $data['user'] . '";' . DW_LF;
            $renderer->doc .= 'var plugin_lastfm_limit = "' . $data['limit'] . '";' . DW_LF;
            $renderer->doc .= 'var plugin_lastfm_utc_offset = "' . $this->getConf('utc_offset') . '";' . DW_LF;
            $renderer->doc .= 'var plugin_lastfm_dformat = "' . $this->getConf('dformat') . '";' . DW_LF;
            $renderer->doc .= '</script>' . DW_LF;

            return true;
        }
        return false;
    }
}
//setup vim:ts=4:sw=4:enc=utf-8:
