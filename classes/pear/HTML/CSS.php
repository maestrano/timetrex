<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997 - 2004 The PHP Group                              |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author:  Klaus Guenther <klaus@capitalfocus.org>                     |
// +----------------------------------------------------------------------+
//
// $Id: CSS.php,v 1.34 2004/05/21 16:54:56 thesaur Exp $

/**
 * Base class for CSS definitions
 *
 * This class handles the details for creating properly constructed CSS declarations.
 *
 * Example for direct output of stylesheet:
 * <code>
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * 
 * // define styles
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 *
 * // output the stylesheet directly to browser
 * $css->display();
 * </code>
 *
 * Example of group usage:
 * <code>
 * require_once 'HTML/CSS.php';
 *
 * $css = new HTML_CSS();
 *
 * // create new group
 * $group1 = $css->createGroup('body, html');
 * $group2 = $css->createGroup('p, div');
 *
 * // define styles
 * $css->setGroupStyle($group1, 'background-color', '#0c0c0c');
 * $css->setGroupStyle($group1, 'color', '#ffffff');
 * $css->setGroupStyle($group2, 'text-align', 'left');
 * $css->setGroupStyle($group2, 'background-color', '#ffffff');
 * $css->setGroupStyle($group2, 'color', '#0c0c0c');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 *
 * // output the stylesheet directly to browser
 * $css->display();
 * </code>
 *
 * Example in combination with HTML_Page:
 * <code>
 * require_once 'HTML/Page.php';
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 *
 * $p = new HTML_Page();
 *
 * $p->setTitle("My page");
 * // it can be added as an object
 * $p->addStyleDeclaration($css, 'text/css');
 * $p->setMetaData("author", "My Name");
 * $p->addBodyContent("<h1>headline</h1>");
 * $p->addBodyContent("<p>some text</p>");
 * $p->addBodyContent("<p>some more text</p>");
 * $p->addBodyContent("<p>yet even more text</p>");
 * $p->display();
 * </code>
 * 
 * Example for generating inline code:
 * <code>
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * 
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 * $css->setSameStyle('body', 'p');
 * 
 * echo '<body style="' . $css->toInline('body') . '">';
 * // will output:
 * // <body style="font:12pt helvetica, arial, sans-serif;background-color:#0c0c0c;color:#ffffff;">
 * </code>
 *
 * @author     Klaus Guenther <klaus@capitalfocus.org>
 * @package    HTML_CSS
 * @version    0.3.3
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */


require_once 'HTML/Common.php';

/**#@+
 * Error logging file
 * 
 * The reason these are required here is because they are needed in
 * _initErrorStack, which is called in the constructor.
 * 
 * @since      0.3.3
 */
require_once 'Log.php';
require_once 'PEAR/ErrorStack.php';
/**#@-*/

/**#@+
 * Basic error codes
 *
 * @var        integer
 * @since      0.3.3
 */
define ('HTML_CSS_ERROR_INVALID_INPUT',         -100);
define ('HTML_CSS_ERROR_INVALID_GROUP',         -101);
define ('HTML_CSS_ERROR_NO_GROUP',              -102);
define ('HTML_CSS_ERROR_NO_ELEMENT',            -103);
define ('HTML_CSS_ERROR_NO_ELEMENT_PROPERTY',   -104);
define ('HTML_CSS_ERROR_NO_FILE',               -105);
define ('HTML_CSS_ERROR_WRITE_FILE',            -106);
/**#@-*/

/**
 * Base class for CSS definitions
 * 
 * This class handles the details for creating properly constructed CSS declarations.
 * 
 * @author     Klaus Guenther <klaus@capitalfocus.org>
 * @package    HTML_CSS
 * @version    0.3.3
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */

class HTML_CSS extends HTML_Common {
    
    /**
     * Contains the CSS definitions.
     *
     * @var     array
     * @since   0.2.0
     * @access  private
     */
    var $_css = array();
    
