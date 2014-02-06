<?php
/*
 Global variables
*/
$TIMETREX_URL = 'https://demo.timetrex.com/api/json/api.php';
$TIMETREX_USERNAME = 'demoadmin';
$TIMETREX_PASSWORD = 'demo283920';

//Build URL given a Class and Method to call.
//Format is: http://demo.timetrex.com/api/json/api.php?Class=<CLASS>&Method=<METHOD>&SessionID=<SessionID>
function buildURL( $class, $method, $session_id = FALSE ) {
	global $TIMETREX_URL, $TIMETREX_SESSION_ID;
	$url = $TIMETREX_URL.'?Class='.$class.'&Method='.$method;
	if ( $session_id != '' OR $TIMETREX_SESSION_ID != '' ) {
		if ( $session_id == '' ) {
			$session_id = $TIMETREX_SESSION_ID;
		}
		$url .= '&SessionID='.$session_id;
	}

	return $url;
}

//Handle complex result.
function handleResult( $result, $raw = FALSE ) {
	if ( is_array($result) AND isset($result['api_retval'])) {
		if ( $raw === TRUE ) {
			return $result;
		} else {
			return $result['api_retval'];
		}
	}

	return $result;
}

//Post data (array of arguments) to URL
function postToURL( $url, $data, $raw_result = FALSE ) {
	$curl_connection = curl_init( $url );
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 600 );
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1 );
	//When sending JSON data to POST, it must be sent as JSON=<JSON DATA>
	//<JSON DATA> should be an associative array with the first level being the number of arguments, where each argument can be of mixed type. ie:
	// array(
	//       0 => <ARG1>,
	//		 1 => <ARG2>,
	//		 2 => <ARG3>,
	//       ...
	//      )
	$post_data = 'json='.urlencode( json_encode($data) );
	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_data );

	echo "--------------------------------------------------------------\n";
	echo "Posting data to URL: ". $url ."\n";
	echo "  POST Data: ". $post_data ."\n\n";
	echo "--------------------------------------------------------------\n";
	$result = curl_exec($curl_connection);
	curl_close($curl_connection);

	return handleResult( json_decode($result, TRUE ), $raw_result );
}

$arguments = array('user_name' => $TIMETREX_USERNAME, 'password' => $TIMETREX_PASSWORD );
$TIMETREX_SESSION_ID = postToURL( buildURL( 'APIAuthentication', 'Login' ), $arguments );
if ( $TIMETREX_SESSION_ID == FALSE ) {
	echo "Login Failed!<br>\n";
	exit;
}
echo "Session ID: $TIMETREX_SESSION_ID<br>\n";

//
//Get data for two employees by primary key/ID.
// - Many other filter methods can be used, such as branch, department, user_name, province, state, etc...
//
$arguments = array( 'filter_data' => array(
													'id' => array(1023,11353)
											)
					);
