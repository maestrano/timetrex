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


/**
 * @package Modules\Company
 */
class CompanyGenericTagFactory extends Factory {
	protected $table = 'company_generic_tag';
	protected $pk_sequence_name = 'company_generic_tag_id_seq'; //PK Sequence name

	protected $name_validator_regex = '/^[a-z0-9-_\[\]\(\)=|\.@]{1,250}$/i'; //Deny +, -

	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'object_type':
				$retval = array(
										//These could be names instead?
										100 => 'company',
										110 => 'branch',
										120 => 'department',
										130 => 'stations',
										140 => 'hierarchy',
										150 => 'request',
										160 => 'message',
										170 => 'policy_group',

										200 => 'users',
										210 => 'user_wage',
										220 => 'user_title',
										230 => 'user_contact',

										250 => 'qualification',
										251 => 'user_skill',
										252 => 'user_education',
										253 => 'user_license',
										254 => 'user_language',
										255 => 'user_membership',

										300 => 'pay_stub_amendment',

										310 => 'kpi',
										320 => 'user_review_control',
										330 => 'user_review',

										350 => 'job_vacancy',
										360 => 'job_applicant',
										370 => 'job_applicant_location',
										380 => 'job_applicant_employment',
										390 => 'job_applicant_reference',

										391 => 'job_applicant_skill',
										392 => 'job_applicant_education',
										393 => 'job_applicant_language',
										394 => 'job_applicant_license',
										395 => 'job_applicant_membership',

										400 => 'schedule',
										410 => 'recurring_schedule_template',

										500 => 'report',
										510 => 'report_schedule',

										600 => 'job',
										610 => 'job_item',

										700 => 'document',

										800 => 'client',
										810 => 'client_contact',
										820 => 'client_payment',

										900 => 'product',
										910 => 'invoice',
										920 => 'invoice_transaction',

										930 => 'expense',

										950 => 'job_application',
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-object_type' => TTi18n::gettext('Object'),
										'-1020-name' => TTi18n::gettext('Name'),
										'-1030-description' => TTi18n::gettext('Description'),

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
								'name',
								'description',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'object_type_id' => 'ObjectType',
										'object_type' => FALSE,
										'description' => 'Description',
										'name' => 'Name',
										'name_metaphone' => 'NameMetaphone',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($id),
															TTi18n::gettext('Company is invalid')
															) ) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return (int)$this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($type) {
		$type = trim($type);

		if ( $this->Validator->inArrayKey(	'object_type',
											$type,
											TTi18n::gettext('Object Type is invalid'),
											$this->getOptions('object_type')) ) {

			$this->data['object_type_id'] = $type;

			return FALSE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		Debug::Arr($this->getCompany(), 'Company: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $this->getCompany(),
					'object_type_id' => $this->getObjectType(),
					'name' => strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .'
					where company_id = ?
						AND object_type_id = ?
						AND lower(name) = ?
						AND deleted = 0';
		$name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($name_id, 'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);

		if	(	$this->Validator->isLength(		'name',
												$name,
												TTi18n::gettext('Tag is too short or too long'),
												2,
												100)
				AND
				$this->Validator->isRegEx(		'name',
												$name,
												TTi18n::gettext('Incorrect characters in tag'),
												$this->name_validator_regex)
				AND
				$this->Validator->isTrue(		'name',
												$this->isUniqueName($name),
												TTi18n::gettext('Tag already exists'))
												) {

			$this->data['name'] = $name;
			$this->setNameMetaphone( $name );

			return TRUE;
		}

		return FALSE;
	}
	function getNameMetaphone() {
		if ( isset($this->data['name_metaphone']) ) {
			return $this->data['name_metaphone'];
		}

		return FALSE;
	}
	function setNameMetaphone($value) {
		$value = metaphone( trim($value) );

		if	( $value != '' ) {
			$this->data['name_metaphone'] = $value;

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

		if (	$this->Validator->isLength(	'description',
											$description,
											TTi18n::gettext('Description is invalid'),
											0, 255) ) {

			$this->data['description'] = $description;

			return TRUE;
		}

		return FALSE;
	}

	function preSave() {
		return TRUE;
	}
	function postSave() {
		$this->removeCache( $this->getId() );

		//if ( $this->getDeleted() == TRUE ) {
			//Unassign all tagged objects.
		//}

		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
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

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'object_type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'name_metaphone':
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	//Each tag needs a + or -. + Adds new tags, - deletes tags. Tags without these are ignores.
	//Tags are separated by a comma.
	static function parseTags($tags) {
		if ( $tags != '' AND !is_array($tags) ) {
			$retarr = array(
							'add' => array(),
							'delete' => array(),
							'all' => array(),
							);
			$split_tags = explode(',', str_replace( array(' ', ';'), ',', $tags) ); //Support " " (space) and ";" and ", " as separators.
			if ( is_array($split_tags) AND count($split_tags) > 0 ) {
				foreach( $split_tags as $raw_tag ) {
					$raw_tag = trim( $raw_tag );
					$tag = trim( preg_replace( '/^[\+\-]/', '', $raw_tag ) );

					if ( $tag == '' ) {
						continue;
					}

					$retarr['all'][] = strtolower($tag);
					if ( substr($raw_tag, 0, 1) == '-' ) {
						$retarr['delete'][] = $tag;
					} else {
						$retarr['add'][] = $tag;
					}
				}
			}

			$retarr['all'] = array_unique( $retarr['all'] );
			$retarr['add'] = array_unique( $retarr['add'] );
			$retarr['delete'] = array_unique( $retarr['delete'] );

			//Debug::Arr($retarr, 'Parsed Tags: '. $tags, __FILE__, __LINE__, __METHOD__, 10);

			return $retarr;
		}

		return FALSE;
	}

	static function getOrCreateTags( $company_id, $object_type_id, $parsed_tags ) {
		if ( is_array($parsed_tags) ) {
			//Get the IDs for all tags
			$cgtlf = TTnew( 'CompanyGenericTagListFactory' );
			$cgtlf->getByCompanyIdAndObjectTypeAndTags($company_id, $object_type_id, $parsed_tags['all']);
			if ( $cgtlf->getRecordCount() > 0 ) {
				foreach( $cgtlf as $cgt_obj ) {
					$existing_tags[strtolower($cgt_obj->getName())] = $cgt_obj->getID();
				}
				//Debug::Arr($existing_tags, 'aExisting tags:', __FILE__, __LINE__, __METHOD__, 10);
				$tags_diff = array_diff( $parsed_tags['all'], array_keys($existing_tags) );
			} else {
				//Debug::Text('No Existing tags!', __FILE__, __LINE__, __METHOD__, 10);
				$tags_diff = array_values( $parsed_tags['add'] );
			}
			unset($cgtlf, $cgt_obj);
			//Debug::Arr($tags_diff, 'Tags Diff: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset($tags_diff) AND is_array($tags_diff) ) {
				//Add new tags.
				foreach( $tags_diff as $new_tag ) {
					$new_tag = trim($new_tag);
					$cgtf = TTnew('CompanyGenericTagFactory');
					$cgtf->setCompany( $company_id );
					$cgtf->setObjectType( $object_type_id );
					$cgtf->setName( $new_tag );
					if ( $cgtf->isValid() ) {
						$insert_id = $cgtf->Save();
						$existing_tags[strtolower($new_tag)] = $insert_id;
					}
				}
				unset($tags_diff, $new_tag, $cgtf, $insert_id);
			}

			//Debug::Arr($existing_tags, 'Existing Tags: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( isset($existing_tags) ) {
				return $existing_tags;
			}
		}

		return FALSE;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Tag') .': '. $this->getName(), NULL, $this->getTable(), $this );
	}

}
?>
