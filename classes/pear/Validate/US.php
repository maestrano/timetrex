<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Specific validation methods for data used in the United States
 *
 * PHP Versions 4 and 5
 *
 * This source file is subject to the New BSD license, That is bundled
 * with this package in the file LICENSE, and is available through
 * the world-wide-web at
 * http://www.opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the new BSDlicense and are unable
 * to obtain it through the world-wide-web, please send a note to
 * pajoye@php.net so we can mail you a copy immediately.
 *
 * @category  Validate
 * @package   Validate_US
 * @author    Brent Cook <busterbcook@yahoo.com>
 * @author    Tim Gallagher <timg@sunflowerroad.com>
 * @copyright 1997-2005 Brent Cook
 * @license   http://www.opensource.org/licenses/bsd-license.php  new BSD
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Validate_US
 */

/**
 * Data validation class for the United States
 *
 * This class provides methods to validate:
 *  - Social insurance number (aka SSN)
 *  - Region (state code)
 *  - Postal code
 *  - Telephone number
 *
 * @category  Validate
 * @package   Validate_US
 * @author    Brent Cook <busterbcook@yahoo.com>
 * @author    Tim Gallagher <timg@sunflowerroad.com>
 * @copyright 1997-2005 Brent Cook
 * @license   http://www.opensource.org/licenses/bsd-license.php  new BSD
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Validate_US
 */
class Validate_US
{
    /**
     * Validates a social security number
     *
     * @param string $ssn         number to validate
     * @param array  $high_groups array of highest issued SSN group numbers
     *
     * @return bool
     */
    function ssn($ssn, $high_groups = null)
    {
        // remove any dashes, spaces, returns, tabs or slashes
        $ssn = str_replace(array('-','/',' ',"\t","\n"), '', $ssn);

        // check if this is a 9-digit number
        if (!is_numeric($ssn) || strlen($ssn) != 9) {
            return false;
        }
        $area   = substr($ssn, 0, 3);
        $group  = intval(substr($ssn, 3, 2));
        $serial = intval(substr($ssn, 5, 4));

        if (!$high_groups) {
            $high_groups = Validate_US::ssnGetHighGroups();
        }
        return Validate_US::ssnCheck($area, $group, $serial, $high_groups);
    }

    /**
    * Returns a range for a supplied group number, which
    * is the middle, two-digit field of a SSN.
    * Group numbers are defined as follows:
    * 1 - Odd numbers, 01 to 09
    * 2 - Even numbers, 10 to 98
    * 3 - Even numbers, 02 to 08
    * 4 - Odd numbers, 11 to 99
    *
    * @param int $groupNumber a group number to check, 00-99
    *
    * @return int
    */
    function ssnGroupRange($groupNumber)
    {
        if (is_array($groupNumber)) {
            extract($groupNumber);
        }
        if ($groupNumber < 10) {
            // is the number odd?
            if ($groupNumber % 2) {
                return 1;
            } else {
                return 3;
            }
        } else {
            // is the number odd?
            if ($groupNumber % 2) {
                return 4;
            } else {
                return 2;
            }
        }
    }

    /**
     * checks if a Social Security Number is valid
     * needs the first three digits and first two digits and the
     * final four digits as separate integer parameters
     *
     * @param int   $area        3-digit group in a SSN
     * @param int   $group       2-digit group in a SSN
     * @param int   $serial      4-digit group in a SSN
     * @param array $high_groups array of highest issued group numbers
     *                           area number=>group number
     *
     * @return bool true if valid
     */
    function ssnCheck($area, $group, $serial, $high_groups)
    {
        if (is_array($area)) {
            extract($area);
        }
        // perform trivial checks
        // no field should contain all zeros
        if (!($area && $group && $serial)) {
            return false;
        }

        // check if this area has been assigned yet
        if (!isset($high_groups[$area])) {
            return false;
        }

        $high_group = $high_groups[$area];

        $high_group_range = Validate_US::ssnGroupRange($high_group);
        $group_range      = Validate_US::ssnGroupRange($group);

        // if the assigned range is higher than this group number, we're OK
        if ($high_group_range > $group_range) {
            return true;
        } elseif ($high_group_range < $group_range) {
            // if the assigned range is lower than the group number, that's bad
            return false;
        } elseif ($high_group >= $group) {
            // we must be in the same range, check the actual numbers
            return true;
        }

        return false;
    }

