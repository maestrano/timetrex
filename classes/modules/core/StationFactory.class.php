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

include_once('Net/IPv4.php');

/**
 * @package Core
 */
class StationFactory extends Factory {
	protected $table = 'station';
	protected $pk_sequence_name = 'station_id_seq'; //PK Sequence name

	protected $company_obj = NULL;

	function _getFactoryOptions( $name, $parent = NULL ) {

		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
											10	=> TTi18n::gettext('DISABLED'),
											20	=> TTi18n::gettext('ENABLED')
									);
				break;
			case 'type':
				$retval = array(
											10	=> TTi18n::gettext('PC'),
									);

				if ( $product_edition_id >= 15 ) {
					$retval[20]	= TTi18n::gettext('PHONE');
					$retval[25]	= TTi18n::gettext('WirelessWeb (WAP)');
					$retval[26]	= TTi18n::gettext('Mobile Web Browser'); //Controls mobile device web browser from quick punch.
					$retval[28]	= TTi18n::gettext('Mobile App (iOS/Android)'); //Controls Mobile application
					$retval[30]	= TTi18n::gettext('iBUTTON');
					$retval[40]	= TTi18n::gettext('Barcode');
					$retval[50]	= TTi18n::gettext('FingerPrint');

					if ( PRODUCTION == FALSE ) {
						$retval[60] = TTi18n::gettext('Desktop PC'); //Single user mode desktop app.
						$retval[61] = TTi18n::gettext('Kiosk: Desktop PC');

						//$retval[70]	= TTi18n::gettext('Kiosk: Web Browser'); //PhoneGap app on WebBrowser KIOSK
						//$retval[71]	= TTi18n::gettext('Web Browser App'); //PhoneGap app on WebBrowser
					}
					$retval[65]	= TTi18n::gettext('Kiosk: Mobile App (iOS/Android)'); //Mobile app in Kiosk Mode

					$retval[100] = TTi18n::gettext('TimeClock: TT-A8');
					$retval[150] = TTi18n::gettext('TimeClock: TT-US100');
					//$retval[200] = TTi18n::gettext('TimeClock: ACTAtek');
				}
				break;
			case 'station_reserved_word':
				$retval = array('any', '*');
				break;
			case 'source_reserved_word':
				$retval = array('any', '*');
				break;
			case 'branch_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Branches'),
										20 => TTi18n::gettext('Only Selected Branches'),
										30 => TTi18n::gettext('All Except Selected Branches'),
									);
				break;
			case 'department_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Departments'),
										20 => TTi18n::gettext('Only Selected Departments'),
										30 => TTi18n::gettext('All Except Selected Departments'),
									);
				break;
			case 'group_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Groups'),
										20 => TTi18n::gettext('Only Selected Groups'),
										30 => TTi18n::gettext('All Except Selected Groups'),
									);
				break;
			case 'poll_frequency':
				$retval = array(
										60 => TTi18n::gettext('1 Minute'),
										120 => TTi18n::gettext('2 Minutes'),
										300 => TTi18n::gettext('5 Minutes'),
										600 => TTi18n::gettext('10 Minutes'),
										900 => TTi18n::gettext('15 Minutes'),
										1800 => TTi18n::gettext('30 Minutes'),
										3600 => TTi18n::gettext('1 Hour'),
										7200 => TTi18n::gettext('2 Hours'),
										10800 => TTi18n::gettext('3 Hours'),
										21600 => TTi18n::gettext('6 Hours'),
										43200 => TTi18n::gettext('12 Hours'),
										86400 => TTi18n::gettext('24 Hours'),
										172800 => TTi18n::gettext('48 Hours'),
										259200 => TTi18n::gettext('72 Hours'),
										604800 => TTi18n::gettext('1 Week'),
									);
				break;
			case 'partial_push_frequency':
			case 'push_frequency':
				$retval = array(
										60 => TTi18n::gettext('1 Minute'),
										120 => TTi18n::gettext('2 Minutes'),
										300 => TTi18n::gettext('5 Minutes'),
										600 => TTi18n::gettext('10 Minutes'),
										900 => TTi18n::gettext('15 Minutes'),
										1800 => TTi18n::gettext('30 Minutes'),
										3600 => TTi18n::gettext('1 Hour'),
										7200 => TTi18n::gettext('2 Hours'),
										10800 => TTi18n::gettext('3 Hours'),
										21600 => TTi18n::gettext('6 Hours'),
										43200 => TTi18n::gettext('12 Hours'),
										86400 => TTi18n::gettext('24 Hours'),
										172800 => TTi18n::gettext('48 Hours'),
										259200 => TTi18n::gettext('72 Hours'),
										604800 => TTi18n::gettext('1 Week'),
									);
				break;
			case 'time_clock_command':
				$retval = array(
										'test_connection' => TTi18n::gettext('Test Connection'),
										'set_date' => TTi18n::gettext('Set Date'),
										'download' => TTi18n::gettext('Download Data'),
										'upload' => TTi18n::gettext('Upload Data'),
										'update_config' => TTi18n::gettext('Update Configuration'),
										'delete_data' => TTi18n::gettext('Delete all Data'),
										'reset_last_punch_time_stamp' => TTi18n::gettext('Reset Last Punch Time'),
										'clear_last_punch_time_stamp' => TTi18n::gettext('Clear Last Punch Time'),
										'restart' => TTi18n::gettext('Restart'),
										'firmware' => TTi18n::gettext('Update Firmware (CAUTION)'),
									);
				break;
			case 'mode_flag':
				Debug::Text('Mode Flag Type ID: '. $parent, __FILE__, __LINE__, __METHOD__, 10);
				if ( $parent == '' ) {
					$parent = 0;
				}
				switch ( (int)$parent ) { //Params should be the station type_id.
					case 28: //Mobile App
						$retval[$parent] = array(
												1		=> TTi18n::gettext('-- Default --'),
												//2			=> TTi18n::gettext('Punch Mode: Quick Punch'), //Enabled by default.
												//4			=> TTi18n::gettext('Punch Mode: QRCode'),
												//8			=> TTi18n::gettext('Punch Mode: QRCode+Face Detection'),
												//16		=> TTi18n::gettext('Punch Mode: Face Recognition'),
												//32		=> TTi18n::gettext('Punch Mode: Face Recognition+QRCode'),
												//64		=> TTi18n::gettext('Punch Mode: Barcode'),
												//128		=> TTi18n::gettext('Punch Mode: iButton'),
												//256
												//512
												//1024
												2048	=> TTi18n::gettext('Disable: GPS'),
												4096	=> TTi18n::gettext('Enable: Punch Images'),
												//8192	=> TTi18n::gettext('Enable: Screensaver'),
												16384	=> TTi18n::gettext('Enable: Auto-Login'),
												//32768	=> TTi18n::gettext('Enable: WIFI Detection - Punch'),
												//65536	=> TTi18n::gettext('Enable: WIFI Detection - Alert'),

												131072	=> TTi18n::gettext('QRCodes: Allow Multiple'), //For single-employee mode scanning.
												262144	=> TTi18n::gettext('QRCodes: Allow MACROs'), //For single-employee mode scanning.
												//1048576	=> TTi18n::gettext('Enable: External Barcode Reader'),
												2097152 => TTi18n::gettext('Enable: Pre-Punch Message'),
												4194304	=> TTi18n::gettext('Enable: Post-Punch Message'),

												1073741824 => TTi18n::gettext('Enable: Diagnostic Logs'),
											);
						break;
					case 61: //PC Station in KIOSK mode.
					case 65: //Mobile App in KIOSK mode.
						$retval[$parent] = array(
												1		=> TTi18n::gettext('-- Default --'),
												2		=> TTi18n::gettext('Punch Mode: Quick Punch'),
												4		=> TTi18n::gettext('Punch Mode: QRCode'),
												8		=> TTi18n::gettext('Punch Mode: QRCode+Face Detection'),
												16		=> TTi18n::gettext('Punch Mode: Facial Recognition'),
												32		=> TTi18n::gettext('Punch Mode: Facial Recognition+QRCode'),
												//64		=> TTi18n::gettext('Punch Mode: Barcode'),
												//128		=> TTi18n::gettext('Punch Mode: iButton'),
												//256
												//512
												//1024
												2048	=> TTi18n::gettext('Disable: GPS'),
												4096	=> TTi18n::gettext('Enable: Punch Images'),
												8192	=> TTi18n::gettext('Disable: Screensaver'),
												//16384		=>
												//32768		=> TTi18n::gettext('Enable: WIFI Detection - Punch'),
												//65536		=> TTi18n::gettext('Enable: WIFI Detection - Alert'),

												131072	=> TTi18n::gettext('QRCodes: Allow Multiple'),
												262144	=> TTi18n::gettext('QRCodes: Allow MACROs'),
												//1048576	=> TTi18n::gettext('Enable: External Barcode Reader'),
												2097152 => TTi18n::gettext('Enable: Pre-Punch Message'),
												4194304	=> TTi18n::gettext('Enable: Post-Punch Message'),

												1073741824 => TTi18n::gettext('Enable: Diagnostic Logs'),
											);
						break;
					case 100: //TimeClock
					case 150: //TimeClock
					default:
						$retval[$parent] = array(
												1		=> TTi18n::gettext('-- Default --'),
												2		=> TTi18n::gettext('Must Select In/Out Status'),
												//4		=> TTi18n::gettext('Enable Work Code (Mode 1)'),
												//8		=> TTi18n::gettext('Enable Work Code (Mode 2)'),
												4		=> TTi18n::gettext('Disable Out Status'),
												8		=> TTi18n::gettext('Enable: Breaks'),
												16		=> TTi18n::gettext('Enable: Lunches'),
												32		=> TTi18n::gettext('Enable: Branch'),
												64		=> TTi18n::gettext('Enable: Department'),

												32768	=> TTi18n::gettext('Authentication: Fingerprint & Password'),
												65536	=> TTi18n::gettext('Authentication: Fingerprint & Proximity Card'),
												131072	=> TTi18n::gettext('Authentication: PIN & Fingerprint'),
												262144	=> TTi18n::gettext('Authentication: Proximity Card & Password'),

												1048576	=> TTi18n::gettext('Enable: External Proximity Card Reader'),
												2097152 => TTi18n::gettext('Enable: Pre-Punch Message'),
												4194304	=> TTi18n::gettext('Enable: Post-Punch Message'),

												1073741824 => TTi18n::gettext('Enable: Diagnostic Logs'),
											);
						if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
							$retval[$parent][128]  = TTi18n::gettext('Enable: Job');
							$retval[$parent][256]  = TTi18n::gettext('Enable: Task');
							$retval[$parent][512]  = TTi18n::gettext('Enable: Quantity');
							$retval[$parent][1024] = TTi18n::gettext('Enable: Bad Quantity');
						}

						break;
				}

				ksort($retval[$parent]);

				//Handle cases where parent isn't defined properly.
				if ( $parent == 0 ) {
					$retval = $retval[$parent];
				}
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => TTi18n::gettext('Status'),
										'-1020-type' => TTi18n::gettext('Type'),
										'-1030-source' => TTi18n::gettext('Source'),

										'-1140-station_id' => TTi18n::gettext('Station'),
										'-1150-description' => TTi18n::gettext('Description'),

										'-1160-time_zone' => TTi18n::gettext('Time Zone'),

										'-1160-branch_selection_type' => TTi18n::gettext('Branch Selection Type'),
										'-1160-department_selection_type' => TTi18n::gettext('Department Selection Type'),
										'-1160-group_selection_type' => TTi18n::gettext('Group Selection Type'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'status',
								'type',
								'source',
								'station_id',
								'description',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'station_id',
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array();
				break;

		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'status_id' => 'Status',
										'status' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'station_id' => 'Station',
										'source' => 'Source',
										'description' => 'Description',
										'branch_id' => 'DefaultBranch',
										'department_id' => 'DefaultDepartment',
										'job_id' => 'DefaultJob',
										'job_item_id' => 'DefaultJobItem',
										'time_zone' => 'TimeZone',
										'user_group_selection_type_id' => 'GroupSelectionType',
										'group_selection_type' => FALSE,
										'group' => FALSE,
										'branch_selection_type_id' => 'BranchSelectionType',
										'branch_selection_type' => FALSE,
										'branch' => FALSE,
										'department_selection_type_id' => 'DepartmentSelectionType',
										'department_selection_type' => FALSE,
										'department' => FALSE,
										'include_user' => FALSE,
										'exclude_user' => FALSE,
										'port' => 'Port',
										'user_name' => 'UserName',
										'password' => 'Password',
										'poll_frequency' => 'PollFrequency',
										'push_frequency' => 'PushFrequency',
										'partial_push_frequency' => 'PartialPushFrequency',
										'enable_auto_punch_status' => 'EnableAutoPunchStatus',
										'mode_flag' => 'ModeFlag',
										'work_code_definition' => 'WorkCodeDefinition',
										'last_punch_time_stamp' => 'LastPunchTimeStamp',
										'last_poll_date' => 'LastPollDate',
										'last_poll_status_message' => 'LastPollStatusMessage',
										'last_push_date' => 'LastPushDate',
										'last_push_status_message' => 'LastPushStatusMessage',
										'last_partial_push_date' => 'LastPartialPushDate',
										'last_partial_push_status_message' => 'LastPartialPushStatusMessage',
										'user_value_1' => 'UserValue1',
										'user_value_2' => 'UserValue2',
										'user_value_3' => 'UserValue3',
										'user_value_4' => 'UserValue4',
										'user_value_5' => 'UserValue5',
										'allowed_date' => 'AllowedDate',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
		}
	}

	function getCompany() {
		return (int)$this->data['company_id'];
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status_id',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return TRUE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type_id',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $type;

			return TRUE;
		}

		return FALSE;
	}

	function isUniqueStation($station) {
		$ph = array(
					'company_id' => $this->getCompany(),
					'station' => $station,
					);

		$query = 'select id from '. $this->table .' where company_id = ? AND station_id = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Station: '. $station, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getStation() {
		if ( isset($this->data['station_id']) ) {
			return (string)$this->data['station_id']; //Should not be cast to INT!
		}

		return FALSE;
	}
	function setStation($station_id = NULL) {
		$station_id = trim($station_id);

		if ( empty($station_id) ) {
			$station_id = $this->genStationID();
		}

		if (	in_array(strtolower($station_id), $this->getOptions('station_reserved_word'))
				OR
				(
				$this->Validator->isLength(	'station_id',
											$station_id,
											TTi18n::gettext('Incorrect Station ID length'),
											2, 250 )
				AND
				$this->Validator->isTrue(	'station_id',
											$this->isUniqueStation($station_id),
											TTi18n::gettext('Station ID already exists'))
				)
			) {

			$this->data['station_id'] = $station_id;

			return TRUE;
		}

		return FALSE;
	}

	function getSource() {
		if ( isset($this->data['source']) ) {
			return $this->data['source'];
		}

		return FALSE;
	}
	function setSource($source) {
		$source = trim($source);

		if (	in_array(strtolower($source), $this->getOptions('source_reserved_word') )
				OR
				(
				$source != NULL
				AND
				$this->Validator->isLength(	'source',
											$source,
											TTi18n::gettext('Incorrect Source ID length'),
											2, 250 )
				)
			) {

			$this->data['source'] = $source;

			return TRUE;
		}

		return FALSE;
	}

	function getDescription() {
		if ( isset($this->data['description']) ) {
			return $this->data['description'];
		}

		return FALSE;
	}
	function setDescription($description) {
		$description = trim($description);

		if ( $this->Validator->isLength(	'description',
											$description,
											TTi18n::gettext('Incorrect Description length'),
											0, 255 ) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultBranch() {
		if ( isset($this->data['branch_id']) ) {
			return (int)$this->data['branch_id'];
		}

		return FALSE;
	}
	function setDefaultBranch($id) {
		$id = trim($id);

		$blf = TTnew( 'BranchListFactory' );

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'branch_id',
														$blf->getByID($id),
														TTi18n::gettext('Invalid Branch')
													) ) {

			$this->data['branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultDepartment() {
		if ( isset($this->data['department_id']) ) {
			return (int)$this->data['department_id'];
		}

		return FALSE;
	}
	function setDefaultDepartment($id) {
		$id = trim($id);

		$dlf = TTnew( 'DepartmentListFactory' );

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'department_id',
														$dlf->getByID($id),
														TTi18n::gettext('Invalid Department')
													) ) {

			$this->data['department_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultJob() {
		if ( isset($this->data['job_id']) ) {
			return (int)$this->data['job_id'];
		}

		return FALSE;
	}
	function setDefaultJob($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		Debug::Text('Job ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
		}

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_id',
														$jlf->getByID($id),
														TTi18n::gettext('Invalid Job')
													) ) {

			$this->data['job_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDefaultJobItem() {
		if ( isset($this->data['job_item_id']) ) {
			return (int)$this->data['job_item_id'];
		}

		return FALSE;
	}
	function setDefaultJobItem($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		Debug::Text('Job Item ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jilf = TTnew( 'JobItemListFactory' );
		}

		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_item_id',
														$jilf->getByID($id),
														TTi18n::gettext('Invalid Task')
													) ) {

			$this->data['job_item_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getTimeZone() {
		if ( isset($this->data['time_zone']) ) {
			return $this->data['time_zone'];
		}

		return FALSE;
	}
	function setTimeZone($time_zone) {
		$time_zone = Misc::trimSortPrefix( trim($time_zone) );

		$upf = TTnew( 'UserPreferenceFactory' );

		if ( $time_zone == 0
				OR
				$this->Validator->inArrayKey(	'time_zone',
											$time_zone,
											TTi18n::gettext('Incorrect Time Zone'),
											Misc::trimSortPrefix( $upf->getOptions('time_zone') ) ) ) {

			$this->data['time_zone'] = $time_zone;

			return TRUE;
		}

		return FALSE;
	}

	function getGroupSelectionType() {
		if ( isset($this->data['user_group_selection_type_id']) ) {
			return (int)$this->data['user_group_selection_type_id'];
		}

		return FALSE;
	}
	function setGroupSelectionType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'user_group_selection_type',
											$value,
											TTi18n::gettext('Incorrect Group Selection Type'),
											$this->getOptions('group_selection_type')) ) {

			$this->data['user_group_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getGroup() {
		$lf = TTnew( 'StationUserGroupListFactory' );
		$lf->getByStationId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getGroup();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setGroup($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationUserGroupListFactory' );
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getGroup();
					Debug::text('Group ID: '. $obj->getGroup() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$lf_b = TTnew( 'UserGroupListFactory' );

			foreach ($ids as $id) {
				if ( isset($ids) AND $id > 0 AND !in_array($id, $tmp_ids) ) {
					$f = TTnew( 'StationUserGroupFactory' );
					$f->setStation( $this->getId() );
					$f->setGroup( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'group',
														$f->Validator->isValid(),
														TTi18n::gettext('Selected Group is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getBranchSelectionType() {
		if ( isset($this->data['branch_selection_type_id']) ) {
			return (int)$this->data['branch_selection_type_id'];
		}

		return FALSE;
	}
	function setBranchSelectionType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'branch_selection_type',
											$value,
											TTi18n::gettext('Incorrect Branch Selection Type'),
											$this->getOptions('branch_selection_type')) ) {

			$this->data['branch_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getBranch() {
		$lf = TTnew( 'StationBranchListFactory' );
		$lf->getByStationId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getBranch();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setBranch($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}
		//Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($ids, 'IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationBranchListFactory' );
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getBranch();
					//Debug::text('Branch ID: '. $obj->getBranch() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$lf_b = TTnew( 'BranchListFactory' );

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = TTnew( 'StationBranchFactory' );
					$f->setStation( $this->getId() );
					$f->setBranch( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'branch',
														$f->Validator->isValid(),
														TTi18n::gettext('Selected Branch is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getDepartmentSelectionType() {
		if ( isset($this->data['department_selection_type_id']) ) {
			return (int)$this->data['department_selection_type_id'];
		}

		return FALSE;
	}
	function setDepartmentSelectionType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'department_selection_type',
											$value,
											TTi18n::gettext('Incorrect Department Selection Type'),
											$this->getOptions('department_selection_type')) ) {

			$this->data['department_selection_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getDepartment() {
		$lf = TTnew( 'StationDepartmentListFactory' );
		$lf->getByStationId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getDepartment();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setDepartment($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		//Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationDepartmentListFactory' );
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getDepartment();
					//Debug::text('Department ID: '. $obj->getDepartment() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$lf_b = TTnew( 'DepartmentListFactory' );

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = TTnew( 'StationDepartmentFactory' );
					$f->setStation( $this->getId() );
					$f->setDepartment( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'department',
														$f->Validator->isValid(),
														TTi18n::gettext('Selected Department is invalid').' ('. $obj->getName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function getIncludeUser() {
		$lf = TTnew( 'StationIncludeUserListFactory' );
		$lf->getByStationId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getIncludeUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setIncludeUser($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationIncludeUserListFactory' );
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getIncludeUser();
					Debug::text('IncludeUser ID: '. $obj->getIncludeUser() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$lf_b = TTnew( 'UserListFactory' );

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = TTnew( 'StationIncludeUserFactory' );
					$f->setStation( $this->getId() );
					$f->setIncludeUser( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'include_user',
														$f->Validator->isValid(),
														TTi18n::gettext('Selected Employee is invalid').' ('. $obj->getFullName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
	function getExcludeUser() {
		$lf = TTnew( 'StationExcludeUserListFactory' );
		$lf->getByStationId( $this->getId() );
		foreach ($lf as $obj) {
			$list[] = $obj->getExcludeUser();
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}
	function setExcludeUser($ids) {
		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($ids) ) {
			$tmp_ids = array();

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationExcludeUserListFactory' );
				$lf_a->getByStationId( $this->getId() );

				foreach ($lf_a as $obj) {
					$id = $obj->getExcludeUser();
					Debug::text('ExcludeUser ID: '. $obj->getExcludeUser() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$lf_b = TTnew( 'UserListFactory' );

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) ) {
					$f = TTnew( 'StationExcludeUserFactory' );
					$f->setStation( $this->getId() );
					$f->setExcludeUser( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'exclude_user',
														$f->Validator->isValid(),
														TTi18n::gettext('Selected Employee is invalid').' ('. $obj->getFullName() .')' )) {
						$f->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}



	/*

		TimeClock specific fields

	*/
	function getPort() {
		if ( isset($this->data['port']) ) {
		return $this->data['port'];
		}

		return FALSE;
	}
	function setPort($value) {
		$value = trim($value);

		if ( $value == ''
				OR
				$this->Validator->isNumeric(	'port',
											$value,
											TTi18n::gettext('Incorrect port')
											) ) {

			$this->data['port'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserName() {
		if ( isset($this->data['user_name']) ) {
			return $this->data['user_name'];
		}

		return FALSE;
	}
	function setUserName($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'user_name',
											$value,
											TTi18n::gettext('Incorrect User Name length'),
											0, 255 ) ) {

			$this->data['user_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPassword() {
		if ( isset($this->data['password']) ) {
			return $this->data['password'];
		}

		return FALSE;
	}
	function setPassword($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'password',
											$value,
											TTi18n::gettext('Incorrect Password length'),
											0, 255 ) ) {

			$this->data['password'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPollFrequency() {
		if ( isset($this->data['poll_frequency']) ) {
			return $this->data['poll_frequency'];
		}

		return FALSE;
	}
	function setPollFrequency($value) {
		$value = trim($value);

		if (	$value == 0
				OR
				$this->Validator->inArrayKey(	'poll_frequency',
											$value,
											TTi18n::gettext('Incorrect Download Frequency'),
											$this->getOptions('poll_frequency')) ) {

			$this->data['poll_frequency'] = $value;

			return TRUE;
		}

		return FALSE;
	}


	function getPushFrequency() {
		if ( isset($this->data['push_frequency']) ) {
			return $this->data['push_frequency'];
		}

		return FALSE;
	}
	function setPushFrequency($value) {
		$value = trim($value);

		if (	$value == 0
				OR
				$this->Validator->inArrayKey(	'push_frequency',
												$value,
												TTi18n::gettext('Incorrect Upload Frequency'),
												$this->getOptions('push_frequency')) ) {

			$this->data['push_frequency'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getPartialPushFrequency() {
		if ( isset($this->data['partial_push_frequency']) ) {
			return $this->data['partial_push_frequency'];
		}

		return FALSE;
	}
	function setPartialPushFrequency($value) {
		$value = trim($value);

		if ( $value == 0
			OR
			$this->Validator->inArrayKey(	'partial_push_frequency',
											$value,
											TTi18n::gettext('Incorrect Partial Upload Frequency'),
											$this->getOptions('push_frequency')) ) {

			$this->data['partial_push_frequency'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getEnableAutoPunchStatus() {
		return $this->fromBool( $this->data['enable_auto_punch_status'] );
	}
	function setEnableAutoPunchStatus($bool) {
		$this->data['enable_auto_punch_status'] = $this->toBool($bool);

		return TRUE;
	}

	function getModeFlag() {
		if ( isset($this->data['mode_flag']) ) {
			return Option::getArrayByBitMask( $this->data['mode_flag'], $this->getOptions('mode_flag', $this->getType() ));
		}

		return FALSE;
	}
	function setModeFlag($arr) {
		$bitmask = Option::getBitMaskByArray( $arr, $this->getOptions('mode_flag', $this->getType() ) );

		if ( $this->Validator->isNumeric(	'mode_flag',
											$bitmask,
											TTi18n::gettext('Incorrect Mode') ) ) {

			$this->data['mode_flag'] = $bitmask;

			return TRUE;
		}

		return FALSE;
	}

	function parseWorkCode( $work_code ) {
		$definition = $this->getWorkCodeDefinition();

		$work_code = str_pad( $work_code, 9, 0, STR_PAD_LEFT);

		$retarr = array( 'branch_id' => 0, 'department_id' => 0, 'job_id' => 0, 'job_item_id' => 0 );

		$start_digit = 0;
		if ( isset($definition['branch']) AND $definition['branch'] > 0 ) {
			$retarr['branch_id'] = (int)substr( $work_code, $start_digit, $definition['branch']);
			$start_digit += $definition['branch'];
		}

		if ( isset($definition['department']) AND $definition['department'] > 0 ) {
			$retarr['department_id'] = (int)substr( $work_code, $start_digit, $definition['department']);
			$start_digit += $definition['department'];
		}

		if ( isset($definition['job']) AND $definition['job'] > 0 ) {
			$retarr['job_id'] = (int)substr( $work_code, $start_digit, $definition['job']);
			$start_digit += $definition['job'];
		}

		if ( isset($definition['job_item']) AND $definition['job_item'] > 0 ) {
			$retarr['job_item_id'] = (int)substr( $work_code, $start_digit, $definition['job_item']);
			$start_digit += $definition['job_item'];
		}

		Debug::Arr($retarr, 'Parsed Work Code: ', __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}

	//Update JUST station last_poll_date AND last_punch_time_stamp without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateLastPollDateAndLastPunchTimeStamp( $id, $last_poll_date = 0, $last_punch_date = 0 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		$slf = TTnew( 'StationListFactory' );
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						'last_poll_date' => $last_poll_date,
						'last_punch_date' => $this->db->BindTimeStamp( $last_punch_date ),
						'id' => $id,
						);
			$query = 'UPDATE '. $this->getTable() .' set last_poll_date = ?, last_punch_time_stamp = ? where id = ?';
			$this->db->Execute($query, $ph);

			return TRUE;
		}

		return FALSE;
	}

	//Update JUST station last_poll_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateLastPollDate( $id, $last_poll_date = 0 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		$slf = TTnew( 'StationListFactory' );
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						'last_poll_date' => $last_poll_date,
						'id' => $id,
						);
			$query = 'UPDATE '. $this->getTable() .' set last_poll_date = ? where id = ?';
			$this->db->Execute($query, $ph);

			return TRUE;
		}

		return FALSE;
	}

	//Update JUST station last_push_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateLastPushDate( $id, $last_push_date = 0 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		$slf = TTnew( 'StationListFactory' );
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						'last_push_date' => $last_push_date,
						'id' => $id,
						);

			$query = 'UPDATE '. $this->getTable() .' set last_push_date = ? where id = ?';
			$this->db->Execute($query, $ph);

			return TRUE;
		}

		return FALSE;
	}

	//Update JUST station last_partial_push_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateLastPartialPushDate( $id, $last_partial_push_date = 0 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		$slf = TTnew( 'StationListFactory' );
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						'last_partial_push_date' => $last_partial_push_date,
						'id' => $id,
						);

			$query = 'UPDATE '. $this->getTable() .' set last_partial_push_date = ? where id = ?';
			$this->db->Execute($query, $ph);

			return TRUE;
		}

		return FALSE;
	}

	function getLastPunchTimeStamp( $raw = FALSE) {
		if ( isset($this->data['last_punch_time_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['last_punch_time_stamp'];
			} else {
				return TTDate::strtotime( $this->data['last_punch_time_stamp'] );
			}
		}

		return FALSE;
	}
	function setLastPunchTimeStamp($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if	(	$this->Validator->isDate(		'last_punch_time_stamp',
												$epoch,
												TTi18n::gettext('Incorrect last punch date')) ) {

			$this->data['last_punch_time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getLastPollDate() {
		if ( isset($this->data['last_poll_date']) ) {
			return $this->data['last_poll_date'];
		}

		return FALSE;
	}
	function setLastPollDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if	(	$this->Validator->isDate(		'last_poll_date',
												$epoch,
												TTi18n::gettext('Incorrect last poll date')) ) {

			$this->data['last_poll_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getLastPollStatusMessage() {
		if ( isset($this->data['last_poll_status_message']) ) {
			return $this->data['last_poll_status_message'];
		}

		return FALSE;
	}
	function setLastPollStatusMessage($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'last_poll_status_message',
											$value,
											TTi18n::gettext('Incorrect Status Message length'),
											0, 255 ) ) {

			$this->data['last_poll_status_message'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLastPushDate() {
		if ( isset($this->data['last_push_date']) ) {
			return $this->data['last_push_date'];
		}

		return FALSE;
	}
	function setLastPushDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if	(	$this->Validator->isDate(		'last_push_date',
												$epoch,
												TTi18n::gettext('Incorrect last push date')) ) {

			$this->data['last_push_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getLastPushStatusMessage() {
		if ( isset($this->data['last_push_status_message']) ) {
			return $this->data['last_push_status_message'];
		}

		return FALSE;
	}
	function setLastPushStatusMessage($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'last_push_status_message',
											$value,
											TTi18n::gettext('Incorrect Status Message length'),
											0, 255 ) ) {

			$this->data['last_push_status_message'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getLastPartialPushDate() {
		if ( isset($this->data['last_partial_push_date']) ) {
			return $this->data['last_partial_push_date'];
		}

		return FALSE;
	}
	function setLastPartialPushDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if	(	$this->Validator->isDate(		'last_partial_push_date',
												$epoch,
												TTi18n::gettext('Incorrect last partial push date')) ) {

			$this->data['last_partial_push_date'] = $epoch;

			return TRUE;
		}

		return FALSE;

	}

	function getLastPartialPushStatusMessage() {
		if ( isset($this->data['last_partial_push_status_message']) ) {
			return $this->data['last_partial_push_status_message'];
		}

		return FALSE;
	}
	function setLastPartialPushStatusMessage($value) {
		$value = trim($value);

		if ( $this->Validator->isLength(	'last_partial_push_status_message',
											$value,
											TTi18n::gettext('Incorrect Status Message length'),
											0, 255 ) ) {

			$this->data['last_partial_push_status_message'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue1() {
		if ( isset($this->data['user_value_1']) ) {
			return $this->data['user_value_1'];
		}

		return FALSE;
	}
	function setUserValue1($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_1',
											$value,
											TTi18n::gettext('User Value 1 is invalid'),
											1, 255) ) {

			$this->data['user_value_1'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue2() {
		if ( isset($this->data['user_value_2']) ) {
			return $this->data['user_value_2'];
		}

		return FALSE;
	}
	function setUserValue2($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_2',
											$value,
											TTi28n::gettext('User Value 2 is invalid'),
											2, 255) ) {

			$this->data['user_value_2'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue3() {
		if ( isset($this->data['user_value_3']) ) {
			return $this->data['user_value_3'];
		}

		return FALSE;
	}
	function setUserValue3($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_3',
											$value,
											TTi38n::gettext('User Value 3 is invalid'),
											3, 255) ) {

			$this->data['user_value_3'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue4() {
		if ( isset($this->data['user_value_4']) ) {
			return $this->data['user_value_4'];
		}

		return FALSE;
	}
	function setUserValue4($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_4',
											$value,
											TTi48n::gettext('User Value 4 is invalid'),
											4, 255) ) {

			$this->data['user_value_4'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getUserValue5() {
		if ( isset($this->data['user_value_5']) ) {
			return $this->data['user_value_5'];
		}

		return FALSE;
	}
	function setUserValue5($value) {
		$value = trim($value);

		if (	$value == ''
				OR
				$this->Validator->isLength(	'user_value_5',
											$value,
											TTi58n::gettext('User Value 5 is invalid'),
											5, 255) ) {

			$this->data['user_value_5'] = $value;

			return TRUE;
		}

		return FALSE;
	}



	private function genStationID() {
		return md5( uniqid( dechex( mt_srand() ) ) );
	}

	function setCookie() {
		if ( $this->getStation() ) {

			setcookie('StationID', $this->getStation(), (time() + 157680000), Environment::getBaseURL() );

			return TRUE;
		}

		return FALSE;
	}

	function destroyCookie() {
		setcookie('StationID', NULL, (time() + 9999999), Environment::getBaseURL() );

		return TRUE;
	}

	//Update JUST station allowed_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	function updateAllowedDate( $id, $user_id ) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$slf = TTnew( 'StationListFactory' );
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = array(
						'allowed_date' => TTDate::getTime(),
						'id' => $id,
						);
			$query = 'UPDATE '. $this->getTable() .' set allowed_date = ? where id = ?';
			$this->db->Execute($query, $ph);

			TTLog::addEntry( $id, 200, TTi18n::getText('Access from station Allowed'), $user_id, $this->getTable() ); //Allow

			return TRUE;
		}

		return FALSE;
	}

	function getAllowedDate() {
		if ( isset($this->data['allowed_date']) ) {
			return $this->data['allowed_date'];
		}

		return FALSE;
	}
	function setAllowedDate($epoch = NULL) {
		$epoch = trim($epoch);

		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		if	(	$this->Validator->isDate(		'allowed_date',
												$epoch,
												TTi18n::gettext('Incorrect allowed date')) ) {

			$this->data['allowed_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function checkSource( $source, $current_station_id ) {
		$source = trim($source);

		if ( isset($_SERVER['REMOTE_ADDR']) ) {
			$remote_addr = $_SERVER['REMOTE_ADDR'];
		} else {
			$remote_addr = NULL;
		}

		//Required for load balancers, however we may need to add another config option to restrict
		//the REMOTE_ADDR <-> HTTP_X_FORWARDED_FOR combination, so when not be a load balancer/proxy it ignores this header.
		if ( isset($_SERVER['X-Forwarded-For'] ) ) {
			$x_forwarded_for = $_SERVER['X-Forwarded-For'];
		} else {
			$x_forwarded_for = NULL;
		}

		//IGNORE x_forwarded_for for now, because anyone could spoof this.
		//Add a switch that will enable/disable this feature.

		//$remote_addr = '192.168.2.10';
		//$remote_addr = '192.168.1.10';
		//$remote_addr = '127.0.0.1';

		if ( in_array( $this->getType(), array(10, 25) ) AND preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(\/[0-9]{1,2})*/', $source) ) {
			Debug::text('Source is an IP address!', __FILE__, __LINE__, __METHOD__, 10);
		} elseif ( in_array( $this->getType(), array(10, 25, 100) ) AND !in_array( strtolower( $this->getStation() ), $this->getOptions('station_reserved_word') ) )  {
			//Do hostname lookups for TTA8 timeclocks as well.
			Debug::text('Source is NOT an IP address, do hostname lookup: '. $source, __FILE__, __LINE__, __METHOD__, 10);

			$hostname_lookup = $this->getCache( $remote_addr.$source );
			if ( $hostname_lookup === FALSE ) {
				$hostname_lookup = gethostbyname( $source );

				$this->saveCache($hostname_lookup, $remote_addr.$source );
			}

			if ($hostname_lookup == $source ) {
				Debug::text('Hostname lookup failed!', __FILE__, __LINE__, __METHOD__, 10);
			} else {
				Debug::text('Hostname lookup succeeded: '. $hostname_lookup, __FILE__, __LINE__, __METHOD__, 10);
				$source = $hostname_lookup;
			}
			unset($hostname_lookup);
		} else {
			Debug::text('Source is not internet related', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text('Source: '. $source .' Remote IP: '. $remote_addr .' Behind Proxy IP: '. $x_forwarded_for, __FILE__, __LINE__, __METHOD__, 10);
		if (	(
					$current_station_id == $this->getStation()
						OR in_array( strtolower( $this->getStation() ), $this->getOptions('station_reserved_word') )
				)
				AND
				(
					in_array( strtolower( $this->getSource() ), $this->getOptions('source_reserved_word') )
					OR
						( $source == $remote_addr
							OR $source == $x_forwarded_for )
					OR
						( $current_station_id == $this->getSource() )
					OR
						( Net_IPv4::ipInNetwork( $remote_addr, $source) )
					OR
						in_array( $this->getType(), array(100, 110, 120, 200) )
				)

			) {

			Debug::text('Returning TRUE', __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		Debug::text('Returning FALSE', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function isAllowed($user_id = NULL, $current_station_id = NULL, $id = NULL) {
		if ($user_id == NULL OR $user_id == '') {
			global $current_user;
			$user_id = $current_user->getId();
		}
		//Debug::text('User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ($current_station_id == NULL OR $current_station_id == '') {
			global $current_station;
			$current_station_id = $current_station->getStation();
		}
		//Debug::text('Station ID: '. $current_station_id, __FILE__, __LINE__, __METHOD__, 10);

		//Debug::text('Status: '. $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getStatus() != 20 ) { //Enabled
			return FALSE;
		}

		$retval = FALSE;

		Debug::text('User ID: '. $user_id .' Station ID: '. $current_station_id .' Status: '. $this->getStatus() .' Current Station: '. $this->getStation(), __FILE__, __LINE__, __METHOD__, 10);

		//Handle IP Addresses/Hostnames
		if ( $this->getType() == 10
				AND !in_array( strtolower( $this->getSource() ), $this->getOptions('source_reserved_word') ) ) {

			if ( strpos( $this->getSource(), ',') !== FALSE ) {
				//Found list
				$source = explode(',', $this->getSource() );
			} else {
				//Found single entry
				$source[] = $this->getSource();
			}

			if ( is_array($source) ) {
				foreach( $source as $tmp_source ) {
					if ( $this->checkSource( $tmp_source, $current_station_id ) == TRUE ) {
						$retval = TRUE;
						break;
					}
				}
				unset($tmp_source);
			}
		} else {
			$source = $this->getSource();

			$retval = $this->checkSource( $source, $current_station_id );
		}

		//Debug::text('Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Current Station ID: '. $current_station_id .' Station ID: '. $this->getStation(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $retval === TRUE ) {
			Debug::text('Station IS allowed! ', __FILE__, __LINE__, __METHOD__, 10);

			//Set last allowed date, so we can track active/inactive stations.
			if ( $id != NULL AND $id != '' ) {
				$this->updateAllowedDate( $id, $user_id );
			}

			return TRUE;
		}

		Debug::text('Station IS NOT allowed! ', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	//A fast way to check many stations if the user is allowed.
	function checkAllowed($user_id = NULL, $station_id = NULL, $type = 10 ) { //10 = PC
		if ($user_id == NULL OR $user_id == '') {
			global $current_user;
			$user_id = $current_user->getId();
		}
		Debug::text('User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $station_id == NULL OR $station_id == '' ) {
			global $current_station;
			if ( is_object($current_station) ) {
				$station_id = $current_station->getStation();
			} elseif ( $this->getId() != '' ) {
				$station_id = $this->getId();
			} else {
				Debug::text('Unable to get Station Object! Station ID: '. $station_id, __FILE__, __LINE__, __METHOD__, 10);
				return FALSE;
			}
		}

		$slf = TTnew( 'StationListFactory' );
		$slf->getByUserIdAndStatusAndType($user_id, 20, $type);
		Debug::text('Station ID: '. $station_id .' Type: '. $type .' Found Stations: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		foreach($slf as $station) {
			Debug::text('Checking Station ID: '. $station->getId(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $station->isAllowed( $user_id, $station_id, $station->getId() ) === TRUE) {
				Debug::text('Station IS allowed! '. $station_id .' - ID: '. $station->getId(), __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
		}

		return FALSE;
	}

	static function getOrCreateStation( $station_id, $company_id, $type_id = 10, $permission_obj = NULL, $user_obj = NULL ) {
		Debug::text('Checking for Station ID: '. $station_id .' Company ID: '. $company_id .' Type: '. $type_id, __FILE__, __LINE__, __METHOD__, 10);
		
		$slf = new StationListFactory();
		$slf->getByStationIdandCompanyId( $station_id, $company_id );
		if ( $slf->getRecordCount() == 1 ) {
			//Handle disabled station here, but only for KIOSK type stations.
			//As non-kiosk stations still need to be able to revert back to the wildcard ANY station and check that for access. Where KIOSK stations will not do that.
			if ( $slf->getCurrent()->getStatus() == 10 AND in_array( $slf->getCurrent()->getType(), array(61, 65) ) ) { //Disabled
				Debug::text('aStation is disabled...'. $station_id, __FILE__, __LINE__, __METHOD__, 10);
				$slf->Validator->isTrue(		'status_id',
												FALSE,
												TTi18n::gettext('Waiting for Administrator approval to activate this device' ));

				return $slf;
			} elseif ( $slf->getCurrent()->getStatus() == 10 AND in_array( $slf->getCurrent()->getType(), array(28) ) ) {
				//Check isAllowed for any wildcard stations first...
				if ( $slf->getCurrent()->checkAllowed( $user_obj->getId(), $station_id, $type_id ) == TRUE ) {
					$retval = $slf->getCurrent()->getStation();
				} else {
					Debug::text('bStation is disabled...'. $station_id, __FILE__, __LINE__, __METHOD__, 10);
					$slf->Validator->isTrue(		'status_id',
													FALSE,
													TTi18n::gettext('You are not authorized to punch in or out from this station!' ));

					return $slf;
				}
			} else {
				$retval = $slf->getCurrent()->getStation();
			}
		} else {
			Debug::text('Station ID: '. $station_id .' (Type: '. $type_id .') does not exist, creating new station', __FILE__, __LINE__, __METHOD__, 10);

			//Insert new station
			$sf = TTnew( 'StationFactory' );
			$sf->setCompany( $company_id );
			$sf->setID( $sf->getNextInsertId() ); //This is required to call setIncludeUser() properly.

			switch ( $type_id ) {
				case 10: //PC
					$status_id = 20; //Enabled, but will be set disabled automatically by isActiveForAnyEmployee()
					$station = NULL; //Using NULL means we generate our own.
					$description = substr( $_SERVER['HTTP_USER_AGENT'], 0, 250);
					$source = $_SERVER['REMOTE_ADDR'];
					break;
				case 28: //Mobile App (iOS/Android)
				case 60: //Desktop App
					$flex_app = FALSE;
					if ( stripos( $_SERVER['HTTP_USER_AGENT'], 'AdobeAIR' ) !== FALSE ) {
						Debug::Text('Flex App Detected...', __FILE__, __LINE__, __METHOD__, 10);
						$flex_app = TRUE;
					}

					if ( $flex_app == TRUE ) {
						$status_id = 20; //Enabled
						$station = NULL; //Can't get UDID on iOS5, but we can on Android. Using NULL means we generate our own.
						$description = TTi18n::getText('Mobile Application').': '.substr( $_SERVER['HTTP_USER_AGENT'], 0, 250);
						$source = $_SERVER['REMOTE_ADDR'];
					} else {
						$status_id = 20; //Enabled
						if ( $station_id != '' ) {
							//Prevent stations from having the type_id appended to the end several times.
							if ( substr( $station_id, ( strlen($type_id) * -1 ) ) != $type_id ) {
								$station = $station_id.$type_id;
							} else {
								$station = $station_id;
							}
						} else {
							$station = NULL; //Can't get UDID on iOS5, but we can on Android. Using NULL means we generate our own.
						}
						$description = TTi18n::getText('Mobile Application').': '.substr( $_SERVER['HTTP_USER_AGENT'], 0, 250);
						$source = $_SERVER['REMOTE_ADDR'];
						//$source = 'ANY';

						$sf->setPollFrequency( 600 );
						$sf->setEnableAutoPunchStatus(TRUE);

						/*
						//If the currently logged in user is higher than regular employee permissions, allow all records.
						//Otherwise just allow the users own records to be synced.
						//FIXME: Now that non-kiosk stations are logged in to download data, I don't think this is needed anymore.
						//		 But we will need to allow at least one user so the station isn't automatically disabled in isActiveForAnyEmployee()
						//FIXME: Maybe check for a wildcard station of the same type, and if it exists allow new stations to work for the same user selection?
						if ( is_object( $permission_obj ) AND ( $permission_obj->Check('user', 'view') OR $permission_obj->Check('user', 'view_child') ) ) {
							Debug::Text('Supervisor or higher, allowing all employees for potential team punch...', __FILE__, __LINE__, __METHOD__, 10);
							$sf->setGroupSelectionType( 10 ); //All allowed
							$sf->setBranchSelectionType( 10 ); //All allowed
							$sf->setDepartmentSelectionType( 10 ); //All allowed
						} else {
							Debug::Text('Regular employee, defaulting to single-user mode...', __FILE__, __LINE__, __METHOD__, 10);
							//The station must be active for the app to allow punches.
							$sf->setGroupSelectionType( 20 ); //Only selected
							$sf->setBranchSelectionType( 20 ); //Only selected
							$sf->setDepartmentSelectionType( 20 ); //Only selected
							$sf->setIncludeUser( array( $user_obj->getID() ) );
						}
						*/
						$sf->setModeFlag( array( 1 ) ); //Default

					}
					break;
				case 61: //Kiosk: Desktop
				case 65: //Kiosk: Mobile App (iOS/Android)
					Debug::Text('KIOSK station...', __FILE__, __LINE__, __METHOD__, 10);

					if ( DEMO_MODE == TRUE ) {
						$status_id = 20; //Always activate immediately when using demo.
					} else {
						$status_id = 10; //Initially create as disabled and admin must manually enable it.
					}

					$sf->setType( $type_id ); //Need to set thie before setModeFlag()

					//Use the passed in station_id, as it will be the UDID and contain the type_id on the end.
					//Add the type_id as the suffix to avoid conflicts if the user switches between kiosk and non-kiosk modes.
					//Prevent stations from having the type_id appended to the end several times.
					if ( substr( $station_id, ( strlen($type_id) * -1 ) ) != $type_id ) {
						$station = $station_id.$type_id;
					} else {
						$station = $station_id;
					}

					if ( DEPLOYMENT_ON_DEMAND == TRUE ) {
						$description = TTi18n::getText('PENDING ACTIVATION [ADDITIONAL FEE REQUIRED] - Automatic KIOSK Setup');
					} else {
						$description = TTi18n::getText('PENDING ACTIVATION - Automatic KIOSK Setup');
					}
					$source = 'ANY';

					$sf->setPollFrequency( 600 );
					$sf->setEnableAutoPunchStatus(TRUE);

					$sf->setGroupSelectionType( 10 ); //All allowed
					$sf->setBranchSelectionType( 10 ); //All allowed
					$sf->setDepartmentSelectionType( 10 ); //All allowed
					
					$sf->setModeFlag( array( 4, 4096 ) ); //Default QRCode (without face), Capture Images in KIOSK mode.

					if ( is_object( $sf->getCompanyObject() ) AND is_object( $sf->getCompanyObject()->getUserDefaultObject() ) ) {
						$sf->setTimeZone( $sf->getCompanyObject()->getUserDefaultObject()->getTimeZone() );
					}

					break;
			}

			//Since we change the station_id (add type_id) for KIOSK stations, check to see if the modified station_id exists and return it.
			if ( in_array($type_id, array(28, 60, 61, 65) ) ) {
				$slf->getByStationIdandCompanyId( $station, $company_id );
				if ( $slf->getRecordCount() == 1 ) {
					Debug::Text('Station already exists with modified station_id, returning that instead.', __FILE__, __LINE__, __METHOD__, 10);
					return $slf->getCurrent()->getStation();
				} else {
					Debug::Text('Station definitely does not exist, attempting to create it...', __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			$sf->setStatus( $status_id );
			$sf->setType( $type_id );
			$sf->setDescription( $description );
			$sf->setStation( $station );
			$sf->setSource( $source );
			if ( $sf->isValid() ) {
				if ( $sf->Save( FALSE, TRUE ) ) {
					$retval = $sf->getStation();
				}
			} else {
				Debug::Text('Station is invalid, returning object...', __FILE__, __LINE__, __METHOD__, 10);
				$retval = $sf;
			}

		}

		Debug::text('Returning StationID: '. $station_id, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function Validate() {
		if ( is_object($this->getCompanyObject() ) AND $this->getCompanyObject()->getProductEdition() == 10 AND $this->getType() > 10 ) {
			$this->Validator->isTrue(		'type_id',
											FALSE,
											TTi18n::gettext('Type is not available in %1 Community Edition, please contact our sales department for more information', APPLICATION_NAME ));
		}

		if ( $this->validate_only == FALSE AND $this->getDescription() == '' ) {
			$this->Validator->isTrue(		'description',
											FALSE,
											TTi18n::gettext('Description must be specified'));
		}
																																												$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(NULL, array("\x73\x74\x61\x74\x69\x6f\x6e" => $this )); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); }
		return TRUE;
	}

	//Check to see if this station is active for any employees, if not, we may as well mark it as disabled to speed up queries.
	function isActiveForAnyEmployee() {
		if (
				( $this->getGroupSelectionType() == 20 AND $this->getGroup() === FALSE )
				AND
				( $this->getBranchSelectionType() == 20 AND $this->getBranch() === FALSE )
				AND
				( $this->getDepartmentSelectionType() == 20 AND $this->getDepartment() === FALSE )
				AND
				( $this->getIncludeUser() === FALSE )
			) {
			Debug::text('Station is not active for any employees, everyone is denied.', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}
		Debug::text('Station IS active for at least some employees...', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function preSave() {
		//New stations are deny all by default, so if they haven't
		//set the selection types, default them to only selected, so
		//everyone is denied, because none are selected.
		if ( $this->getGroupSelectionType() == FALSE ) {
			$this->setGroupSelectionType( 20 ); //Only selected.
		}
		if ( $this->getBranchSelectionType() == FALSE ) {
			$this->setBranchSelectionType( 20 ); //Only selected.
		}
		if ( $this->getDepartmentSelectionType() == FALSE ) {
			$this->setDepartmentSelectionType( 20 ); //Only selected.
		}

		if ( $this->getStatus() == 20 AND $this->isActiveForAnyEmployee() == FALSE ) {
			$this->setStatus( 10 ); //Disabled
		}
		
		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getStation() );
/*
		foreach ($this->getUser() as $user_id ) {
			$cache_id = 'station_checkAllowed_'.$this->getId().$user_id;
			$this->removeCache( $cache_id );
		}
*/
		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'last_punch_time_stamp':
						case 'last_poll_date':
						case 'last_push_date':
						case 'last_partial_push_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						case 'group':
							$this->setGroup( $data[$key] );
							break;
						case 'branch':
							$this->setBranch( $data[$key] );
							break;
						case 'department':
							$this->setDepartment( $data[$key] );
							break;
						case 'include_user':
							$this->setIncludeUser( $data[$key] );
							break;
						case 'exclude_user':
							$this->setExcludeUser( $data[$key] );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'last_punch_time_stamp':
						case 'last_poll_date':
						case 'last_push_date':
						case 'last_partial_push_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() );
							break;
						case 'group':
							$data[$variable] = $this->getGroup();
							break;
						case 'branch':
							$data[$variable] = $this->getBranch();
							break;
						case 'department':
							$data[$variable] = $this->getDepartment();
							break;
						case 'include_user':
							$data[$variable] = $this->getIncludeUser();
							break;
						case 'exclude_user':
							$data[$variable] = $this->getExcludeUser();
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		if ( !( $log_action == 10 AND $this->getType() == 10 ) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Station'), NULL, $this->getTable(), $this );
		}
	}
}
?>
