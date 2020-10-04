<?php
/**
 * Then no difficulty in handling path.
 *
 * @author    CrazyWhite <moe@mailo.com>
 * @copyright 2020 (c) CrazyWhite - pathHandler
 * @license   https://opensource.org/licenses/MIT - The MIT License (MIT)
 * @link      https://github.com/Crazy-White/unpkg-proxy/tree/master/lib/pathHandler
 * @since     1.0.0
 */

namespace pathHandler;

function redirect()
{
    /*
    'localhost/sn.php?/path/to/file?fakeQuery'
    to
    'localhost/sn.php?/path/to/file&fakeQuery'
    then $_GET works
    */
    $uri   = $_SERVER['REQUEST_URI'];
    $query = $_SERVER['QUERY_STRING'];
    if (strpos($query, '?') > 0) {
        $fixed_query = str_replace('?', '&', $query);
        header('Location: ' . str_replace($query, $fixed_query, $uri));
        die();
    }
}

function get($is_include_query = false)
{
    $pi    = $_SERVER['PATH_INFO'];
    $uri   = $_SERVER['REQUEST_URI'];
    $query = $_SERVER['QUERY_STRING'];
    
    if (isset($pi) && strlen($pi) > 0) {
        
        if (!$is_include_query)
            return $pi;
        //$_pi = str_replace('/', '\/', $pi);
        $sn = addcslashes($_SERVER['SCRIPT_NAME'], '/');
        if (preg_match("/{$sn}(.+)$/", $uri, $matches)) {
            return $matches[1];
        }
        
    }
    
    if ($query[0] === '/') {
        
        if ($is_include_query) {
            return $query;
        } else {
        
            $p = strpos($query, '&');
            if (!$p) { 
                $p = strpos($query, '?');
                if(!$p) return $query;
            }
            return substr($query, 0, $p);
            
        }
        
    }
    
    return '/';
}