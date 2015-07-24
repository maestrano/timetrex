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

class SugarCRM {
	private $soap_client_obj = NULL;
	private $session_id = NULL;

	private $sugarcrm_url = NULL;
	private $sugarcrm_user_name = NULL;
	private $sugarcrm_password = NULL;

	function __construct( $url = NULL ) {
		if ( $url != '' ) {
			$this->sugarcrm_url = $url;
		}
	}

	function getSoapObject() {
		if ( $this->soap_client_obj == NULL ) {
			$this->soap_client_obj = new SoapClient(NULL, array(
											'location' => $this->sugarcrm_url,
											'uri' => 'urn:http://www.sugarcrm.com/sugarcrm',
											'style' => SOAP_RPC,
											'use' => SOAP_ENCODED,
											'connection_timeout' => 5,
											'keep_alive' => FALSE, //This prevents "Error fetching HTTP headers" SOAP error.
											'trace' => 1,
											'exceptions' => 0
											)
									);
		}

		return $this->soap_client_obj;
	}

	function convertToNameValueList( $data ) {
		if ( is_array($data) ) {
			foreach( $data as $key => $value ) {
				$row = new stdClass();
				$row->name = $key;
				$row->value = $value;
				$retarr[] = $row;
				unset($row);
			}

			return $retarr;
		}

		return FALSE;
	}

