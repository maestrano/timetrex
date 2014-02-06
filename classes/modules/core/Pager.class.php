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
 * $Revision: 4045 $
 * $Id: Pager.class.php 4045 2010-12-20 22:38:30Z ipso $
 * $Date: 2010-12-20 14:38:30 -0800 (Mon, 20 Dec 2010) $
 */

/**
 * @package Core
 */
class Pager {
	protected $rs = NULL;
	protected $count_rows = TRUE; //Specify if we count the total rows or not.

	function __construct($arr) {
		if ( isset($arr->rs) ) {
			//If there is no RS to return, something is seriously wrong. Check interface.inc.php?
			//Make sure the ListFactory function is doing a pageselect
			$this->rs = $arr->rs;

			$this->count_rows = $arr->db->pageExecuteCountRows;

			return TRUE;
		}

		return FALSE;
	}

	function getPreviousPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->absolutepage() - 1;
		}

		return FALSE;
	}

	function getCurrentPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->absolutepage();
		}

		return FALSE;
	}

	function getNextPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->absolutepage() + 1;
		}

		return FALSE;
	}

	function isFirstPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->atfirstpage();
		}

		return TRUE;
	}

	function isLastPage() {
		//If the first page is also the last, return true.
		if ( $this->isFirstPage() AND $this->LastPageNumber() == 1) {
			return TRUE;
		}

		if ( is_object($this->rs) ) {
			return $this->rs->atlastpage();
		}

		return TRUE;
	}

	function LastPageNumber() {
		if ( is_object($this->rs) ) {
			if ( $this->count_rows === FALSE ) {
				if ( $this->getCurrentPage() < 0 ) {
					//Only one page in result set.
					return $this->rs->lastpageno();
				} else {
					//More than one page in result set.
					if ( $this->rs->atlastpage() == TRUE ) {
						return $this->getCurrentPage();
					} else {
						//Since we don't know what the actual last page is, just add 100 pages to the current one.
						//The user may need to click this several times if there are more than 100 pages.
						return $this->getCurrentPage()+99;
					}
				}
			} else {
				return $this->rs->lastpageno();
			}
		}

		return FALSE;
	}

	//Return maximum rows per page
	function getRowsPerPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->recordcount();
		}

		return FALSE;
	}

	function getTotalRows() {
		if ( is_object($this->rs) ) {
			return $this->rs->maxrecordcount();
		}

		return FALSE;
	}

	function getPageVariables() {
		//Make sure the ListFactory function is doing a pageselect
		$paging_data = array(
							'previous_page' 	=> $this->getPreviousPage(),
							'current_page' 		=> $this->getCurrentPage(),
							'next_page'			=> $this->getNextPage(),
							'is_first_page'		=> $this->isFirstPage(),
							'is_last_page'		=> $this->isLastPage(),
							'last_page_number'	=> $this->LastPageNumber(),
							'rows_per_page' 	=> $this->getRowsPerPage(),
							'total_rows' 		=> $this->getTotalRows(),
							);
		//var_dump($paging_data);
		return $paging_data;
	}
}
?>
