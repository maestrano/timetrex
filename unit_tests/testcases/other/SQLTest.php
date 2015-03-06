<?php
require_once('PHPUnit/Framework/TestCase.php');

/**
 * @group SQL
 */
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

	function testSQL() {
		global $TT_PRODUCT_EDITION, $global_class_map;

		$original_product_edition = getTTProductEdition();

		//Check all SQL queries in each product edition.
		$product_editions = array( TT_PRODUCT_COMMUNITY, TT_PRODUCT_PROFESSIONAL, TT_PRODUCT_CORPORATE, TT_PRODUCT_ENTERPRISE );

		foreach( $product_editions as $product_edition ) {
			$this->assertTrue( TRUE );
			if ( $product_edition <= $original_product_edition ) {
				$TT_PRODUCT_EDITION = $product_edition;
				Debug::text('Checking against Edition: '. getTTProductEditionName(), __FILE__, __LINE__, __METHOD__,10);

				//Loop through all ListFactory classes testing SQL queries.
				foreach( $global_class_map as $class_name => $class_file_name ) {
					if ( strpos( $class_name, 'ListFactory' ) !== FALSE ) {
						$this->runSQLTestOnListFactoryMethods( $class_name );
						$this->runSQLTestOnListFactory( $class_name );
					}
				}
			}
		}

		return TRUE;
	}

/*
	//Old function to test all ListFactory methods.
	function runSQLTestOnListFactory( $factory_name ) {
		if ( class_exists( $factory_name ) ) {
			global $db;
			$current_tables = $db->MetaTables();

			$reflectionClass = new ReflectionClass( $factory_name );
			$class_file_name = $reflectionClass->getFileName();

			Debug::text('Checking Class: '. $factory_name .' File: '. $class_file_name, __FILE__, __LINE__, __METHOD__, 10);
			foreach( $reflectionClass->getMethods() as $method ) {
				$params = array();

				$method_name = $method->name;
				if ( strpos( $method->class, 'ListFactory' ) !== FALSE AND strpos( $method_name, 'API' ) === FALSE AND strpos( $method_name, 'Array' ) === FALSE ) {
					Debug::text('  Checking Method: '. $method_name .' Class: '. $method->class, __FILE__, __LINE__, __METHOD__, 10);

					$reflectionMethod = new ReflectionMethod( $method->class, $method_name );
					$method_param_names = $reflectionMethod->getParameters();
					//Debug::Arr( $method_param_names, '    Method Parameters: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( is_array($method_param_names) ) {
						foreach( $method_param_names as $method_param_obj ) {
							$method_param_name = $method_param_obj->name;
							switch ( $method_param_name ) {
								case 'limit':
									$params[] = 1;
									break;
								case 'page':
									$params[] = 1;
									break;
								case 'order':
									$params[] = NULL;
									break;
								case 'where':
									$params[] = NULL;
									break;
								default:
									if ( strpos( $method_param_name, '_id' ) !== FALSE ) {
										//ID column, send integer
										$params[] = rand(1, 128);
									} else {
										$params[] = '1';
									}
									break;
							}
						}

						//Debug::Arr( $params, '    Method Parameter Values: ', __FILE__, __LINE__, __METHOD__, 10);

						$lf = TTNew( $factory_name );
						if ( in_array( $lf->getTable(), $current_tables ) ) {
							switch ( $method_name ) {
								//Skip these methods
								case 'getByPasswordResetKey': //ClientContactListFactory
								case 'getStringByCompanyIDAndObjectTypeIDAndObjectID': //CompanyGenericTagMapListFactory
								case 'getReportByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate': //ExceptionListFactory
								case 'getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate': //UserDateListFactory
								case 'getReportHoursByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate': //UserDateTotalListFactory
								case 'getByHierarchyLevelMapAndStatusAndNotAuthorized': //PayPeriodTimeSheetVerifyListFactory
								case 'getByHierarchyLevelMapAndTypeAndStatusAndNotAuthorized': //RequestListFactory
									break;
								default:
									//Some SQL queries are for PostgreSQL only, so skip them when using MySQL.
									if ( strncmp($db->databaseType, 'mysql', 5) == 0
											AND (
													in_array( $method_name, array( 'getByPhonePunchDataByCompanyIdAndStartDateAndEndDate', 'getByCompanyIDAndUserIdAndObjectTypeAndObject', 'getByCompanyIDAndObjectTypeAndObject' ) )
													OR
													( $factory_name == 'DocumentListFactory' AND in_array( $method_name, array( 'getByCompanyId', 'getByCompanyIdAndPrivate', 'getByCompanyIdAndObjectTypeAndObject', 'getByCompanyIdAndObjectTypeAndObjectAndPrivate' ) ) )
												)
										) {
										continue;
									}
									$retarr = call_user_func_array( array( $lf, $method_name ), $params );

									//Debug::Arr( $retarr, '    RetVal: ', __FILE__, __LINE__, __METHOD__, 10);
									if ( is_object($retarr) ) {
										$this->assertNotEquals( $retarr, FALSE );
										$this->assertTrue( is_object($retarr), TRUE );
									} elseif ( is_array($retarr) ) {
										$this->assertTrue( !is_object($retarr), TRUE );
										$this->assertTrue( is_array($retarr) );
									} elseif ( $retarr === TRUE ) {
										$this->assertTrue( $retarr );
									} elseif ( $retarr === FALSE ) {
										$this->assertFalse( $retarr );
									} elseif ( is_numeric( $retarr ) ) {
										$this->assertGreaterThanOrEqual( 0, (float)$retarr );
									} elseif ( $retarr === NULL ) {
										$this->assertNull( $retarr );
									} else {
										$this->assertNotEquals( $retarr, FALSE );
									}
									break;
							}
						} else {
							Debug::Text('    ERROR: Table does not exist in database: '. $lf->getTable(), __FILE__, __LINE__, __METHOD__, 10);
						}
						unset($lf, $params );
					}
				}
			}

			unset($current_tables, $reflectionClass, $reflectionMethod);
		}

		return TRUE;
	}
*/

}