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

/**
 * @group UserReview
 */
class UserReviewTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
    protected $currency_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;
	protected $branch_ids = NULL;
	protected $department_ids = NULL;
    protected $expense_policy_ids = NULL;
    //protected $user_expense_id = NULL;

    public function setUp() {
		global $dd;
        Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__,10);

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__,10);

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, 100 );

        $this->branch_ids[] = $dd->createBranch( $this->company_id, 10 );
		$this->branch_ids[] = $dd->createBranch( $this->company_id, 20 );

		$this->department_ids[] = $dd->createDepartment( $this->company_id, 10 );
		$this->department_ids[] = $dd->createDepartment( $this->company_id, 20 );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

        return TRUE;
    }

    public function tearDown() {
        Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__,10);

        return TRUE;
    }
    

    function createKPIGroup( $company_id , $type, $parent_id = 0 ) {
        $kgf = TTnew( 'KPIGroupFactory' );
        $kgf->setCompany( $company_id );
        switch( $type ) {
            case 10:
                $kgf->setParent( $parent_id );
                $kgf->setName( 'Carpenter' );
                break;
            case 20:
                $kgf->setParent( $parent_id );
                $kgf->setName( 'Painter' );
                break;
            case 30:
                $kgf->setParent( $parent_id );
                $kgf->setName(  'General Laborer' );
                break;
            case 40:
                $kgf->setParent( $parent_id );
                $kgf->setName( 'Plumber' );
                break;
            case 50:
                $kgf->setParent( $parent_id );
                $kgf->setName( 'Electrician' );
                break;

        }
        if ( $kgf->isValid() ) {
			$insert_id = $kgf->Save();
			return $insert_id;
		}

		return FALSE;
    }
    
    function createKPI( $company_id , $type, $rate_type, $kpi_group_id = NULL, $minimum_rate = NULL, $maximum_rate = NULL ) {
        $kf = TTnew( 'KPIFactory' );
        $kf->setCompany( $company_id );
        switch($type) {
            case 10:
                $kf->setName('Works well with others?');
                $kf->setStatus(10);
                $kf->setType(10); //Scale
                $kf->setDescription('');
                break;
            case 20:
                $kf->setName('Ability to manage time efficiently?');
                $kf->setStatus(10);
                $kf->setType(10); //Scale
                $kf->setDescription('');
                break;
            case 30:
                $kf->setName('Finishes projects on time?');
                $kf->setStatus(10);
                $kf->setType(10); //Scale
                $kf->setDescription('');
                break;
            case 40:
                $kf->setName('How satisified are you with your current position?');
                $kf->setStatus(15);
                $kf->setType(10); //Scale
                $kf->setDescription('');
                break;
            case 50:
                $kf->setName('Positive Attitude?');
                $kf->setStatus(15);
                $kf->setType(10); //Scale
                $kf->setDescription('');
                break;
            case 60:
                $kf->setName('What can I do to make you more successful?');
                $kf->setStatus(10);
                $kf->setType(20); // Yes/No
                $kf->setDescription('');
                break;
            case 70:
                $kf->setName('How can you work better with your supervisor?');
                $kf->setStatus(10);
                $kf->setType(30); //Text
                $kf->setDescription('In the past 12 months tell me what you have learnt about the role you play in a group and how your supervisor can best work with you in that role.');
                break;
        }

        if( $rate_type != 60 AND $rate_type != 70 ) {
            $kf->setMinimumRate( $minimum_rate );
            $kf->setMaximumRate( $maximum_rate );
        }

        if ( $kf->isValid() ) {
            $insert_id = $kf->Save(FALSE);

            if ( isset( $kpi_group_id ) ) {
                $kf->setGroup( $kpi_group_id );
            } else {
                $kf->setGroup( array() );
            }

            if ( $kf->isValid() ) {
				$kf->Save();

				Debug::Text('Creating KPI ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

				return $insert_id;
			}
        }

        Debug::Text('Failed Creating KPI!', __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
    }

    function createUserReviewControl( $user_id , $reviewer_user_id) {
        $urcf = TTnew('UserReviewControlFactory');
        $urcf->setUser( $user_id );
        $urcf->setReviewerUser( $reviewer_user_id );
        $urcf->setStartDate( time()-(86400*rand(21,30)) );
        $urcf->setEndDate( time()-(86400*rand(11,20))  );
        $urcf->setDueDate( time()-(86400*rand(1,10)) );
        $urcf->setType( rand(2,9) * 5 );
        $urcf->setSeverity( rand(1,5) * 10 );
        $urcf->setTerm( rand(1,3) * 10 );
        $urcf->setStatus( rand(1,3) * 10 );
        if ( $urcf->isValid() ) {
			$insert_id = $urcf->Save();
			Debug::Text('User Review Control ID: '. $insert_id, __FILE__, __LINE__, __METHOD__,10);

			return $insert_id;
		}

		Debug::Text('Failed Creating User Review Control!', __FILE__, __LINE__, __METHOD__,10);

		return FALSE;
    }

    function getKPIArrayByGroupId( $id ) {

        $klf = TTnew( 'KPIListFactory' );
        $klf->getByCompanyIDAndGroupID( $this->company_id, $id );
        if ( $klf->getRecordCount() > 0 ) {
            foreach( $klf as $kpi_obj ) {
                $kpi_arr[] = array(
                    'group_id' => $kpi_obj->getGroup(),
                    'type_id' => $kpi_obj->getType(),
                    'minimum_rate' => $kpi_obj->getMinimumRate(),
                    'maximum_rate' => $kpi_obj->getMaximumRate(),
                );
            }
        }
       
        if ( isset( $kpi_arr ) ) {
			return $kpi_arr;
		}

		return FALSE;
    }

    function getKPIArrayByControlId( $id ) {
        $urlf = TTnew( 'UserReviewListFactory' );
        $urlf->getByUserReviewControlId( $id );
        if ( $urlf->getRecordCount() > 0 ) {
            foreach( $urlf as $ur_obj ) {
                $kpi_arr[] = array(
                    'group_id' => $ur_obj->getKPIObject()->getGroup(),
                    'type_id' => $ur_obj->getKPIObject()->getType(),
                    'minimum_rate' => $ur_obj->getKPIObject()->getMinimumRate(),
                    'maximum_rate' => $ur_obj->getKPIObject()->getMaximumRate(),
                    'rating' => $ur_obj->getRating(),
                    'note' => $ur_obj->getNote(),
                );
            }
        }
        if ( isset( $kpi_arr ) ) {
			return $kpi_arr;
		}

		return FALSE;
    }



    function testKPIA(){
        // Test Scale Rating type
        global $dd;
        $kpi_group_id = $this->createKPIGroup( $this->company_id, 10, 0 );
        $kpi_id = $this->createKPI( $this->company_id, 10, 10, $kpi_group_id, 1, 10 );
        
        $kpi_arr = $this->getKPIArrayByGroupId( $kpi_group_id );
        
        $this->assertEquals( $kpi_arr[0]['type_id'], 10 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], 1 );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], 10 );

        $user_ids[] = $dd->createUser( $this->company_id, 10 );
        $user_ids[] = $dd->createUser( $this->company_id, 11 );

        $user_review_control_id = $this->createUserReviewControl( $user_ids[0], $user_ids[1] );
        if ( $user_review_control_id != '' ) {
            $urf = TTnew( 'UserReviewFactory' );
            $urf->setUserReviewControl( $user_review_control_id );
            $urf->setKPI( $kpi_id );
            $urf->setNote('');
            $urf->setRating(3);
            if ( $urf->isValid() ) {
                $urf->Save();
            }
            unset( $urf );
        }


        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_id );

        $this->assertEquals( $kpi_arr[0]['type_id'], 10 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], 1 );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], 10 );
        if ( $kpi_arr[0]['rating'] < 10 AND $kpi_arr[0]['rating'] > 1 ) {
            $this->assertTrue( TRUE );
        } else {
            $this->assertTrue( FALSE );
        }

        unset( $kpi_id, $kpi_arr, $kpi_group_id, $user_ids, $user_review_control_id );

        $kpi_group_ids[] = $this->createKPIGroup( $this->company_id, 20, 0 );
        $kpi_group_ids[] = $this->createKPIGroup( $this->company_id, 30, 0 );
        
        $kpi_id = $this->createKPI( $this->company_id, 20, 10, $kpi_group_ids, 10, 100 );

        $kpi_arr = $this->getKPIArrayByGroupId( $kpi_group_ids[0] );

        $this->assertEquals( $kpi_arr[0]['type_id'], 10 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], 10 );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], 100 );

        $kpi_arr = $this->getKPIArrayByGroupId( $kpi_group_ids[1] );

        $this->assertEquals( $kpi_arr[0]['type_id'], 10 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], 10 );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], 100 );

        unset( $kpi_id, $kpi_arr, $kpi_group_ids );

        return TRUE;

    }

    function testKPIB() {
        global $dd;

        // Test Yes/No KPI type
        $kpi_group_id = $this->createKPIGroup( $this->company_id, 40, 0 );
        $kpi_id = $this->createKPI( $this->company_id, 60, 60, $kpi_group_id );
        
        $user_ids[] = $dd->createUser( $this->company_id, 14 );
        $user_ids[] = $dd->createUser( $this->company_id, 16 );

        $user_review_control_id = $this->createUserReviewControl( $user_ids[0], $user_ids[1] );
        if ( $user_review_control_id != '' ) {
            $urf = TTnew( 'UserReviewFactory' );
            $urf->setUserReviewControl( $user_review_control_id );
            $urf->setKPI( $kpi_id );
            $urf->setNote('');
            $urf->setRating(TRUE); // TRUE
            if ( $urf->isValid() ) {
                 $user_review_id = $urf->Save();
            }

        }

        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_id );

        $this->assertEquals( $kpi_arr[0]['type_id'], 20 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], FALSE );
        if ( (int)$kpi_arr[0]['rating'] == 0 OR (int)$kpi_arr[0]['rating'] == 1 ) {
            $this->assertTrue( TRUE );
        } else {
            $this->assertTrue( FALSE );
        }

        if ( isset( $user_review_id ) AND $user_review_id != '' ) {
            $urf->setId( $user_review_id );
            $urf->setUserReviewControl( $user_review_control_id );
            $urf->setKPI( $kpi_id );
            $urf->setRating(FALSE); // FALSE
            if ( $urf->isValid() ) {
                $urf->Save();
            }
            unset( $urf );
        }

        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_id );

        $this->assertEquals( $kpi_arr[0]['type_id'], 20 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], FALSE );
        if ( (int)$kpi_arr[0]['rating'] == 0 OR (int)$kpi_arr[0]['rating'] == 1 ) {
            $this->assertTrue( TRUE );
        } else {
            $this->assertTrue( FALSE );
        }

        unset( $kpi_id, $kpi_arr, $kpi_group_id, $user_ids, $user_review_control_id );

        return TRUE;


    }

    function testKPIC(){
        // Test text KPI type
        global $dd;
        $kpi_group_id = $this->createKPIGroup( $this->company_id, 50 );
        $kpi_id = $this->createKPI( $this->company_id, 70, 70, $kpi_group_id );

        $kpi_arr = $this->getKPIArrayByGroupId( $kpi_group_id );

        $this->assertEquals( $kpi_arr[0]['type_id'], 30 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], FALSE );

        $user_ids[] = $dd->createUser( $this->company_id, 17 );
        $user_ids[] = $dd->createUser( $this->company_id, 19 );

        $user_review_control_id = $this->createUserReviewControl( $user_ids[0], $user_ids[1] );
        if ( $user_review_control_id != '' ) {
            $urf = TTnew( 'UserReviewFactory' );
            $urf->setUserReviewControl( $user_review_control_id );
            $urf->setKPI( $kpi_id );
            if ( $urf->isValid() ) {
                 $user_review_id = $urf->Save();
            }

        }

        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_id );

        $this->assertEquals( $kpi_arr[0]['type_id'], 30 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['rating'], FALSE );

        return TRUE;


    }

    function testEditKPI() {
        global $dd;
        $kpi_group_id = $this->createKPIGroup( $this->company_id, 40 );
        $kpi_id = $this->createKPI( $this->company_id, 70, 70, $kpi_group_id );

        $kpi_arr = $this->getKPIArrayByGroupId( $kpi_group_id );

        $this->assertEquals( $kpi_arr[0]['type_id'], 30 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], FALSE );

        $user_ids[] = $dd->createUser( $this->company_id, 12 );
        $user_ids[] = $dd->createUser( $this->company_id, 13 );

        $user_review_control_id = $this->createUserReviewControl( $user_ids[0], $user_ids[1] );

        if ( $user_review_control_id != '' ) {
            $urf = TTnew( 'UserReviewFactory' );
            $urf->setUserReviewControl( $user_review_control_id );
            $urf->setKPI( $kpi_id );
            if ( $urf->isValid() ) {
                 $user_review_id = $urf->Save();
            }

        }

        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_id );

        $this->assertEquals( $kpi_arr[0]['type_id'], 30 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['rating'], FALSE );

        unset( $kpi_arr, $user_ids, $user_review_control_id, $urf );
        // edit
        $kf = TTnew( 'KPIFactory' );
        $kf->setId( $kpi_id );
        $kf->setType(10);
        $kf->setMinimumRate( 10 );
        $kf->setMaximumRate( 100 );
        if ( $kf->isValid() ) {
			$kf->Save();
		}

        $kpi_arr = $this->getKPIArrayByGroupId( $kpi_group_id );
        $this->assertEquals( $kpi_arr[0]['type_id'], 10 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], 10 );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], 100 );

        $user_ids[] = $dd->createUser( $this->company_id, 14 );
        $user_ids[] = $dd->createUser( $this->company_id, 16 );

        $user_review_control_id = $this->createUserReviewControl( $user_ids[0], $user_ids[1] );
        if ( $user_review_control_id != '' ) {
            $urf = TTnew( 'UserReviewFactory' );
            $urf->setUserReviewControl( $user_review_control_id );
            $urf->setKPI( $kpi_id );
            $urf->setNote('');
            $urf->setRating(50);
            if ( $urf->isValid() ) {
                $urf->Save();
            }
            unset( $urf );
        }


        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_id );

        $this->assertEquals( $kpi_arr[0]['type_id'], 10 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], 10 );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], 100 );
        if ( $kpi_arr[0]['rating'] < 100 AND $kpi_arr[0]['rating'] > 10 ) {
            $this->assertTrue( TRUE );
        } else {
            $this->assertTrue( FALSE );
        }

        return TRUE;
    }


    function testDeleteKPI() {
        global $dd;
        $kpi_group_ids[] = $this->createKPIGroup( $this->company_id, 10 );
        $kpi_group_ids[] = $this->createKPIGroup( $this->company_id, 20 );

        // Create a KPI( Rating Type )
        $kpi_ids[10] = $this->createKPI( $this->company_id, 10, 10, $kpi_group_ids[0], 1, 10 );
        // Create another KPI( Text Type )
        $kpi_ids[30] = $this->createKPI( $this->company_id, 70, 70, $kpi_group_ids[1] );
        // Create reviews
        $user_ids[] = $dd->createUser( $this->company_id, 12 );
        $user_ids[] = $dd->createUser( $this->company_id, 13 );
        $user_ids[] = $dd->createUser( $this->company_id, 14 );        
        $user_ids[] = $dd->createUser( $this->company_id, 15 );
        

        foreach( $kpi_ids as $type => $kpi_id ) {
            $user_key = rand(0, 2);
            $reviewer_user_key = $user_key + 1;
            $user_review_control_ids[] = $user_review_control_id = $this->createUserReviewControl( $user_ids[$user_key], $user_ids[$reviewer_user_key] );
            if ( $user_review_control_id != '' ) {
                $urf = TTnew( 'UserReviewFactory' );
                $urf->setUserReviewControl( $user_review_control_id );
                $urf->setKPI( $kpi_id );
                switch( $type ) {
                    case 10:
                        $urf->setRating(7);
                        break;
                    case 30:
                        $urf->setNote('');
                        break;
                }

                if ( $urf->isValid() ) {
                     $user_review_ids[] = $urf->Save();
                }

            }

        }

        // Delete the KPI which is in use, it will fail to delete
        foreach( $kpi_ids as $kpi_id ) {
            $lf = new KPIListFactory();
            $lf->getById( $kpi_id );
            if ( $lf->getRecordCount() == 1 ) {
                $lf = $lf->getCurrent();
                $lf->setDeleted(TRUE);
				$is_valid = $lf->isValid();
				if ( $is_valid == TRUE ) {
					$lf->Save();
				}
            }
        }
        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_ids[0] );
        $this->assertEquals( $kpi_arr[0]['type_id'], 10 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], 1 );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], 10 );
        $this->assertEquals( $kpi_arr[0]['group_id'][0], $kpi_group_ids[0] );
        $this->assertEquals( $kpi_arr[0]['rating'], 7 );

        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_ids[1] );
        $this->assertEquals( $kpi_arr[0]['type_id'], 30 );
        $this->assertEquals( $kpi_arr[0]['minimum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['maximum_rate'], FALSE );
        $this->assertEquals( $kpi_arr[0]['group_id'][0], $kpi_group_ids[1] );
        $this->assertEquals( $kpi_arr[0]['rating'], FALSE );


        // Delete reviews first and then delete KPIs
        foreach( $user_review_ids as $user_review_id ) {
            $lf = new UserReviewListFactory();
            $lf->getById( $user_review_id );
            if ( $lf->getRecordCount() == 1 ) {
                $lf = $lf->getCurrent();
                $lf->setDeleted(TRUE);
				$is_valid = $lf->isValid();
				if ( $is_valid == TRUE ) {
					$lf->Save();
				}
            }
        }
        foreach( $kpi_ids as $kpi_id ) {
            $lf = new KPIListFactory();
            $lf->getById( $kpi_id );
            if ( $lf->getRecordCount() == 1 ) {
                $lf = $lf->getCurrent();
                $lf->setDeleted(TRUE);
				$is_valid = $lf->isValid();
				if ( $is_valid == TRUE ) {
					$lf->Save();
				}
            }
        }
        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_ids[0] );
        $this->assertEquals( $kpi_arr, FALSE );

        $kpi_arr = $this->getKPIArrayByControlId( $user_review_control_ids[1] );
        $this->assertEquals( $kpi_arr, FALSE );

        return TRUE;

    }

}
?>