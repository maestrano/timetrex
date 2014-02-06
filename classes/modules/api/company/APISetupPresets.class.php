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
 * $Revision: 2196 $
 * $Id: APICompanyGenericTag.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Company
 */
class APISetupPresets extends APIFactory {
	protected $main_class = 'SetupPresets';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	function createPresets( $data ) {
		if ( !$this->getPermissionObject()->Check('pay_period_schedule','enabled')
				OR !( $this->getPermissionObject()->Check('pay_period_schedule','edit') OR $this->getPermissionObject()->Check('pay_period_schedule','edit_own') OR $this->getPermissionObject()->Check('pay_period_schedule','edit_child') OR $this->getPermissionObject()->Check('pay_period_schedule','add') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($data)+1, NULL, TTi18n::getText('Creating policies...') );

			$this->getMainClassObject()->setCompany( $this->getCurrentCompanyObject()->getId() );
			$this->getMainClassObject()->setUser( $this->getCurrentUserObject()->getId() );

			$this->getMainClassObject()->createPresets();

			$already_processed_country = array();
			$i=1;
			foreach( $data as $location ) {
				if ( isset($location['country']) AND isset($location['province']) ) {
					if ( $location['province'] == '00' ) {
						$location['province'] = NULL;
					}

					if ( !in_array($location['country'], $already_processed_country)) {
						$this->getMainClassObject()->createPresets( $location['country'] );
					}

					$this->getMainClassObject()->createPresets( $location['country'], $location['province'] );
					Debug::text('Creating presets for Country: '. $location['country'] .' Province: '. $location['province'], __FILE__, __LINE__, __METHOD__,9);

					$already_processed_country[] = $location['country'];
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $i );
				$i++;
			}

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $i++, TTi18n::getText('Creating Permissions...') );
			$this->getMainClassObject()->Permissions();
			$this->getMainClassObject()->UserDefaults();

			//Assign the current user to the only existing pay period schedule.
			$ppslf = TTnew('PayPeriodScheduleListFactory');
			$ppslf->getByCompanyId( $this->getCurrentCompanyObject()->getId() );
			if ( $ppslf->getRecordCount() == 1 ) {
				$pps_obj = $ppslf->getCurrent();
				$pps_obj->setUser( $this->getCurrentUserObject()->getId() );

				Debug::text('Assigning current user to pay period schedule: '. $pps_obj->getID(), __FILE__, __LINE__, __METHOD__,9);
				if ( $pps_obj->isValid() ) {
					$pps_obj->Save();
				}
			}

			$this->getCurrentCompanyObject()->setSetupComplete( TRUE );
			if ( $this->getCurrentCompanyObject()->isValid() ) {
				$this->getCurrentCompanyObject()->Save();
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
		}

		return TRUE;
	}
}
?>
