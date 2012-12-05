<?php

    // Require ClassLoader to provide other useful tools
    require_once('ClassLoader.php');
    

    // Define some cool methods
    class Tools {
    
        // Disallow instancing
        private function __construct() {}
    
        /**
         * Gets the distance between two coordinate points
         * Stolen from somewhere out of the internet because of lazyness
         *
         * @static
         * @param float $lat1
         * @param float $lng1
         * @param float $lat2
         * @param float $lng2
         * @param bool $miles Whether to return miles or kilometers
         * @return float
         */
        public static function distance($lat1, $lng1, $lat2, $lng2, $miles = false) {
        
             $pi80 = M_PI / 180;
             $lat1 *= $pi80;
             $lng1 *= $pi80;
             $lat2 *= $pi80;
             $lng2 *= $pi80;
             
             $r = 6372.797; // mean radius of Earth in km
             $dlat = $lat2 - $lat1;
             $dlng = $lng2 - $lng1;
             $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
             $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
             $km = $r * $c;
             
             return ($miles ? ($km * 0.621371192) : $km);
            
        }

        /**
         * Calls a URL with given POST-arguments
         *
         * @param string $url
         * @param Array $opts
         * @return string
         */
        public static function getPostUrl($url, $opts) {

            $body = http_build_query($opts);

            $contextOptions = array(
                'http' => array(
                    'Content-type' => 'application/x-www-form-urlencoded',
                    'method' => 'POST',
                    'content' => $body
                )
            );

            $context = stream_context_create($contextOptions);
            $page    = @file_get_contents($url, false, $context);

            return $page;

        }

        /**
         * Replaces the first occurence of a string
         *
         * @param string $needle
         * @param string $replacement
         * @param string $haystack
         * @return string
         */
        public static function str_replace_once($needle, $replacement, $haystack) {
            $strpos = strpos($needle, $needle);
            return substr_replace($haystack, $replacement, $strpos, ($strpos + strlen($needle)));
        }

        /**
         * Parses a URL and extends missing values
         *
         * @see parse_url()
         * @param string $url
         * @return Array
         */
        public static function parse_url($url) {

            $urlPattern = Array(
                'scheme' => 'http',
                'host'   => 'thomann.de',
                'user'   => '',
                'pass'   => '',
                'path'   => '/de',
                'query'  => ''
            );

            $url = parse_url($url);
            foreach ($urlPattern as $name => $val) {
                if (!isset($url[$name])) {
                    $url[$name] = $val;
                }
            }

            return $url;

        }

        /**
         * Returns a URL based on a parse_url array
         *
         * @param Array $url
         * @return string
         */
        public static function url2string($url) {
            $urlStr = $url['scheme'] . '://';

            $urlStr .= (!empty($url['user'])) ? $url['user'] : '';
            $urlStr .= (!empty($url['host']) && !empty($url['pass'])) ? ':' . $url['pass'] : '';
            $urlStr .= $url['host'];
            $urlStr .= (!empty($url['path'])) ? $url['path'] : '';
            $urlStr .= (!empty($url['query'])) ? '?' . $url['query'] : '';

            return $urlStr;
        }

        /**
         * Parses an XML document and returns an array
         *
         * @param string $xml
         * @return Array
         */
        public static function xml2array($xml) {
            return (array) simplexml_load_string($xml);
        }

        /**
         * Formats a money integer/float to readable locale-based string
         *
         * @param int|float $amount
         * @param bool $withPlus Whether to show a plus on a positve value or not
         * @return string
         */
        public static function money2str($amount, $withPlus = false) {
            $return = ($withPlus&&$amount>0) ? '+' : '';
            return $return . number_format($amount, 2, ',', '.');
        }

        /**
         * Formats an integer/float to readable local-based string
         * Actually, almost the same as Tools::money2str, except that this method hides useless decimals
         *
         * @see Tools::money2str
         * @param int|float $num
         * @param bool $withPlus Whether to show a plus on a positive value or not
         * @return string
         */
        public static function num2str($num, $withPlus = false) {
            $num     = self::money2str($num, $withPlus);
            $decimal = trim(substr($num, strpos($num, ',')), '0');
            if ($decimal == ',') {
                $decimal = '';
            }
            return substr($num, 0, strpos($num, ',')) .  $decimal;
        }

        /**
         * Randomize array with seed
         *
         * @param &array $items
         * @param int|float $seed
         * @return void
         */
        public static function shuffle(&$items, $seed) {
            @mt_srand($seed);
            for ($i=count($items)-1; $i>0; $i--) {
                $j = @mt_rand(0, $i);
                $tmp = $items[$i];
                $items[$i] = $items[$j];
                $items[$j] = $tmp;
            }
        }
    
    }