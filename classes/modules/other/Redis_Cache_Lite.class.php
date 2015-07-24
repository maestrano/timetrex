<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/

class Redis_Cache_Lite extends Cache_Lite {
	function Redis_Cache_Lite( $options = array(NULL) ) {
		$this->Cache_Lite( $options );

		if ( defined('ADODB_DIR') ) {
			include_once( ADODB_DIR .'/adodb-csvlib.inc.php');
		}
		
		$this->redisConnectMaster();

		return TRUE;
	}

	function redisConnect( $key ) {
		if ( isset($this->_redisHostConn[$key]) AND $this->_redisHostConn[$key] === FALSE ) {
			Debug::Text( 'Previous error connecting to the Redis database, not attempting again during this request...', __FILE__, __LINE__, __METHOD__, 1);
			return FALSE;
		}

		try {
			global $config_vars;
			if ( !isset($config_vars['database']['persistent_connections']) ) {
				$config_vars['database']['persistent_connections'] = FALSE;
			}

			$this->_redisHostConn[$key] = new Redis();

			//Try with 2 second timeout, we don't want redis to block requests if its down.
			if ( $config_vars['database']['persistent_connections'] == TRUE ) {
				$connection_retval = $this->_redisHostConn[$key]->pconnect( $this->_redisHostHost[$key], NULL, 2 );
			} else {
				$connection_retval = $this->_redisHostConn[$key]->connect( $this->_redisHostHost[$key], NULL, 2 );
			}
			
			if ( $connection_retval === TRUE ) {
				if ( isset($this->_redisDB) AND $this->_redisDB != '' ) {
					if ( $this->_redisHostConn[$key]->select( $this->_redisDB ) === FALSE ) {
						//return $this->raiseError('Cache_Lite : Unable to switch redis DB to: '. $this->_redisDB, -2);  //In order to catch these we need to include PEAR.php all the time.
						return FALSE;
					}
					//else {
					//	Debug::text('Switched REDIS DB to: '. $this->_redisDB, __FILE__, __LINE__, __METHOD__, 10);
					//}
				}
				return $this->_redisHostConn[$key];
			} else {
				$this->_redisHostConn[$key] = FALSE; //Prevent further connections from timing out during this request...
				Debug::Text( 'Error connecting to the Redis database! (a)', __FILE__, __LINE__, __METHOD__, 1);				
			}
		} catch (Exception $e) {
			$this->_redisHostConn[$key] = FALSE; //Prevent further connections from timing out during this request...
			Debug::Text( 'Error connecting to the Redis database! (b)', __FILE__, __LINE__, __METHOD__, 1);
			//throw new DBError($e);
		}

		//return $this->raiseError('Cache_Lite : Unable to connect to redis host: '. $key, -2);  //In order to catch these we need to include PEAR.php all the time.
		return FALSE;
	}

	function redisConnectMaster() {
		Debug::text('Connecting to REDIS Host: '. $this->_redisHost, __FILE__, __LINE__, __METHOD__, 10);
		if ( isset( $this->_redisHost ) AND $this->_redisHost != '' ) {
			$split_server = explode(',', $this->_redisHost );
			$i = 0;
			foreach( $split_server as $server ) {
				if ( $i == 0 ) {
					$this->_redisHostHost['master'] = $server;
				} else {
					$this->_redisHostHost['slave_'.$i] = $server;
				}
				$i++;
			}
			//Debug::Arr($this->_redisHostHost, 'REDIS Hosts: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->redisConnect( 'master' );
		}
	}

	function setOption( $name, $value ) {
		$availableOptions = array('redisHost', 'redisDB', 'errorHandlingAPIBreak', 'hashedDirectoryUmask', 'hashedDirectoryLevel', 'automaticCleaningFactor', 'automaticSerialization', 'fileNameProtection', 'memoryCaching', 'onlyMemoryCaching', 'memoryCachingLimit', 'cacheDir', 'caching', 'lifeTime', 'fileLocking', 'writeControl', 'readControl', 'readControlType', 'pearErrorMode', 'hashedDirectoryGroup', 'cacheFileMode', 'cacheFileGroup');
		if (in_array($name, $availableOptions)) {
			$property = '_'.$name;
			$this->$property = $value;
		}
	}

	function get( $id, $group = 'default', $doNotTestCacheValidity = FALSE ) {
		$this->_id = $id;
		$this->_group = $group;
		$data = FALSE;
		if ($this->_caching) {
			$this->_setRefreshTime();
			$this->_setFileName($id, $group);
			clearstatcache();
			if ($this->_memoryCaching) {
				if (isset($this->_memoryCachingArray[$this->_file])) {
					if ($this->_automaticSerialization) {
						return unserialize($this->_memoryCachingArray[$this->_file]);
					}
					return $this->_memoryCachingArray[$this->_file];
				}
				if ($this->_onlyMemoryCaching) {
					return FALSE;
				}
			}
			$data = $this->_read();
			if (($data) AND ($this->_memoryCaching)) {
				$this->_memoryCacheAdd($data);
			}
			if (($this->_automaticSerialization) AND (is_string($data))) {
				$data = unserialize($data);
			}
			return $data;
		}
		return FALSE;
	}

