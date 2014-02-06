<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at                              |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Piotr Klaban <makler@man.torun.pl>                           |
// +----------------------------------------------------------------------+
//
// $Id: Rates_NBP.php,v 1.1.1.1 2003/09/13 15:03:54 mroch Exp $

/**
 * Exchange rate driver - Yahoo.com
 *
 * Retrieves exchange rates from Yahoo.com
 * Snippet from RatesA.html:
 *
 * @link http://www.yahoo.com
 *
 * @author Unknown
 * @copyright
 * @license http://www.php.net/license/2_02.txt PHP License 2.0
 * @package Services_ExchangeRates
 */

/**
 * Include common functions to handle cache and fetch the file from the server
 */
require_once 'Services/ExchangeRates/Common.php';

/**
 * National Bank of Poland Exchange Rate Driver
 *
 * @package Services_ExchangeRates
 */
class Services_ExchangeRates_Rates_YAHOO extends Services_ExchangeRates_Common {
   /**
    * URL of HTML page where the rates are given
    * @var string
    */
    var $feedHTMLUrl = 'http://download.finance.yahoo.com/d/quotes.csv?f=sl1d1t1ba&e=.csv&';

    //For some reason Yahoo won't return more then one currency at a time if we use PEAR HTTP_Request class.
    //Very strange.
    function _retrieveFile($url, $cacheLength, $cacheDir) {
        $cacheID = md5($url);

        $cache = new Cache_Lite(array("cacheDir" => $cacheDir,
                                      "lifeTime" => $cacheLength));

        if ($data = $cache->get($cacheID)) {
            return $data;
        } else {
            $fp = fopen($url,'r');
            $data = stream_get_contents($fp);

            if ( strlen($data) > 10 ) {
                // data is changed, so save it to cache
                $cache->save($data, $cacheID);
                return $data;
            } else {
                // retrieve the data, since the first time we did this failed
                if ($data = $cache->get($cacheID, 'default', true)) {
                    return $data;
                }
            }
        }

        Services_ExchangeRates::raiseError("Unable to retrieve file ${url} (unknown reason)", SERVICES_EXCHANGERATES_ERROR_RETRIEVAL_FAILED);
        return false;

    }

   /**
    * Downloads exchange rates from XE.com
    * This information is updated daily, and is cached by default for 1 hour.
    *
    * Returns a multi-dimensional array containing:
    * 'rates' => associative array of currency codes to exchange rates
    * 'source' => URL of feed
    * 'date' => date feed last updated, pulled from the feed (more reliable than file mod time)
    *
    * @param int Length of time to cache (in seconds)
    * @return array
    */
    function retrieve($cacheLength, $cacheDir) {

        $return['rates'] = array('USD' => 1.0);

        $return['source'] = $this->feedHTMLUrl;

        $return['date'] = time();

        $supported_currencies = array('ALL','DZD','XAL','ARS','AWG','AUD','BSD','BHD','BDT','BBD','BYR','BZD','BMD','BTN','BOB','BWP','BRL','GBP','BND','BGN','BIF','KHR','CAD','CVE','KYD','XOF','XAF','CLP','CNY','COP','KMF','XCP','CRC','HRK','CUP','CYP','CZK','DKK','DJF','DOP','XCD','ECS','EGP','SVC','ERN','EEK','ETB','EUR','FKP','FJD','GMD','GHC','GIP','XAU','GTQ','GNF','GYD','HTG','HNL','HKD','HUF','ISK','INR','IDR','IRR','IQD','ILS','JMD','JPY','JOD','KZT','KES','KRW','KWD','LAK','LVL','LBP','LSL','LRD','LYD','LTL','MOP','MKD','MWK','MYR','MVR','MTL','MRO','MUR','MXN','MDL','MNT','MAD','MMK','NAD','NPR','ANG','TRY','NZD','ZWN','NIO','NGN','KPW','NOK','OMR','XPF','PKR','XPD','PAB','PGK','PYG','PEN','PHP','XPT','PLN','QAR','RON','RUB','RWF','WST','STD','SAR','SCR','SLL','SGD','SKK','SIT','SBD','SOS','ZAR','LKR','SHP','SDD','SZL','SEK','CHF','SYP','TWD','TZS','THB','TOP','TTD','TND','USD','AED','UGX','UAH','UYU','VUV','VEB','VND','YER','ZMK');

        //Loop through all currencies making URLs in batch of 10
        //YAHOO stopped allowing batch downloads it seems 06-Apr-08.
        $batch_size = 10;

        if ( is_array($supported_currencies) AND count($supported_currencies) > 0 ) {
            $i=1;
            $max = count($supported_currencies);
            foreach( $supported_currencies as $currency ) {
                $batch_currency[] = 's=USD'. $currency . urlencode('=X');

                if ( $i % $batch_size == 0 OR $i == $max ) {
                    $batch_currency_url = implode('&',$batch_currency);

                    $feed_url = $this->feedHTMLUrl . $batch_currency_url;
                    //echo "Feed URL: $feed_url<br>\n";
                    $htmlpage = $this->_retrieveFile( $feed_url, $cacheLength, $cacheDir);
                    //echo "Page: $htmlpage<br>\n";

                    $lines = explode("\n", $htmlpage);

                    if ( is_array($lines) ) {
                        foreach($lines as $line) {
                            $values = explode(',', $line);
                            if ( is_array($values) AND isset($values[1]) AND is_numeric($values[1]) AND $values[1] > 0 ) {
                                $currency = substr($values[0],4,3);
                                $return['rates'][$currency] = (float)$values[1];
                            }

                        }
                    }
                    unset($batch_currency);
                }

                $i++;
            }
        }

        return $return;
    }

}

?>
