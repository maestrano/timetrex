<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at                              |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Ian Eure <ieure@php.net>                                    |
// |          Joe Stump <joe@joestump.net>                                |
// +----------------------------------------------------------------------+
//
// $Id: eCheck.php,v 1.9 2005/09/07 22:50:35 jstump Exp $

/**
 * Payment_Process_Type_eCheck
 *
 * @author Joe Stump <joe@joestump.net>
 * @package Payment_Process
 */
class Payment_Process_Type_EFT extends Payment_Process_Type
{
    /**
     * $_type
     *
     * @var string $_type
     */
    var $_type = 'EFT';

    /**
     * $type
     *
     * @var $type
     */
    var $type;
    var $accountNumber;
    var $routingCode;
    var $institutionCode;
    var $name;

    function Payment_Process_Type_EFT()
    {

    }

    function _validateAccountNumber()
    {
        if (!isset($this->accountNumber)) {
            return PEAR::raiseError('Account number is required');
        }

        return true;
    }

    function _validateInstituionCode()
    {
        if (!isset($this->accountNumber)) {
            return PEAR::raiseError('Institution code is required');
        }

        return true;
    }

    function _validateRoutingCode()
    {
        if (!isset($this->routingCode)) {
            return PEAR::raiseError('Routing code is required');
        }

        return true;
    }

    function _validateName()
    {
        if (!isset($this->routingCode)) {
            return PEAR::raiseError('Name is required');
        }

        return true;
    }

}

?>
