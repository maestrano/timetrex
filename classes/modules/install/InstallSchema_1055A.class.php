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
class InstallSchema_1055A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}

	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		//Make sure Medicare Employer uses the same include/exclude accounts as Medicare Employee.
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach( $clf as $c_obj ) {
				Debug::text('Company: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9);
				if ( $c_obj->getStatus() != 30 AND $c_obj->getCountry() == 'US' ) {
					//Get PayStub Link accounts
					$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' );
					$pseallf->getByCompanyId( $c_obj->getID() );
					if	( $pseallf->getRecordCount() > 0 ) {
						$psea_obj = $pseallf->getCurrent();
					} else {
						Debug::text('Failed getting PayStubEntryLink for Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 10);
						continue;
					}

					$include_pay_stub_accounts = FALSE;
					$exclude_pay_stub_accounts = FALSE;

					$cdlf = TTnew( 'CompanyDeductionListFactory' );
					$cdlf->getByCompanyIdAndName($c_obj->getID(), 'Medicare - Employee' );
					if ( $cdlf->getRecordCount() == 1 ) {
						$cd_obj = $cdlf->getCurrent();
						Debug::text('Found Medicare Employee Tax / Deduction, ID: '. $c_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);
						$include_pay_stub_accounts = $cd_obj->getIncludePayStubEntryAccount();
						$exclude_pay_stub_accounts = $cd_obj->getExcludePayStubEntryAccount();
					} else {
						Debug::text('Failed to find Medicare Employee Tax / Deduction for Company: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9);
					}
					unset($cdlf, $cd_obj);

					//Debug::Arr($include_pay_stub_accounts, 'Include Pay Stub Accounts: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9);
					//Debug::Arr($exclude_pay_stub_accounts, 'Exclude Pay Stub Accounts: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9);

					$cdlf = TTnew( 'CompanyDeductionListFactory' );
					$cdlf->getByCompanyIdAndName($c_obj->getID(), 'Medicare - Employer' );
					if ( $cdlf->getRecordCount() == 1 ) {
						$cd_obj = $cdlf->getCurrent();
						Debug::text('Found Medicare Employer Tax / Deduction, ID: '. $c_obj->getID(), __FILE__, __LINE__, __METHOD__, 9);

						Debug::text('Medicare Employer Tax / Deduction Matches... Adjusting specific formula Percent...', __FILE__, __LINE__, __METHOD__, 9);
						if ( $include_pay_stub_accounts !== FALSE ) {
							Debug::text('Matching Include/Exclude accounts with Medicare Employee Entry...', __FILE__, __LINE__, __METHOD__, 9);
							//Match include accounts with employee entry.
							$cd_obj->setIncludePayStubEntryAccount( $include_pay_stub_accounts );
							$cd_obj->setExcludePayStubEntryAccount( $exclude_pay_stub_accounts );
						} else {
							Debug::text('NOT Matching Include/Exclude accounts with Medicare Employee Entry...', __FILE__, __LINE__, __METHOD__, 9);
							$cd_obj->setIncludePayStubEntryAccount( array( $psea_obj->getTotalGross() ));
						}

						$cd_obj->ignore_column_list = TRUE; //Prevents SQL errors due to new columns being added later on.
						if ( $cd_obj->isValid() ) {
							$cd_obj->Save();
						}
					} else {
						Debug::text('Failed to find Medicare Employer Tax / Deduction for Company: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9);
					}
				}
			}
		}

		return TRUE;
	}
}
?>
