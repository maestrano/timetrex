<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id: select.php,v 1.26 2004/02/28 22:10:16 avb Exp $

require_once('HTML/QuickForm/element.php');

/**
 * Class to dynamically create an HTML SELECT
 *
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Bertrand Mansion <bmansion@mamasam.com>
 * @version      1.0
 * @since        PHP4.04pl1
 * @access       public
 */
class HTML_QuickForm_select extends HTML_QuickForm_element {
    
    // {{{ properties

    /**
     * Contains the select options
     *
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_options = array();
    
    /**
     * Default values of the SELECT
     * 
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_values = null;

    // }}}
    // {{{ constructor
        
    /**
     * Class constructor
     * 
     * @param     string    Select name attribute
     * @param     mixed     Label(s) for the select
     * @param     mixed     Data to be used to populate options
     * @param     mixed     Either a typical HTML attribute string or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_select($elementName=null, $elementLabel=null, $options=null, $attributes=null)
    {
        //HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        new HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        self::$_persistantFreeze = true;
        self::$_type = 'select';
        if (isset($options)) {
            self::load($options);
        }
    } //end constructor
    
    // }}}
    // {{{ apiVersion()

    /**
     * Returns the current API version 
     * 
     * @since     1.0
     * @access    public
     * @return    double
     */
    static function apiVersion()
    {
        return 2.3;
    } //end func apiVersion

    // }}}
    // {{{ setSelected()

    /**
     * Sets the default values of the select box
     * 
     * @param     mixed    $values  Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    void
     */
    static function setSelected($values)
    {
        if (is_string($values) && self::getMultiple()) {
            $values = split("[ ]?,[ ]?", $values);
        }
        if (is_array($values)) {
            self::$_values = array_values($values);
        } else {
            self::$_values = array($values);
        }
    } //end func setSelected
    
    // }}}
    // {{{ getSelected()

    /**
     * Returns an array of the selected values
     * 
     * @since     1.0
     * @access    public
     * @return    array of selected values
     */
    static function getSelected()
    {
        return self::$_values;
    } // end func getSelected

    // }}}
    // {{{ setName()

