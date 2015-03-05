<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Default Filter: Handle all the tokens. Uses K & R style
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 * @category   PHP
 * @package PHP_Beautifier
 * @subpackage Filter
 * @author Claudio Bustos <cdx@users.sourceforge.com>
 * @copyright  2004-2010 Claudio Bustos
 * @link     http://pear.php.net/package/PHP_Beautifier
 * @link     http://beautifyphp.sourceforge.net
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id:$
 */
/**
 * Default Filter: Handle all the tokens. Uses K & R style
 *
 * This filters is loaded by default in {@link PHP_Beautifier}. Can handle all the tokens.
 * If one of the tokens doesn't have a function, is added without modification (See {@link __call()})
 * The most important modifications are:
 * - All the statements inside control structures, functions and class are indented with K&R style
 * <CODE>
 * function myFunction() {
 *     echo 'hi';
 * }
 * </CODE>
 * - All the comments in new lines are indented. In multi-line comments, all the lines are indented, too.
 * This class is final, so don't try to extend it!
 * @category   PHP
 * @package PHP_Beautifier
 * @subpackage Filter
 * @author Claudio Bustos <cdx@users.sourceforge.com>
 * @copyright  2004-2010 Claudio Bustos
 * @link     http://pear.php.net/package/PHP_Beautifier
 * @link     http://beautifyphp.sourceforge.net
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: 0.1.15
 */
class PHP_Beautifier_Filter_TTDefault extends PHP_Beautifier_Filter
{
    protected $sDescription = 'Default Filter for PHP_Beautifier';
    function __call($sMethod, $aArgs)
    {
        if (!is_array($aArgs) or count($aArgs) != 1) {
            throw (new Exception('Call to Filter::__call with wrong argument'));
        }
        PHP_Beautifier_Common::getLog()->log('Default Filter:unhandled[' . $aArgs[0] . ']', PEAR_LOG_DEBUG);
        $this->oBeaut->add($aArgs[0]);
    }
    // Bypass the function!
    public function off()
    {
    }

