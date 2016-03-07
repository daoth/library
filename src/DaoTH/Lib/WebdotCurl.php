<?php

namespace DaoTH\Lib;

/*
 * Convert from native php to class
 * Convert By DaoTH
 * 
 * function to get via cUrl 
 * From lastRSS 0.9.1 by Vojtech Semecky, webmaster @ webdot . cz
 * See      http://lastrss.webdot.cz/
 */
class WebdotCurl {

    protected $outgoing_ip;
    protected $config = array('multipleIPs' => false, 'IPs' => array());

    /*     * ********|| Multiple IPs ||************** */
# You can enable this option if you are having problems with youtube IP limit / IP ban.
# This option will work only if the IP you add are available for the server.
# That means you have to buy some additionnal public IPs and assign these new static IPs to the server.
# This should work only if you have a dedicated server...
#
    #
  # Example of adding additional IPs to Ubuntu server 14.04 LTS 
# !!!! Be very careful, you may block yourself !!!!
# !!!! If you are connecting to your server remotly by ssh. You would do this only if you know what you do !!!!
# !!!! This is only an example with a specific dedicated server (ovh.net) !!!!
#
    # For this example, the main IP on the server is 123.456.789.001
# We want to add additionnal IPs 789.456.123.001 and 789.456.123.002
#
    # Edit /etc/network/interfaces and put something like this:
#
    # # The loopback network interface
# auto lo
# iface lo inet loopback
#
    # # The Main server IP: 
# auto eth0
# iface eth0 inet static
#     address 123.456.789.001
#     netmask 255.255.255.0
#     network 123.456.789.0
#     broadcast 123.456.789.255
#     gateway 123.456.789.254
#
    # # Additionnal IP: 789.456.123.001
# auto eth0:0
# iface eth0:0 inet static
#     address 789.456.123.001
#     netmask 255.255.255.255
#     broadcast 789.456.123.001
#     gateway 123.456.789.254
#
    # # Additionnal IP: 789.456.123.002
# auto eth0:1
# iface eth0:0 inet static
#     address 789.456.123.002
#     netmask 255.255.255.255
#     broadcast 789.456.123.002
#     gateway 123.456.789.254
#
    # # Additionnal IP xxx.xxx.xxx.xxx
# auto eth0:2
# iface eth0:2 inet static
# (...)
#
    # and so on for each IP you want to add....
#
    #
  # Reboot your server
# If you are having trouble and cannot connect anymore over ssh to your server,
# that means your new network configuration has errors...
# So be very careful before applying your configuration.
# Try it first on a local dev server before messing up with your pro server.
# 
# 

    /*
     * If multipleIPs mode is enabled, select randomly one IP from
     * the config IPs array and put it in $outgoing_ip variable.
     */

    public function __construct($config = array()) {
        if (isset($config['multipleIPs']) && $config['multipleIPs']) {
            $this->config['multipleIPs'] = true;
            $this->config['IPs'] = $config['IPs'];
            $this->outgoing_ip = $config['IPs'][mt_rand(0, count($config['IPs']) - 1)];
        }
    }

    function curlGet($URL) {
        $config = $this->config;
        $ch = curl_init();
        $timeout = 3;
        if ($config['multipleIPs'] === true) {
            curl_setopt($ch, CURLOPT_INTERFACE, $this->outgoing_ip);
        }
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        /* if you want to force to ipv6, uncomment the following line */
//curl_setopt( $ch , CURLOPT_IPRESOLVE , 'CURLOPT_IPRESOLVE_V6');
        $tmp = curl_exec($ch);
        curl_close($ch);
        return $tmp;
    }

    /*
     * function to use cUrl to get the headers of the file 
     */
    function get_location($url) {
        $config = $this->config;
        $my_ch = curl_init();
        if ($config['multipleIPs'] === true) {
            curl_setopt($my_ch, CURLOPT_INTERFACE, $this->outgoing_ip);
        }
        curl_setopt($my_ch, CURLOPT_URL, $url);
        curl_setopt($my_ch, CURLOPT_HEADER, true);
        curl_setopt($my_ch, CURLOPT_NOBODY, true);
        curl_setopt($my_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($my_ch, CURLOPT_TIMEOUT, 10);
        $r = curl_exec($my_ch);
        foreach (explode("\n", $r) as $header) {
            if (strpos($header, 'Location: ') === 0) {
                return trim(substr($header, 10));
            }
        }
        return '';
    }

    function get_size($url) {
        $config = $this->config;
        $my_ch = curl_init();
        if ($config['multipleIPs'] === true) {
            curl_setopt($my_ch, CURLOPT_INTERFACE, $this->outgoing_ip);
        }
        curl_setopt($my_ch, CURLOPT_URL, $url);
        curl_setopt($my_ch, CURLOPT_HEADER, true);
        curl_setopt($my_ch, CURLOPT_NOBODY, true);
        curl_setopt($my_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($my_ch, CURLOPT_TIMEOUT, 10);
        $r = curl_exec($my_ch);
        foreach (explode("\n", $r) as $header) {
            if (strpos($header, 'Content-Length:') === 0) {
                return trim(substr($header, 16));
            }
        }
        return '';
    }

    function get_description($url) {
        $fullpage = curlGet($url);
        $dom = new DOMDocument();
        @$dom->loadHTML($fullpage);
        $xpath = new DOMXPath($dom);
        $tags = $xpath->query('//div[@class="info-description-body"]');
        foreach ($tags as $tag) {
            $my_description .= (trim($tag->nodeValue));
        }

        return utf8_decode($my_description);
    }

}