    /**
     * Contains "alibis" (other elements that share a definition) of an element defined in CSS
     *
     * @var     array
     * @since   0.2.0
     * @access  private
     */
    var $_alibis = array();
    
    /**
     * Controls caching of the page
     *
     * @var     bool
     * @since   0.2.0
     * @access  private
     */
    var $_cache = true;
    
    /**
     * Contains the character encoding string
     *
     * @var     string
     * @since   0.2.0
     * @access  private
     */
    var $_charset = 'iso-8859-1';

    /**
     * Contains grouped styles
     *
     * @var     array
     * @since   0.3.0
     * @access  private
     */
    var $_groups = array();
    
    /**
     * Number of CSS definition groups
     *
     * @var     int
     * @since   0.3.0
     * @access  private
     */
    var $_groupCount = 0;

    /**
     * Defines whether to output all properties on one line
     *
     * @var     bool
     * @since   0.3.3
     * @access  private
     */
    var $_singleLine = false;

    /**
     * Defines whether element selectors should be automatically lowercased.
     * Determines how parseSelectors treats the data.
     *
     * @var     bool
     * @since   0.3.2
     * @access  private
     */
    var $_xhtmlCompliant = true;

    /**
     * Package name used by PEAR_ErrorStack functions
     *
     * @var        string
     * @since      0.3.3
     * @access     private
     */
    var $_package;

    /**
     * Class constructor
     *
     * @param   array    Pass options to the constructor. Valid options are
     *                   xhtml (sets xhtml compliance), tab (sets indent
     *                   string), cache (determines whether the nocache
     *                   headers are sent), filename (name of file to be
     *                   parsed), oneline (bool, whether to output each
     *                   definition on one line)
     * @since   0.2.0
     * @access  public
     */
    function HTML_CSS($attributes = array(), $errorPrefs = array())
    {
        $this->_initErrorStack($errorPrefs);

        if ($attributes) {
            $attributes = $this->_parseAttributes($attributes);
        }
        
        if (isset($attributes['xhtml'])) {
            $this->setXhtmlCompliance($attributes['xhtml']);
        }
        
        if (isset($attributes['tab'])) {
            $this->setTab($attributes['tab']);
        }
        
        if (isset($attributes['filename'])) {
            $this->parseFile($attributes['filename']);
        }
        
        if (isset($attributes['cache'])) {
            $this->setCache($attributes['cache']);
        }
        
        if (isset($attributes['oneline'])) {
            $this->setSingleLineOutput(true);
        }
    } // end constructor HTML_CSS
    
    /**
     * Returns the current API version
     *
     * @access   public
     * @since    0.2.0
     * @return   double
     */
    function apiVersion()
    {
        return 0.3;
    } // end func apiVersion
    
    /**
     * Determines whether definitions are output single line or multiline
     *
     * @param    bool     $value
     * @access   public
     * @since    0.3.3
     * @return   void
     */
    function setSingleLineOutput($value)
    {
        $this->_singleLine = $value;
    } // end func apiVersion
    