    /**
     * Gets the most current list the highest SSN group numbers issued
     * from the Social Security Administration website. This info can be
     * cached for performance (and to lessen the load on the SSA website)
     *
     * @param string $uri     Path to the SSA highgroup.txt file
     * @param bool   $is_text Take the $uri param as directly the contents
     *
     * @return array
     */
    function ssnGetHighGroups($uri = 'http://www.ssa.gov/employer/highgroup.txt',
                              $is_text = false)
    {
        /**
         * Stores high groups that have been fetched from any given web page to
         * keep the load down if having to validate more then one ssn in a row
         */
        static $high_groups = array();
        static $lastUri     = '';

        if ($lastUri == $uri && !empty($high_groups)) {
            return $high_groups;
        }
        $lastUri = $uri;

        if ($is_text) {
            $source = $uri;
        } else {
            if (!$fd = @fopen($uri, 'r')) {
                $lastUri = '';
                trigger_error('Could not access the SSA High Groups file', 
                               E_USER_WARNING);
                return array();
            }
            $source = '';
            while ($data = fread($fd, 2048)) {
                $source .= $data;
            }
            fclose($fd);
        }

        $lines       = explode("\n", preg_replace("/[^\n0-9]/", '', $source));
        $high_groups = array();

        foreach ($lines as $line) {
            if (preg_match('/^[0-9]+$/', $line) && !(($len = strlen($line)) % 5)) {
                for ($x=0; $x<$len; $x+=5) {
                    $index               = substr($line, $x, 3);
                    $value               = substr($line, $x+3, 2);
                    $high_groups[$index] = $value;
                }
            }
        }

        return $high_groups;
    }

    /**
     * Validates a US Postal Code format (ZIP code)
     *
     * @param string $postalCode the ZIP code to validate
     * @param bool   $strong     optional; strong checks (e.g. against a list 
     *                           of postcodes) (not implanted)
     *
     * @return boolean TRUE if code is valid, FALSE otherwise
     * @access public
     * @static
     * @todo Integrate with USPS web API
     */
    function postalCode($postalCode, $strong = false)
    {
        return (bool)preg_match('/^[0-9]{5}((-| )[0-9]{4})?$/', $postalCode);
    }

