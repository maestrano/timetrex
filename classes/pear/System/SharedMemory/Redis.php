<?php
/**
*
* The Redis driver for SharedMemory
*
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license/3_0.txt.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @category   System
* @package    System_Sharedmemory
* @author     Evgeny Stepanischev <bolk@lixil.ru>
* @copyright  2014 Mike Benoit
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    CVS: $Id:$
* @link       http://pear.php.net/package/System_SharedMemory
*/

/**
*
* The methods PEAR SharedMemory uses to interact with PHP's Redis extension
* for interacting with Redis shared memory
*
* These methods overload the ones declared System_SharedMemory_Common
*
* @category   System
* @package    System_Sharedmemory
* @package    System_Sharedmemory
* @author     Evgeny Stepanischev <bolk@lixil.ru>
* @copyright  2005 Evgeny Stepanischev
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    CVS: $Id:$
* @link       http://pear.php.net/package/System_SharedMemory
*/


require_once 'System/SharedMemory/Common.php';

// {{{ class System_SharedMemory_Memache

class System_SharedMemory_Redis extends System_SharedMemory_Common
{
    // {{{ properties
    /**
    * true if plugin was connected to backend
    *
    * @var bool
    *
    * @access private
    */
    var $_connected;

    /**
    * Redis object instance
    *
    * @var object
    *
    * @access private
    */
    var $_mc;
    // }}}
    // {{{ constructor

    /**
     * Constructor. Init all variables.
     *
     * @param array $options
     *
     * @access public
     */
    function System_SharedMemory_Redis($options)
    {
        extract($this->_default($options, array
        (
            'host'  => '127.0.0.1',
            'port'  => NULL, //Required to use socket connections.
            'timeout' => false,
            'persistent' => false,
			'db' => 0,
        )));

        $func = $persistent ? 'pconnect' : 'connect';

        $this->_mc  = new Redis;
        $this->_connected = $timeout === false ?
            $this->_mc->$func($host, $port) :
            $this->_mc->$func($host, $port, $timeout);

		if ( $db > 0 ) {
			$this->_mc->select( $db );
		}
    }
    // }}}
    // {{{ isConnected()

    /**
     * returns true if plugin was
     * successfully connected to backend
     *
     * @return bool true if connected
     * @access public
     */
    function isConnected()
    {
        return $this->_connected;
    }
    // }}}
    // {{{ get()

    /**
     * returns value of variable in shared mem
     *
     * @param string $name name of variable
     *
     * @return mixed value of the variable
     * @access public
     */
    function get($name)
    {
		try {
			return unserialize( $this->_mc->get($name) );
		} catch ( Exception $e ) {
			return FALSE;
		}
    }
    // }}}
    // {{{ set()

    /**
     * set value of variable in shared mem
     *
     * @param string $name  name of the variable
     * @param string $value value of the variable
     * @param int $ttl (optional) time to life of the variable
     *
     * @return bool true on success
     * @access public
     */
    function set($name, $value, $ttl = 0)
    {
		try {
			return $this->_mc->set($name, serialize( $value ), $ttl);
		} catch ( Exception $e ) {
			return FALSE;
		}
    }
    // }}}
    // {{{ rm()

    /**
     * remove variable from memory
     *
     * @param string $name  name of the variable
     *
     * @return bool true on success
     * @access public
     */
    function rm($name, $ttl = false)
    {
		return $this->_mc->delete($name);
    }
    // }}}
}
// }}}
?>