    /**
     * Parses a string containing selector(s).
     * It processes it and returns an array or string containing
     * modified selectors (depends on XHTML compliance setting;
     * defaults to ensure lowercase element names)
     *
     * @param    string  $selectors   Selector string
     * @param    int     $outputMode  0 = string; 1 = array; 3 = deep array
     * @since    0.3.2
     * @access   public
     * @return   mixed
     */
    function parseSelectors($selectors, $outputMode = 0)
    {
        $selectors_array =  explode(',', $selectors);
        $i = 0;
        foreach ($selectors_array as $selector) {
            // trim to remove possible whitespace
            $selector = trim($this->collapseInternalSpaces($selector));
            if (strpos($selector, ' ')) {
                $sel_a = array();
                foreach(explode(' ', $selector) as $sub_selector) {
                    $sel_a[] = $this->parseSelectors($sub_selector, $outputMode);
                }
                if ($outputMode === 0) {
                        $array[$i] = implode(' ', $sel_a);
                } else {
                    $sel_a2 = array();
                    foreach ($sel_a as $sel_a_temp) {
                        $sel_a2 = array_merge($sel_a2, $sel_a_temp);
                    }
                    if ($outputMode == 2) {
                        $array[$i]['inheritance'] = $sel_a2;
                    } else {
                        $array[$i] = implode(' ', $sel_a2);
                    }
                }
                $i++;
            } else {
                // initialize variables
                $element = '';
                $id      = '';
                $class   = '';
                $pseudo  = '';
                
                if (strpos($selector, ':') !== false) {
                    $pseudo   = strstr($selector, ':');
                    $selector = substr($selector, 0 , strpos($selector, ':'));
                }
                if (strpos($selector, '.') !== false){
                    $class    = strstr($selector, '.');
                    $selector = substr($selector, 0 , strpos($selector, '.'));
                }
                if (strpos($selector, '#') !== false) {
                    $id       = strstr($selector, '#');
                    $selector = substr($selector, 0 , strpos($selector, '#'));
                }
                if ($selector != '') {
                    $element  = $selector;
                }
                if ($this->_xhtmlCompliant){
                    $element  = strtolower($element);
                    $pseudo   = strtolower($pseudo);
                }
                if ($outputMode == 2) {
                    $array[$i]['element'] = $element;
                    $array[$i]['id']      = $id;
                    $array[$i]['class']   = $class;
                    $array[$i]['pseudo']  = $pseudo;
                } else {
                    $array[$i] = $element.$id.$class.$pseudo;
                }
                $i++;
            }
        }
        if ($outputMode == 0) {
            $output = implode(', ', $array);
            return $output;
        } else {
            return $array;
        }
    } // end func parseSelectors
    
    /**
     * Strips excess spaces in string.
     *
     * @return    string
     * @since     0.3.2
     * @access    public
     */
    function collapseInternalSpaces($subject){
        $string = preg_replace('/\s+/', ' ', $subject);
        return $string;
    } // end func collapseInternalSpaces
    
    /**
     * Sets or adds a CSS definition for a CSS definition group
     *
     * @param    bool     $value    Boolean value
     * @since    0.3.2
     * @access   public
     */
    function setXhtmlCompliance($value)
    {
        if (!is_bool($value)) {
            $this->raiseError(HTML_CSS_ERROR_INVALID_INPUT, 'exception',
                array('var' => '$value',
                      'was' => gettype($value),
                      'expected' => 'boolean',
                      'paramnum' => 1));
        }
        $this->_xhtmlCompliant = $value;
    } // end func setGroupStyle

    /**
     * Creates a new CSS definition group. Returns an integer identifying the group.
     *
     * @param    string  $selectors   Selector(s) to be defined, comma delimited.
     * @param    mixed   $identifier  Group identifier. If not passed, will return an automatically assigned integer.
     * @return   int
     * @since    0.3.0
     * @access   public
     */
    function createGroup($selectors, $identifier = null)
    {
        if ($identifier === null) {
            $this->_groupCount++;
            $group = $this->_groupCount;
        } else {
            if (isset($this->_groups[$identifier])){
                return $this->raiseError(HTML_CSS_ERROR_INVALID_GROUP, 'error',
                    array('identifier' => $identifier));
            }
            $group = $identifier;
        }
        $selectors = $this->parseSelectors($selectors, 1);
        foreach ($selectors as $selector) {
            $this->_groups[$group]['selectors'][] = $selector;
            $this->_alibis[$selector][$group] = true;
        }
        if ($identifier == null){
            return $group;
        }
    } // end func createGroup

    /**
     * Sets or adds a CSS definition for a CSS definition group
     *
     * @param    int     $group     CSS definition group identifier
     * @since    0.3.0
     * @access   public
     */
    function unsetGroup($group)
    {
        if ($group < 0 || $group > $this->_groupCount) {
            return $this->raiseError(HTML_CSS_ERROR_NO_GROUP, 'error',
                array('identifier' => $group));
        }
        
        foreach ($this->_groups[$group]['selectors'] as $selector) {
            unset ($this->_alibis[$selector][$group]);
            if (count($this->_alibis[$selector]) == 0) {
                unset($this->_alibis[$selector]);
            }
        }
        unset($this->_groups[$group]);
    } // end func unsetGroup