    /**
     * Sets the input field name
     * 
     * @param     string    $name   Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     */
    static function setName($name)
    {
        self::updateAttributes(array('name' => $name));
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    static function getName()
    {
        return self::getAttribute('name');
    } //end func getName

    // }}}
    // {{{ getPrivateName()

    /**
     * Returns the element name (possibly with brackets appended)
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    static function getPrivateName()
    {
        if (self::getAttribute('multiple')) {
            return self::getName() . '[]';
        } else {
            return self::getName();
        }
    } //end func getPrivateName

    // }}}
    // {{{ setValue()

    /**
     * Sets the value of the form element
     *
     * @param     mixed    $values  Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    void
     */
    static function setValue($value)
    {
        self::setSelected($value);
    } // end func setValue

    // }}}
    // {{{ getValue()

    /**
     * Returns an array of the selected values
     * 
     * @since     1.0
     * @access    public
     * @return    array of selected values
     */
    static function getValue()
    {
        return self::$_values;
    } // end func getValue

    // }}}
    // {{{ setSize()

    /**
     * Sets the select field size, only applies to 'multiple' selects
     * 
     * @param     int    $size  Size of select  field
     * @since     1.0
     * @access    public
     * @return    void
     */
    static function setSize($size)
    {
        self::updateAttributes(array('size' => $size));
    } //end func setSize
    
    // }}}
    // {{{ getSize()

    /**
     * Returns the select field size
     * 
     * @since     1.0
     * @access    public
     * @return    int
     */
    static function getSize()
    {
        return self::getAttribute('size');
    } //end func getSize

    // }}}
    // {{{ setMultiple()

    /**
     * Sets the select mutiple attribute
     * 
     * @param     bool    $multiple  Whether the select supports multi-selections
     * @since     1.2
     * @access    public
     * @return    void
     */
    static function setMultiple($multiple)
    {
        if ($multiple) {
            self::updateAttributes(array('multiple' => 'multiple'));
        } else {
            self::removeAttribute('multiple');
        }
    } //end func setMultiple
    
    // }}}
    // {{{ getMultiple()

    /**
     * Returns the select mutiple attribute
     * 
     * @since     1.2
     * @access    public
     * @return    bool    true if multiple select, false otherwise
     */
    static function getMultiple()
    {
        return (bool)self::getAttribute('multiple');
    } //end func getMultiple

    // }}}
    // {{{ addOption()

    /**
     * Adds a new OPTION to the SELECT
     *
     * @param     string    $text       Display text for the OPTION
     * @param     string    $value      Value for the OPTION
     * @param     mixed     $attributes Either a typical HTML attribute string 
     *                                  or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    static function addOption($text, $value, $attributes=null)
    {
        if (null === $attributes) {
            $attributes = array('value' => $value);
        } else {
            $attributes = self::_parseAttributes($attributes);
            if (isset($attributes['selected'])) {
                // the 'selected' attribute will be set in toHtml()
                self::_removeAttr('selected', $attributes);
                if (is_null(self::$_values)) {
                    self::$_values = array($value);
                } elseif (!in_array($value, self::$_values)) {
                    self::$_values[] = $value;
                }
            }
            self::_updateAttrArray($attributes, array('value' => $value));
        }
        self::$_options[] = array('text' => $text, 'attr' => $attributes);
    } // end func addOption
    
    // }}}
    // {{{ loadArray()

    /**
     * Loads the options from an associative array
     * 
     * @param     array    $arr     Associative array of options
     * @param     mixed    $values  (optional) Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    static function loadArray($arr, $values=null)
    {
        if (!is_array($arr)) {
            return PEAR::raiseError('Argument 1 of HTML_Select::loadArray is not a valid array');
        }
        if (isset($values)) {
            self::setSelected($values);
        }
        foreach ($arr as $key => $val) {
            // Warning: new API since release 2.3
            self::addOption($val, $key);
        }
        return true;
    } // end func loadArray

    // }}}
    // {{{ loadDbResult()

    /**
     * Loads the options from DB_result object
     * 
     * If no column names are specified the first two columns of the result are
     * used as the text and value columns respectively
     * @param     object    $result     DB_result object 
     * @param     string    $textCol    (optional) Name of column to display as the OPTION text 
     * @param     string    $valueCol   (optional) Name of column to use as the OPTION value 
     * @param     mixed     $values     (optional) Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    static function loadDbResult(&$result, $textCol=null, $valueCol=null, $values=null)
    {
        if (!is_object($result) || !is_a($result, 'db_result')) {
            return PEAR::raiseError('Argument 1 of HTML_Select::loadDbResult is not a valid DB_result');
        }
        if (isset($values)) {
            self::setValue($values);
        }
        $fetchMode = ($textCol && $valueCol) ? DB_FETCHMODE_ASSOC : DB_FETCHMODE_DEFAULT;
        while (is_array($row = $result->fetchRow($fetchMode)) ) {
            if ($fetchMode == DB_FETCHMODE_ASSOC) {
                self::addOption($row[$textCol], $row[$valueCol]);
            } else {
                self::addOption($row[0], $row[1]);
            }
        }
        return true;
    } // end func loadDbResult
    
    // }}}
    // {{{ loadQuery()

    /**
     * Queries a database and loads the options from the results
     *
     * @param     mixed     $conn       Either an existing DB connection or a valid dsn 
     * @param     string    $sql        SQL query string
     * @param     string    $textCol    (optional) Name of column to display as the OPTION text 
     * @param     string    $valueCol   (optional) Name of column to use as the OPTION value 
     * @param     mixed     $values     (optional) Array or comma delimited string of selected values
     * @since     1.1
     * @access    public
     * @return    void
     * @throws    PEAR_Error
     */
    static function loadQuery(&$conn, $sql, $textCol=null, $valueCol=null, $values=null)
    {
        if (is_string($conn)) {
            require_once('DB.php');
            $dbConn = &DB::connect($conn, true);
            if (DB::isError($dbConn)) {
                return $dbConn;
            }
        } elseif (is_subclass_of($conn, "db_common")) {
            $dbConn = &$conn;
        } else {
            return PEAR::raiseError('Argument 1 of HTML_Select::loadQuery is not a valid type');
        }
        $result = $dbConn->query($sql);
        if (DB::isError($result)) {
            return $result;
        }
        self::loadDbResult($result, $textCol, $valueCol, $values);
        $result->free();
        if (is_string($conn)) {
            $dbConn->disconnect();
        }
        return true;
    } // end func loadQuery

    // }}}
    // {{{ load()

    /**
     * Loads options from different types of data sources
     *
     * This method is a simulated overloaded method.  The arguments, other than the
     * first are optional and only mean something depending on the type of the first argument.
     * If the first argument is an array then all arguments are passed in order to loadArray.
     * If the first argument is a db_result then all arguments are passed in order to loadDbResult.
     * If the first argument is a string or a DB connection then all arguments are 
     * passed in order to loadQuery.
     * @param     mixed     $options     Options source currently supports assoc array or DB_result
     * @param     mixed     $param1     (optional) See function detail
     * @param     mixed     $param2     (optional) See function detail
     * @param     mixed     $param3     (optional) See function detail
     * @param     mixed     $param4     (optional) See function detail
     * @since     1.1
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    static function load(&$options, $param1=null, $param2=null, $param3=null, $param4=null)
    {
        switch (true) {
            case is_array($options):
                return self::loadArray($options, $param1);
                break;
            case (is_a($options, 'db_result')):
                return self::loadDbResult($options, $param1, $param2, $param3);
                break;
            case (is_string($options) && !empty($options) || is_subclass_of($options, "db_common")):
                return self::loadQuery($options, $param1, $param2, $param3, $param4);
                break;
        }
    } // end func load
    
    // }}}
    // {{{ toHtml()

    /**
     * Returns the SELECT in HTML
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    static function toHtml()
    {
        if (self::$_flagFrozen) {
            return self::getFrozenHtml();
        } else {
            $tabs    = self::_getTabs();
            $strHtml = '';

            if (self::getComment() != '') {
                $strHtml .= $tabs . '<!-- ' . self::getComment() . " //-->\n";
            }

            if (!self::getMultiple()) {
                $attrString = self::_getAttrString(self::$_attributes);
            } else {
                $myName = self::getName();
                self::setName($myName . '[]');
                $attrString = self::_getAttrString(self::$_attributes);
                self::setName($myName);
            }
            $strHtml .= $tabs . '<select' . $attrString . ">\n";

            foreach (self::$_options as $option) {
                if (is_array(self::$_values) && in_array((string)$option['attr']['value'], self::$_values)) {
                    self::_updateAttrArray($option['attr'], array('selected' => 'selected'));
                }
                $strHtml .= $tabs . "\t<option" . self::_getAttrString($option['attr']) . '>' .
                            $option['text'] . "</option>\n";
            }

            return $strHtml . $tabs . '</select>';
        }
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    static function getFrozenHtml()
    {
        $value = array();
        if (is_array(self::$_values)) {
            foreach (self::$_values as $key => $val) {
                for ($i = 0, $optCount = count(self::$_options); $i < $optCount; $i++) {
                    if ($val == self::$_options[$i]['attr']['value']) {
                        $value[$key] = self::$_options[$i]['text'];
                        break;
                    }
                }
            }
        }
        $html = empty($value)? '&nbsp;': join('<br />', $value);
        if (self::$_persistantFreeze) {
            $name = self::getPrivateName();
            // Only use id attribute if doing single hidden input
            if (1 == count($value)) {
                $id     = self::getAttribute('id');
                $idAttr = isset($id)? ' id="' . $id . '"': '';
            } else {
                $idAttr = '';
            }
            foreach ($value as $key => $item) {
                $html .= '<input type="hidden"' . $idAttr . ' name="' . 
                    $name . '" value="' . self::$_values[$key] . '" />';
            }
        }
        return $html;
    } //end func getFrozenHtml

    // }}}
    // {{{ exportValue()

   /**
    * We check the options and return only the values that _could_ have been
    * selected. We also return a scalar value if select is not "multiple"
    */
    static function exportValue(&$submitValues, $assoc = false)
    {
        $value = self::_findValue($submitValues);
        if (is_null($value)) {
            $value = self::getValue();
        } elseif(!is_array($value)) {
            $value = array($value);
        }
        if (is_array($value) && !empty(self::$_options)) {
            $cleanValue = null;
            foreach ($value as $v) {
                for ($i = 0, $optCount = count(self::$_options); $i < $optCount; $i++) {
                    if ($v == self::$_options[$i]['attr']['value']) {
                        $cleanValue[] = $v;
                        break;
                    }
                }
            }
        } else {
            $cleanValue = $value;
        }
        if (is_array($cleanValue) && !self::getMultiple()) {
            return self::_prepareValue($cleanValue[0], $assoc);
        } else {
            return self::_prepareValue($cleanValue, $assoc);
        }
    }
    
    // }}}
} //end class HTML_QuickForm_select
?>
