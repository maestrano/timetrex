<?php
define('TIMETREX_AMF_API', TRUE );

//Add timetrex.ini.php setting to enable/disable the API. Make an entire [API] section.
require_once('../../includes/global.inc.php');
require_once('../../includes/API.inc.php');

class ServiceMapper {
	private $server = NULL;
	private $object_cache = array();

	function __construct( $callback = 'invokeService' ) {
		//Include framework
		require_once('../../classes/SabreAMF/CallbackServer.php');

		//Construct the server
		$this->server = new SabreAMF_CallbackServer();

		// Listen to the event
		$this->server->onInvokeService = array( $this, $callback );

		return TRUE;
	}

	//Recursively check ALL data to see if it is an object, if so, throw an exception.
	function hasObject( $object ) {
		if ( is_object( $object ) AND is_a( $object, 'stdClass' ) ) {
			return TRUE;
		} elseif ( is_array($object) ) {
			foreach( $object as $key => $value ) {
				return $this->hasObject( $value );
			}
		}

		return FALSE;
	}

	//User is not authenticated, return error message.
	function notAuthenticatedInvokeService( $serviceName, $methodName, $arguments, $extras = NULL ) {
		Debug::text('Service: '. $serviceName .' Method: '. $methodName, __FILE__, __LINE__, __METHOD__, 10);
		//Allow core.APIEnvironment calls in this state, otherwise Flex can't set the proper URLs.
		if ( in_array( $serviceName, array('APIAuthentication', 'core.APIEnvironment') ) ) {
			return $this->invokeService($serviceName, $methodName, $arguments );
		} else {
			$obj = new APIAuthentication();
			return $obj->returnHandler( FALSE, 'NOT_AUTHENTICATED', TTi18n::getText('Session timed out, please login again.') );
		}
	}

	//Use this to sandbox the client into a specific class until they are authenticated.
	function unauthenticatedInvokeService( $serviceName, $methodName, $arguments, $extras = NULL ) {
		Debug::text('Service: '. $serviceName .' Method: '. $methodName, __FILE__, __LINE__, __METHOD__, 10);

		//Allow several classes to be available before the user is logged in.
		if ( in_array( $serviceName, array('APIAuthentication', 'core.APIEnvironment') ) ) {
			return $this->invokeService($serviceName, $methodName, $arguments );
		} else {
			$serviceName = 'APIAuthentication';
			return $this->invokeService($serviceName, $methodName, $arguments );
		}
	}

	function invokeService( $serviceName, $methodName, $arguments, $extras = NULL ) {
		$invoke_service_start_time = (float)microtime(TRUE);

		//Convert . to '/', then class name is basename()
		$className = TTgetPluginClassName( basename( str_replace('.', '/' , $serviceName ) ) );

		Debug::text('Service: '. $serviceName .' Method: '. $methodName .' Class: '. $className, __FILE__, __LINE__, __METHOD__, 10);

		if ( class_exists( $className ) == FALSE ) {
			Debug::text('Service: '. $serviceName .' does not exist!', __FILE__, __LINE__, __METHOD__, 10);
			throw new Exception('Service: '. $serviceName .' does not exist!');
			return FALSE;
		}

		Debug::Arr($arguments, 'Arguments: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( PRODUCTION == FALSE AND $this->hasObject( $arguments ) == TRUE ) {
			$argument_var_dump = Debug::varDump( $arguments );

			throw new Exception('ERROR: Passed an object as argument to: Method: '. $methodName .' as part of service: '. $serviceName .'! -- Arguments: '. $argument_var_dump);
			unset($argument_var_dump);
			return FALSE;
		}

		//Holds all objects in memory until the entire request is done, so multiple function calls in a single request can share data with each other.
		//This is necessary for getPageData() to work.
		if ( !isset($this->object_cache[$className]) ) {
			$this->object_cache[$className] = new $className;
		}

		if ( isset( $extras['messageId'] ) AND method_exists( $this->object_cache[$className], 'setAMFMessageID') ) {
			$this->object_cache[$className]->setAMFMessageID( $extras['messageId'] );
		}

		if ( method_exists( $this->object_cache[$className], $methodName ) == FALSE ) {
			throw new Exception('Method: '. $methodName .' as part of service: '. $serviceName .' does not exist!');
			return FALSE;
		}

		try {
			$retval = call_user_func_array( array( &$this->object_cache[$className], $methodName ), $arguments );
		} catch ( Exception $e ) {
			$argument_var_dump = Debug::varDump( $arguments );
			$backtrace = Debug::backTrace();

			Debug::Arr($backtrace, 'FAILED CALL... Service: '. $serviceName .' Method: '. $methodName .' Class: '. $className .' Message: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);

			throw new Exception('ERROR: Failed calling method: '. $methodName .' as part of serivce: '. $serviceName .'! Exception: '. $e->getMessage() .' Arguments: '. $argument_var_dump .' BackTrace: '. $backtrace );
			unset($argument_var_dump, $backtrace);
			return FALSE;
		}

		//Debug::Arr($retval, 'RetVal: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::text('Returning '. strlen(serialize($retval)) .' bytes of data... Response Time: '. ((float)microtime(TRUE)-$invoke_service_start_time), __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function exec() {
		$this->server->exec();
	}
}

//APIAuthentication->isLoggedIn() checks for active session or not as well.
$session_id = getSessionID();
//Debug::Arr($_COOKIE,' API Cookies: ', __FILE__, __LINE__, __METHOD__, 10);
//Debug::Arr($_POST,' API POST: ', __FILE__, __LINE__, __METHOD__, 10);

if ( $session_id != '' AND !isset($_GET['session']) ) { //When Flex calls PING() on a regular basis it will send Session=0, so always skip authentication checks for this.
	$authentication = new Authentication();

	Debug::text('AMF Session ID: '. $session_id .' Source IP: '. $_SERVER['REMOTE_ADDR'], __FILE__, __LINE__, __METHOD__, 10);
	if ( isset($config_vars['other']['web_session_timeout']) AND $config_vars['other']['web_session_timeout'] != '' ) {
		$authentication->setIdle( (int)$config_vars['other']['web_session_timeout'] );
	}
	if ( $authentication->Check( $session_id ) === TRUE ) {
		$current_user = $authentication->getObject();

		if ( is_object( $current_user ) ) {
			$current_user->getUserPreferenceObject()->setDateTimePreferences();
			$current_user_prefs = $current_user->getUserPreferenceObject();

			Debug::text('Locale Cookie: '. TTi18n::getLocaleCookie() , __FILE__, __LINE__, __METHOD__, 10);
			if ( TTi18n::getLocaleCookie() != '' AND $current_user_prefs->getLanguage() !== TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) ) {
				Debug::text('Changing User Preference Language to match cookie...', __FILE__, __LINE__, __METHOD__, 10);
				$current_user_prefs->setLanguage( TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) );
				if ( $current_user_prefs->isValid() ) {
					$current_user_prefs->Save(FALSE);
				}
			} else {
				Debug::text('User Preference Language matches cookie!', __FILE__, __LINE__, __METHOD__, 10);
			}
			if ( isset($_GET['language']) AND $_GET['language'] != '' ) {
				TTi18n::setLocale( $_GET['language'] ); //Sets master locale
			} else {
				TTi18n::setLanguage( $current_user_prefs->getLanguage() );
				TTi18n::setCountry( $current_user->getCountry() );
				TTi18n::setLocale(); //Sets master locale
			}

			$clf = new CompanyListFactory();
			$current_company = $clf->getByID( $current_user->getCompany() )->getCurrent();

			if ( is_object( $current_company ) ) {
				Debug::text('Handling AMF Call To API... UserName: '. $current_user->getUserName(), __FILE__, __LINE__, __METHOD__, 10);
				try {
					$gateway = new ServiceMapper();
					$gateway->exec();
				} catch( Exception $e ) {
					Debug::Arr($_POST, '(a) No data to process... Likely not using flash?', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::text('Failed to get Company Object!', __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::text('Failed to get User Object!', __FILE__, __LINE__, __METHOD__, 10);
		}
	} else {
		TTi18n::chooseBestLocale(); //Make sure we set the locale as best we can when not logged in
		Debug::text('User not authenticated!', __FILE__, __LINE__, __METHOD__, 10);
		try {
			$gateway = new ServiceMapper( 'notAuthenticatedInvokeService' );
			$gateway->exec();
		} catch( Exception $e ) {
			Debug::Arr($_POST, '(c) No data to process... Likely not using flash?', __FILE__, __LINE__, __METHOD__, 10);
		}
	}
} else {
	TTi18n::chooseBestLocale(); //Make sure we set the locale as best we can when not logged in

	//User is not authenticated, restrict them to only classes available to non-logged in users.
	try {
		$gateway = new ServiceMapper( 'unauthenticatedInvokeService' );
		$gateway->exec();
	} catch( Exception $e ) {
		Debug::Arr($_POST, '(b) No data to process... Likely not using flash?', __FILE__, __LINE__, __METHOD__, 10);
	}
}

Debug::text('Server Response Time: '. ((float)microtime(TRUE)-$_SERVER['REQUEST_TIME']), __FILE__, __LINE__, __METHOD__, 10);
Debug::writeToLog();
?>