    /**
     * Sets or adds a CSS definition for a CSS definition group
     *
     * @param    int     $group     CSS definition group identifier
     * @param    string  $property  Property defined
     * @param    string  $value     Value assigned
     * @since    0.3.0
     * @access   public
     */
    function setGroupStyle($group, $property, $value)
    {
        if ($group < 0 || $group > $this->_groupCount) {
            return $this->raiseError(HTML_CSS_ERROR_NO_GROUP, 'error',
                array('identifier' => $group));
        }
        $this->_groups[$group]['properties'][$property]= $value;
    } // end func setGroupStyle

    /**
     * Returns a CSS definition for a CSS definition group
     *
     * @param    int     $group     CSS definition group identifier
     * @param    string  $property  Property defined
     * @return   string
     * @since    0.3.0
     * @access   public
     */
    function getGroupStyle($group, $property)
    {
        if ($group < 0 || $group > $this->_groupCount) {
            return $this->raiseError(HTML_CSS_ERROR_NO_GROUP, 'error',
                array('identifier' => $group));
        }
        return $this->_groups[$group]['properties'][$property];
    } // end func getGroupStyle

    /**
     * Adds a selector to a CSS definition group.
     *
     * @param    int     $group       CSS definition group identifier
     * @param    string  $selectors   Selector(s) to be defined, comma delimited.
     * @return   int
     * @since    0.3.0
     * @access   public
     */
    function addGroupSelector($group, $selectors)
    {
        if ($group < 0 || $group > $this->_groupCount) {
            return $this->raiseError(HTML_CSS_ERROR_NO_GROUP, 'error',
                array('identifier' => $group));
        }
        $selectors = $this->parseSelectors($selectors, 1);
        foreach ($selectors as $selector) {
            $this->_groups[$group]['selectors'][] = $selector;
            $this->_alibis[$selector][$group] = true;
        }
    } // end func addGroupSelector

    /**
     * Removes a selector from a group.
     *
     * @param    int     $group       CSS definition group identifier
     * @param    string  $selectors   Selector(s) to be removed, comma delimited.
     * @return   int
     * @since    0.3.0
     * @access   public
     */
    function removeGroupSelector($group, $selectors)
    {
        if ($group < 0 || $group > $this->_groupCount) {
            return $this->raiseError(HTML_CSS_ERROR_NO_GROUP, 'error',
                array('identifier' => $group));
        }
        $selectors =  $this->parseSelectors($selectors, 1);
        foreach ($selectors as $selector) {
            foreach ($this->_groups[$group]['selectors'] as $key => $value) {
                if ($value == $selector) {
                    unset($this->_groups[$group]['selectors'][$key]);
                }
            }
            unset($this->_alibis[$selector][$group]);
        }
    } // end func removeGroupSelector
   
    /**
     * Sets or adds a CSS definition
     *
     * @param    string  $element   Element (or class) to be defined
     * @param    string  $property  Property defined
     * @param    string  $value     Value assigned
     * @since    0.2.0
     * @access   public
     */
    function setStyle ($element, $property, $value)
    {
        $element = $this->parseSelectors($element);
        $this->_css[$element][$property]= $value;
    } // end func setStyle
    