$user_data = postToURL( buildURL( 'APIUser', 'getUser' ), array( $arguments ) );
//var_dump( $user_data );
/* //Example returned data: )
Array
(
    [0] => Array
        (
            [id] => 1023
            [company_id] => 1001
            [status_id] => 10
            [status] => Active
            [group_id] => 0
            [group] =>
            [user_name] => demoadmin1
            [phone_id] => 12345
            [phone_password] => 12345
            [employee_number] => 10
            [title_id] => 0
            [title] =>
            [default_branch_id] => 0
            [default_branch] =>
            [default_department_id] => 1
            [default_department] => Administration
            [permission_control_id] => 7
            [permission_control] => Administrator (92) #1
            [pay_period_schedule_id] => 260
            [pay_period_schedule] => USBiWeekly
            [policy_group_id] => 9
            [policy_group] => Default
            [first_name] => Demo
            [middle_name] => J
            [last_name] => Admin
            [second_last_name] =>
            [sex_id] => 10
            [sex] => MALE
            [address1] => Blah
            [address2] =>
            [city] => Kelowna
            [country] => CA
            [province] => BC
            [postal_code] => V4T1E3
            [work_phone] => 555-555-5555
            [work_phone_ext] =>
            [home_phone] => 555-555-5555
            [mobile_phone] => 555-555-5555
            [fax_phone] =>
            [home_email] => test@democo.com
            [work_email] => test@democo.com
            [birth_date] => 07-Nov-04
            [hire_date] => 07-Nov-04
            [termination_date] =>
            [currency_id] => 3
            [currency] => CAD
            [sin] => 1XXXXX789
            [note] =>
            [deleted] =>
            [created_by_id] => 1
            [created_by] =>
            [created_date] => 08-Nov-04 11:18 AM
            [updated_by_id] => 1023
            [updated_by] => Demo Admin
            [updated_date] => 01-Oct-08 12:49 PM
        )

    [1] => Array
        (
            [id] => 11353
            [company_id] => 1001
            [status_id] => 10
            [status] => Active
            [group_id] => 0
            [group] =>
            [user_name] => payroll
            [phone_id] =>
            [phone_password] =>
            [employee_number] => 16
            [title_id] => 0
            [title] =>
            [default_branch_id] => 297
            [default_branch] => Mississauga
            [default_department_id] => 0
            [default_department] =>
            [permission_control_id] => 7
            [permission_control] => Administrator (92) #1
            [pay_period_schedule_id] => 260
            [pay_period_schedule] => USBiWeekly
            [policy_group_id] => 9
            [policy_group] => Default
            [first_name] => Mrs. Payroll
            [middle_name] =>
            [last_name] => Admin
            [second_last_name] =>
            [sex_id] => 10
            [sex] => MALE
            [address1] =>
            [address2] =>
            [city] =>
            [country] => CA
            [province] => ON
            [postal_code] =>
            [work_phone] =>
            [work_phone_ext] =>
            [home_phone] =>
            [mobile_phone] =>
            [fax_phone] =>
            [home_email] =>
            [work_email] =>
            [birth_date] => 13-Aug-09
            [hire_date] => 01-Aug-08
            [termination_date] =>
            [currency_id] => 3
            [currency] => CAD
            [sin] =>
            [note] =>
            [deleted] =>
            [created_by_id] => 1023
            [created_by] => Demo Admin
            [created_date] => 13-Aug-09 8:09 PM
            [updated_by_id] => 1023
            [updated_by] => Demo Admin
            [updated_date] => 13-Aug-09 8:09 PM
        )
)
*/

//
//Update data for the second employee, mark their status as Terminated and update Termination Date
//
$user_data[1]['status_id'] = 20; //Terminated
$user_data[1]['termination_date'] = '01-Jul-09';

$result = postToURL( buildURL( 'APIUser', 'setUser' ), array( $user_data[1] ) );
if ( $result === TRUE ) {
	echo "Employee data saved successfully.<br>\n";
} else {
	echo "Employee save failed.<br>\n";
	print $result; //Show error messages
}


//
//Update employee record in a single operation. Several records can be updated in a single operation as well.
//
$user_data = array(
				   'id' => 11353,
				   'termination_date' => '02-Jul-09'
				   );

$result = postToURL( buildURL( 'APIUser', 'setUser' ), array( $user_data ) );
if ( $result === TRUE ) {
	echo "Employee data saved successfully.<br>\n";
} else {
	echo "Employee save failed.<br>\n";
	print $result; //Show error messages
}


//
//Add new employee, several new employees can be added in a single operation as well.
//
$user_data = array(
					'status_id' => 10, //Active
					'first_name' => 'Michael',
					'last_name' => 'Jackson',
					'employee_number' => 239842,
					'user_name' => 'mjackson',
					'password' => 'whiteglove123',
					'hire_date' => '01-Oct-09',
					'currency_id' => 3,
					);

$result = postToURL( buildURL( 'APIUser', 'setUser' ), array( $user_data ) );
if ( $result !== FALSE ) {
	echo "Employee added successfully.<br>\n";
	$insert_id = $result; //Get employees new ID on success.
} else {
	echo "Employee save failed.<br>\n";
	print $result; //Show error messages
}


//
//Get TimeSheet Summary report data in raw PHP native array format. 'csv' and 'pdf' are also valid formats.
//
$config = postToURL( buildURL( 'APITimesheetSummaryReport', 'getTemplate' ), array( 'by_employee+regular+overtime+premium+absence' ) );
$result = postToURL( buildURL( 'APITimesheetSummaryReport', 'getTimesheetSummaryReport' ), array( $config, 'raw' ) );
echo "Report Data: <br>\n";
var_dump($result);
?>