<?php
/**
 * Syntax Plugin LastFm
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Klier <chi@chimeric.de>
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


    /**
     * General Info
     */
    function getInfo(){
        return array(
            'author' => 'Michael Klier (chi)',
            'email'  => 'chi@chimeric.de',
            'date'   => '2006-07-10',
            'name'   => 'LastFm',
            'desc'   => 'Displays lastfm statistics for a given user',
            'url'    => 'http://www.chimeric.de/dokuwiki/plugins/lastfm'
        );
    }

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     */
    function getType(){ return 'substition'; }
    function getPType() { return 'block'; }
    function getSort() { return 312; }
    
    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{lastfm>[a-zA-Z0-9.-_]*\?.*?}}',$mode,'plugin_lastfm');
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
        if(preg_match('/\trackchart\b/',$params))    $data['params'][] = 'weeklytrackchart';

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

        if($mode == 'xhtml'){
            $renderer->info['cache'] = false;
            $renderer->doc .= '<div id="plugin_lastfm">' . DW_LF;
            $renderer->doc .= $this->_render_data($data);
            $renderer->doc .= '</div>' . DW_LF;
            return true;
        }
        return false;
    }

    /**
     * creates final the output from the fetched data
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _render_data($data) {
        require_once(DOKU_INC.'inc/html.php');
        $ret = '';
        $lastfm_data = $this->_fetch_data($data);

        foreach($lastfm_data as $key => $val) {
            $ret .= '<div class="plugin_lastfm_'.$key.'">' . DW_LF;
            $ret .= '  <div class="plugin_lastfm_type">' . $this->getLang($key). ':</div>' . DW_LF;

            if(array_key_exists('error',$lastfm_data[$key])) {
                $ret .= $lastfm_data[$key]['error']['status'] . ':' . $lastfm_data[$key]['error']['resp_body'] . DW_LF;
            } else {
                $items = $this->_get_items($lastfm_data[$key],$data['limit']);
                if(!empty($items)) {
                    $this->_add_list_level($items);
                    $ret .= html_buildlist($items, 'plugin_lastfm plugin_lastfm_'.$key, array($this,'_render_item'));
                } else {
                    $ret .= $this->getLang('nothing');
                }
            }

            $ret .= '</div>' . DW_LF;
        }

        return ($ret);
    }

    /**
     * checks if only one item exists and reduces the array
     * to the value given with limit
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _get_items($lastfm_data,$limit) {
        $ret = array();
        $key = array_keys($lastfm_data);
        if(@array_key_exists('url',$lastfm_data[$key[0]])) {
            $ret[0] = $lastfm_data[$key[0]];
        } else {
            $ret = @array_slice($lastfm_data[$key[0]],0,$limit);
        }
        return ($ret);
    }

    /**
     * adds the list level which is need by html_buildlist
     * to the array elements
     * 
     * @author M9chael Klier <chi@chimeric.de>
     */
    function _add_list_level(&$array,$level=1) {
        $num = count($array);
        for($i=0;$i<$num;$i++) {
           $array[$i]['level'] = $level; 
        }
    }

    /**
     * callback function for html_buildlist
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _render_item($item) {
        $ret .= '<span class="li">' . DW_LF;

        if(array_key_exists('rank',$item)) {
            $ret .= '  <span class="plugin_lastfm_rank">'.$item['rank'].'.</span> ' . DW_LF;
        }

        if(array_key_exists('artist',$item)) {
            $ret .= '  <a href="http://www.last.fm/music/'.$item['artist'].'" class="extern">'.$item['artist'].'</a> &middot; ' . DW_LF;
        }

        if(array_key_exists('url',$item)) {
            $ret .= '  <a href="'.$item['url'].'" class="extern">';
            if(array_key_exists('attributes',$item)) {
                $ret .= $item['attributes']['username'];
            } else {
                $ret .= $item['name'];
            }
            $ret .= '</a> ' . DW_LF;
        }

        if(array_key_exists('playcount',$item)) {
            $ret .= '  <span class="plugin_lastfm_playcount">('.$item['playcount'] . ')</span>' .  DW_LF;
        }

        if(array_key_exists('date',$item)) {
            $ret .= '  <span class="plugin_lastfm_date">('.$item['date'] . ')</span>' . DW_LF;
        }

        $ret .= '</span>' . DW_LF;

        return ($ret);
    }

    /**
     * fetches the xml from lastfm and turns it into 
     * an assoziative array
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _fetch_data($data) {

        $lastfm_data = array();
        
        $http = new DokuHTTPClient();

        // the url for the audioscrobbler service
        $url = 'http://ws.audioscrobbler.com/1.0/user/'.$data['user'].'/'; 

        // fetch everything
        foreach($data['params'] as $param) {
            $xml_url = $url.$param.'.xml';
            $xml_raw = $http->get($xml_url);
            if($http->status != 200) {
                print "hello";
                $lastfm_data[$param]['error']['status']     = $http->status;
                $lastfm_data[$param]['error']['resp_body']  = $http->resp_body;
            } else {
                $lastfm_data[$param] = $this->_xmlToArray($xml_raw);
            }
        }

        return $lastfm_data;
    }

    /**
     * This static method converts an xml file to an associative array 
     * duplicating the xml file structure.
     *
     * @param    $fileName. String. The name of the xml file to convert. 
     *             This method returns an Error object if this file does not 
     *             exist or is invalid.
     * @param    $includeTopTag. booleal. Whether or not the topmost xml tag 
     *             should be included in the array. The default value for this is false.
     * @param    $lowerCaseTags. boolean. Whether or not tags should be 
     *            set to lower case. Default value for this parameter is true.
     * @access    public static
     * @return    Associative Array
     * @author    Jason Read <jason@ace.us.com>
     * @author    Michael Klier <chi@chimeric.de> 
     */
    function & _xmlToArray($xml_raw, $includeTopTag = false, $lowerCaseTags = true)
    {
        $p = xml_parser_create();
        xml_parse_into_struct($p,$xml_raw,$vals,$index);
        xml_parser_free($p);
        $xml = array();
        $levels = array();
        $multipleData = array();
        $prevTag = "";
        $currTag = "";
        $topTag = false;
        foreach ($vals as $val)
        {
            // Open tag
            if ($val["type"] == "open")
            {
                if (!$this->_xmlFileToArrayOpen($topTag, $includeTopTag, $val, $lowerCaseTags, 
                                               $levels, $prevTag, $multipleData, $xml))
                {
                    continue;
                }
            }
            // Close tag
            else if ($val["type"] == "close")
            {
                if (!$this->_xmlFileToArrayClose($topTag, $includeTopTag, $val, $lowerCaseTags, 
                                                $levels, $prevTag, $multipleData, $xml))
                {
                    continue;
                }
            }
            // Data tag
            else if ($val["type"] == "complete" && isset($val["value"]))
            {
                $loc =& $xml;
                foreach ($levels as $level)
                {
                    $temp =& $loc[str_replace(":arr#", "", $level)];
                    $loc =& $temp;
                }
                $tag = $val["tag"];
                if ($lowerCaseTags)
                {
                    $tag = strtolower($val["tag"]);
                }
                $loc[$tag] = str_replace("\\n", "\n", $val["value"]);
            }
            // Tag without data
            else if ($val["type"] == "complete")
            {
                $this->_xmlFileToArrayOpen($topTag, $includeTopTag, $val, $lowerCaseTags, 
                                          $levels, $prevTag, $multipleData, $xml);
                $this->_xmlFileToArrayClose($topTag, $includeTopTag, $val, $lowerCaseTags, 
                                          $levels, $prevTag, $multipleData, $xml);
            }
        }
        return $xml;
    }

    /**
     * Private support function for xmlFileToArray. Handles an xml OPEN tag.
     *
     * @param    $topTag. String. xmlFileToArray topTag variable
     * @param    $includeTopTag. boolean. xmlFileToArray includeTopTag variable
     * @param    $val. String[]. xmlFileToArray val variable
     * @param    $currTag. String. xmlFileToArray currTag variable
     * @param    $lowerCaseTags. boolean. xmlFileToArray lowerCaseTags variable
     * @param    $levels. String[]. xmlFileToArray levels variable
     * @param    $prevTag. String. xmlFileToArray prevTag variable
     * @param    $multipleData. boolean. xmlFileToArray multipleData variable
     * @param    $xml. String[]. xmlFileToArray xml variable
     * @access    private static
     * @return    boolean
     * @author    Jason Read <jason@ace.us.com>
     */
    function _xmlFileToArrayOpen(& $topTag, & $includeTopTag, & $val, & $lowerCaseTags, 
                                 & $levels, & $prevTag, & $multipleData, & $xml)
    {
        // don't include top tag
        if (!$topTag && !$includeTopTag)
        {
            $topTag = $val["tag"];
            return false;
        }
        $currTag = $val["tag"];
        if ($lowerCaseTags)
        {
            $currTag = strtolower($val["tag"]);
        }
        $levels[] = $currTag;
        
        // Multiple items w/ same name. Convert to array.
        if ($prevTag === $currTag)
        {
            if (!array_key_exists($currTag, $multipleData) || 
                !$multipleData[$currTag]["multiple"])
            {
                $loc =& $xml;
                foreach ($levels as $level)
                {
                    $temp =& $loc[$level];
                    $loc =& $temp;
                }
                $loc = array($loc);
                $multipleData[$currTag]["multiple"] = true;
                $multipleData[$currTag]["multiple_count"] = 0;
            }
            $multipleData[$currTag]["popped"] = false;
            $levels[] = ":arr#" . ++$multipleData[$currTag]["multiple_count"];
        }
        else
        {
            $multipleData[$currTag]["multiple"] = false;
        }
        
        // Add attributes array
        if (array_key_exists("attributes", $val))
        {
            $loc =& $xml;
            foreach ($levels as $level)
            {
                $temp =& $loc[str_replace(":arr#", "", $level)];
                $loc =& $temp;
            }
            $keys = array_keys($val["attributes"]);
            foreach ($keys as $key)
            {
                $tag = $key;
                if ($lowerCaseTags)
                {
                    $tag = strtolower($tag);
                }
                $loc["attributes"][$tag] = & $val["attributes"][$key];
            }
        }
        return true;
    }

    /**
     * Private support function for xmlFileToArray. Handles an xml OPEN tag.
     *
     * @param    $topTag. String. xmlFileToArray topTag variable
     * @param    $includeTopTag. boolean. xmlFileToArray includeTopTag variable
     * @param    $val. String[]. xmlFileToArray val variable
     * @param    $currTag. String. xmlFileToArray currTag variable
     * @param    $lowerCaseTags. boolean. xmlFileToArray lowerCaseTags variable
     * @param    $levels. String[]. xmlFileToArray levels variable
     * @param    $prevTag. String. xmlFileToArray prevTag variable
     * @param    $multipleData. boolean. xmlFileToArray multipleData variable
     * @param    $xml. String[]. xmlFileToArray xml variable
     * @access    private static
     * @return    boolean
     * @author    Jason Read <jason@ace.us.com>
     */
    function _xmlFileToArrayClose(& $topTag, & $includeTopTag, & $val, & $lowerCaseTags, 
                                  & $levels, & $prevTag, & $multipleData, & $xml)
    {
        // don't include top tag
        if ($topTag && !$includeTopTag && $val["tag"] == $topTag)
        {
            return false;
        }
        if ($multipleData[$currTag]["multiple"])
        {
            $tkeys = array_reverse(array_keys($multipleData));
            foreach ($tkeys as $tkey)
            {
                if ($multipleData[$tkey]["multiple"] && !$multipleData[$tkey]["popped"])
                {
                    array_pop($levels);
                    $multipleData[$tkey]["popped"] = true;
                    break;
                }
                else if (!$multipleData[$tkey]["multiple"])
                {
                    break;
                }
            }
        }
        $prevTag = array_pop($levels);
        if (strpos($prevTag, "arr#"))
        {
            $prevTag = array_pop($levels);
        }
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