    /**
     * Retrieves the value of a CSS property
     *
     * @param    string  $element   Element (or class) to be defined
     * @param    string  $property  Property defined
     * @since    0.3.0
     * @access   public
     */
    function getStyle($element, $property)
    {
        $property_value = '';
        $element = $this->parseSelectors($element);
        if (!isset($this->_css[$element]) && !isset($this->_alibis[$element])) {
            return $this->raiseError(HTML_CSS_ERROR_NO_ELEMENT, 'error',
                array('identifier' => $element));
        }
        
        if (isset($this->_alibis[$element])) {
            foreach ($this->_alibis[$element]as $group_id => $empty) {
                if (isset($this->_groups[$group_id]['properties'][$property])) {
                    $property_value = $this->_groups[$group_id]['properties'][$property];
                }
            }
        }
        if (isset($this->_css[$element])) {
            if (isset($this->_css[$element][$property])) {
                $property_value = $this->_css[$element][$property];
            }
        }
        if ($property_value == '') {
            return $this->raiseError(HTML_CSS_ERROR_NO_ELEMENT_PROPERTY, 'error',
                array('identifier' => $element, 
                      'property'   => $property));
        }
        return $property_value;
    } // end func getStyle
    
    /**
     * Sets or changes the properties of new selectors to the values of an existing selector
     *
     * @param    string  $old    Selector that is already defined
     * @param    string  $new    New selector(s) that should share the same 
     *                           definitions, separated by commas
     * @since    0.2.0
     * @access   public
     */
    function setSameStyle ($new, $old)
    {
        $old = $this->parseSelectors($old);
        if (!isset($this->_css[$old])) {
            return $this->raiseError(HTML_CSS_ERROR_NO_ELEMENT, 'error',
                array('identifier' => $old));
        }
        $others = $this->parseSelectors($new, 1);
        foreach ($others as $other) {
            $other = trim($other);
            foreach($this->_css[$old] as $property => $value) {
                $this->_css[$other][$property] = $value;
            }
        }
    } // end func setSameStyle
    
    /**
     * Defines if the document should be cached by the browser. Defaults to false.
     *
     * @param string $cache Options are currently 'true' or 'false'. Defaults to 'true'.
     * @since  0.2.0
     * @access public
     */
    function setCache($cache = 'true')
    {
        if ($cache == 'true'){
            $this->_cache = true;
        } else {
            $this->_cache = false;
        }
    } // end func setCache
    
    /**
     * Defines the charset for the file. defaults to ISO-8859-1 because of CSS1
     * compatability issue for older browsers.
     *
     * @param string $type Charset encoding; defaults to ISO-8859-1.
     * @since  0.2.0
     * @access public
     */
    function setCharset($type = 'iso-8859-1')
    {
        $this->_charset = $type;
    } // end func setCharset
    
    /**
     * Returns the charset encoding string
     *
     * @since  0.2.0
     * @access public
     */
    function getCharset()
    {
        return $this->_charset;
    } // end func getCharset
    
    /**
     * Parse a textstring that contains css information
     *
     * @param    string  $str    text string to parse
     * @since    0.3.0
     * @access   public
     * @return   void
     */
    function parseString($str) 
    {
        // Remove comments
        $str = preg_replace("/\/\*(.*)?\*\//Usi", '', $str);
        
        // Parse each element of csscode
        $parts = explode("}",$str);
        foreach($parts as $part) {
            $part = trim($part);
            if (strlen($part) > 0) {
                
                // Parse each group of element in csscode
                list($keystr,$codestr) = explode("{",$part);
                $key_a = $this->parseSelectors($keystr, 1);
                $keystr = implode(', ', $key_a);
                // Check if there are any groups.
                if (strpos($keystr, ',')) {
                    $group = $this->createGroup($keystr);
                    
                    // Parse each property of an element
                    $codes = explode(";",trim($codestr));
                    foreach ($codes as $code) {
                        if (strlen(trim($code)) > 0) {
                            // find the property and the value
                            $property = trim(substr($code, 0 , strpos($code, ':', 0)));
                            $value    = trim(substr($code, strpos($code, ':', 0) + 1));
                            
                            // check to see if this group comes after an individual
                            // selector setting. If this is the case, the group setting
                            // overrides the individual setting
                            foreach ($key_a as $key) {
                                if (isset($this->_css[$key][$property])) {
                                    unset($this->_css[$key][$property]);
                                }
                            }
                            $this->setGroupStyle($group, $property, $value);
                        }
                    }
                } else {
                    
                    // let's get on with regular definitions
                    $key = trim($keystr);
                    if (strlen($key) > 0) {
                        // Parse each property of an element
                        $codes = explode(";",trim($codestr));
                        foreach ($codes as $code) {
                            if (strlen(trim($code)) > 0) {
                                $property = trim(substr($code, 0 , strpos($code, ':')));
                                $value    = substr($code, strpos($code, ':') + 1);
                                $this->setStyle($key, $property, trim($value));
                            }
                        }
                    }
                }
            }
        }
    } // end func parseString
    
