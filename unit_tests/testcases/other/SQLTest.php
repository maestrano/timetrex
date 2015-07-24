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

require_once('PHPUnit/Framework/TestCase.php');

class SQLTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
		global $dd;
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__,10);

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__,10);
		$this->assertGreaterThan( 0, $this->company_id );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$this->branch_id = $dd->createBranch( $this->company_id, 10 ); //NY

		//$dd->createPayStubAccount( $this->company_id );
		//$dd->createPayStubAccountLink( $this->company_id );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, 100 );
		$this->assertGreaterThan( 0, $this->user_id );

        return TRUE;
    }

    public function tearDown() {
        Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__,10);
        return TRUE;
    }

	function getListFactoryClassList( $equal_parts = 1 ) {
		global $global_class_map;

		$retarr = array();

		//Get all ListFactory classes
		foreach( $global_class_map as $class_name => $class_file_name ) {
			if ( strpos( $class_name, 'ListFactory' ) !== FALSE ) {
				$retarr[] = $class_name;
			}
		}

		$chunk_size = ceil( ( count($retarr) / $equal_parts ) );
		return array_chunk( $retarr, $chunk_size );
	}
	
	function runSQLTestOnListFactory( $factory_name ) {
		if ( class_exists( $factory_name ) ) {
			$reflectionClass = new ReflectionClass( $factory_name );
			$class_file_name = $reflectionClass->getFileName();

			Debug::text('Checking Class: '. $factory_name .' File: '. $class_file_name, __FILE__, __LINE__, __METHOD__,10);

			$filter_data_types = array(
										'not_set', //passes
										'true', //passes
										'false', //passes
										'null', //passes
										'negative_small_int', //passes
										'small_int', //passes
										'large_int',
										'string', //passes
										'array', //passes
									);

			//Parse filter array keys from class file so we can populate them with dummy data.
			preg_match_all( '/\$filter_data\[\'([a-z0-9_]*)\'\]/i', file_get_contents($class_file_name), $filter_data_match);
			if ( isset($filter_data_match[1]) ) {
				//Debug::Arr($filter_data_match, 'Filter Data Match: ', __FILE__, __LINE__, __METHOD__,10);
				foreach( $filter_data_types as $filter_data_type ) {
					Debug::Text('Filter Data Type: '. $filter_data_type, __FILE__, __LINE__, __METHOD__,10);

					$filter_data = array();

					$filter_data_match[1] = array_unique( $filter_data_match[1] );
					foreach( $filter_data_match[1] as $filter_data_key ) {
						//Skip sort_column/sort_order
						if ( in_array( $filter_data_key, array('sort_column', 'sort_order' ) ) ) {
							continue;
						}

						//Test with:
						// Small Integers
						// Large Integers (64bit)
						// Strings
						// Arrays
						switch ( $filter_data_type ) {
							case 'true':
								$filter_data[$filter_data_key] = TRUE;
								break;
							case 'false':
								$filter_data[$filter_data_key] = FALSE;
								break;
							case 'null':
								$filter_data[$filter_data_key] = NULL;
								break;
							case 'negative_small_int':
								$filter_data[$filter_data_key] = ( rand(0, 128) * -1 );
								break;
							case 'small_int':
								$filter_data[$filter_data_key] = rand(0, 128);
								break;
							case 'large_int':
								$filter_data[$filter_data_key] = rand(2147483648, 21474836489);
								break;
							case 'string':
								$filter_data[$filter_data_key] = 'A'.substr( md5(microtime()), rand(0,26), 10 );
								break;
							case 'array':
								$filter_data[$filter_data_key] = array( rand(0, 128), rand(2147483648, 21474836489), 'A'.substr( md5(microtime()), rand(0,26), 10 ) );
								break;
							case 'not_set':
								break;
						}
					}
					//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__,10);

					$lf = TTNew( $factory_name );
					switch( $factory_name ) {
						case 'RecurringScheduleControlListFactory':
							$retarr = $lf->getAPIExpandedSearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
							$this->assertNotEquals( $retarr, FALSE );
							$this->assertTrue( is_object($retarr), TRUE );

							$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
							$this->assertNotEquals( $retarr, FALSE );
							$this->assertTrue( is_object($retarr), TRUE );
							break;
						case 'ScheduleListFactory':
							$retarr = $lf->getSearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
							$this->assertNotEquals( $retarr, FALSE );
							$this->assertTrue( is_object($retarr), TRUE );

							$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
							$this->assertNotEquals( $retarr, FALSE );
							$this->assertTrue( is_object($retarr), TRUE );
							break;
						case 'MessageControlListFactory':
							$filter_data['current_user_id'] = $this->user_id;
						default:
							if ( method_exists( $lf, 'getAPISearchByCompanyIdAndArrayCriteria' ) ) {
								//Make sure we test pagination, especially with MySQL due to its limitation with subqueries and need for _ADODB_COUNT workarounds, $limit = NULL, $page = NULL, $where = NULL, $order = NULL
								$retarr = $lf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data, 1, 1, NULL, NULL );
								$this->assertNotEquals( $retarr, FALSE );
								$this->assertTrue( is_object($retarr), TRUE );
							}
							break;
					}
				}
			}
			unset($filter_data_match);

			Debug::text('Done...', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		} else {
			Debug::text('Class does not exist: '. $factory_name, __FILE__, __LINE__, __METHOD__,10);
		}

		return FALSE;
	}

	function runSQLTestOnListFactoryMethods( $factory_name ) {
		if ( in_array( $factory_name, array(
											'HierarchyListFactory',
											'PolicyGroupAccrualPolicyListFactory',
											'PolicyGroupOverTimePolicyListFactory',
											'PolicyGroupPremiumPolicyListFactory',
											'PolicyGroupRoundIntervalPolicyListFactory',
											'ProductTaxPolicyProductListFactory',
											)
				)
			) {
			return TRUE; //Deprecated classes.
		}
		
		if ( class_exists( $factory_name ) ) {
			$reflectionClass = new ReflectionClass( $factory_name );
			Debug::text('Checking Class: '. $factory_name, __FILE__, __LINE__, __METHOD__,10);

			$raw_methods = $reflectionClass->getMethods( ReflectionMethod::IS_PUBLIC );
			if ( is_array($raw_methods) ) {
				global $db;
				foreach( $raw_methods as $raw_method ) {
					if ( $factory_name == $raw_method->class
							AND (
									strpos( $raw_method->name, 'getAll') !== FALSE
									OR strpos( $raw_method->name, 'getBy') !== FALSE
									OR strpos( $raw_method->name, 'Report') !== FALSE
								)
							AND (
									strncmp($db->databaseType, 'mysql', 5) != 0
									OR
									//Exclude function calls that are known to not work in MySQL.
									( strncmp($db->databaseType, 'mysql', 5) == 0 AND !in_array( $raw_method->name, array( 'getByPhonePunchDataByCompanyIdAndStartDateAndEndDate') ) )
								)
							AND (
									//Skip getByCompanyIdArray() functions, but include getBy*AndArrayCriteria(). So just check if its ends with Array or not.
									( substr( $raw_method->name, -5 ) !== 'Array' )
								)
						) {
						Debug::text('Class: '. $factory_name .' Method: '. $raw_method->name, __FILE__, __LINE__, __METHOD__, 10);

						//Get method arguments.
						$method_parameters = $raw_method->getParameters();
						if ( is_array( $method_parameters ) ) {
							$input_arguments = array();
							foreach( $method_parameters as $method_parameter ) {
								//if ( !in_array( $method_parameter->name, array( 'where', 'order', 'page', 'limit' ) ) ) {
									Debug::text('  Parameter: '. $method_parameter->name, __FILE__, __LINE__, __METHOD__, 10);
									switch( $factory_name ) {
										case 'ClientContactListFactory':
											switch( $method_parameter->name ) {
												case 'key':
													$input_argument = '900d6136975e3a728051a62ed1191910';
													break;
											}
											break;
										case 'ClientContactListFactory':
											switch( $method_parameter->name ) {
												case 'name':
													$input_argument = 'test';
													break;
											}
											break;
										case 'RoundIntervalPolicyListFactory':
											switch( $method_parameter->name ) {
												case 'type_id':
													$input_argument = 40;
													break;
											}
											break;
										case 'ScheduleListFactory':
											switch( $method_parameter->name ) {
												case 'direction':
													$input_argument = 'before';
													break;
											}
											break;
										case 'UserListFactory':
										case 'UserContactListFactory':
											switch( $method_parameter->name ) {
												case 'email':
													$input_argument = 'hi@hi.com';
													break;
												case 'key':
													$input_argument = '900d6136975e3a728051a62ed1191910';
													break;
											}
											break;
										case 'PayPeriodTimeSheetVerifyListFactory':
										case 'RequestListFactory':
											switch( $method_parameter->name ) {
												case 'hierarchy_level_map':
													$input_argument = array(
																				array(
																					  'hierarchy_control_id' => 1,
																					  'level' => 1,
																					  'last_level' => 2,
																					  'object_type_id' => 10,
																					  )
																			);
													break;

											}
											break;
										case 'ExceptionListFactory':
											switch( $method_parameter->name ) {
												case 'time_period':
													$input_argument = 'week';
													break;

											}
											break;
									}

									//If LIMIT argument is available always set it to 1 to reduce memory usage.
									if ( in_array( $method_parameter->name, array( 'where', 'order', 'page' ) ) ) {
										$input_argument = NULL;
									} elseif ( !isset($input_argument) AND ( $method_parameter->name == 'id' OR strpos( $method_parameter->name, '_id' ) !== FALSE OR $method_parameter->name == 'limit' ) ) { //Use integer as its a ID argument.
										$input_argument = 1;
									} elseif ( !isset($input_argument) ) {
										$input_argument = 2;
									}

									$input_arguments[] = $input_argument;
								//}
								unset($input_argument);
							}

							Debug::Arr($input_arguments, '    Calling Class: '. $factory_name .' Method: '. $raw_method->name, __FILE__, __LINE__, __METHOD__, 10);
							$lf = TTNew( $factory_name );
							switch( $factory_name.'::'.$raw_method->name ) {
								case 'StationListFactory::getByUserIdAndStatusAndType':
								case 'PayStubEntryAccountListFactory::getByTypeArrayByCompanyIdAndStatusId':
									//Skip due to failures.
									break;
								case 'MessageControlListFactory::getByCompanyIdAndObjectTypeAndObjectAndNotUser':
									$retarr = call_user_func_array( array( $lf, $raw_method->name ), $input_arguments );
									$this->assertEquals( $retarr, FALSE ); //This will be FALSE, but it still executes a query.
									//$this->assertTrue( is_object($retarr), TRUE );
									break;
								default:
									$retarr = call_user_func_array( array( $lf, $raw_method->name ), $input_arguments );
									//Debug::Arr($retarr, '    RetArr: ', __FILE__, __LINE__, __METHOD__, 10);
									$this->assertNotEquals( $retarr, FALSE );
									$this->assertTrue( is_object($retarr), TRUE );
									break;
							}

						}
					} else {
						Debug::text('Skipping... Class: '. $factory_name .' Method: '. $raw_method->name, __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}

			Debug::text('Done...', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		} else {
			Debug::text('Class does not exist: '. $factory_name, __FILE__, __LINE__, __METHOD__,10);
		}

		return FALSE;
	}

	function runSQLTestOnEdition( $product_edition = TT_PRODUCT_ENTERPRISE, $class_list ) {
		global $TT_PRODUCT_EDITION;

		$original_product_edition = getTTProductEdition();

		$this->assertTrue( TRUE );
		if ( $product_edition <= $original_product_edition ) {
			$TT_PRODUCT_EDITION = $product_edition;
			Debug::text('Checking against Edition: '. getTTProductEditionName(), __FILE__, __LINE__, __METHOD__,10);

			//Loop through all ListFactory classes testing SQL queries.
			foreach( $class_list as $class_name ) {
				$this->runSQLTestOnListFactoryMethods( $class_name );
				$this->runSQLTestOnListFactory( $class_name );
			}
		}

		return TRUE;
	}

	/**
	 * @group SQL_CommunityA
	 */
	function testSQLCommunityA() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_COMMUNITY, $classes[0] );
	}
	/**
	 * @group SQL_CommunityB
	 */
	function testSQLCommunityB() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_COMMUNITY, $classes[1] );
	}
	/**
	 * @group SQL_CommunityC
	 */
	function testSQLCommunityC() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_COMMUNITY, $classes[2] );
	}
	/**
	 * @group SQL_CommunityD
	 */
	function testSQLCommunityD() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_COMMUNITY, $classes[3] );
	}



	/**
	 * @group SQL_ProfessionalA
	 */
	function testSQLProfessionalA() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_PROFESSIONAL, $classes[0] );
	}
	/**
	 * @group SQL_ProfessionalB
	 */
	function testSQLProfessionalB() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_PROFESSIONAL, $classes[1] );
	}
	/**
	 * @group SQL_ProfessionalC
	 */
	function testSQLProfessionalC() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_PROFESSIONAL, $classes[2] );
	}
	/**
	 * @group SQL_ProfessionalD
	 */
	function testSQLProfessionalD() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_PROFESSIONAL, $classes[3] );
	}




	/**
	 * @group SQL_CorporateA
	 */
	function testSQLCorporateA() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_CORPORATE, $classes[0] );
	}
	/**
	 * @group SQL_CorporateB
	 */
	function testSQLCorporateB() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_CORPORATE, $classes[1] );
	}
	/**
	 * @group SQL_CorporateC
	 */
	function testSQLCorporateC() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_CORPORATE, $classes[2] );
	}
	/**
	 * @group SQL_CorporateD
	 */
	function testSQLCorporateD() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_CORPORATE, $classes[3] );
	}




	/**
	 * @group SQL_EnterpriseA
	 */
	function testSQLEnterpriseA() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_ENTERPRISE, $classes[0] );
	}
	/**
	 * @group SQL_EnterpriseB
	 */
	function testSQLEnterpriseB() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_ENTERPRISE, $classes[1] );
	}
	/**
	 * @group SQL_EnterpriseC
	 */
	function testSQLEnterpriseC() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_ENTERPRISE, $classes[2] );
	}
	/**
	 * @group SQL_EnterpriseD
	 */
	function testSQLEnterpriseD() {
		$classes = $this->getListFactoryClassList( 4 );
		$this->runSQLTestOnEdition( TT_PRODUCT_ENTERPRISE, $classes[3] );
	}

}