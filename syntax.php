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
 *                 - L=n (limit number of records)
 *                 - C=n (limit numer of columns)
 *                 - IMGONLY (show only images in topalbums)
 * 
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Michael Klier <chi@chimeric.de>
 */
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();

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
            'date'   => @file_get_contents(DOKU_PLUGIN.'lastfm/VERSION'),
            'name'   => 'LastFM Plugin (syntax component)',
            'desc'   => 'Displays lastfm statistics for a given user',
            'url'    => 'http://dokuwiki.org/plugin:lastfm'
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
    function handle($match, $state, $pos, Doku_Handler $handler){

        $data   = array();
        $charts = array('topartists', 'topalbums', 'toptracks', 'tags', 'friends',
                        'neighbours', 'recenttracks', 'artistchart', 'albumchart',
                        'trackchart', 'profile');

        $match = substr($match,9,-2);

        list($user,$params) = explode('?',$match);

        $data['user'] = $user;
        $params = explode(' ', $params);

        $data['charts']  = array();
        $data['limit']   = 10;
        $data['cols']    = 5;
        $data['imgonly'] = 0;
        
        foreach($params as $param) {
            if(in_array($param, $charts)) {
                if($param == 'artistchart' || $param == 'albumchart' || $param == 'trackchart') {
                    array_push($data['charts'], 'weekly'.$param);
                } else {
                    array_push($data['charts'], $param);
                }
            } else {
                if(@preg_match('/\bL=([0-9]{1,2})\b/', $param, $match)) $data['limit'] = $match[1];
                elseif(@preg_match('/\bC=([0-9]{1})\b/', $param, $match)) $data['cols'] = $match[1];
                elseif(@preg_match('/\bIMGONLY\b/', $param, $match)) $data['imgonly'] = 1;
            }
        }

        return ($data);
    }

    /**
     * Handles the actual output creation.
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        global $ID;
        global $lang;

        if($mode == 'xhtml'){
            // disable caching
            $renderer->info['cache'] = false;

            $renderer->doc .= '<div class="plugin_lastfm">' . DW_LF;

            $renderer->doc .= '  <ul class="plugin_lastfm_opts">' . DOKU_LF;
            $renderer->doc .= '     <li class="plugin_lastfm_opt"><span class="plugin_lastfm_user">' . $data['user'] . '</span></li>' . DOKU_LF;
            $renderer->doc .= '     <li class="plugin_lastfm_opt"><span class="plugin_lastfm_limit">' . $data['limit'] . '</span></li>' . DOKU_LF;
            $renderer->doc .= '     <li class="plugin_lastfm_opt"><span class="plugin_lastfm_cols">' . $data['cols'] . '</span></li>' . DOKU_LF;
            $renderer->doc .= '     <li class="plugin_lastfm_opt"><span class="plugin_lastfm_imgonly">' . $data['imgonly'] . '</span></li>' . DOKU_LF;
            $renderer->doc .= '  </ul>'. DOKU_LF;

            foreach($data['charts'] as $chart) {
                $renderer->doc .= '  <span class="plugin_lastfm_charttype">last.fm ' . $this->getLang($chart) . '</span>' . DW_LF;
                $renderer->doc .= '  <div class="plugin_lastfm_chart" id="plugin_lastfm_' . $chart . '"></div>' . DW_LF;
            }

            $renderer->doc .= '</div>' . DW_LF;

            return true;
        }
        return false;
    }
}
// vim:ts=4:sw=4:et:enc=utf-8:
