<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2013 TimeTrex Software Inc.
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
/*
 * $Revision: 8371 $
 * $Id: InstallSchema_1011A.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1011A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion() , __FILE__, __LINE__, __METHOD__,9);

		return TRUE;
	}

	function postInstall() {
		global $cache;

		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__,9);

		//Configure currencies for Standard Edition.
		if ( $this->getIsUpgrade() == TRUE ) {

			$clf = TTnew( 'CompanyListFactory' );
			$clf->StartTransaction();
			$clf->getAll();
			if ( $clf->getRecordCount() > 0 ) {
				foreach( $clf as $c_obj ) {
					if ( $c_obj->getStatus() == 10 ) {
						//Converting to new Accrual Policy table.
						Debug::text('Converting to new Accrual Policy Table: '. $c_obj->getName() .' ID: '. $c_obj->getId() , __FILE__, __LINE__, __METHOD__,9);
						$pglf = TTnew( 'PolicyGroupListFactory' );
						$pglf->getByCompanyId( $c_obj->getId() );
						if ( $pglf->getRecordCount() > 0 ) {
							foreach( $pglf as $pg_obj ) {
								Debug::text('Accrual Policy ID: '. $pg_obj->getColumn('accrual_policy_id'), __FILE__, __LINE__, __METHOD__,9);
								if ( $pg_obj->getColumn('accrual_policy_id') != ''
										AND $pg_obj->getColumn('accrual_policy_id') != 0 ) {
									$pg_obj->setAccrualPolicy( array($pg_obj->getColumn('accrual_policy_id') ) );
									if ( $pg_obj->isValid() ) {
										$pg_obj->Save();
									}
								}
							}
						}

						Debug::text('Adding Currency Information to Company: '. $c_obj->getName() .' ID: '. $c_obj->getId() , __FILE__, __LINE__, __METHOD__,9);
						$crlf = TTnew( 'CurrencyListFactory' );
						$crlf->getByCompanyId( $c_obj->getId() );
						if ( $crlf->getRecordCount() == 0 ) {
							$cf = TTnew( 'CurrencyFactory' );
							$country_to_currency_map_arr = $cf->getOptions('country_currency');

							if ( isset($country_to_currency_map_arr[$c_obj->getCountry()]) ) {
								$base_currency = $country_to_currency_map_arr[$c_obj->getCountry()];
								Debug::text('Found Base Currency For Country: '. $c_obj->getCountry() .' Currency: '. $base_currency , __FILE__, __LINE__, __METHOD__,9);
							} else {
								Debug::text('DID NOT Find Base Currency For Country: '. $c_obj->getCountry() .' Using default USD.', __FILE__, __LINE__, __METHOD__,9);
								$base_currency = 'USD';
							}

							$cf->setCompany( $c_obj->getId() );
							$cf->setStatus( 10 );
							$cf->setName( $base_currency );
							$cf->setISOCode( $base_currency );

							$cf->setConversionRate( '1.000000000' );
							$cf->setAutoUpdate( FALSE );
							$cf->setBase( TRUE );
							$cf->setDefault( TRUE );

							if ( $cf->isValid() ) {
								$base_currency_id = $cf->Save();

								Debug::text('Base Currency ID: '. $base_currency_id, __FILE__, __LINE__, __METHOD__,10);

								//Set Employee Hire Defaults.
								$udlf = TTnew( 'UserDefaultListFactory' );
								$udlf->getByCompanyId($c_obj->getId() );
								if ( $udlf->getRecordCount() > 0 ) {
									$ud_obj = $udlf->getCurrent();
									$ud_obj->setCurrency( $base_currency_id );
									$ud_obj->setLanguage( 'en' );
									if ( $ud_obj->isValid() ) {
										$ud_obj->Save();
									}
								}
								unset($udlf, $ud_obj);

								if ( is_numeric( $base_currency_id ) ) {
									$ulf = TTnew( 'UserListFactory' );
									$ulf->getByCompanyId( $c_obj->getId() );
									if ( $ulf->getRecordCount() > 0 ) {
										foreach( $ulf as $u_obj ) {
											$user_id = $u_obj->getID();

											Debug::text('Setting Base Currency For User: '. $u_obj->getUserName() .' ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

											$u_obj->setCurrency( $base_currency_id );

											if ( $u_obj->isValid() ) {
												if ( $u_obj->Save() == TRUE ) {
													//Set User Default Language
													$uplf = TTnew( 'UserPreferenceListFactory' );
													$uplf->getByUserIDAndCompanyID( $user_id, $c_obj->getId() );
													if ( $uplf->getRecordCount() > 0 ) {
														$up_obj = $uplf->getCurrent();
														$up_obj->setLanguage('en'); //Englist
														if ( $up_obj->isValid() ) {
																$up_obj->Save();
														}
													}
													unset($uplf, $up_obj);

													Debug::text('  Setting Base Currency for Pay Stubs, User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

													//Change all pay stubs for this user to the base currency.
													//Do this in a single query for speed purposes.
													$ph = array(
																'currency_id' => $base_currency_id,
																'currency_rate' => '1.000000000',
																'user_id' => $user_id,
																);
													$query = 'update pay_stub set currency_id = ?, currency_rate = ? where user_id = ?';
													$u_obj->db->Execute($query, $ph);

													/*
													$pslf = TTnew( 'PayStubListFactory' );
													$pslf->getByUserIdAndCompanyId( $user_id, $c_obj->getId() );
													if ( $pslf->getRecordCount() > 0 ) {
														foreach( $pslf as $ps_obj ) {
															//Debug::text('    Setting Base Currency for Pay Stub ID: '. $ps_obj->getId(), __FILE__, __LINE__, __METHOD__,10);

															$ps_obj->setCurrency( $base_currency_id );
															if ( $ps_obj->isValid() ) {

																$ps_obj->setEnableLinkedAccruals( FALSE );
																$ps_obj->setEnableCalcYTD( FALSE );
																$ps_obj->setEnableProcessEntries( FALSE );

																$ps_obj->Save();
															}

															unset($ps_obj);
														}
													}
													unset($pslf);
													*/
												} else {
													Debug::text('Failed saving user ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
												}
											} else {
												Debug::text('Failed saving user ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);
											}
											unset($u_obj, $user_id);
										}
									}
									unset($ulf);
								}

							}
							unset($cf);
						}
					} else {
						Debug::text('Company is not active! '. $c_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
					}
					unset($c_obj, $base_currency, $base_currency_id, $crlf);
				}
			}

			//$clf->FailTransaction();
			$clf->CommitTransaction();
		}

		//Add currency updating to cron.
		$maint_base_path = Environment::getBasePath() . DIRECTORY_SEPARATOR .'maint'. DIRECTORY_SEPARATOR;
		if ( PHP_OS == 'WINNT' ) {
			$cron_job_base_command =  'php-win.exe '. $maint_base_path;
		} else {
			$cron_job_base_command =  'php '. $maint_base_path;
		}
		Debug::text('Cron Job Base Command: '. $cron_job_base_command, __FILE__, __LINE__, __METHOD__,9);

		$cjf = TTnew( 'CronJobFactory' );
		$cjf->setName('UpdateCurrencyRates');
		$cjf->setMinute(45);
		$cjf->setHour(1);
		$cjf->setDayOfMonth('*');
		$cjf->setMonth('*');
		$cjf->setDayOfWeek('*');
		$cjf->setCommand($cron_job_base_command.'UpdateCurrencyRates.php');
		$cjf->Save();

		return TRUE;

	}
}
?>