    /**
     * Parse a file that contains CSS information
     *
     * @param    string  $filename    file to parse
     * @since    0.3.0
     * @return   void
     * @access   public
     */
    function parseFile($filename) 
    { 
        if (!file_exists($filename)) {
            return $this->raiseError(HTML_CSS_ERROR_NO_FILE, 'error',
                    array('identifier' => $filename));
        }

        if (function_exists('file_get_contents')){
            $this->parseString(file_get_contents($filename));
        } else {
            $file = fopen("$filename", "rb");
            $this->parseString(fread($file, filesize($filename)));
            fclose($file);
        }
    } // func parseFile
    
    /**
     * Generates and returns the array of CSS properties
     *
     * @return  array
     * @since   0.2.0
     * @access  public
     */
    function toArray()
    {
        // initialize $alibis
        $alibis = array();

        $newCssArray = array();

        // If there are groups, iterate through the array and generate the CSS
        if (count($this->_groups) > 0) {
            foreach ($this->_groups as $group) {

                // Start group definition
                foreach ($group['selectors'] as $selector){
                    $selector = trim($selector);
                    $alibis[] = $selector;
                }
                $alibis = implode(', ',$alibis);

                foreach ($group['properties'] as $key => $value) {
                    $newCssArray[$alibis][$key] = $value;
                }
                unset($alibis);

            }
        }

        // Iterate through the array and process each element
        foreach ($this->_css as $element => $property) {

            foreach ($property as $key => $value) {
                $newCssArray[$element][$key] = $value;
            }

        }
        return $newCssArray;
    } // end func toArray
    
    /**
     * Generates and returns the CSS properties of an element or class as a string for inline use.
     *
     * @param   string  $element    Element or class for which inline CSS should be generated
     * @return  string
     * @since   0.2.0
     * @access  public
     */
    function toInline($element)
    {
        $strCss = '';
        $newCssArray = '';
        
        // Iterate through the array of properties for the supplied element
        // This allows for grouped elements definitions to work
        if (isset($this->_alibis[$element])) {
            foreach ($this->_alibis[$element] as $group => $status) {
                foreach ($this->_groups[$group]['properties'] as $key => $value) {
                    $newCssArray[$key] = $value;
                }
            }
        }
        
        // The reason this comes second is because if something is defined twice,
        // the value specifically assigned to this element should override
        // values inherited from other element definitions
        if ($this->_css[$element]) {
            foreach ($this->_css[$element] as $key => $value) {
                if ($key != 'other-elements') {
                    $newCssArray[$key] = $value;
                }
            }
        }
        
        foreach ($newCssArray as $key => $value) {
            $strCss .= $key . ':' . $value . ";";
        }
        
        // Let's roll!
        return $strCss;
    } // end func toInline
    
    /**
     * Generates CSS and stores it in a file.
     *
     * @return  void
     * @since   0.3.0
     * @access  public
     */
    function toFile($filename)
    {
        if (function_exists('file_put_content')){
            file_put_content($filename, $this->toString());
        } else {
            $file = fopen($filename,'wb');
            fwrite($file, $this->toString());
            fclose($file);
        }
        if (!file_exists($filename)){
            return $this->raiseError(HTML_CSS_ERROR_WRITE_FILE, 'error',
                    array('filename' => $filename));
        }
        
    } // end func toFile
    
