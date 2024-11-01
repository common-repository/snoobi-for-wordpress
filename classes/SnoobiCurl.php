<?php
/**
 * Curl helper class
 * @package Snoobi for Wordpress
 */
class SnoobiCurl {
    public static function get_curl_response($toHeader, $url, $post = true) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($toHeader));
        curl_setopt($ch, CURLOPT_URL, $url);

        if($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        $output = curl_exec($ch);
           curl_close($ch);

        return $output;
    }
}