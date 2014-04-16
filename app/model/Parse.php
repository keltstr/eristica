<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 16.04.14 at 10:09
 */

/**
 * Generic class for interaction with parse.com REST API
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2013 SamsonOS
 * @version 0.0.1
 */
class Parse 
{
    public $appCode = 'rMRzdHyHQlEwDjJbUrPLpUSYTV3JXtQZfTRH9WXH';
    public $appKey = 'a0mFgSRKnHHtgPDnXTYgtVRN9cbYo1AkL5bCkSE2';

    /** Last REST API response data */
    public $lastResponse;

    public function request($url, $method, array $params = array())
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_TIMEOUT, 30);
        curl_setopt($c, CURLOPT_USERAGENT, 'parse.com-php-library/2.0');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLINFO_HEADER_OUT, true);

        // Set REST API headers
        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Parse-Application-Id: '.$this->appCode,
            'X-Parse-REST-API-Key: '.$this->appKey,
        ));

        curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);

        // Add post data of necessary
        if ($method == 'POST') {
            curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($params) );
        }

        curl_setopt($c, CURLOPT_URL, $url);

        // Perform HTTP request
        $this->lastResponse = curl_exec($c);

        // Ger response status
        $responseCode = curl_getinfo($c, CURLINFO_HTTP_CODE);

        // Return true if response is success
        return in_array($responseCode, array('200','201'));
    }
}
 