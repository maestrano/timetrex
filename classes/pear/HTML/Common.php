<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Adam Daniel <adaniel1@eesus.jnj.com>                         |
// +----------------------------------------------------------------------+
//
// $Id: Common.php,v 1.10 2005/09/01 10:28:57 thesaur Exp $

/**
 * Base class for all HTML classes
 * 
 * @author    Adam Daniel <adaniel1@eesus.jnj.com>
 * @category  HTML
 * @package   HTML_Common
 * @version   1.2.2
 * @abstract
 */

/**
 * Base class for all HTML classes
 *
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @version     1.7
 * @since       PHP 4.0.3pl1
 * @abstract
 */
class HTML_Common {

    /**
     * Associative array of table attributes
     * @var     array
     * @access  private
     */
    static $_attributes = array();

    /**
     * Tab offset of the table
     * @var     int
     * @access  private
     */
    static $_tabOffset = 0;

    /**
     * Tab string
     * @var       string
     * @since     1.7
     * @access    private
     */
    static $_tab = "\11";

    /**
     * Contains the line end string
     * @var       string
     * @since     1.7
     * @access    private
     */
    static $_lineEnd = "\12";

    /**
     * HTML comment on the object
     * @var       string
     * @since     1.5
     * @access    private
     */
    static $_comment = '';

    /**
     * Class constructor
     * @param    mixed   $attributes     Associative array of table tag attributes 
     *                                   or HTML attributes name="value" pairs
     * @param    int     $tabOffset      Indent offset in tabs
     * @access   public
     */
    function HTML_Common($attributes = null, $tabOffset = 0)
    {
        self::setAttributes($attributes);
        self::setTabOffset($tabOffset);
    } // end constructor

    /**
     * Returns the current API version
     * @access   public
     * @returns  double
     */
    static function apiVersion()
    {
        return 1.7;
    } // end func apiVersion

    /**
     * Returns the lineEnd
     * 
     * @since     1.7
     * @access    private
     * @return    string
     * @throws
     */
    static function _getLineEnd()
    {
        return self::$_lineEnd;
    } // end func getLineEnd

    /**
     * Returns a string containing the unit for indenting HTML
     * 
     * @since     1.7
     * @access    private
     * @return    string
     */
    static function _getTab()
    {
        return self::$_tab;
    } // end func _getTab

    /**
     * Returns a string containing the offset for the whole HTML code
     * 
     * @return    string
     * @access   private
     */
    static function _getTabs()
    {
        return str_repeat(self::_getTab(), self::$_tabOffset);
    } // end func _getTabs

    /**
     * Returns an HTML formatted attribute string
     * @param    array   $attributes
     * @return   string
     * @access   private
     */
    static function _getAttrString($attributes)
    {
        $strAttr = '';

        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $strAttr .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        return $strAttr;
    } // end func _getAttrString

    /**
     * Returns a valid atrributes array from either a string or array
     * @param    mixed   $attributes     Either a typical HTML attribute string or an associative array
     * @access   private
     */
    static function _parseAttributes($attributes)
    {
        if (is_array($attributes)) {
            $ret = array();
            foreach ($attributes as $key => $value) {
                if (is_int($key)) {
                    $key = $value = strtolower($value);
                } else {
                    $key = strtolower($key);
                }
                $ret[$key] = $value;
            }
            return $ret;

        } elseif (is_string($attributes)) {
            $preg = "/(([A-Za-z_:]|[^\\x00-\\x7F])([A-Za-z0-9_:.-]|[^\\x00-\\x7F])*)" .
                "([ \\n\\t\\r]+)?(=([ \\n\\t\\r]+)?(\"[^\"]*\"|'[^']*'|[^ \\n\\t\\r]*))?/";
            if (preg_match_all($preg, $attributes, $regs)) {
                for ($counter=0; $counter<count($regs[1]); $counter++) {
                    $name  = $regs[1][$counter];
                    $check = $regs[0][$counter];
                    $value = $regs[7][$counter];
                    if (trim($name) == trim($check)) {
                        $arrAttr[strtolower(trim($name))] = strtolower(trim($name));
                    } else {
                        if (substr($value, 0, 1) == "\"" || substr($value, 0, 1) == "'") {
                            $value = substr($value, 1, -1);
                        }
                        $arrAttr[strtolower(trim($name))] = trim($value);
                    }
                }
                return $arrAttr;
            }
        }
    } // end func _parseAttributes

    /**
     * Returns the array key for the given non-name-value pair attribute
     * 
     * @param     string    $attr         Attribute
     * @param     array     $attributes   Array of attribute
     * @since     1.0
     * @access    private
     * @return    bool
     * @throws
     */
    static function _getAttrKey($attr, $attributes)
    {
        if (isset($attributes[strtolower($attr)])) {
            return true;
        } else {
            return null;
        }
    } //end func _getAttrKey