    function t_logical($sTag)
    {

        if ( $sTag == '||' ) {
            $sTag = 'OR';
        } elseif ( $sTag == '&&' ) {
            $sTag = 'AND';
        }

        //If there is no newline before the logical AND/OR, then remove all whitespace.
        if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
            $this->oBeaut->removeWhitespace();
            $this->oBeaut->add( ' '. strtoupper($sTag) .' ');
            $this->oBeaut->removeWhitespace();
        } else {
            //Newline before
            $this->oBeaut->add( strtoupper($sTag) .' ');
            $this->oBeaut->removeWhitespace();
        }
    }

    public function t_comma($sTag)
    {
        if ($this->oBeaut->getControlParenthesis() != T_ARRAY) {
            $aNextToken = $this->oBeaut->getToken($this->oBeaut->iCount+1);
            $sNextWhitespace = ($aNextToken[0] == T_WHITESPACE) ? $aNextToken[1] : '';

            //echo "Test3: \"". $sTag ."\"\n";
            //echo "  Test1: \"". $this->oBeaut->getPreviousTokenContent() ."\"\n";
            //echo "  Test1: \"". $this->oBeaut->getNextTokenContent() ."\"\n";
            //echo "  Test1: \"". $this->oBeaut->getPreviousWhitespace() ."\"\n";
            //echo "  Test1: \"".  $sNextWhitespace ."\"\n";

            //Don't add more whitespace if it already exists.
            if ( strlen( $sNextWhitespace ) == 0 ) {
                $this->oBeaut->add($sTag . ' ');
            } else {
                $this->oBeaut->add($sTag);
            }
        } else {
            $this->oBeaut->add($sTag . ' ');
            //if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
            //    $this->oBeaut->addNewLine();
            //}
            //$this->oBeaut->addIndent();
        }
    }

    public function t_parenthesis_open($sTag)
    {

        //var_dump($this->oBeaut->getControlParenthesis());
        //echo "Test1: \"". $sTag ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getPreviousTokenContent() ."\"\n";

        //echo "Test3: \"". $sTag ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getPreviousTokenContent() ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getNextTokenContent() ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getPreviousWhitespace() ."\"\n";


        if ($this->oBeaut->getControlParenthesis() == T_ARRAY) {
            $this->oBeaut->add($sTag . ' ' );
            ////$this->oBeaut->addNewLine();
            //$this->oBeaut->incIndent();
            //$this->oBeaut->addIndent();
        } elseif ( $this->oBeaut->getControlParenthesis() == T_IF OR $this->oBeaut->getControlParenthesis() == T_ELSEIF OR $this->oBeaut->getControlParenthesis() == T_ELSE ) {
            //echo "Test2: \"". $sTag ."\"\n";

            $this->oBeaut->removeWhitespace();
            $this->oBeaut->add( ' '. $sTag );
            $this->oBeaut->removeWhitespace();
        } else {
            $aNextToken = $this->oBeaut->getToken($this->oBeaut->iCount+1);
            $sNextWhitespace = ($aNextToken[0] == T_WHITESPACE) ? $aNextToken[1] : '';

            if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
                $this->oBeaut->removeWhitespace();
            }
            if ( strlen( $sNextWhitespace ) == 0 ) {
                $this->oBeaut->add($sTag . ' ');
            } else {
                $this->oBeaut->add($sTag);
            }
        }
    }
    public function t_parenthesis_close($sTag)
    {
        //var_dump($this->oBeaut->getControlParenthesis());
        //echo "sTag: ". $sTag ."\n";
        //echo "Previous Content: ". $this->oBeaut->getPreviousTokenContent() ."\n";

        if ($this->oBeaut->getControlParenthesis() == T_ARRAY) {
            //$this->oBeaut->removeWhitespace();
            //$this->oBeaut->decIndent();
            //if ($this->oBeaut->getPreviousTokenContent() != '(') {
            //    $this->oBeaut->addNewLine();
            //    $this->oBeaut->addIndent();
            //}
            $this->oBeaut->add($sTag . ' ');
        } else {
            if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
                $this->oBeaut->removeWhitespace();
                if ( $this->oBeaut->getPreviousTokenContent() == '(' ) {
                    $this->oBeaut->add($sTag);
                } else {
                    $this->oBeaut->add(' '. $sTag);
                }
            } else {
                $this->oBeaut->add($sTag);
            }
        }
    }

    function t_assigment($sTag)
    {
        $aNextToken = $this->oBeaut->getToken($this->oBeaut->iCount+1);
        $sNextWhitespace = ($aNextToken[0] == T_WHITESPACE) ? $aNextToken[1] : '';

        $this->oBeaut->removeWhitespace();
        if ( strlen( $sNextWhitespace ) == 0 ) {
            $this->oBeaut->add(' '. $sTag . ' ');
        } else {
            $this->oBeaut->add(' '. $sTag);
        }
    }
    function t_assigment_pre($sTag)
    {
        $aNextToken = $this->oBeaut->getToken($this->oBeaut->iCount+1);
        $sNextWhitespace = ($aNextToken[0] == T_WHITESPACE) ? $aNextToken[1] : '';

        $this->oBeaut->removeWhitespace();
        if ( strlen( $sNextWhitespace ) == 0 ) {
            $this->oBeaut->add(' '. $sTag . ' ');
        } else {
            $this->oBeaut->add(' '. $sTag);
        }
    }

    function t_open_brace($sTag)
    {
        if ($this->oBeaut->openBraceDontProcess()) {
            $this->oBeaut->add($sTag);
        } else {
            if ($this->oBeaut->removeWhiteSpace()) {
                $this->oBeaut->add(' ' . $sTag);
            } else {
                $this->oBeaut->add($sTag);
            }
            $this->oBeaut->incIndent();
            if ($this->oBeaut->getControlSeq() == T_SWITCH) {
                $this->oBeaut->incIndent();
            }
            //$this->oBeaut->addNewLineIndent(); //This adds a new line after open brace, like on IF statements. Don't want this.
        }
    }

    function t_close_brace($sTag)
    {
        if ($this->oBeaut->getMode('string_index') or $this->oBeaut->getMode('double_quote')) {
            $this->oBeaut->add($sTag);

        } else {
            $this->oBeaut->removeWhitespace();
            $this->oBeaut->decIndent();
            if ($this->oBeaut->getControlSeq() == T_SWITCH) {
                $this->oBeaut->decIndent();
            }
            $this->oBeaut->addNewLineIndent();
            $this->oBeaut->add($sTag);
            if ($this->oBeaut->getControlSeq() == T_DO) {
                $this->oBeaut->add(' ');
            }else{
                //$this->oBeaut->addNewLineIndent();
            }
        }
    }

    public function t_string( $sTag ) {
        if ( $sTag == 'true' OR $sTag == 'false' OR $sTag == 'null' OR strtolower($this->oBeaut->getPreviousTokenContent()) == 'const' ) { //Uppercase TRUE/FALSE/NULL and all constants
            $this->oBeaut->add( strtoupper($sTag) );
        } else {
            $this->oBeaut->add( $sTag);
        }
    }