    /**
     * Validates a US ZIP code by region (i.e. state)
     *
     * Note: Some ZIP codes overlap between states. Do not use this data for
     * reverse lookup of states.
     *
     * @param string $postalCode the ZIP code to validate.
     * @param stirng $region     the 2-letter region code of the state.
     *
     * @return boolean true if the ZIP code is valid for the specified region
     *                 code, false otherwise.
     *
     * @access public
     * @static
     */
    function postalCodeByRegion($postalCode, $region)
    {
        /*
         * Start and end ZIP codes by state taken from Wikipedia:
         * http://en.wikipedia.org/wiki/Image:ZIP_code_zones.png
         *  and
         * http://en.wikipedia.org/wiki/List_of_ZIP_Codes_in_the_United_States
         *
         * NOTE: Some codes overlap. Do not use this for reverse lookup of
         *       states.
         */
        switch ($region) {
        case 'PW': // Palau
        case 'FM': // Micronesia
        case 'MH': // Marshall Islands
        case 'MP': // North Marina Islands
        case 'GU': // Guam
            $ranges = array('969' => '969');
            break;
        case 'AS': // American Samoa
            $ranges = array('96799' => '96799');
            break;
        case 'AP': // American Forces (Pacific)
            $ranges = array('962' => '966');
            break;
        case 'WA': // Washington
            $ranges = array('980' => '994');
            break;
        case 'OR': // Oregon
            $ranges = array('97' => '97');
            break;
        case 'HI': // Hawii
            $ranges = array('967' => '968');
            break;
        case 'CA': // California
            $ranges = array('900' => '961');
            break;
        case 'AK': // Alaska
            $ranges = array('995' => '999');
            break;
        case 'WY': // Wyoming
            $ranges = array('820' => '831', '83414' => '83414');
            break;
        case 'UT': // Utah
            $ranges = array('84' => '84');
            break;
        case 'NM': // New Mexico
            $ranges = array('870' => '884');
            break;
        case 'NV': // Nevada
            $ranges = array('889' => '899');
            break;
        case 'ID': // Idaho
            $ranges = array('832' => '839');
            break;
        case 'CO': // Colorado
            $ranges = array('80' => '81');
            break;
        case 'AZ': // Arizona
            $ranges = array('85' => '86');
            break;
        case 'TX': // Texas
            $ranges = array('75' => '79', '885' => '885', '73301' => '73301',
                '73344' => '73344');

            break;
        case 'OK': // Oklahoma
            $ranges = array('73' => '74');
            break;
        case 'LA': // Louisiana
            $ranges = array('700' => '715');
            break;
        case 'AR': // Arkansas
            $ranges = array('716' => '729');
            break;
        case 'NE': // Nebraska
            $ranges = array('68' => '69');
            break;
        case 'MO': // Missouri
            $ranges = array('63' => '65');
            break;
        case 'KS': // Kansas
            $ranges = array('66' => '67');
            break;
        case 'IL': // Illinois
            $ranges = array('60' => '62');
            break;
        case 'WI': // Wisconsin
            $ranges = array('53' => '54');
            break;
        case 'SD': // South Dakota
            $ranges = array('57' => '57');
            break;
        case 'ND': // North Dakota
            $ranges = array('58' => '58');
            break;
        case 'MT': // Montana
            $ranges = array('59' => '59');
            break;
        case 'MN': // Minnesota
            $ranges = array('550' => '567');
            break;
        case 'IA': // Iowa
            $ranges = array('50' => '52');
            break;
        case 'OH': // Ohio
            $ranges = array('43' => '45');
            break;
        case 'MI': // Michigan
            $ranges = array('48' => '49');
            break;
        case 'KY': // Kentucky
            $ranges = array('400' => '427');
            break;
        case 'IN': // Indiana
            $ranges = array('46' => '47');
            break;
        case 'AA': // American Forces (Central and South America)
            $ranges = array('340' => '340');
            break;
        case 'TN': // Tennessee
            $ranges = array('370' => '385');
            break;
        case 'MS': // Mississippi
            $ranges = array('386' => '397');
            break;
        case 'GA': // Georgia
            $ranges = array('30' => '31', '398' => '398', '39901' => '39901');
            break;
        case 'FL': // Flordia
            $ranges = array('32' => '34');
            break;
        case 'AL': // Alabama
            $ranges = array('35' => '36');
            break;
        case 'WV': // West Virginia
            $ranges = array('247' => '269');
            break;
        case 'VA': // Virginia (partially overlaps with DC)
            $ranges = array('220' => '246', '200' => '201');
            break;
        case 'SC': // South Carolina
            $ranges = array('29' => '29');
            break;
        case 'NC': // North Carolina
            $ranges = array('27' => '28');
            break;
        case 'MD': // Maryland
            $ranges = array('206' => '219');
            break;
        case 'DC': // District of Columbia
            $ranges = array('200' => '200', '202' => '205', '569' => '569');
            break;
        case 'PA': // Pennsylvania
            $ranges = array('150' => '196');
            break;
        case 'NY': // New York
            $ranges = array('10' => '14', '06390' => '06390',
                '00501' => '00501', '00544' => '00544');

            break;
        case 'DE': // Delaware
            $ranges = array('197' => '199');
            break;
        case 'VI': // Virgin Islands
            $ranges = array('008' => '008');
            break;
        case 'PR': // Puerto Rico
            $ranges = array('006' => '007', '009' => '009');
            break;
        case 'AE': // American Forces (Europe)
            $ranges = array('09' => '09');
            break;
        case 'VT': // Vermont
            $ranges = array('05' => '05');
            break;
        case 'RI': // Rhode Island
            $ranges = array('028' => '029');
            break;
        case 'NJ': // New Jersey
            $ranges = array('07' => '08');
            break;
        case 'NH': // New Hampshire
            $ranges = array('030' => '038');
            break;
        case 'MA': // Massachusetts
            $ranges = array('010' => '027', '05501' => '05501',
                '05544' => '05544');

            break;
        case 'ME': // Maine
            $ranges = array('039' => '049');
            break;
        case 'CT': // Connecticut
            $ranges = array('06' => '06');
            break;
        case 'UM': // U.S. Minor Outlying Islands
        default: // Not Found
            $ranges = array('' => '');
            break;
        }

        // truncate code if longer than 5 characters
        if (strlen($postalCode) > 5) {
            $postalCode = substr($postalCode, 0, 5);
        }
        // prepend code with zeros if shorter than 5 characters
        if (strlen($postalCode) < 5) {
            $postalCode = str_repeat('0', 5 - strlen($postalCode)).$postalCode;
        }
        // is code between some start and end range?
        $valid = false;
        foreach ($ranges as $start => $end) {
            $zip_start = substr($postalCode, 0, strlen($start));
            if ((integer)$zip_start >= (integer)$start &&
                (integer)$zip_start <= (integer)$end) {
                $valid = true;
                break;
            }
        }

        return $valid;
    }

    /**
     * Validates a "region" (i.e. state) code
     *
     * @param string $region 2-letter state code
     *
     * @return bool Whether the code is a valid state
     * @static
     */
    function region($region)
    {
        switch (strtoupper($region)) {
        case 'AL':
        case 'AK':
        case 'AZ':
        case 'AR':
        case 'CA':
        case 'CO':
        case 'CT':
        case 'DE':
        case 'DC':
        case 'FL':
        case 'GA':
        case 'HI':
        case 'ID':
        case 'IL':
        case 'IN':
        case 'IA':
        case 'KS':
        case 'KY':
        case 'LA':
        case 'ME':
        case 'MD':
        case 'MA':
        case 'MI':
        case 'MN':
        case 'MS':
        case 'MO':
        case 'MT':
        case 'NE':
        case 'NV':
        case 'NH':
        case 'NJ':
        case 'NM':
        case 'NY':
        case 'NC':
        case 'ND':
        case 'OH':
        case 'OK':
        case 'OR':
        case 'PA':
        case 'RI':
        case 'SC':
        case 'SD':
        case 'TN':
        case 'TX':
        case 'UT':
        case 'VT':
        case 'VA':
        case 'WA':
        case 'WV':
        case 'WI':
        case 'WY':
            return true;
        }
        return false;
    }

    /**
     * Validate a US phone number.
     * 
     * Allowed formats
     * <ul>
     *  <li>xxxxxxx <-> 7 digits format</li>
     *  <li>(xxx) xxx-xxxx  <-> area code with brackets around it (or not) + 
     *                          phone number with dash or not </li>
     *  <li>xxx xxx-xxxx  <-> area code + number +- dash/space + 4 digits</li> 
     *  <li>(1|0) xxx xxx-xxxx  <-> 1 or 0 + area code + 3 digits +- dash/space
     *      + 4 digits</li>
     *  <li>xxxxxxxxxx  <-> 10 digits</li> 
     * </ul>
     *
     * or various combination without spaces or dashes.
     * THIS SHOULD EVENTUALLY take a FORMAT in the options, instead
     *
     * @param string $number          phone to validate
     * @param bool   $requireAreaCode require the area code? (default: true)
     *
     * @return bool The valid or invalid phone number
     * @access public
     */
    function phoneNumber($number, $requireAreaCode = true)
    {
        if (strlen(trim($number)) <= 6) {
            return false;
        }

        if (!$requireAreaCode) {
            // just seven digits, maybe a space or dash
            if (preg_match('/^[2-9]\d{2}[- ]?\d{4}$/', $number)) {
                return  true;
            }
        } else {
            // ten digits, maybe  spaces and/or dashes and/or parentheses 
            // maybe a 1 or a 0...
            $reg = '/^[0-1]?[- ]?(\()?[2-9]\d{2}(?(1)\))[- ]?[2-9]\d{2}[- ]?\d{4}$/';
            if (preg_match($reg,
                           $number)) {
                return true;
            }
        }
        return false;
    }
}