    /**
     * Updates the attributes in $attr1 with the values in $attr2 without changing the other existing attributes
     * @param    array   $attr1      Original attributes array
     * @param    array   $attr2      New attributes array
     * @access   private
     */
    static function _updateAttrArray(&$attr1, $attr2)
    {
        if (!is_array($attr2)) {
            return false;
        }
        foreach ($attr2 as $key => $value) {
            $attr1[$key] = $value;
        }
    } // end func _updateAtrrArray

    /**
     * Removes the given attribute from the given array
     * 
     * @param     string    $attr           Attribute name
     * @param     array     $attributes     Attribute array
     * @since     1.4
     * @access    private
     * @return    void
     * @throws
     */
    static function _removeAttr($attr, &$attributes)
    {
        $attr = strtolower($attr);
        if (isset($attributes[$attr])) {
            unset($attributes[$attr]);
        }
    } //end func _removeAttr

    /**
     * Returns the value of the given attribute
     * 
     * @param     string    $attr   Attribute name
     * @since     1.5
     * @access    public
     * @return    void
     * @throws
     */
    static function getAttribute($attr)
    {
        $attr = strtolower($attr);
        if (isset(self::$_attributes[$attr])) {
            return self::$_attributes[$attr];
        }
        return null;
    } //end func getAttribute

    /**
     * Sets the HTML attributes
     * @param    mixed   $attributes     Either a typical HTML attribute string or an associative array
     * @access   public
     */
    static function setAttributes($attributes)
    {
        self::$_attributes = self::_parseAttributes($attributes);
    } // end func setAttributes

    /**
     * Returns the assoc array (default) or string of attributes
     *
     * @param     bool    Whether to return the attributes as string 
     * @since     1.6
     * @access    public
     * @return    mixed   attributes
     */
    static function getAttributes($asString = false)
    {
        if ($asString) {
            return self::_getAttrString(self::$_attributes);
        } else {
            return self::$_attributes;
        }
    } //end func getAttributes

    /**
     * Updates the passed attributes without changing the other existing attributes
     * @param    mixed   $attributes     Either a typical HTML attribute string or an associative array
     * @access   public
     */
    static function updateAttributes($attributes)
    {
        self::_updateAttrArray(self::$_attributes, self::_parseAttributes($attributes));
    } // end func updateAttributes

    /**
     * Removes an attribute
     * 
     * @param     string    $attr   Attribute name
     * @since     1.4
     * @access    public
     * @return    void
     * @throws
     */
    static function removeAttribute($attr)
    {
        self::_removeAttr($attr, self::$_attributes);
    } //end func removeAttribute

    /**
     * Sets the line end style to Windows, Mac, Unix or a custom string.
     * 
     * @param   string  $style  "win", "mac", "unix" or custom string.
     * @since   1.7
     * @access  public
     * @return  void
     */
    static function setLineEnd($style)
    {
        switch ($style) {
            case 'win':
                self::$_lineEnd = "\15\12";
                break;
            case 'unix':
                self::$_lineEnd = "\12";
                break;
            case 'mac':
                self::$_lineEnd = "\15";
                break;
            default:
                self::$_lineEnd = $style;
        }
    } // end func setLineEnd

    /**
     * Sets the tab offset
     *
     * @param    int     $offset
     * @access   public
     */
    static function setTabOffset($offset)
    {
        self::$_tabOffset = $offset;
    } // end func setTabOffset

    /**
     * Returns the tabOffset
     * 
     * @since     1.5
     * @access    public
     * @return    int
     */
    static function getTabOffset()
    {
        return self::$_tabOffset;
    } //end func getTabOffset

    /**
     * Sets the string used to indent HTML
     * 
     * @since     1.7
     * @param     string    $string     String used to indent ("\11", "\t", '  ', etc.).
     * @access    public
     * @return    void
     */
    static function setTab($string)
    {
        self::$_tab = $string;
    } // end func setTab

    /**
     * Sets the HTML comment to be displayed at the beginning of the HTML string
     *
     * @param     string
     * @since     1.4
     * @access    public
     * @return    void
     */
    static function setComment($comment)
    {
        self::$_comment = $comment;
    } // end func setHtmlComment

    /**
     * Returns the HTML comment
     * 
     * @since     1.5
     * @access    public
     * @return    string
     */
    static function getComment()
    {
        return self::$_comment;
    } //end func getComment

    /**
     * Abstract method.  Must be extended to return the objects HTML
     *
     * @access    public
     * @return    string
     * @abstract
     */
    static function toHtml()
    {
        return '';
    } // end func toHtml

    /**
     * Displays the HTML to the screen
     *
     * @access    public
     */
    static function display()
    {
        print self::toHtml();
    } // end func display

} // end class HTML_Common
?>