/*
    public function t_access($sTag)
    {
        $this->oBeaut->add($sTag . ' ');
    }
    public function t_end_heredoc($sTag)
    {
        $this->oBeaut->add(trim($sTag));
        $this->oBeaut->addNewLineIndent();
    }
    public function t_open_tag($sTag)
    {
        $this->oBeaut->add(trim($sTag));
        preg_match("/([\s\r\n\t]+)$/", $sTag, $aMatch);
        $aNextToken = $this->oBeaut->getToken($this->oBeaut->iCount+1);
        $sNextWhitespace = ($aNextToken[0] == T_WHITESPACE) ? $aNextToken[1] : '';
        $sWhitespace = @$aMatch[1] . $sNextWhitespace;
        if (preg_match("/[\r\n]+/", $sWhitespace)) {
            $this->oBeaut->addNewLineIndent();
        } else {
            $this->oBeaut->add(' ');
        }
    }
    function t_close_tag($sTag)
    {
        $this->oBeaut->removeWhitespace();
        if (preg_match("/\r|\n/", $this->oBeaut->getPreviousWhitespace())) {
            $this->oBeaut->addNewLine();
            $this->oBeaut->add($sTag);
        } else {
            $this->oBeaut->add(' ' . $sTag);
        }
    }
    function t_switch($sTag)
    {
        $this->t_control($sTag);
    }
    function t_control($sTag)
    {
        $this->oBeaut->add($sTag . ' ');
    }
    function t_case($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->decIndent();
        $this->oBeaut->addNewLineIndent();
        $this->oBeaut->add($sTag . ' ');
        //$this->oBeaut->incIndent();

    }

    public function t_string( $sTag ) {
        //echo "Test3: \"". $sTag ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getPreviousTokenContent() ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getNextTokenContent() ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getPreviousWhitespace() ."\"\n";
        //echo "  Test1: \"". token_name( $this->oBeaut->getControlParenthesis() )."\"\n";

        //$this->oBeaut->add($sTag);


         //Too many cases where this breaks things.
        if ( $this->oBeaut->getControlParenthesis() == T_STRING OR $this->oBeaut->getPreviousTokenContent() == 'function' OR $this->oBeaut->getPreviousTokenContent() == 'return'  ) {
            $this->oBeaut->add($sTag);
        } else {
            if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
                $this->oBeaut->removeWhitespace();
            }
            if ( $this->oBeaut->getPreviousTokenContent() == '->' OR $this->oBeaut->getPreviousTokenContent() == '::' ) {
                $this->oBeaut->add($sTag);
            } else {
                $this->oBeaut->add(' '.$sTag);
            }
        }

    }

    public function t_constant_encapsed_string( $sTag ) {
        if ( $this->oBeaut->getPreviousTokenContent() == '(' AND strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
            $this->oBeaut->removeWhitespace();
            $this->oBeaut->add(' ' . $sTag);
        } else {
            $this->oBeaut->add($sTag);
        }
    }

    public function t_parenthesis_open($sTag)
    {
        //var_dump($this->oBeaut->getControlParenthesis());
        //echo "Test1: \"". $sTag ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getPreviousTokenContent() ."\"\n";

        //echo "Test3: \"". $sTag ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getPreviousTokenContent() ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getNextTokenContent() ."\"\n";
        //echo "  Test1: \"". $this->oBeaut->getPreviousWhitespace() ."\"\n";

        if ($this->oBeaut->getControlParenthesis() == T_ARRAY) {
            $this->oBeaut->add($sTag . ' ' );
            ////$this->oBeaut->addNewLine();
            //$this->oBeaut->incIndent();
            //$this->oBeaut->addIndent();
        } elseif ( $this->oBeaut->getControlParenthesis() == T_IF OR $this->oBeaut->getControlParenthesis() == T_ELSEIF OR $this->oBeaut->getControlParenthesis() == T_ELSE ) {
            //echo "Test2: \"". $sTag ."\"\n";
            $this->oBeaut->removeWhitespace();
            $this->oBeaut->add( ' '. $sTag );
            $this->oBeaut->removeWhitespace();
        } else {
            if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
                $this->oBeaut->removeWhitespace();
            }
            $this->oBeaut->add( $sTag . ' ' );
        }
    }
    public function t_parenthesis_close($sTag)
    {
        //var_dump($this->oBeaut->getControlParenthesis());
        //echo "sTag: ". $sTag ."\n";
        //echo "Previous Content: ". $this->oBeaut->getPreviousTokenContent() ."\n";

        if ($this->oBeaut->getControlParenthesis() == T_ARRAY) {
            //$this->oBeaut->removeWhitespace();
            //$this->oBeaut->decIndent();
            //if ($this->oBeaut->getPreviousTokenContent() != '(') {
            //    $this->oBeaut->addNewLine();
            //    $this->oBeaut->addIndent();
            //}
            $this->oBeaut->add($sTag . ' ');
        } else {
            if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
                $this->oBeaut->removeWhitespace();
                if ( $this->oBeaut->getPreviousTokenContent() == '(' ) {
                    $this->oBeaut->add($sTag);
                } else {
                    $this->oBeaut->add(' '. $sTag);
                }
            } else {
                $this->oBeaut->add($sTag);
            }
        }
    }
    public function t_comma($sTag)
    {
        if ($this->oBeaut->getControlParenthesis() != T_ARRAY) {
            $this->oBeaut->add($sTag . ' ');
        } else {
            $this->oBeaut->add($sTag);
            //if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
            //    $this->oBeaut->addNewLine();
            //}
            //$this->oBeaut->addIndent();
        }
    }
    function t_open_brace($sTag)
    {
        if ($this->oBeaut->openBraceDontProcess()) {
            $this->oBeaut->add($sTag);
        } else {
            if ($this->oBeaut->removeWhiteSpace()) {
                $this->oBeaut->add(' ' . $sTag);
            } else {
                $this->oBeaut->add($sTag);
            }
            $this->oBeaut->incIndent();
            if ($this->oBeaut->getControlSeq() == T_SWITCH) {
                $this->oBeaut->incIndent();
            }
            //$this->oBeaut->addNewLineIndent(); //This adds a new line after open brace, like on IF statements. Don't want this.
        }
    }

    function t_close_brace($sTag)
    {
        if ($this->oBeaut->getMode('string_index') or $this->oBeaut->getMode('double_quote')) {
            $this->oBeaut->add($sTag);

        } else {
            $this->oBeaut->removeWhitespace();
            $this->oBeaut->decIndent();
            if ($this->oBeaut->getControlSeq() == T_SWITCH) {
                $this->oBeaut->decIndent();
            }
            $this->oBeaut->addNewLineIndent();
            $this->oBeaut->add($sTag);
            if ($this->oBeaut->getControlSeq() == T_DO) {
                $this->oBeaut->add(' ');
            }else{
                //$this->oBeaut->addNewLineIndent();
            }
        }
    }
    function t_semi_colon($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add($sTag);
        if ($this->oBeaut->getControlParenthesis() != T_FOR) {
            //$this->oBeaut->addNewLineIndent();
        }
    }
    function t_as($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag . ' ');
    }
    function t_new($sTag)
    {
        $this->oBeaut->add(' ' . $sTag . ' ');
    }
    function t_whitespace($sTag)
    {
        $this->oBeaut->add( $sTag );
    }
    function t_else($sTag)
    {
        if ($this->oBeaut->isPreviousTokenConstant(T_COMMENT)) {
            // do nothing!

        } elseif ($this->oBeaut->isPreviousTokenContent('}')) {
            $this->oBeaut->removeWhitespace();
            $this->oBeaut->add(' ');
        } else {
            $this->oBeaut->removeWhitespace();
            if ($this->oBeaut->isNextTokenContent(':') or ($sTag == 'elseif' and $this->detect_colon_after_parenthesis())) {
                $this->oBeaut->decIndent();
            }
            $this->oBeaut->addNewLineIndent();
        }
        $this->oBeaut->add($sTag . ' ');
    }
    private function detect_colon_after_parenthesis()
    {
        $iPar = 1;
        $x = 2;
        while ($iPar and $x < 100) {
            if ($this->oBeaut->isNextTokenContent('(', $x)) {
                $iPar++;
            } elseif ($this->oBeaut->isNextTokenContent(')', $x)) {
                $iPar--;
            }
            $x++;
        }
        if ($x == 100) {
            throw new Exception("Elseif doesn't have an ending parenthesis");
        }
        return $this->oBeaut->isNextTokenContent(':', $x);
    }
    function t_equal($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag );
    }
    function t_logical($sTag)
    {
        //If there is no newline before the logical AND/OR, then remove all whitespace.
        if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
            $this->oBeaut->removeWhitespace();
            $this->oBeaut->add( ' '. strtoupper($sTag) .' ');
            $this->oBeaut->removeWhitespace();
        } else {
            //Newline before
            $this->oBeaut->add( strtoupper($sTag) .' ');
            $this->oBeaut->removeWhitespace();
        }
    }
    function t_foreach($sTag)
    {
        $this->oBeaut->add($sTag . ' ');
    }
    function t_for($sTag)
    {
        $this->oBeaut->add($sTag . ' ');
    }
    function t_dot($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag . ' ');
    }
    function t_include($sTag)
    {
        $this->oBeaut->add($sTag . ' ');
    }
    function t_language_construct($sTag)
    {
        $this->oBeaut->add($sTag . ' ');
        $this->oBeaut->removeWhitespace();
    }
    function t_variable($sTag)
    {
        if ($this->oBeaut->isPreviousTokenConstant(T_STRING) and !$this->oBeaut->getMode("double_quote")) {
            $this->oBeaut->add(' ');
        }

        //Make sure we don't add a space when the line begins with a variable.
        if ( strpos( $this->oBeaut->getPreviousWhitespace(), "\n" ) === FALSE ) {
            $this->oBeaut->removeWhitespace();
            $this->oBeaut->add( ' '. $sTag);
        } else {
            $this->oBeaut->add( $sTag);
        }
    }
    function t_question($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag . ' ');
    }
    function t_colon($sTag)
    {
        $this->oBeaut->removeWhitespace();
        if ($this->oBeaut->getMode('ternary_operator')) {
            $this->oBeaut->add(' ' . $sTag . ' ');
        } else {
            $this->oBeaut->add($sTag);
            $this->oBeaut->incIndent();
            //$this->oBeaut->addNewLineIndent();
        }
    }
    function t_double_colon($sTag)
    {
        $this->oBeaut->add($sTag);
    }
    function t_break($sTag)
    {
        if ($this->oBeaut->getControlSeq() == T_SWITCH) {
            $this->oBeaut->removeWhitespace();
            //$this->oBeaut->incIndent();
            //$this->oBeaut->decIndent();
            $this->oBeaut->addNewLineIndent();
            $this->oBeaut->add($sTag);
            //$this->oBeaut->incIndent();
        } else {
            $this->oBeaut->add($sTag);
        }
        if ($this->oBeaut->isNextTokenConstant(T_LNUMBER)) {
            $this->oBeaut->add(' ');
        }
    }
    function t_continue($sTag)
    {
        $this->oBeaut->add($sTag);
        if ($this->oBeaut->isNextTokenConstant(T_LNUMBER)) {
            $this->oBeaut->add(' ');
        }
    }
    function t_default($sTag)
    {
        $this->t_case($sTag);
    }
    function t_end_suffix($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->decIndent();
        $this->oBeaut->addNewLineIndent();
        $this->oBeaut->add($sTag);
    }
    function t_extends($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag . ' ');
    }
    function t_implements($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag . ' ');
    }
    function t_instanceof($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag . ' ');
    }
    function t_equal_sign($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag . ' ');
    }
    function t_assigment($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add(' ' . $sTag);
    }
    function t_assigment_pre($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add($sTag . ' ');
    }
    function t_clone($sTag) {
        $this->oBeaut->add($sTag.' ');
    }
    function t_array($sTag)
    {
        $this->oBeaut->add($sTag);
		// Check this, please!
		if (!$this->oBeaut->isNextTokenContent('(')) {
            $this->oBeaut->add(' ');
        }
    }
    function t_object_operator($sTag)
    {
        $this->oBeaut->removeWhitespace();
        $this->oBeaut->add($sTag);
    }
    function t_operator($sTag)
    {
        $this->oBeaut->removeWhitespace();
        // binary operators should have a space before and after them.  unary ones should just have a space before them.
        switch ($this->oBeaut->getTokenFunction($this->oBeaut->getPreviousTokenConstant())) {
            case 't_question':
            case 't_colon':
            case 't_comma':
            case 't_dot':
            case 't_case':
            case 't_echo':
            case 't_language_construct': // print, echo, return, etc.
            case 't_operator':
                $this->oBeaut->add(' ' . $sTag);
                break;
            case 't_parenthesis_open':
            case 't_open_square_brace':
            case 't_open_brace':
                $this->oBeaut->add($sTag);
                break;
            default:
                $this->oBeaut->add(' ' . $sTag . ' ');
        }
    }
*/
}
?>
