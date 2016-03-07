<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DaoTH\Lib;

class Common {

    static function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . '' . $units[$pow];
    }

    static function is_chrome() {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match("/like\sGecko\)\sChrome\//", $agent)) { // if user agent is google chrome
            if (!strstr($agent, 'Iron')) // but not Iron
                return true;
        }
        return false; // if isn't chrome return false
    }

}