	function _read() {
		$redis = $this->redisConnect('master');
		//if ( !PEAR::isError($redis) ) {
		if ( is_object($redis) AND get_class($redis) == 'Redis' ) {
			return $redis->get( $this->_file );
		}

		//return $this->raiseError('Cache_Lite : Unable to read cache !', -2); //In order to catch these we need to include PEAR.php all the time.
		return FALSE;
	}

	function save( $data, $id = NULL, $group = 'default' ) {
		if ($this->_caching) {
			if ($this->_automaticSerialization) {
				$data = serialize($data);
			}
			if (isset($id)) {
				$this->_setFileName($id, $group);
			}
			if ($this->_memoryCaching) {
				$this->_memoryCacheAdd($data);
				if ($this->_onlyMemoryCaching) {
					return TRUE;
				}
			}
			if ($this->_automaticCleaningFactor > 0 AND ($this->_automaticCleaningFactor == 1 OR mt_rand(1, $this->_automaticCleaningFactor) == 1)) {
				$this->clean(FALSE, 'old');
			}
			$res = $this->_write($data);

			if (is_object($res)) {
				// $res is a PEAR_Error object
				if (!($this->_errorHandlingAPIBreak)) {
					return FALSE; // we return false (old API)
				}
			}
			return $res;
		}
		return FALSE;
	}

	function _setFileName( $id, $group ) {
		//if ($this->_fileNameProtection) {
		//    $suffix = md5($group).'_'.md5($id);
		//} else {
			$suffix = $group.'_'.$id;
		//}

		$this->_fileName = $suffix;
		$this->_file = $suffix;
	}

	function _write( $data ) {
		$redis = $this->redisConnect('master');
		//if ( !PEAR::isError($redis) ) {
		if ( is_object($redis) AND get_class($redis) == 'Redis' ) {
			//Debug::text('Writing to REDIS as KEY: '. $this->_file, __FILE__, __LINE__, __METHOD__, 10);
			return $redis->set( $this->_file, $data, $this->_lifeTime );
		}

		//return $this->raiseError('Cache_Lite : Unable to write cache file : '.$this->_file, -1); //In order to catch these we need to include PEAR.php all the time.
		return FALSE;
	}

	function _unlink( $file, $skip_master = FALSE ) {
		//When multiple redis servers are specified, we need to expire cache on them all.
		foreach( $this->_redisHostHost as $server_key => $value ) {
			if ( $skip_master == FALSE OR ( $skip_master == TRUE AND $server_key != 'master' ) ) {
				$redis = $this->redisConnect( $server_key );
				//if ( !PEAR::isError($redis) ) {
				if ( is_object($redis) AND get_class($redis) == 'Redis' ) {
					//Debug::text('Deleting REDIS as KEY: '. $this->_file .' Server Key: '. $server_key, __FILE__, __LINE__, __METHOD__, 10);
					if ( $redis->del( $this->_file ) === FALSE ) {
						//return $this->raiseError('Cache_Lite : Unable to delete cache file : '.$this->_file, -1);  //In order to catch these we need to include PEAR.php all the time.
						return FALSE;
					}
				}
			}
		}

		return TRUE;
	}

	function clean( $group = FALSE, $mode = 'ingroup', $skip_master = FALSE ) {
		//When multiple redis servers are specified, we need to expire cache on them all.
		foreach( $this->_redisHostHost as $server_key => $value ) {
			if ( $skip_master == FALSE OR ( $skip_master == TRUE AND $server_key != 'master' ) ) {
				$redis = $this->redisConnect( $server_key );
				//if ( !PEAR::isError($redis) ) {
				if ( is_object($redis) AND get_class($redis) == 'Redis' ) {
					if ( $group != '' ) {
						$redis->eval( 'return redis.call(\'del\', unpack(redis.call(\'keys\', ARGV[1])))', array( $group .'_*' ) );
					} else {
						$redis->flushdb(); //If no group is specified, flush all keys in DB.
					}
				}
			}
		}

		return TRUE;
	}


	/*
	 * Support ADODB Cache module.
	 */
	var $createdir = FALSE; // do not set this to true unless you use temp directories in cache path
	function writecache($filename, $contents, $debug = FALSE ) {
		return $this->save( $contents, $filename, 'adodb' );
	}
	function readcache($filename, &$err, $secs2cache, $rsClass) {
		$rs = explode("\n", $this->get( $filename, 'adodb' ) );
		unset($rs[0]);
		$rs = join("\n", $rs);
		return unserialize( $rs );
	}
	function flushall($debug = FALSE) {
		return $this->clean( 'adodb' );
	}
	function flushcache($filename, $debug = FALSE ) {
		return $this->remove( $filename, 'adodb' );
	}
	function createdir($dir, $hash) {
		return TRUE;
	}
}
?>
