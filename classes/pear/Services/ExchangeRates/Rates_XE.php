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
class Services_ExchangeRates_Rates_XE extends Services_ExchangeRates_Common {
    
   /**
    * URL of HTML page where the rates are given
    * @var string
    */
    var $feedHTMLUrl = 'http://www.xe.com/ict/?basecur=USD&hide_inverse=true&historical=false';

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
        
        // retrieve XML address
        $htmlpage = $this->retrieveFile($this->feedHTMLUrl, $cacheLength, $cacheDir);
        
        //Get date rates were generated
        preg_match('/rates as of <b>(.*)<\/td>/i', $htmlpage, $raw_date);
        if ( isset($raw_date[1]) )
        {
            $return['date'] = strtotime( $raw_date[1] ); 
        }
        
        //Remove any HTML comments.
        $htmlpage = preg_replace('/<!--\s+.*\s+-->/i','', $htmlpage );
        
        //Get actual rates here        
        preg_match_all('/<td align="left" class="bbl">([A-Z]{3,5})<\/td><td align="left" class="bbr">(.*)<\/td><td align="right" class="bbr">([0-9\.,]{9,20})<\/td>/i', $htmlpage, $matches);
        
        if ( is_array($matches) )
        {
            foreach( $matches[1] as $key => $val ) {
               if ( isset($matches[3][$key]) ){
                  $return['rates'][trim($val)] = str_replace( array(' ', ','), '', $matches[3][$key] );
               }
            }
        }
        
        return $return; 

    }

}

?>