	function login( $user_name = NULL, $password = NULL ) {
		if ( $user_name == '' ) {
			$user_name = $this->sugarcrm_user_name;
		}

		if ( $password == '' ) {
			$password = $this->sugarcrm_password;
		}
		$user_auth = array(
								'user_name' => $user_name,
								'password' => md5($password),
								'version' => '0.1'
						);
		$result = $this->getSoapObject()->login($user_auth, 'timetrex' );

		//echo "Request :\n".htmlspecialchars($this->getSoapObject()->__getLastRequest()) ."\n";
		//echo "Response :\n".htmlspecialchars($this->getSoapObject()->__getLastResponse()) ."\n";
		//Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $result->error->number == 0 ) {
			$this->session_id = $result->id;
			Debug::Text('SugarCRM Login Success! Session ID: '. $this->session_id, __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}
		Debug::Arr($result, 'SOAP Login Result Array: ', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getUserGUID() {
		$user_guid = $this->getSoapObject()->get_user_id( $this->session_id );
		Debug::Text('User GUID: '. $user_guid, __FILE__, __LINE__, __METHOD__, 10);

		return $user_guid;
	}

	function getAvailableModules() {
		$result = $this->getSoapObject()->get_available_modules( $this->session_id );
		Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	//Search by account name as well, if the email doesn't match but company name does.
	function getLeads( $search_field, $search_value, $select_fields = '', $limit = '' ) {
		switch( $search_field ) {
			case 'id':
				$query = "( leads.id = '". $search_value ."' )";
				break;
			case 'email':
				//This query can take around 1 second to run.
				$query = "leads.lead_source = 'Web Site' AND leads.assigned_user_id != 1 AND leads.id in ( SELECT eabr.bean_id FROM email_addr_bean_rel eabr LEFT JOIN email_addresses ea ON eabr.email_address_id = ea.id WHERE eabr.bean_module = 'Leads' AND ea.email_address = '".$search_value."' AND ( eabr.deleted = 0 AND ea.deleted = 0 ) )";
				break;
			case 'any_phone':
				$query = "( replace(replace(replace(replace(replace(leads.phone_work, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' OR replace(replace(replace(replace(replace(leads.phone_mobile, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' OR replace(replace(replace(replace(replace(leads.phone_home, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' OR replace(replace(replace(replace(replace(leads.phone_other, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' OR replace(replace(replace(replace(replace(leads.phone_fax, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' )";
				break;
			case 'status':
				$query = "( leads.status LIKE '". $search_value ."' )";
				break;
		}

		// get_entry_list($session, $module_name, $query, $order_by, $offset, $select_fields, $max_results, $deleted ) {
		$result = $this->getSoapObject()->get_entry_list( $this->session_id, 'Leads', $query, '', 0, $select_fields, $limit, FALSE );
		//Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		//return $result;
		return new SugarCRMReturnHandler( $result, $select_fields, $limit );
	}

	//Search by account name as well, if the email doesn't match but company name does.
	function getContacts( $search_field, $search_value, $select_fields = '', $limit = '' ) {
		switch( $search_field ) {
			case 'email':
				//This query can take around 1 second to run.
				$query = "contacts.assigned_user_id != 1 AND contacts.id in ( SELECT eabr.bean_id FROM email_addr_bean_rel eabr LEFT JOIN email_addresses ea ON eabr.email_address_id = ea.id WHERE eabr.bean_module = 'contacts' AND ea.email_address = '".$search_value."' AND ( eabr.deleted = 0 AND ea.deleted = 0 ) )";
				break;
			case 'any_phone':
				$query = "( replace(replace(replace(replace(replace(contacts.phone_work, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' OR replace(replace(replace(replace(replace(contacts.phone_mobile, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' OR replace(replace(replace(replace(replace(contacts.phone_home, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' OR replace(replace(replace(replace(replace(contacts.phone_other, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' OR replace(replace(replace(replace(replace(contacts.phone_fax, ' ', ''), '.', ''), '-', ''), '(', ''), ')', '') = '". $search_value ."' )";
				break;
			case 'status':
				$query = "( contacts.status LIKE '". $search_value ."' )";
				break;
		}

		// get_entry_list($session, $module_name, $query, $order_by, $offset, $select_fields, $max_results, $deleted ) {
		$result = $this->getSoapObject()->get_entry_list( $this->session_id, 'Contacts', $query, '', 0, $select_fields, $limit );
		//Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		//return $result;
		return new SugarCRMReturnHandler( $result, $select_fields, $limit );
	}

	function getEmails( $search_field, $search_value, $select_fields = '', $limit = '' ) {
		Debug::Text('Get Emails for Field: '. $search_field .' Value: '.  $search_value, __FILE__, __LINE__, __METHOD__, 10);

		switch( $search_field ) {
			case 'lead_id':
				$query = "emails.id in ( SELECT email_id FROM emails_beans WHERE bean_module = 'Leads' AND bean_id = '". $search_value ."' )";
				break;
		}

		$result = $this->getSoapObject()->get_entry_list( $this->session_id, 'Emails', $query, '', 0, $select_fields, $limit );

		return new SugarCRMReturnHandler( $result, $select_fields, $limit );
	}

	function getCalls( $search_field, $search_value, $select_fields = '', $limit = '' ) {
		Debug::Text('Get Calls for Field: '. $search_field .' Value: '.	 $search_value, __FILE__, __LINE__, __METHOD__, 10);

		switch( $search_field ) {
			case 'id':
				//Get a call by call_id.
				$query = "calls.id = '". $search_value ."'";
				break;
			case 'lead_id':
				$query = "calls.id in ( SELECT call_id FROM calls_leads WHERE lead_id = '". $search_value ."' )";
				break;
		}

		$result = $this->getSoapObject()->get_entry_list( $this->session_id, 'Calls', $query, '', 0, $select_fields, $limit );
		//Debug::Arr($result, 'bSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return new SugarCRMReturnHandler( $result, $select_fields, $limit );
	}

	function setLeadStatus( $id, $status ) {

		$data = array(
					array( 'name' => 'status', 'value' => $status ),
					array( 'name' => 'id', 'value' => $id ),
		);

		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Leads', $data );

		if ( $result->error->number == 0 ) {
			Debug::Text('Changed lead status success! ID: '. $id .' Status: '. $status, __FILE__, __LINE__, __METHOD__, 10);

			//Send out an email to sales@timetrex.com stating that the lead was converted?

			return TRUE;
		} else {
			Debug::Text('Changed lead status FAILED!: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	function setContact( $data ) {
		/*
		Fields:
			array( 'name' => 'first_name', 'value' => $this->getFirstName() ),
			array( 'name' => 'last_name', 'value' => $this->getLastName() ),
			array( 'name' => 'phone_work', 'value' => $this->getPhone() ),
			array( 'name' => 'phone_mobile', 'value' => $this->getPhone() ),
			array( 'name' => 'account_name', 'value' => $this->getCompanyName() ),
			array( 'name' => 'email1', 'value' => $this->getEmail() ),
			array( 'name' => 'primary_address_city', 'value' => $this->getCity() ),
			array( 'name' => 'primary_address_country', 'value' => $this->getCountry() ),
			array( 'name' => 'lead_source', 'value' => 'Web Site' ),
			array( 'name' => 'description', 'value' => $request_text_arr['body'] ),
			array( 'name' => 'assigned_user_id', 'value' => '8db3bb58-8203-7ef3-b33f-4db9e7891adc' ), //Murray
		*/
		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Contacts', $this->convertToNameValueList( $data ) );
		Debug::Arr($result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return new SugarCRMReturnHandler( $result );
	}

	function setLead( $data ) {
		/*
		Fields:
			array( 'name' => 'first_name', 'value' => $this->getFirstName() ),
			array( 'name' => 'last_name', 'value' => $this->getLastName() ),
			array( 'name' => 'phone_work', 'value' => $this->getPhone() ),
			array( 'name' => 'account_name', 'value' => $this->getCompanyName() ),
			array( 'name' => 'email1', 'value' => $this->getEmail() ),
			array( 'name' => 'primary_address_city', 'value' => $this->getCity() ),
			array( 'name' => 'primary_address_country', 'value' => $this->getCountry() ),
			array( 'name' => 'Employees_c', 'value' => $this->getEmployee() ),
			array( 'name' => 'time_zone_c', 'value' => $this->getTimeZone() .' ('. $time_zone_offset .')' ),
			array( 'name' => 'status', 'value' => 'New' ),
			array( 'name' => 'lead_source', 'value' => 'Web Site' ),
			array( 'name' => 'opportunity_amount', 'value' => $this->getBudgetAmount() ),
			array( 'name' => 'description', 'value' => $request_text_arr['body'] ),
			//array( 'name' => 'assigned_user_id', 'value' => $user_guid ),
			array( 'name' => 'assigned_user_id', 'value' => '8db3bb58-8203-7ef3-b33f-4db9e7891adc' ), //Murray
		*/
		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Leads', $this->convertToNameValueList( $data ) );
		Debug::Arr($result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return new SugarCRMReturnHandler( $result );
	}

	function setCall( $data ) {
		/*
		Fields:
			Name (subject)
			Description
			duration_hours
			duration_minutes //15 minute increments only.
			date_start
			date_entered
			status (Planned, Held, Not Held)
			direction (Inbound/Outbound)
			assigned_user_id
			parent_type (Module related too: Leads, Accounts, Contacts)
			parent_id (module object id)
		*/
		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Calls', $this->convertToNameValueList( $data ) );
		Debug::Arr($result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return new SugarCRMReturnHandler( $result );
	}

	function setEmail( $data ) {
		// - http://panther.sugarcrm.com/forums/showthread.php?t=68490&highlight=set_entry+email
		/*
		Fields:
			from_addr
			to_addrs
			status (Sent/Read/UnRead/Replied)
			name (subject)
			description (body?)
			date_start (Date/Time email was sent.)
			assigned_user_id
			parent_type (Module related too: Leads, Accounts, Contacts)
			parent_id (module object id)
		*/
		$result = $this->getSoapObject()->set_entry( $this->session_id, 'Emails', $this->convertToNameValueList( $data ) );
		Debug::Arr($result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return new SugarCRMReturnHandler( $result );
	}

	function setRelationship( $module1, $module1_id, $module2, $module2_id ) {
		//Examples on relating contacts/leads to emails.
		//$result = $sugarcrm->setRelationship( 'Contacts', '5b3826da-78d8-568a-73f3-43d92903b54d', 'Emails', '5c5a8553-f3d1-ce01-3b66-4dbc746092d7' );
		//$result = $sugarcrm->setRelationship( 'Leads', '5f1f58a8-45c4-15da-ea0e-4d3f43f0f71f', 'Emails', '5c5a8553-f3d1-ce01-3b66-4dbc746092d7' );
		//$result = $sugarcrm->setRelationship( 'Contacts', '5b3826da-78d8-568a-73f3-43d92903b54d', 'Accounts', '5c5a8553-f3d1-ce01-3b66-4dbc746092d7' );
		$data = array(
						'module1' => $module1,
						'module1_id' => $module1_id,
						'module2' => $module2,
						'module2_id' => $module2_id,
					);

		//Debug::Arr($data, 'Relationship Data: ', __FILE__, __LINE__, __METHOD__, 10);
		$result = $this->getSoapObject()->set_relationship( $this->session_id, $data );
		//Debug::Arr($result, 'SOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return new SugarCRMReturnHandler( $result );
	}
}

class SugarCRMReturnHandler {
	protected $result_data = NULL;
	protected $select_fields = array();
	protected $limit = NULL;

	function __construct( $result_data, $select_fields = array(), $limit = '' ) {
		$this->result_data = $result_data;
		$this->select_fields = $select_fields;
		$this->limit = $limit;

		return TRUE;
	}

	function convertFromNameValueList( $data ) {
		//Debug::Arr($data, 'Raw data to convert: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( isset($data->name_value_list) ) {
			foreach( $data->name_value_list as $field ) {
				$retarr[$field->name] = $field->value;
			}
			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return FALSE;
	}

	function isValid() {
		if ( isset($this->result_data->error) AND $this->result_data->error->number == 0 ) {
			return TRUE;
		} elseif ( isset($this->result_data->number) AND $this->result_data->number == 0  ) { //For set_relationship()
			return TRUE;
		}

		return FALSE;
	}

	function getRecordCount() {
		if ( isset($this->result_data->result_count) ) {
			return (int)$this->result_data->result_count;
		}

		return FALSE;
	}

	//Returns the array of just one row.
	function getRow( $select_fields = array() ) {
		if ( $this->result_data->error->number == 0 AND isset($this->result_data->result_count) AND $this->result_data->result_count == 1 AND count($this->result_data->entry_list ) == 1 ) {
			//One row
			Debug::Text('Single row...', __FILE__, __LINE__, __METHOD__, 10);
			$tmp_data = $this->convertFromNameValueList( $this->result_data->entry_list[0] );
			if ( is_array($tmp_data) ) {
				Debug::Arr($tmp_data, 'Tmp Data', __FILE__, __LINE__, __METHOD__, 10);
				$retarr = $tmp_data;
			}

			//Debug::Arr($retarr, 'Handle Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return FALSE;
	}

	//Returns one column from one row returned.
	function getOne() {
		//Debug::Arr($this->result_data, 'Handle Result Array: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->result_data->error->number == 0 AND isset($this->result_data->result_count) AND $this->result_data->result_count == 1 AND count( $this->result_data->entry_list ) == 1 ) {
			//One row
			Debug::Text('Single row...', __FILE__, __LINE__, __METHOD__, 10);
			$tmp_data = $this->convertFromNameValueList( $this->result_data->entry_list[0] );
			if ( is_array($tmp_data) ) {
				//Debug::Arr($tmp_data, 'Tmp Data', __FILE__, __LINE__, __METHOD__, 10);

				//Check for one field too
				if ( count(array_keys($tmp_data)) == 1 OR count($select_fields) == 1 ) {
					Debug::Text('Single field...', __FILE__, __LINE__, __METHOD__, 10);
					$key = key($tmp_data);
					$retarr = $tmp_data[$key];
				}
			}

			//Debug::Arr($retarr, 'Handle Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($retarr) ) {
				return $retarr;
			}
		} elseif ( $this->result_data->error->number == 0 AND isset($this->result_data->id)	 ) {
			//Saved record, just return the ID.
			return $this->result_data->id;
		}

		return FALSE;
	}

	//Used by getResult()
	function handleResult( $result, $select_fields = array(), $limit = '' ) {
		if ( !is_array($select_fields) ) {
			$select_fields = array($select_fields);
		}

		if ( $result->error->number == 0 AND isset($result->result_count) AND $result->result_count > 0 ) {
			if ( is_array( $result->entry_list ) ) {
				//Use getOne or getRow() if only one result is returned.
				foreach( $result->entry_list as $row ) {
					//Debug::Arr($row, 'zSOAP Result Array: ', __FILE__, __LINE__, __METHOD__, 10);
					$retarr[] = $this->convertFromNameValueList( $row );
				}
			}
			//Debug::Arr($retarr, 'Handle Result Array: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($retarr) ) {
				return $retarr;
			}
		} elseif ( $result->error->number == 0 AND !isset($result->result_count) ) {
			//Saving a record, Sugar returns just the ID and any error message?
			if ( isset($result->id) ) {
				return $result->id;
			}
		}

		return FALSE;
	}

	function getResult( $select_fields = array(), $limit = '') {
		if ( count($select_fields) == 0 ) {
			$select_fields = $this->select_fields;
		}

		if ( $limit == '' ) {
			$limit = $this->limit;
		}

		return $this->handleResult( $this->result_data, $select_fields, $limit );
	}

}
?>