    /**
     * Generates and returns the complete CSS as a string.
     *
     * @return string
     * @since   0.2.0
     * @access public
     */
    function toString()
    {
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tabs  = $this->_getTabs();
        $tab   = $this->_getTab();
        
        // initialize $alibis
        $alibis = array();
        
        $strCss = '';
        
        // Allow a CSS comment
        if ($this->_comment) {
            $strCss = $tabs . '/* ' . $this->getComment() . ' */' . $lnEnd;
        }
        
        // If there are groups, iterate through the array and generate the CSS
        if (count($this->_groups) > 0) {
            foreach ($this->_groups as $group) {
                
                // Start group definition
                foreach ($group['selectors'] as $selector){
                    $selector = trim($selector);
                    $alibis[] = $selector;
                }
                $alibis = implode(', ',$alibis);
                $definition = $alibis . ' {' . $lnEnd;
                unset($alibis);
                
                // Add CSS definitions
                foreach ($group['properties'] as $key => $value) {
                    $definition .= $tabs . $tab . $key . ': ' . $value . ';' . $lnEnd;
                }
                $definition .= $tabs . '}';
                
                if ($this->_singleLine) {
                    $definition = $this->collapseInternalSpaces($definition);
                }
                $strCss .= $lnEnd . $tabs . $definition . $lnEnd;
            }
        }
        
        // Iterate through the array and process each element
        foreach ($this->_css as $element => $property) {

            //start CSS element definition
            $definition = $element . ' {' . $lnEnd;
            
            foreach ($property as $key => $value) {
                $definition .= $tabs . $tab . $key . ': ' . $value . ';' . $lnEnd;
            }
            
            // end CSS element definition
            $definition .= $tabs . '}';
            
            // if this is to be on a single line, collapse
            if ($this->_singleLine) {
                $definition = $this->collapseInternalSpaces($definition);
            }
            
            // add to strCss
            $strCss .= $lnEnd . $tabs . $definition . $lnEnd;
        }
        
        if ($this->_singleLine) {
            $strCss = str_replace($lnEnd.$lnEnd, $lnEnd, $strCss);
        }
        
        // Let's roll!
        $strCss = preg_replace('/^(\n|\r\n|\r)/', '', $strCss);
        return $strCss;
    } // end func toString
    
