<?php
/**
 * lastfm functions for the lastfm plugin
 * 
 * @license:    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author:     Michael Klier <chi@chimeric.de>
 */

if(!defined('DW_LF')) define('DW_LF',"\n");
require_once(DOKU_INC.'inc/html.php');

/**
 * outputs the requested lastfm chart
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function lastfm_xhtml($user,$chart,$limit,$dformat,$utc_offset) {
    global $lang;

    $data = array();
    $xml  = lastfm_get_xml($user,$chart);
    $data = ($chart != 'profile') ? @array_pop(lastfm_xml2array($xml)) : lastfm_xml2array($xml); 

    // do we have any data?
    if(!is_array($data)) {
        print $lang['nothingfound'];
        return;
    };

    $data = array_slice($data,0,$limit);
    
    print '<table class="plugin_lastfm_chart plugin_lastfm_' . $chart . '">' . DW_LF;

    switch($chart) {

        case 'topartists':
            foreach($data as $rcd) {
                print '<tr>' . DW_LF;
                print '  <td class="plugin_lastfm_rank">' . $rcd['rank'] . '.</td>' . DW_LF;
                print '  <td class="plugin_lastfm_artist">' . DW_LF;
                print '    <a href="' . $rcd['url'] . '" title="' . $rcd['name'] . '">' . $rcd['name'] . '</a>' . DW_LF;
                print '  </td>' . DW_LF;
                print '  <td>' . DW_LF;
                print '    <div class="plugin_lastfm_playcount"style="width:'; 
                print round($rcd['playcount'] / 2);
                print 'px;">' . $rcd['playcount'] . '</div>' . DW_LF;
                print '  </td>' . DW_LF;
                print '</tr>' . DW_LF;
            }
            break;

        case 'topalbums':
            foreach($data as $rcd) {
                print '<tr>' . DW_LF;
                print '  <td class="plugin_lastfm_rank">' . $rcd['rank'] . '.</td>' . DW_LF;
                print '  <td>' . DW_LF;
                print '    <a href="' . $rcd['url'] . '" title="' . $rcd['name'] . '">' . DW_LF;
                print '      <img src="' . $rcd['image']['small'] . '" height="40px" width="40px" alt="' . $rcd['name'] . '" />' . DW_LF;
                print '    </a>' . DW_LF;
                print '  </td>' . DW_LF;
                print '  <td class="plugin_lastfm_artist"><a href="' . $rcd['url'] .'" title="' . $rcd['artist'] . '">' . $rcd['artist'] . ' [' . $rcd['name'] . ']</a></td>' . DW_LF;
                print '  <td class="plugin_lastfm_count">' . $rcd['playcount'] . '</td>' . DW_LF;
                print '</tr>' . DW_LF;
            }
            break;

        case 'toptracks':
            foreach($data as $rcd) {
                print '<tr>' . DW_LF;
                print '  <td class="plugin_lastfm_rank">' . $rcd['rank'] . '.</td>' . DW_LF;
                print '  <td class="plugin_lastfm_track"><a href="' . $rcd['url'] . '" title="' . $rcd['artist'] . '">' . $rcd['name'] . '</a></td>' . DW_LF;
                print '  <td class="plugin_lastfm_count">' . $rcd['playcount'] . '</td>' . DW_LF;
                print '</tr>' . DW_LF;
            }
            break;

        case 'tags':
            foreach($data as $rcd) {
                print '<tr>' . DW_LF;
                print '  <td class="plugin_lastfm_count">' . $rcd['count'] . '</td>' . DW_LF;
                print '  <td class="plugin_lastfm_tag"><a href="' . $rcd['url'] . '" title="' . $rcd['name'] . '">' . $rcd['name'] . '</a></td>' . DW_LF;
                print '</tr>' . DW_LF;
            }
            break;

        case 'friends':
            print '<tr>' . DW_LF;
            foreach($data as $rcd) {
                print '  <td class="plugin_lastfm_friend">' . DW_LF;
                print '    <a href="' . $rcd['url'] . '" title="' . $rcd['attributes']['username'] . '">' . DW_LF;
                print '     <img src="' . $rcd['image'] . '" alt="' . $rcd['attributes']['username'] . '" width="40px" height="40px" />' . DW_LF;
                print '    </a>' . DW_LF;
                print '  </td>' . DW_LF;
            }
            print '</tr>' . DW_LF;
            break;

        case 'neighbours':
            print '<tr>' . DW_LF;
            foreach($data as $rcd) {
                print '  <td class="plugin_lastfm_friend">' . DW_LF;
                print '    <a href="' . $rcd['url'] . '" title="' . $rcd['attributes']['username'] . '">' . DW_LF;
                print '     <img src="' . $rcd['image'] . '" alt="' . $rcd['attributes']['username'] . ' ' . $rcd['match'] . '%" width="40px" height="40px" />' . DW_LF;
                print '    </a>' . DW_LF;
                print '  </td>' . DW_LF;
            }
            print '</tr>' . DW_LF;
            break;

        case 'recenttracks':
            foreach($data as $rcd) {
                print '<tr>' . DW_LF;
                print '  <td class="plugin_lastfm_artist">' . DW_LF;
                print '    <a href="' . $rcd['url'] . '" title="' . $rcd['artist'] . ' &middot; ' . $rcd['name'] . '">' . $rcd['artist'] . ' &middot; ' . $rcd['name'] . '</a>' . DW_LF;
                print '  </td>' . DW_LF;
                print '  <td class="plugin_lastfm_date">' . lastfm_cvdate($rcd['date'],$dformat,$utc_offset) . '</td>' . DW_LF;
                print '</tr>' . DW_LF;
            }
            break;

        case 'weeklyartistchart':
            foreach($data as $rcd) {
                print '<tr>' . DW_LF;
                print '  <td class="plugin_lastfm_rank">' . $rcd['chartposition'] . '.</td>' . DW_LF;
                print '  <td class="plugin_lastfm_artist">' . DW_LF;
                print '    <a href="' . $rcd['url'] . '" title="' . $rcd['name'] . '">' . $rcd['name'] . '</a>' . DW_LF;
                print '  </td>' . DW_LF;
                print '  <td class="plugin_lastfm_playcount">' . $rcd['playcount'] . '</td>' . DW_LF;
                print '</tr>' . DW_LF;
            }
            break;

        case 'weeklyalbumchart':
            foreach($data as $rcd) {
                // FIXME mbid field
                print '<tr>' . DW_LF;
                print '  <td class="plugin_lastfm_rank">' . $rcd['chartposition'] . '.</td>' . DW_LF;
                print '  <td class="plugin_lastfm_artist">' . DW_LF;
                print '    <a href="' . $rcd['url'] . '" title="' . $rcd['artist'] . ' &middot; ' . $rcd['name'] . '">' . $rcd['artist'] . ' &middot; ' . $rcd['name'] . '</a>' . DW_LF;
                print '  </td>' . DW_LF;
                print '  <td class="plugin_lastfm_playcount">' . $rcd['playcount'] . '</td>' . DW_LF;
                print '</tr>' . DW_LF;
            }
            break;

        case 'weeklytrackchart':
            foreach($data as $rcd) {
                print '<tr>' . DW_LF;
                print '  <td class="plugin_lastfm_rank">' . $rcd['chartposition'] . '.</td>' . DW_LF;
                print '  <td class="plugin_lastfm_artist">' . DW_LF;
                print '    <a href="' . $rcd['url'] . '" title="' . $rcd['artist'] . ' &middot; ' . $rcd['name'] . '">' . $rcd['artist'] . ' &middot; ' . $rcd['name'] . '</a>' . DW_LF;
                print '  </td>' . DW_LF;
                print '  <td class="plugin_lastfm_playcount">' . $rcd['playcount'] . '</td>' . DW_LF;
                print '</tr>' . DW_LF;
            }
            break;

        case 'profile':
            //print_r($data);
            print '<a href="' . $data['url'] . '" title="' . $user . '">' . DW_LF;
            print '  <img src="' . $data['avatar'] . '" alt="' . $user . '" />' . DW_LF;
            print '</a>' . DW_LF;
            break;
    }

    print '</table>' . DW_LF;
}

/**
 * gets the xml file from the lastfm page
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function lastfm_get_xml($user,$chart) {
    $xml  = '';
    $http = new DokuHTTPClient();
    $url  = 'http://ws.audioscrobbler.com/1.0/user/'; 
    $url .= $user . '/' . $chart . '.xml';

    $xml = $http->get($url);
    if($http->status == 200)
        return $xml;
}

/**
 * converst the date provided by the lastfm service
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function lastfm_cvdate($date,$dformat,$utc_offset) {
    list($day,$month,$year,$time) = explode(' ',$date);
    list($hour,$min) = explode(':',$time);
    $year  = substr($year,0,-1); // remove trailing comma
    $hour  = $hour + $utc_offset;
    return date($dformat,strtotime($day." ".$month." ".$year. " ".$hour.":".$min));
}

/**
 * wrapper function around _xmlToArray()
 */
function lastfm_xml2array($xml) {
    return _xmlToArray($xml);
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
            if (!_xmlFileToArrayOpen($topTag, $includeTopTag, $val, $lowerCaseTags, 
                                           $levels, $prevTag, $multipleData, $xml))
            {
                continue;
            }
        }
        // Close tag
        else if ($val["type"] == "close")
        {
            if (!_xmlFileToArrayClose($topTag, $includeTopTag, $val, $lowerCaseTags, 
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
            _xmlFileToArrayOpen($topTag, $includeTopTag, $val, $lowerCaseTags, 
                                      $levels, $prevTag, $multipleData, $xml);
            _xmlFileToArrayClose($topTag, $includeTopTag, $val, $lowerCaseTags, 
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

// vim:ts=4:sw=4:et:enc=utf8:
