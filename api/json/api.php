<?php
//Add timetrex.ini.php setting to enable/disable the API. Make an entire [API] section.
require_once('../../includes/global.inc.php');
require_once('../../includes/API.inc.php');

define('TIMETREX_JSON_API', TRUE );

//header('Access-Control-Allow-Origin: '. ( isset($_SERVER['HTTP_ORIGIN']) ) ? $_SERVER['HTTP_ORIGIN'] : '*' );
header('Access-Control-Allow-Origin: *' );

/*
 Arguments:
	GET: SessionID
	GET: Class
	GET: Method
	POST: Arguments for method.
*/
$class_prefix = 'API';
$class_name = FALSE;
$method = FALSE;

if ( isset($_GET['Class']) AND $_GET['Class'] != '' ) {
	$class_name = $_GET['Class'];

	//If API wasn't already put on the class, add it manually.
	if ( strtolower( substr( $class_name, 0, 3 ) ) != 'api' ) {
		$class_name = $class_prefix.$class_name;
	}

	$class_name = TTgetPluginClassName( $class_name );
}

if ( isset($_GET['Method']) AND $_GET['Method'] != '' ) {
	$method = $_GET['Method'];
}

if ( isset($_GET['MessageID']) AND $_GET['MessageID'] != '' ) {
	$message_id = $_GET['MessageID'];
} else {
	$message_id = md5( time() ); //Random message_id
}

Debug::text('Handling JSON Call To API Factory: '.  $class_name .' Method: '. $method .' Message ID: '. $message_id, __FILE__, __LINE__, __METHOD__, 10);

//URL: api.php?SessionID=fc914bf32711bff031a6c80295bbff86&Class=APIPayStub&Method=getPayStub
/*
 RAW POST: data[filter_data][id][0]=101561&paging=TRUE&format=pdf
 JSON (URL encoded): %7B%22data%22%3A%7B%22filter_data%22%3A%7B%22id%22%3A%5B101561%5D%7D%7D%2C%22paging%22%3Atrue%2C%22format%22%3A%22pdf%22%7D

 FULL URL: SessionID=fc914bf32711bff031a6c80295bbff86&Class=APIPayStub&Method=test&json={"data":{"filter_data":{"id":[101561]}},"paging":true,"format":"pdf"}
*/
/*
$_POST = array( 'data' => array('filter_data' => array('id' => array(101561) ) ),
				'paging' => TRUE,
				'format' => 'pdf',
				);
*/
//Debug::Arr(file_get_contents('php://input'), 'POST: ', __FILE__, __LINE__, __METHOD__, 10);
//Debug::Arr($_POST, 'POST: ', __FILE__, __LINE__, __METHOD__, 10);

$arguments = $_POST;
if ( isset($_POST['json']) OR isset($_GET['json']) ) {
	if ( isset($_GET['json']) AND $_GET['json'] != '' ) {
		$arguments = json_decode( $_GET['json'], TRUE );
	} elseif ( isset($_POST['json']) AND $_POST['json'] != '' ) {
		$arguments = json_decode( $_POST['json'], TRUE );
	}
}
Debug::Arr($arguments, 'Arguments: ', __FILE__, __LINE__, __METHOD__, 10);

if ( isset($_GET['SessionID']) AND $_GET['SessionID'] != '' ) {
	$authentication = new Authentication();

	Debug::text('Session ID: '. $_GET['SessionID'] .' Source IP: '. $_SERVER['REMOTE_ADDR'], __FILE__, __LINE__, __METHOD__, 10);
	if ( $authentication->Check( $_GET['SessionID'] ) === TRUE ) {
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
				//Debug::text('Handling JSON Call To API Factory: '.  $class_name .' Method: '. $method .' Message ID: '. $message_id .' UserName: '. $current_user->getUserName(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $class_name != '' AND class_exists( $class_name ) ) {
					$obj = new $class_name;
					if ( method_exists( $obj, 'setAMFMessageID') ) {
						$obj->setAMFMessageID( $message_id ); //Sets AMF message ID so progress bar continues to work.
					}

					if ( $method != '' AND method_exists( $obj, $method ) ) {
						$retval = call_user_func_array( array($obj, $method), (array)$arguments );
						if ( $retval !== NULL ) {
							echo json_encode( $retval );
						} else {
							Debug::text('NULL return value, not JSON encoding any additional data.', __FILE__, __LINE__, __METHOD__, 10);
						}
					} else {
						Debug::text('Method: '. $method .' does not exist!', __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					Debug::text('Class: '. $class_name .' does not exist!', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::text('Failed to get Company Object!', __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::text('Failed to get User Object!', __FILE__, __LINE__, __METHOD__, 10);
		}
	} else {
		Debug::text('User not authenticated!', __FILE__, __LINE__, __METHOD__, 10);

		echo "User not authenticated!<br>\n";
	}
} else {
	TTi18n::chooseBestLocale(); //Make sure we set the locale as best we can when not logged in
	Debug::text('Handling UNAUTHENTICATED JSON Call To API Factory: '.  $class_name .' Method: '. $method .' Message ID: '. $message_id, __FILE__, __LINE__, __METHOD__, 10);
	$class_name = 'APIAuthentication';
	if ( $class_name != '' AND class_exists( $class_name ) ) {
		$obj = new $class_name;
		$obj->setAMFMessageID( $message_id ); //Sets AMF message ID so progress bar continues to work.
		if ( $method != '' AND method_exists( $obj, $method ) ) {
			$retval = call_user_func_array( array($obj, $method), $arguments );
			//If the function returns anything else, encode into JSON and return it.
			//Debug::Arr($retval, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10);
			echo json_encode( $retval );
		} else {
			Debug::text('Method: '. $method .' does not exist!', __FILE__, __LINE__, __METHOD__, 10);
		}
	} else {
		Debug::text('Class: '. $class_name .' does not exist!', __FILE__, __LINE__, __METHOD__, 10);
	}
}

Debug::text('Server Response Time: '. ((float)microtime(TRUE)-$_SERVER['REQUEST_TIME']), __FILE__, __LINE__, __METHOD__, 10);
Debug::writeToLog();
?>