    /**
     * Outputs the stylesheet to the browser.
     *
     * @since     0.2.0
     * @access    public
     */
    function display()
    {
        
        if(! $this->_cache) {
            header("Expires: Tue, 1 Jan 1980 12:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
        }
        
        // set character encoding
        header("Content-Type: text/css; charset=" . $this->_charset);
        
        $strCss = $this->toString();
        print $strCss;
    } // end func display

    /**
     * Initialize Error Stack engine
     *
     * @param      array     $prefs         hash of params for PEAR::Log object list
     *
     * @return     void
     * @since      0.3.3
     * @access     private
     */
    function _initErrorStack($prefs = array())
    {
        $this->_package = 'HTML_CSS';
        $stack = PEAR_ErrorStack::singleton($this->_package);

        if (isset($prefs['pushCallback']) && is_callable($prefs['pushCallback'])) {
            $cb = $prefs['pushCallback'];
        } else {
            $cb = array('HTML_CSS', '_handleError');
        }
        $stack->pushCallback($cb);

        if (isset($prefs['msgCallback'])) {
            $cb = $prefs['msgCallback'];
        } else {
            $cb = array('HTML_CSS', '_msgCallback');
        }
        $stack->setMessageCallback($cb);
        if (isset($prefs['contextCallback'])) {
            $stack->setContextCallback($prefs['contextCallback']);
        }
        $messages = $this->_getErrorMessage();
        $stack->setErrorMessageTemplate($messages);
        $composite = &Log::singleton('composite');
        $stack->setLogger($composite);

        $drivers = isset($prefs['handler']) ? $prefs['handler'] : array();
        $display_errors = isset($prefs['display_errors']) ? strtolower($prefs['display_errors']) : 'on';
        $log_errors = isset($prefs['log_errors']) ? strtolower($prefs['log_errors']) : 'on';
        
        foreach ($drivers as $handler => $params) {
            if ((strtolower($handler) == 'display') && ($display_errors == 'off')) {
                continue;
            }
            if ((strtolower($handler) != 'display') && ($log_errors == 'off')) {
                continue;
            }       
            $name = isset($params['name']) ? $params['name'] : '';
            $ident = isset($params['ident']) ? $params['ident'] : '';
            $conf = isset($params['conf']) ? $params['conf'] : array();
            $level = isset($params['level']) ? $params['level'] : PEAR_LOG_DEBUG;
            
            $logger = &Log::singleton(strtolower($handler), $name, $ident, $conf, $level);
            $composite->addChild($logger);
        }

        // Add at least the Log::display driver to output errors on browser screen
        if (!array_key_exists('display', $drivers)) {
            if ($display_errors == 'on') {
                $logger = &Log::singleton('display');
                $composite->addChild($logger);
            }
        }
    }

    /**
     * User callback to generate error messages for any instance
     *
     * @param      object    $stack         PEAR_ErrorStack instance
     * @param      array     $err           current error with context info 
     *
     * @return     string
     * @since      0.3.3
     * @access     private
     */
    function _msgCallback(&$stack, $err)
    {
        $message = call_user_func_array(array(&$stack, 'getErrorMessage'), array(&$stack, $err));

        if (isset($err['context']['function'])) {
            $message .= ' in ' . $err['context']['class'] . '::' . $err['context']['function'];
        }
        if (isset($err['context']['file'])) {
            $message .= ' (file ' . $err['context']['file'] . ' at line ' . $err['context']['line'] .')';
        }
        return $message;
    }

    /**
     * Error Message Template array
     *
     * @return     string
     * @since      0.3.3
     * @access     private
     */
    function _getErrorMessage()
    {
        $messages = array(
            HTML_CSS_ERROR_INVALID_INPUT =>
                'invalid input, parameter #%paramnum% '
                    . '"%var%" was expecting '
                    . '"%expected%", instead got "%was%"',
            HTML_CSS_ERROR_INVALID_GROUP => 
                'group "%identifier%" already exist ',
            HTML_CSS_ERROR_NO_GROUP => 
                'group "%identifier%" does not exist ',
            HTML_CSS_ERROR_NO_ELEMENT => 
                'element "%identifier%" does not exist ',
            HTML_CSS_ERROR_NO_ELEMENT_PROPERTY => 
                'element "%identifier%" does not have property "%property%" ',
            HTML_CSS_ERROR_NO_FILE => 
                'filename "%identifier%" does not exist ',
            HTML_CSS_ERROR_WRITE_FILE =>
                'failed to write to "%filename%"'
        );
        return $messages;
    }

    /**
     * Default internal error handler
     * Dies if the error is an exception (and would have died anyway)
     *
     * @since      0.3.3
     * @access     private
     */
    function _handleError($err)
    {
        if ($err['level'] == 'exception') {
            die();
        } else {
            return $err;
        }
    }

    /**
     * Add an error to the stack
     * Dies if the error is an exception (and would have died anyway)
     *
     * @param      integer   $code       Error code.
     * @param      string    $level      The error level of the message. 
     *                                   Valid are PEAR_LOG_* constants
     * @param      array     $params     Associative array of error parameters
     * @param      array     $trace      Error context info (see debug_backtrace() contents)
     *
     * @return     array     PEAR_ErrorStack instance. And with context info (if PHP 4.3+)
     * @since      0.3.3
     * @access     public
     */
    function raiseError($code, $level, $params)
    {
        if (function_exists('debug_backtrace')) {
            $trace = debug_backtrace();     // PHP 4.3+
        } else {
            $trace = null;                  // PHP 4.1.x, 4.2.x (no context info available)
        }
        $err = PEAR_ErrorStack::staticPush($this->_package, $code, $level, $params, false, false, $trace);
        return $err;
    }
}
?>
