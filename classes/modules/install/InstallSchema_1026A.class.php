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
 * @package Modules\Install
 */
class InstallSchema_1026A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}


	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		//Copy iButton, Fingerprint, EmployeeNumber (barcode/proximity) fields to new UserIdentification table.

		//Find out if they have both TimeClocks and FingerPrint stations. If they do
		//we need to copy the fingerprint data to both types of UserIdentification rows.
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getAll();

		$clf->StartTransaction();
		foreach ( $clf as $c_obj ) {
			Debug::text('Company: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9);

			$max_templates = 4;

			$slf = TTnew( 'StationListFactory' );
			$slf->getByCompanyIdAndTypeId( $c_obj->getId(), array(30, 40, 50, 100, 110) );
			if ( $slf->getRecordCount() > 0 ) {
				$slf_tmp1 = $slf->getByCompanyIdAndTypeId( $c_obj->getId(), array(50) );
				$griaule_stations = $slf_tmp1->getRecordCount();
				Debug::text('  Found Griaule Stations: '. $griaule_stations, __FILE__, __LINE__, __METHOD__, 9);
				unset($slf_tmp1);

				$slf_tmp2 = $slf->getByCompanyIdAndTypeId( $c_obj->getId(), array(100, 110) );
				$zk_stations = $slf_tmp2->getRecordCount();
				Debug::text('  Found ZK Stations: '. $zk_stations, __FILE__, __LINE__, __METHOD__, 9);
				unset($slf_tmp2);

				$slf_tmp3 = $slf->getByCompanyIdAndTypeId( $c_obj->getId(), array(40) );
				$barcode_stations = $slf_tmp3->getRecordCount();
				Debug::text('  Found Barcode Stations: '. $barcode_stations, __FILE__, __LINE__, __METHOD__, 9);
				unset($slf_tmp3);

				//Loop through each user copying their data to the UserIdenfification Table.
				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByCompanyID( $c_obj->getId() );
				if ( $ulf->getRecordCount() > 0 ) {
					foreach( $ulf as $u_obj ) {
						Debug::text('  User: '. $u_obj->getUserName(), __FILE__, __LINE__, __METHOD__, 9);
						//if ( $u_obj->getIButtonID() != '' ) {
						if ( $u_obj->getColumn('ibutton_id') != '' ) {
							Debug::text('	 Converting iButton...', __FILE__, __LINE__, __METHOD__, 9);
							$uif = TTnew( 'UserIdentificationFactory' );
							$uif->setUser( $u_obj->getId() );
							$uif->setType( 10 ); //10=iButton
							$uif->setNumber( 0 );
							$uif->setValue( $u_obj->getColumn('ibutton_id') );
							if ( $uif->isValid() == TRUE ) {
								$uif->Save();
								//$u_obj->setIButtonID( '' );
							}
						}

						//if ( $u_obj->getRFID() != '' ) {
						if ( $u_obj->getColumn('rf_id') ) {
							Debug::text('	 Converting RFID...', __FILE__, __LINE__, __METHOD__, 9);
							$uif = TTnew( 'UserIdentificationFactory' );
							$uif->setUser( $u_obj->getId() );
							$uif->setType( 40 ); //40=Proximity
							$uif->setNumber( 0 );
							$uif->setValue( $u_obj->getColumn('rf_id') );
							if ( $uif->isValid() == TRUE ) {
								$uif->Save();
								//$u_obj->setRFID( '' );
							}
						}

						if ( $barcode_stations > 0 AND $u_obj->getEmployeeNumber() != '' ) {
							Debug::text('	 Converting EmployeeNumber...', __FILE__, __LINE__, __METHOD__, 9);
							$uif = TTnew( 'UserIdentificationFactory' );
							$uif->setUser( $u_obj->getId() );
							$uif->setType( 30 ); //30=Barcode
							$uif->setNumber( 0 );
							$uif->setValue( $u_obj->getEmployeeNumber() );
							if ( $uif->isValid() == TRUE ) {
								$uif->Save();
							}
						}

						if ( $griaule_stations > 0 ) {
							for ($t = 1; $t <= $max_templates; $t++ ) {
								//$set_fingerprint_function = 'setFingerPrint'. $t;
								$get_fingerprint_function = 'getFingerPrint'. $t;

								//Griaule fingerprint templates start with: "p/8B"
								//if ( $u_obj->$get_fingerprint_function() != '' AND substr($u_obj->$get_fingerprint_function(), 0, 4) == 'p/8B' ) {
								if ( $u_obj->getColumn( $get_fingerprint_function ) != '' AND substr( $u_obj->getColumn( $get_fingerprint_function ), 0, 4) == 'p/8B' ) {
									Debug::text('	 Converting Griaule FingerPrint: '. $t, __FILE__, __LINE__, __METHOD__, 9);

									$uif = TTnew( 'UserIdentificationFactory' );
									$uif->setUser( $u_obj->getId() );
									$uif->setType( 20 ); //20=Griaule, 100=ZK
									$uif->setNumber( ($t * 10) );
									$uif->setValue( $u_obj->getColumn( $get_fingerprint_function ) );
									if ( $uif->isValid() == TRUE ) {
										$uif->Save();
										//$u_obj->$set_fingerprint_function( '' );
									}
								}
							}
						}

						if ( $zk_stations > 0 ) {
							for ($t = 1; $t <= $max_templates; $t++ ) {
								//$set_fingerprint_function = 'setFingerPrint'. $t;
								$get_fingerprint_function = 'getFingerPrint'. $t;

								//ZK fingerprint templates start with: "oco"
								//if ( $u_obj->$get_fingerprint_function() != '' AND substr($u_obj->$get_fingerprint_function(), 0, 3) == 'oco' ) {
								if ( $u_obj->getColumn( $get_fingerprint_function ) != '' AND substr( $u_obj->getColumn( $get_fingerprint_function ), 0, 3) == 'oco' ) {
									Debug::text('	 Converting ZK FingerPrint: '. $t, __FILE__, __LINE__, __METHOD__, 9);
									$uif = TTnew( 'UserIdentificationFactory' );
									$uif->setUser( $u_obj->getId() );
									$uif->setType( 100 ); //20=Griaule, 100=ZK
									$uif->setNumber( $t );
									$uif->setValue( $u_obj->getColumn( $get_fingerprint_function ) );
									if ( $uif->isValid() == TRUE ) {
										$uif->Save();
										//$u_obj->$set_fingerprint_function( '' );
									}
								}
							}
						}

						if ( $u_obj->isValid() ) {
							$u_obj->Save();
						}
					}
				}
			}
		}
		$clf->CommitTransaction();


		return TRUE;

	}
}
?>
