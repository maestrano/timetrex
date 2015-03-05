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
 * Exchange rate driver - XE.net
 *
 * Retrieves exchange rates from XE.net
 * Snippet from RatesA.html:
 *
 * @link http://www.xe.net
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
class Services_ExchangeRates_Rates_GOOGLE extends Services_ExchangeRates_Common {

   /**
    * URL of HTML page where the rates are given
    * @var string
    */
    var $feedHTMLUrl = 'http://www.google.com/search?num=0&q=';

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

      $supported_currencies = array('DZD','XAL','ARS','AWG','AUD','BSD','BHD','BDT','BBD','BYR','BZD','BMD','BTN','BOB','BWP','BRL','GBP','BND','BGN','BIF','KHR','CAD','CVE','KYD','XOF','XAF','CLP','CNY','COP','KMF','XCP','CRC','HRK','CUP','CYP','CZK','DKK','DJF','DOP','XCD','ECS','EGP','SVC','ERN','EEK','ETB','EUR','FKP','FJD','GMD','GHC','GIP','XAU','GTQ','GNF','GYD','HTG','HNL','HKD','HUF','ISK','INR','IDR','IRR','IQD','ILS','JMD','JPY','JOD','KZT','KES','KRW','KWD','LAK','LVL','LBP','LSL','LRD','LYD','LTL','MOP','MKD','MWK','MYR','MVR','MTL','MRO','MUR','MXN','MDL','MNT','MAD','MMK','NAD','NPR','ANG','TRY','NZD','ZWN','NIO','NGN','KPW','NOK','OMR','XPF','PKR','XPD','PAB','PGK','PYG','PEN','PHP','XPT','PLN','QAR','RON','RUB','RWF','WST','STD','SAR','SCR','SLL','SGD','SKK','SIT','SBD','SOS','ZAR','LKR','SHP','SDD','SZL','SEK','CHF','SYP','TWD','TZS','THB','TOP','TTD','TND','USD','AED','UGX','UAH','UYU','VUV','VEB','VND','YER','ZMK');

      if ( is_array($supported_currencies) AND count($supported_currencies) > 0 ) {
          foreach( $supported_currencies as $currency ) {
             $feed_url = $this->feedHTMLUrl . urlencode('1.0 USD in '. $currency);
             var_dump($feed_url);

             $htmlpage = $this->retrieveFile( $feed_url, $cacheLength, $cacheDir);
             var_dump($htmlpage);
             preg_match_all('/<b>1.0 U.S. dollar = ([0-9\.,]{2,20}) .*<\/b>/i', $htmlpage, $matches);

             if ( is_array($matches) AND isset($matches[1][0]) AND is_numeric($matches[1][0]) ) {
                 $return['rates'][$currency] = (float)$matches[1][0];
             }
             break;
          }
      }

      return $return;

   }

}

?>
