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
 * $Revision: 10118 $
 * $Id: global.js.php 10118 2013-06-05 17:05:52Z ipso $
 * $Date: 2013-06-05 10:05:52 -0700 (Wed, 05 Jun 2013) $
 */
$disable_cache_control = TRUE;
require_once('..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR.'global.inc.php');

//When going through the installer or using quickpunch the user isnt logged in, so we still
//need to be able to load this file.
if ( ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 1 )
		OR ( isset($_SERVER['HTTP_REFERER']) AND stristr( $_SERVER['HTTP_REFERER'], 'quick_punch') ) ) {
	//FIXME: Remove the authenticate flag from sm_header and installer.
	$authenticate = FALSE;
}
require_once('..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR.'Interface.inc.php');
//Use session created date or login date.
//So this file is updated each time they login.
forceCacheHeaders( NULL, $authentication->getCreatedDate() );

$pplf = TTnew( 'PayPeriodListFactory' );
$js_calendar_pay_period_dates = $pplf->getJSCalendarPayPeriodArray()
?>
var TTProductEdition = <?php if ( isset($current_company) AND is_object($current_company) AND getTTProductEdition() >= $current_company->getProductEdition() ) { echo (int)$current_company->getProductEdition(); } else { echo (int)getTTProductEdition(); } ?>;

var JSCalendarPayPeriodEndDates = <?php echo Misc::getJSArray( $js_calendar_pay_period_dates['end_date'] )."\n"; ?>
var JSCalendarPayPeriodTransactionDates = <?php echo Misc::getJSArray( $js_calendar_pay_period_dates['transaction_date'] )."\n"; ?>
function JSCalendarDateStatus(date, y, m, d) {
	year = date.getFullYear();
	month = date.getMonth()+1;
	day = date.getDate();

	month = month.toString().pad(2,'0',0);
	day = day.toString().pad(2,'0',0);

	iso_date = year+month+day;

	if ( array_contains( JSCalendarPayPeriodTransactionDates, iso_date) ) {
		return 'JSCalendarPPTransactionDate';
	} else if ( array_contains( JSCalendarPayPeriodEndDates, iso_date) ) {
		return 'JSCalendarPPStartDate';
	}

	return false;
}
function calendar_setup(input_field, button_id, show_time) {
	if ( show_time == true ) {
		date_format = "<?php if ( isset($current_user_prefs) ) { echo $current_user_prefs->getJSDateFormat().' '.$current_user_prefs->getJSTimeFormat(); } else { echo ''; } ?>";
	} else {
		date_format = "<?php if ( isset($current_user_prefs) ) { echo $current_user_prefs->getJSDateFormat(); } else { echo ''; } ?>";
	}

	Calendar.setup(
		{
			inputField  : ""+ input_field +"",     		// ID of the input field
			ifFormat    : date_format,    				// the date format
			button      : ""+ button_id +"",       		// ID of the button
			showsTime   : show_time,
			electric 	: false,
			firstDay 	: <?php if ( isset($current_user_prefs) ) { echo (int)$current_user_prefs->getStartWeekDay(); } else { echo '0'; } ?>,
			weekNumbers : false,
			dateStatusFunc : JSCalendarDateStatus
		}
	);
}

function timePunch() {
	try {
		tP=window.open('<?php echo Environment::getBaseURL();?>punch/Punch.php',"Time_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=600,height=470,resizable=1");
		if (window.focus) {
			tP.focus()
		}
	} catch (e) {
		//DN
	}
}

function CheckAll(checkbox) {
        for (i=0; i<checkbox.form.elements.length; i++) {
                if (checkbox.form.elements[i].type == checkbox.type) {
                        checkbox.form.elements[i].checked = checkbox.checked;
                }
        }
        return true;
}


String.prototype.pad = function(l, s, t){
    return s || (s = " "), (l -= this.length) > 0 ? (s = new Array(Math.ceil(l / s.length)
        + 1).join(s)).substr(0, t = !t ? l : t == 1 ? 0 : Math.ceil(l / 2))
        + this + s.substr(0, l - t) : this;
};

/*
//Conflicts with AutoSuggest, use below array_unique instead.
//Removes duplicates from an array.
Array.prototype.removeDuplicates = function() {
        for(var i = 0; i < this.length; i++) {
                for(var j = 0; j < this.length; j++) {
                        if((i != j) && (this[i] === this[j])) {
                                this.splice(j--, 1);
                        }
                }
        }
}
*/

function isArray(obj) {
   if (obj.constructor.toString().indexOf("Array") == -1) {
      return false;
   } else {
      return true;
	}
}

function MoneyFormat( val ) {
	if ( isNaN(val) == false ) {
		//Floats are PITA, need to round to 4 decimal places, then to two to keep things consistent with PHP.
		//PHP rounds 77.69500000000001 to 77.70, JS needs to do the same.
		retval = Math.round( val * Math.pow(10, 2) ) / Math.pow(10, 2);
		return parseFloat( retval ).toFixed(2);
	}

	return '';
}

function isInt(myNum) {
	// get the modulus: if it's 0, then it's an integer
	var myMod = myNum % 1;

	if (myMod == 0) {
			return true;
	} else {
			return false;
	}
}

function isFunction(a) {
	return typeof a == 'function';
}
function isObject(a) {
	return (typeof a == 'object' && !!a) || isFunction(a);
}
function isUndefined(a) {
	return typeof a == 'undefined';
}
function isString(a) {
     return typeof a == 'string';
}

function array_unique(a) {
	tmp = new Array(0);
	for( i=0; i < a.length; i++ ) {
		if( !array_contains(tmp, a[i]) ) {
			tmp.length+=1;
			tmp[tmp.length-1]=a[i];
		}
	}

	return tmp;
}

function array_contains(a, e) {
	if ( a instanceof Array ) {
		for( j=0; j < a.length; j++) {
			if ( a[j] == e ) {
				return true;
			}
		}
	}

	return false;
}

function implode(array, glue) {
        count = array.length;

        if (count > 1) {
                for (i=0; i < count; i++) {
                        if (array[i].length > 0) {
                                if (i==0 && array[i] != 'undefined') {
                                        ret = array[i];
                                }
                                ret += glue + array[i];
                        }
                }
                return ret;
        } else {
                return array;
        }
}

function nl2br( str ) {
	var regXString = "\\n"
	var regX = new RegExp(regXString, "g");
	var replaceString = "<br />\n";

	return str.replace(regX, replaceString);
}

function ResizeTextArea(txtBox, min, max) {
	nCols = txtBox.cols;
	sVal = txtBox.value;
	nVal = sVal.length;
	nRowCnt = 1;

	for (i=0; i < nVal; i++) {
		if ( sVal.charAt(i).charCodeAt(0) == 13 || sVal.charAt(i).charCodeAt(0) == 10 ) {
			nRowCnt += 1;
		}
	}

	if ( nRowCnt < (nVal / nCols) ) {
		nRowCnt = 1 + (nVal / nCols);
	}

	if ( nRowCnt < min ) {
		nRowCnt = min;
	}

	if ( nRowCnt > max ) {
		nRowCnt = max;
	}

	txtBox.rows = nRowCnt;
}

function getCookie(cookiename) {
        var cookiestring=""+document.cookie;
        var index1=cookiestring.indexOf(cookiename);

        if (index1==-1 || cookiename=="") {
                return "";
        }

        var index2=cookiestring.indexOf(';',index1);

        if (index2==-1) {
                index2=cookiestring.length;
        }

        retval = unescape(cookiestring.substring(index1+cookiename.length+1,index2));
        //alert('Get Cookie: ' + retval);
        return retval;
}

function splitCookie(cookie) {
        //Split cookie
        split_cookie = cookie.split(/:/);

        //Unique the array
        //split_cookie.removeDuplicates();
		split_cookie = array_unique( split_cookie );

        return split_cookie;
}

function setCookie(name,value,type) {
        //Grab current cookie
        var cookie = getCookie(name);

        cookie = splitCookie(cookie);

        //Rid duplicates here too
        //cookie.removeDuplicates();
		cookie = array_unique( cookie );

        switch (type) {
                case 'expand':
                        cookie = implode(cookie, ':');
                        if (cookie == '') {
                                cookiestring = name + "=" + escape(value);
                        } else {
                                cookiestring = name + "=" + cookie + ":" + escape(value);
                        }
                        break;
                case 'collapse':
                        var new_cookie = [''];

                        n=0;
                        //Search through current cookie and remove 'name'
                        for (i=0; i < cookie.length; i++) {

                                if (cookie[i] != value) {
                                        new_cookie[n] = cookie[i];
                                        n++;
                                }
                        }
                        cookiestring = name + '=' + implode(new_cookie, ':');
                        break;
                case 'toggle':
                        //alert('Toggle');
                        cookiestring = name + "=" + escape(value);
                default:
                        alert('Default: ' + cookiestring);
                        break;

        }
        //alert('Cookie String: ' + cookiestring);
        //Set cookie
        document.cookie=cookiestring+'; path=/';
}

function delCookie(name) {
        document.cookie= name+'; path=/;expires=Thu, 01-Jan-1970 00:00:01 GMT';
}

function toggleAllRowObjects(name) {
        objects = new Array();

        tbody = document.getElementsByTagName("tbody");

        for (i=0; i < tbody.length; i++) {
                if ( tbody[i].id != '' ) {
                        //alert('Strstr: ' + tbody[i].id.indexOf(name) );
                        if ( tbody[i].id.indexOf(name) != -1 ) {
                                //alert('Found object, toggling: ' + tbody[i].id);
                                //Can't toggle object until the end, becase tbody tag changes mid flight.
                                objects.push( tbody[i].id );

                        }

                }
        }

        //alert('Found objects to toggle: ' + objects.length);
        for (x=0; x < objects.length; x++) {
                //alert('Toggling: ' + objects[x]);
                toggleRowObject( objects[x] );
        }
        //alert('x: ' + x);
}

function toggleRowObject(objectID) {
	if(document.getElementById) {
		if ( document.getElementById(objectID).style.display == 'none' ) {
			//Show
			document.getElementById(objectID).className = '';
			document.getElementById(objectID).style.display = '';
		} else {
			document.getElementById(objectID).style.display = 'none';
		}
	}
}

function toggleColumnObject(tbl_name,col_name) {
	var tbl  = document.getElementById( tbl_name );
	var rows = document.getElementsByTagName('tr');

	setCookie( 'column', tbl_name+'-'+col_name,  'expand');

	x=0;
	for (var row=0; row < rows.length;row++) {
		var cells = rows[row].getElementsByTagName('td');
		for (var cell=0; cell<cells.length;cell++) {
			if ( cells[cell].id.substr(0,4) == 'col_' ) {
				//alert('Row: '+ row +' ID: '+ cells[cell].id +' Tag: '+ cells[cell].tagName +' Span Name: '+ col_name);
				if ( cells[cell].id == 'col_'+col_name ) {
					cells[cell].style.display='';
					cells[cell].className='';
				} else {
					cells[cell].style.display='none';
					if ( x == 0 ) {
						setCookie('column',tbl_name+'-'+cells[cell].id.substr(4,25),'collapse');
						x++;
					}
				}
			}
		}
	}
}

function onload_column_expand() {
	var cookie = getCookie('column');

	cookie = splitCookie(cookie);

	if ( cookie != '' ) {
		for (i=0; i < cookie.length; i++) {
			split_cookie = cookie[i].split(/-/);
			toggleColumnObject( split_cookie[0], split_cookie[1]);
		}
	}
}

function onload_expand(cookiename) {
	var cookie = getCookie(cookiename);

	cookie = splitCookie(cookie);

	if ( cookie != '' ) {
		for (i=0; i < cookie.length; i++) {
			if ( document.getElementById( cookie[i] ) ) {
				//alert('Expanding onload: ' + cookie[i]);
				document.getElementById( cookie[i] ).className = '';
				document.getElementById( cookie[i] ).style.display = '';
			}
		}
	}
}

//Help functions
function onload_quick_help() {
	persistent = getCookie('persistent_quick_help');
	if ( persistent == 1 ) {
		placeQuickHelpWindow();
		showQuickHelpWindow();
	}
}

function hideQuickHelpWindow() {
	if (parent.document.layers) {
		parent.document.layers["quick_help"].visibility='hide';
	} else {
		parent.document.getElementById("quick_help").style.visibility='hidden';
	}
	parent.document.cookie='persistent_quick_help=0; path=/';
}

document.onkeypress = keyhandler;
enable_barcode = false;
barcode_str = '';
function keyhandler(e) {
	var code;

	if (!e) {
        var e = window.event;
    }

	if (e.keyCode) {
        code = e.keyCode;
    } else if (e.which) {
        code = e.which;
    }

	var character = String.fromCharCode(code);

    //alert('Character was Code: ' + code +' Char: '+ character);

    if ( enable_barcode == true ) {
        //alert('Appending: '+ character);
        barcode_str = barcode_str + character;
        if ( code == 13 ) {
			//alert('Barcode Is: '+ barcode_str);
			cmd = barcode_str.substr(0,1);
			id = barcode_str.substr(1,25);
			if ( cmd == 'J' ) {
				window.location = '<?php echo Environment::getBaseURL();?>/job/ViewJob.php?manual_id='+id;
			} else if (cmd == 'T') {
				window.location = '<?php echo Environment::getBaseURL();?>/job_item/EditJobItem.php?manual_id='+id;
			} else if (cmd == 'I') {
				window.location = '<?php echo Environment::getBaseURL();?>/invoice/EditInvoice.php?id='+id;
			} else {
				//Assume product
				current_location = window.location.href;
				if ( current_location.indexOf('EditProduct') != -1 ) {
					document.getElementById('product_upc').value = barcode_str;
				} else if ( current_location.indexOf('EditInvoice') != -1 ) {
					document.getElementById('product_upc').value = barcode_str;
					getProductData('product_upc');
				} else {
					//alert('No idea what page were on.');
				}
			}

			enable_barcode = false;
			barcode_str = '';
        }

        return false;
    }

    if ( code == 27 ) { //Esc
        //hideQuickHelpWindow()
    }

    if ( code == 96 ) { //`= 96
        //placeQuickHelpWindow();
        //showQuickHelpWindow()
    }

    if ( code == 94 || code == 126 ) { //^ = 94 ~ == 126
        //Start looking for barcode. This can cause problems if the user is entering text in a text input that contains a "~", especially confusing for password fields.
        //alert('Barcode Enabled: '+ code);
        enable_barcode = true;

		return false; //so it doesn't print ~ to a text input.
    }

    return true;
}


function showQuickHelpWindow() {
	if (parent.document.layers) {
		parent.document.layers["quick_help"].visibility='show';
	} else {
		parent.document.getElementById("quick_help").style.visibility='visible';
	}
	parent.document.cookie='persistent_quick_help=1; path=/';
}

function placeQuickHelpWindow() {
	//alert('ScrollTop: '+document.body.scrollTop +' Scroll Height: '+ document.body.scrollHeight +'Client Height: '+document.body.clientHeight+'Client Width: '+document.body.clientWidth);

	adjust = 75 - parent.document.body.scrollTop;
	if ( adjust < 0 ) {
		adjust = 0;
	}

	//Do window posistioning
	posistion = getCookie('quick_help_posistion');
	if ( posistion != 'bottom' ) {
		parent.document.getElementById("quick_help").style.width = '250px';
		parent.document.getElementById("quick_help").style.height = '100px';
	}

	switch(posistion) {
		case 'left':
			//Top Left
			parent.document.getElementById("quick_help").style.top = document.body.scrollTop + (document.body.clientHeight - (document.body.clientHeight-adjust));
			parent.document.getElementById("quick_help").style.left = 5;
			timeout=250;
			break;
		case 'bottom':
			//Bottom
			parent.document.getElementById("quick_help").style.width = (document.body.clientWidth - 10) +'px';
			parent.document.getElementById("quick_help").style.top = ( document.body.scrollTop + (document.body.clientHeight - document.getElementById('quick_help_table').offsetHeight) );
			parent.document.getElementById("quick_help").style.left = 5;
			timeout=100;
			break;
		case 'right':
		default:
			//Top Right
			parent.document.getElementById("quick_help").style.top = document.body.scrollTop + (document.body.clientHeight - (document.body.clientHeight-adjust));
			parent.document.getElementById("quick_help").style.left = (document.body.clientWidth - 250 - 3);
			timeout=250;
	}

	window.setTimeout("placeQuickHelpWindow()", timeout);
}

function QuickHelpWindowPosistion(posistion) {
	parent.document.cookie='quick_help_posistion='+ posistion +'; path=/';
	placeQuickHelpWindow();
}

function hideAllHelpEntries() {
	objects = new Array();

	tbody = parent.document.getElementsByTagName("tbody");

	for (i=0; i < tbody.length; i++) {
		if ( tbody[i].id != '' ) {
			//alert('Strstr: ' + tbody[i].id.indexOf(name) );
			if ( tbody[i].id.indexOf(name) != -1 && tbody[i].id.substr(0,4) == 'help' ) {
				//alert('Found object, toggling: ' + tbody[i].id);
				//Can't toggle object until the end, becase tbody tag changes mid flight.
				objects.push( tbody[i].id );
			}
		}
	}

	//alert('Found objects to toggle: ' + objects.length);
	for (x=0; x < objects.length; x++) {
		//alert('Toggling: ' + objects[x]);
		//toggleRowObject( objects[x] );
		hideHelpEntry( objects[x] );
	}
	//alert('x: ' + x);
}

function hideHelpEntry(objectID) {
	if(document.getElementById) {
		if ( parent.document.getElementById(objectID).style.display != 'none' ) {
			parent.document.getElementById(objectID).style.display = 'none';
		}
	}
}

function showHelpEntry(objectID) {
	//placeQuickHelpWindow();
	//showQuickHelpWindow();

	hideAllHelpEntries();

	//Use for creating edit help links..
	help_object_id = objectID;

	objectID = 'help-' + objectID;

	if(parent.document.getElementById) {
		if ( parent.document.getElementById(objectID) ) {
			if ( parent.document.getElementById(objectID).style.display == 'none' ) {
				//Show
				parent.document.getElementById(objectID).className = '';
				parent.document.getElementById(objectID).style.display = '';
			}
		} else {
			objectID = 'help-missing';
			if ( parent.document.getElementById(objectID) ) {
				if ( parent.document.getElementById(objectID).style.display == 'none' ) {
					//Show
					parent.document.getElementById(objectID).className = '';
					parent.document.getElementById(objectID).style.display = '';
				}
			}
		}
	}
}

function handleMenuOverlapLogo() {
	//Find the last image in the menu
	var imgs = document.getElementsByTagName('img');
	for (var img=0; img < imgs.length; img++) {
		if ( imgs[img].src.indexOf('tab_menu.gif') >= 0 ) {
			image_id = imgs[img].id;
			image_width = imgs[img].width;
		}
	}

	logo_img = document.getElementById('header_logo');

	menu_x_pos = findXPosition( document.getElementById( image_id ) )+26;
	screen_width = getWindowSize('width');
	//alert('Scr Width: '+ screen_width +' Pos: '+ menu_x_pos +' Img Width: '+ logo_img.width);

	if ( (screen_width - menu_x_pos) < (logo_img.width+7) ) {
		logo_img.style.visibility = 'hidden';
	} else {
		logo_img.style.visibility = '';
	}
	return true;
}

function findXPosition(obj) {
    var curleft = 0;
    if ( obj.offsetParent ) {
        while(1) {
			curleft += obj.offsetLeft;
			if ( !obj.offsetParent ) {
				break;
			}
          obj = obj.offsetParent;
        }
    } else if ( obj.x ) {
        curleft += obj.x;
	}

    return curleft;
}

function findYPosition(obj) {
    var curtop = 0;
    if ( obj.offsetParent ) {
        while(1) {
			curtop += obj.offsetTop;
			if ( !obj.offsetParent ) {
				break;
			}
			obj = obj.offsetParent;
        }
    } else if ( obj.y ) {
        curtop += obj.y;
	}

    return curtop;
}

//Select box ordering
function select_swap_item(obj,i,j) {
	var o = obj.options;
	var i_selected = o[i].selected;
	var j_selected = o[j].selected;
	var temp = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
	var temp2= new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);

	o[i] = temp2;
	o[j] = temp;

	o[i].selected = j_selected;
	o[j].selected = i_selected;
}

function select_item_move_up(obj) {
	if (!hasOptions(obj)) {
		return;
	}

	for ( i=0; i < obj.options.length; i++ ) {
		if ( obj.options[i].selected ) {
			if ( i != 0 && !obj.options[i-1].selected ) {
				select_swap_item( obj, i, i-1);
				obj.options[i-1].selected = true;
			}
		}
	}
}

function select_item_move_down(obj) {
	if ( !hasOptions(obj) ) {
		return;
	}

	for ( i=obj.options.length-1; i >= 0; i-- ) {
		if ( obj.options[i].selected ) {
			if ( i != (obj.options.length-1) && ! obj.options[i+1].selected ) {
				select_swap_item( obj, i, i+1 );
				obj.options[i+1].selected = true;
			}
		}
	}
}


function selectOptionByValue( form_element, option_value ) {
    for (i=0; i < form_element.options.length; i++) {
		if ( form_element.options[i].value == option_value ) {
			form_element.options[i].selected = true;

			return true;
		}
	}

	return false;
}

//Select an item by "copying" it from one select box to another
function select_item(src_form_element, dst_form_element) {
	//alert('Src: ' + src_form_element);
	//alert('Dst: ' + dst_form_element);
	found_dup=false;
	//Copy it over to the dst element
	for (i=0; i < src_form_element.options.length; i++) {
		if (src_form_element.options[i].selected) {
			//Check to see if duplicate entries exist.
			for (n=0; n < dst_form_element.options.length; n++) {
				if ( src_form_element.options[i].value == dst_form_element.options[n].value) {
						found_dup=true;
				}
			}

			//Only add if its not a duplicate entry.
			if (!found_dup) {
				//Grab the current selected value from the parent
				src_id = src_form_element.options[i].value;
				src_text = src_form_element.options[i].text;

				//src_section_id = parent_form_element.selectedIndex].value;
				//src_section_text = parent_form_element.options[parent_form_element.selectedIndex].text;

				options_length = dst_form_element.options.length;
				dst_form_element.options[options_length] = new Option(src_text, src_id);
				dst_form_element.options[options_length].selected = true;
			}
		}

		found_dup=false;
	}
}

//Used for moving items to and from the selected combo box.
function deselect_item(form_element) {
	//alert('Src: ' + src_form_element);
	//alert('Dst: ' + dst_form_element);

	//Copy it over to the dst element
	for (i=0; i < form_element.options.length; i++) {
		if (form_element.options[i].selected) {
			form_element.options[i] = null;
			i=i - 1;
		}
	}
}

function select_all(select_box) {
	for (i=0; i < select_box.options.length; i++) {
		select_box.options[i].selected = true;
	}
}

function confirmSubmit(text) {

	if ( text == null ) {
		text = "<?php echo TTi18n::gettext('You are about to delete data, once data is deleted it can not be recovered.\nAre you sure you wish to continue?'); ?>";
	}

	var agree = confirm(text);

	if ( agree ) {
		return true;
	} else {
		return false;
	}
}

var submitcount = new Array();
function singleSubmitHandler( button ) {
	button_name = button.name;
	if ( isUndefined( submitcount[button_name] ) ) {
		submitcount[button_name] = 0;
	}

	if ( submitcount[button_name] == 0 ) {
		submitcount[button_name]++;
		return true;
	} else {
		alert('<?php echo TTi18n::gettext('You have already submitted this form! Please wait...'); ?>');
		return false;
	}
}

function centerLayer(layer) {
	var pageWidth = document.body.offsetWidth ? document.body.offsetWidth : window.innerWidth;
	var pageHeight = window.innerHeight;

	document.getElementById(layer).style.left = ((pageWidth - document.getElementById(layer).offsetWidth) /2)+'px';
	document.getElementById(layer).style.top = ((( pageHeight - document.getElementById(layer).offsetHeight) /2)+ document.body.scrollTop)+'px';

	return true;
}

function showLayer(layer) {
	if (parent.document.layers) {
		parent.document.layers[layer].visibility='show';
	} else {
		parent.document.getElementById(layer).style.visibility='visible';
	}
	centerLayer(layer);
	return true;
}

function hideLayer(layer) {
	if (parent.document.layers) {
		parent.document.layers[layer].visibility='hide';
	} else {
		parent.document.getElementById(layer).style.visibility='hidden';
	}

	return true;
}

function toggleLayer(layer) {
	if ( document.getElementById(layer).style.display == 'none' ) {
		showLayer(layer);
	} else {
		hideLayer(layer);
	}

	return true;
}


function showObject(objectID) {
	if ( document.getElementById && document.getElementById(objectID) ) {
		//Dont need to clear the classname, just change the display value. Classname could be a header field, and this clears the formatting.
        //document.getElementById(objectID).className = '';
        document.getElementById(objectID).style.display = '';
	}
}

function hideObject(objectID) {
	if ( document.getElementById && document.getElementById(objectID) ) {
		if ( document.getElementById(objectID).style.display != 'none' ) {
			document.getElementById(objectID).style.display = 'none';
		}
	}
}

function toggleObject(objectID) {
	if(document.getElementById) {
		if ( document.getElementById(objectID).style.display == 'none' ) {
			showObject(objectID);
		} else {
			hideObject(objectID);
		}
	}
}

function SubmitWithMessage(form) {
		var alertMsg= "<?php echo TTi18n::gettext('Frame data DOES NOT exists:'); ?> ";
        if ( window.frames['LayerMessageFactoryFrame'].document.message_data ) {
			total_messages = window.frames['LayerMessageFactoryFrame'].document.message_data.total_messages.value;

			if ( total_messages > 0 ) {
				return true;
			}
        } else {
			alert(alertMsg);
        }

        disableForm(form);
        showLayer('MessageFactoryLayer');

        //Re-postion layer due to IE focus bug
        parent.document.getElementById("MessageFactoryLayer").style.left = 50;

        return false;
}

function disableForm(theform) {
	if (document.all || document.getElementById) {
		for (i = 0; i < theform.length; i++) {
			var tempobj = theform.elements[i];
			if (tempobj.type.toLowerCase() == "submit" || tempobj.type.toLowerCase() == "reset")
			tempobj.disabled = true;
		}
	}

	return true;
}

function disableElements( element_arr ) {
	if ( isArray(element_arr) == true ) {
		for (i = 0; i < element_arr.length; i++) {
			document.getElementById( element_arr[i] ).disabled = true;

		}
	} else {
		return false;
	}

	return true;
}


function firstElementFocus() {
    for(i=0; i < 10; i++) {
        //alert('Object: '+document.forms[0].elements[i]);
        if ( document.forms != 'undefined'
			&& document.forms.length > 0
            && document.forms[0].elements[i] != 'undefined'
            && document.forms[0].elements[i] != null
            && document.forms[0].elements[i].type == 'text' ) {
            document.forms[0].elements[i].focus();
            return true;
        }
    }

    return false;
}

window.onerror = handleError;

function handleError (err, url, line) {
        //Ignore focus errors
        if (err.indexOf('focus') != -1) {
                return true; // error is handled
        } else {
                //alert('Error - ' + err);
                return false; // let the browser handle the error
        }
}

function navSelectBox(element, direction) {
	selected = document.getElementById(element).selectedIndex;

	if ( direction == 'prev' ) {
		document.getElementById(element).options[selected-1].selected = true;
	} else {
		document.getElementById(element).options[selected+1].selected = true;
	}
}

function newMailPopUp(base_url) {
		var resultMsg = "<?php echo TTi18n::gettext('You have new mail waiting. Would you like to read it now?'); ?>";
        cookie_result = getCookie('newMailPopUp');
        if ( cookie_result != 1 ) {
                result = window.confirm(resultMsg);
                if ( result == true ) {
                        window.location.href = base_url +'/message/UserMessageList.php';
                }
        }
        document.cookie='newMailPopUp=1'+'; path=/';
}

var form_modified = false;

function formChangeDetect() {
	var onchangeFunction = new Function("","modifiedForm();");

	for(i=0; i < 500; i++) {
		if (
				document.forms[0].elements[i] != 'undefined'
				&& document.forms[0].elements[i] != null
				&& document.forms[0].elements[i].onchange == null
                && document.forms[0].elements[i].type != 'undefined'
				&& (
                        document.forms[0].elements[i].type == 'select-one'
                            || document.forms[0].elements[i].type == 'text'
                            || document.forms[0].elements[i].type == 'checkbox'
							|| document.forms[0].elements[i].type == 'radio'
                   ) ) {
				document.forms[0].elements[i].onchange = onchangeFunction;
		}
	}
}

function modifiedForm() {
        if (document.getElementById && document.getElementById('data_saved') ) {
                document.getElementById('data_saved').style.display = 'none';
        }

        form_modified = true;
}

function isModifiedForm() {
	var msg = "<?php echo TTi18n::gettext('You have modified data without saving, if you press OK you will lose any changes you have made!'); ?>";
	if ( form_modified == true ) {
		return confirmSubmit(msg);
	}

    return true;
}

function submitModifiedForm(select_box, dir, form) {
    if ( isModifiedForm() == true ) {
        if ( dir != '' ) {
			navSelectBox(select_box, dir);
        }
        form.submit();
    } else {
        //Change it back to the original selected value.
        selected_value = document.getElementById('old_'+select_box).value;
        obj = document.getElementById(select_box);

        for (i=0; i < obj.length; i++) {
			if (obj[i].value == selected_value) {
				obj[i].selected = true;
			}
        }
    }
}

//Removes all duplicate options from select box.
function uniqueSelect(obj, obj2) {
    var test_arr = new Array();

	if ( obj.options != null ) {
		for(var i = 0; i < obj.options.length; i++) {
			//alert('I:'+ i +' Option1: '+obj.options[i].text );

			if ( obj.options[i] != 'undefined'
					&& obj.options[i] != null
					&& test_arr[obj.options[i].value] == true ) {
				//alert('Found Duplicate: Value: '+ obj.options[i].value +' Text: '+ obj.options[i].text);
				obj.options[i] = null;
				i = i - 1;
			} else {
				//alert('No Duplicate: Value: '+ obj.options[i].value +' Text: '+ obj.options[i].text);
				test_arr[obj.options[i].value] = true;
			}
		}
	}

    if ( hasOptions(obj2) ) {
        for(var x = 0; x < obj2.options.length; x++) {
            //alert('Option1: '+obj2.options[i].value );

            if ( obj2.options[x] != 'undefined'
                    && obj2.options[x] != null
                    && test_arr[obj2.options[x].value] == true ) {
                obj2.options[x] = null;
                x = x - 1;
            } else {
                test_arr[obj2.options[x].value] = true;
            }
        }
    }
}

function filterCountSelect( element_id ) {
	layer_id = element_id+'_count';
	total = countSelect( document.getElementById( element_id ) );
	writeLayer( layer_id, total);
}

function countSelect(obj) {
	if ( obj != null ) {
		total = obj.options.length;
		return total;
	}

	return 'N/A';
}

function selectContains( obj, value ) {
	if ( obj != null && obj.options != null ) {
		for(var i = 0; i < obj.options.length; i++) {
			if ( obj.options[i].value == value ) {
				return true;
			}
		}
	}

	return false;
}

//Used for moving items to and from the selected combo box.
function moveItem(src_obj, dst_obj) {
	//alert('Src: ' + src_obj);
	//alert('Dst: ' + dst_obj);
	for (i=0; i < src_obj.options.length; i++) {
		if (src_obj.options[i].selected) {
            //Grab the current selected value from the parent
            src_id = src_obj.options[i].value;
            src_text = src_obj.options[i].text;

            if ( dst_obj != null ) {
                options_length = dst_obj.options.length;
                dst_obj.options[options_length] = new Option(src_text, src_id);
                dst_obj.options[options_length].selected = true;
            }

			src_obj.options[i] = null;
			i=i - 1;
		}
	}
}

function clearSelect(src_obj) {
	if ( src_obj != null ) {
		for (i=0; i < src_obj.options.length; i++) {
			src_obj.options[i] = null;
			i=i - 1;
		}
	}
}

//Used to unselect all items in a combo box
function unselectAll(obj) {
	if ( obj != null ) {
		for (i=0; i < obj.options.length; i++) {
			obj.options[i].selected = false;
		}
	}
}

function selectAll(obj) {
	if ( obj != null && obj.options != null && obj.multiple == true ) {
		for (i=0; i < obj.options.length; i++) {
			obj.options[i].selected = true;
		}
	}
}

function UserSearch(src_element_id, dst_element_id) {
	try {
		uS=window.open('<?php echo Environment::getBaseURL();?>users/UserSearch.php?src_element_id='+ src_element_id +'&dst_element_id='+ dst_element_id,"User_Search","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=550,height=550,resizable=1");
		if (window.focus) {
			uS.focus()
		}
	} catch (e) {
		//DN
	}
}

function Upload(obj_type, obj_id) {
	try {
		uS=window.open('<?php echo Environment::getBaseURL();?>upload/Upload.php?object_type='+ obj_type +'&object_id='+ obj_id,"File_Upload","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=400,height=250,resizable=1");
		if (window.focus) {
			fU.focus()
		}
	} catch (e) {
		//fU
	}
}

function hasOptions(obj) {
	if ( obj != null && obj.options != null) {
        return true;
    }

	return false;
}

function sortSelect(obj, sort_value ) {
    var o = new Array();
	if ( !hasOptions(obj) ) {
        return;
    }

	for (var i=0; i < obj.options.length; i++) {
		o[o.length] = new Option( obj.options[i].text, obj.options[i].value, obj.options[i].defaultSelected, obj.options[i].selected);
	}

    if ( o.length==0 ) {
        return;
    }

	o = o.sort(
		function(a,b) {
			if ( sort_value == 'value' ) {
				tmp_a = a.value+"";
				tmp_b = b.value+"";
			} else {
				tmp_a = a.text+"";
				tmp_b = b.text+"";
			}

			if ( tmp_a.charAt(0) == "-" && tmp_b.charAt(0) != "-") {
                return -1;
			} else if ( tmp_b.charAt(0) == "-" && tmp_a.charAt(0) != "-" ) {
				return 1;
            } else if ( tmp_a.charAt(0) == "(" && tmp_b.charAt(0) != "-") {
				return 1;
			} else if ( tmp_b.charAt(0) == "(" && tmp_a.charAt(0) != "-" ) {
				return -1;
			} else {
				if (tmp_a < tmp_b) {
					return -1;
				}
				if (tmp_a > tmp_b) {
					return 1;
				}

				return 0;
			}
		}
		);

	for (var i=0; i < o.length; i++) {
		obj.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
	}
}

function resizeSelect(src_obj, dst_obj, max) {
    //src_obj = document.getElementById(src_element_id);
    //dst_obj = document.getElementById(dst_element_id);

    //return true;
    if ( max == 'undefined' ) {
        max = 50;
    }

    //Get current size, and set that as the max.
    if ( hasOptions(dst_obj)
            && dst_obj.options.length > src_obj.options.length) {
        size = dst_obj.options.length;
    } else if ( src_obj.options != null ) {
        size = src_obj.options.length;
    } else {
		size = 2;
	}

    if ( size > max ) {
        size = max;
    }
    //alert('Size: '+ size );

    src_obj.size = size;
    if ( hasOptions(dst_obj) ) {
        dst_obj.size = size;
    }
}

function strReplace(myString, search, replace) {
    return myString.replace(search, replace);
}

function getInnerHTML(id) {
	if (document.getElementById) {
		x = document.getElementById(id);
        retval = x.innerHTML;
	} else if (document.all) {
		x = document.all[id];
        retval = x.innerHTML;
	}

    return retval;
}

function writeLayer(id, text) {
	if (document.getElementById) {
		x = document.getElementById(id);
		if ( x != null ) {
			x.innerHTML = '';
			x.innerHTML = text;
		}
	} else if (document.all) {
		x = document.all[id];
		x.innerHTML = text;
	} else if (document.layers) {
		x = document.layers[id];
		text2 = '<P CLASS="testclass">' + text + '</P>';
		x.document.open();
		x.document.write(text2);
		x.document.close();
	}
}

function getWindowSize(type) {
    var myWidth = 0, myHeight = 0;
    if( typeof( window.innerWidth ) == 'number' ) {
        //Non-IE
        myWidth = parent.window.innerWidth;
        myHeight = parent.window.innerHeight;
    } else if( parent.document.documentElement &&
      ( parent.document.documentElement.clientWidth || parent.document.documentElement.clientHeight ) ) {
        //IE 6+ in 'standards compliant mode'
        myWidth = parent.document.documentElement.clientWidth;
        myHeight = parent.document.documentElement.clientHeight;
    } else if( parent.document.body && ( parent.document.body.clientWidth || parent.document.body.clientHeight ) ) {
        //IE 4 compatible
        myWidth = parent.document.body.clientWidth;
        myHeight = parent.document.body.clientHeight;
    }
    //window.alert( 'myWidth = ' + myWidth );
    //window.alert( 'myHeight = ' + myHeight );

    if ( type == 'width' ) {
        return myWidth;
    }

    return myHeight;
}

function changeHeight(contentObj, targetObj, max_height, padding) {
	var padding = 25; //Padding, plus scrollbar size.
    if ( max_height == 'screen_size' ) {
        max_height = getWindowSize('height')-64;
    }
    //alert('Max Hieght: '+ max_height);
	if (navigator.appName.indexOf('Microsoft') != -1) {
		var contentHeight = contentObj.scrollHeight; //Works with IE too
	} else {
		var contentHeight = contentObj.offsetHeight;
	}
	//alert('Content Height: '+ contentHeight);
    var newTargetHeight = contentHeight + padding;

    if ( max_height != 'undefined'
            && newTargetHeight > max_height ) {
        newTargetHeight = max_height;
    }

    targetObj.style.height = newTargetHeight + "px";
}

function getRefToDivMod( divID, oDoc ) {
	if ( !oDoc ) {
		oDoc = document;
	}
	if ( document.layers ) {
		if ( oDoc.layers[divID] ) {
			return oDoc.layers[divID];
		} else {
			for( var x = 0, y; !y && x < oDoc.layers.length; x++ ) {
				y = getRefToDivMod(divID,oDoc.layers[x].document);
			}
			return y;
		}
	}

	if ( document.getElementById ) {
		return oDoc.getElementById(divID);
	}
	if ( document.all ) {
		return oDoc.all[divID];
	}

	return document[divID];
}

function resizeWindowToFit( contentObj, type, offset ) {
	if ( isObject( contentObj ) ) {
		idOfDiv = contentObj.id;
	} else {
		idOfDiv = contentObj;
	}

	if ( isUndefined( offset ) ) {
		offset = 0;
	}

	var oH = getRefToDivMod( idOfDiv );
	if( !oH ) {
		return false;
	}

	var oW = oH.clip ? oH.clip.width : oH.offsetWidth;
	var oH = oH.clip ? oH.clip.height : oH.offsetHeight;
	if( !oH ) {
		return false;
	}

	var x = window;

	//Get original window size.
	original_w = x.getWindowSize('width');
	original_h = x.getWindowSize('height');

	x.resizeTo( oW + 200, oH + 200 );
	var myW = 0, myH = 0, d = x.document.documentElement, b = x.document.body;

	if ( x.innerWidth ) {
		myW = x.innerWidth; myH = x.innerHeight;
	} else if ( d && d.clientWidth ) {
		myW = d.clientWidth; myH = d.clientHeight;
	} else if ( b && b.clientWidth ) {
		myW = b.clientWidth; myH = b.clientHeight;
	}
	if ( window.opera && !document.childNodes ) {
		myW += 16;
	}

	final_w = oW + ( ( oW + 200 ) - myW );
	final_h = oH + ( (oH + 200 ) - myH );

	if ( type == 'width' ) {
		final_w = final_w+offset;
		final_h = original_h;
	} else if ( type == 'height' ) {
		final_w = original_w;
		final_h = final_h+offset;
	} else if ( type == 'both' ) {
		final_w = final_w+offset;
		final_h = final_h+offset;
	}

	//Don't make window larger than screen size
	if ( final_w > screen.availWidth ) {
		final_w = screen.availWidth;
	}

	if ( final_h > screen.availHeight ) {
		final_h = screen.availHeight;
	}
	//alert('Content Width: '+ final_w +' Height: '+ final_h );

	x.resizeTo( final_w, final_h );
}

function toggleImage(obj, src1, src2) {
	//alert('Obj Src: '+obj.src +' Src1: '+src1);
	if ( obj.src.indexOf(src1) != -1 ) {
		obj.src = src2;
		//alert('Using Src2: '+obj.src);
	} else {
		//alert('Using Src1: '+obj.src);
		obj.src = src1;
	}
}

function changeImgSrc(obj, src) {
	obj.src = src;
}

function populateSelectBox( select_box_obj, options, selected, include_blank) {
	clearSelect(select_box_obj);

	if ( include_blank == true ) {
		select_box_obj.options[0] = new Option('--', 0);

		var i=1;
	} else {
		var i=0;
	}

	if ( options != null && options != '') {
		for ( x in options ) {
			if ( isFunction( options[x] ) ) {
				//AJAX sometimes returns weird functions, ignore them here.
				continue;
			}
			select_box_obj.options[i] = new Option(options[x], x);

			if ( selected instanceof Array ) {
				if ( array_contains(selected, x) ) {
					select_box_obj.options[i].selected = true;
				}
			} else {
				if ( x == selected ) {
					select_box_obj.options[i].selected = true;
				}
			}

			var i = i + 1;
		}
	}

	return true;
}

//
// Report functions
//
function exportReport( ) {
	opener.document.getElementById('action').name = 'action:Export'

	var form_obj = opener.document.forms.report;
	form_obj.target = '_self';
	form_obj.submit();
}

function selectAllReportCriteria() {
	if ( report_criteria_elements != 'undefined' ) {
		for(var i = 0; i < report_criteria_elements.length; i++) {
			selectAll( document.getElementById( report_criteria_elements[i] ) );
		}
	}
}

function countAllReportCriteria() {
	if ( report_criteria_elements != 'undefined' ) {
		for(var i = 0; i < report_criteria_elements.length; i++) {
			countReportCriteria( report_criteria_elements[i] );
		}
	}
}

function countReportCriteria( object_id ) {
	if (  selectContains( document.getElementById( object_id ), '-1' ) == true ) {
		total = "<?php echo TTi18n::gettext('All'); ?>";
	} else {
		total = countSelect( document.getElementById( object_id ) );
	}

	writeLayer( object_id+'_count', total);
}

function hideReportCriteria( object_id ) {
	//javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();
	toggleRowObject( object_id+'_on');
	toggleRowObject( object_id+'_off');

	//Call the count function.
	//eval( object_id+'Count();');
	countReportCriteria( object_id );

}

function showReportCriteria( object_id, src_object_id, dst_object_id, select_size ) {
	toggleRowObject( object_id+'_on' );
	toggleRowObject( object_id+'_off' );

	uniqueSelect( document.getElementById( dst_object_id ), document.getElementById( src_object_id ) );
	resizeSelect( document.getElementById( src_object_id ), document.getElementById( dst_object_id ), select_size );

	sortSelect( document.getElementById( dst_object_id ) );
}

function toggleReportCriteria( object_id ) {
	if ( document.getElementById( object_id+'_on' ).style.display == 'none' ) {
		//Show Extra criteria
		showReportCriteria2( object_id );
	} else {
		//Hide Extra criteria
		hideReportCriteria2( object_id );
	}
}

function hideReportCriteria2( object_id ) {
	//javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();
	toggleRowObject( object_id+'_on');
	toggleRowObject( object_id+'_off');

	//Call the count function.
	//eval( object_id+'Count();');
	countReportCriteria( object_id );

	toggleImage( document.getElementById( object_id+'_img' ), '<?php echo Environment::getImagesURL()?>nav_top_sm.gif','<?php echo Environment::getImagesURL()?>nav_bottom_sm.gif' );

	document.getElementById( object_id+'_right_cell' ).className = 'cellRightEditTableHeader';
}

function showReportCriteria2( object_id, src_object_id, dst_object_id, select_size ) {
	toggleRowObject( object_id+'_on' );
	toggleRowObject( object_id+'_off' );

	uniqueSelect( document.getElementById( dst_object_id ), document.getElementById( src_object_id ) );
	resizeSelect( document.getElementById( src_object_id ), document.getElementById( dst_object_id ), select_size );

	sortSelect( document.getElementById( dst_object_id ) );

	toggleImage( document.getElementById( object_id+'_img' ), '<?php echo Environment::getImagesURL()?>nav_top_sm.gif','<?php echo Environment::getImagesURL()?>nav_bottom_sm.gif' );

	document.getElementById( object_id+'_right_cell' ).className = '';
}

function moveReportCriteriaItems( src_object_id, dst_object_id, select_size, sort, sort_value ) {
	moveItem( document.getElementById( src_object_id ), document.getElementById( dst_object_id ) );
	uniqueSelect( document.getElementById( dst_object_id ) );
	if ( sort != false ) {
		sortSelect( document.getElementById( dst_object_id ), sort_value );
	}
	resizeSelect( document.getElementById( src_object_id ), document.getElementById( dst_object_id ), select_size);
}

//Namespace for TimeTrex objects
var TIMETREX = function () {
	return {
		form: {},
		searchForm: {},
		invoice: {},
		currency: {},
		punch: {},
		schedule: {}
	};
}();

//Schedule functions
TIMETREX.schedule = function() {
	return {
		editSchedule: function( scheduleID, userID, date, status_id, start_time, end_time, schedule_policy_id, absence_policy_id) {
			try {
				eS=window.open('<?php echo Environment::getBaseURL();?>schedule/EditSchedule.php?id='+ encodeURI(scheduleID) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date) +'&status_id='+ encodeURI(status_id) +'&start_time='+ encodeURI(start_time) +'&end_time='+ encodeURI(end_time) +'&schedule_policy_id='+ encodeURI(schedule_policy_id) +'&absence_policy_id='+ encodeURI(absence_policy_id),"Edit_Schedule","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
			} catch (e) {
				//DN
			}
		}
	};
}();

//Punch/Edit Punch functions
TIMETREX.punch = function() {
	return {
		selectJobOption: function( src_quick_job_id, src_job_id ) {
			if ( isUndefined( src_quick_job_id ) ) {
				quick_job_obj = document.getElementById('quick_job_id');
			} else {
				quick_job_obj = document.getElementById( src_quick_job_id );
			}

			if ( isUndefined( src_job_id ) ) {
				job_obj = document.getElementById('job_id');
			} else {
				job_obj = document.getElementById( src_job_id );
			}

			if ( jmido[quick_job_obj.value] != null ) {
				selectOptionByValue( job_obj, jmido[quick_job_obj.value] );
			} else {
				selectOptionByValue( job_obj, 0 );
			}

			TIMETREX.punch.showJobItem();

			return true;
		},
		getJobManualId: function( src_job_id ) {
			if ( isUndefined( src_job_id ) ) {
				job_obj = document.getElementById('job_id');
			} else {
				job_obj = document.getElementById( src_job_id );
			}

			if ( job_obj ) {
				selected_job_id = job_obj.value;

				for ( x in jmido ) {
					if ( jmido[x] == selected_job_id ) {
						document.getElementById('quick_job_id').value = x;

						return true;
					}
				}
			}

			return false;
		},
		selectJobItemOption: function() {
			quick_job_item_id = document.getElementById('quick_job_item_id').value;

			if ( jimido[quick_job_item_id] != null ) {
				selectOptionByValue( document.getElementById('job_item_id'), jimido[quick_job_item_id] );
			} else {
				selectOptionByValue( document.getElementById('job_item_id'), 0 );
			}

			return true;
		},
		getJobItemManualId: function() {
			if ( document.getElementById('job_id') && document.getElementById('job_item_id') ) {
				selected_job_item_id = document.getElementById('job_item_id').value;

				for ( x in jimido ) {
					if ( jimido[x] == selected_job_item_id ) {
						document.getElementById('quick_job_item_id').value = x;

						return true;
					}
				}
			}

			return false;
		},
		getJobOptionsCallBack: function( result ) {
			job_obj = document.getElementById('job_id');
			selected_job = document.getElementById('selected_job').value;

			populateSelectBox( job_obj, result, selected_job);

			TIMETREX.punch.getJobManualId();
			TIMETREX.punch.selectJobOption();
		},
		getJobItemOptionsCallBack: function( result ) {
			if ( document.getElementById('job_item_id') && document.getElementById('selected_job_item') ) {
				job_item_obj = document.getElementById('job_item_id');
				selected_job_item = document.getElementById('selected_job_item').value;

				populateSelectBox( job_item_obj, result, selected_job_item);

				TIMETREX.punch.getJobItemManualId();
				TIMETREX.punch.selectJobItemOption();
			}
		},
		showJob: function() {
			job_id = document.getElementById('job_id');

			if ( job_id != null ) {
				clearSelect( document.getElementById('job_id') );
				user_id = document.getElementById('user_id').value;

				if ( TTProductEdition >= 20 ) {
					remoteHW.getJobOptions( user_id );
				}
			}
		},
		showJobItem: function( include_disabled ) {
			job_id = document.getElementById('job_id');

			if ( job_id != null ) {
				clearSelect( document.getElementById('job_item_id') );
				job = document.getElementById('job_id').value;

				if ( TTProductEdition >= 20 ) {
					remoteHW.getJobItemOptions( job, include_disabled );
				}
			}
		}
	};
}();

//Currency functions
TIMETREX.currency = function() {
	return {
		// copy one input to another
		convert: function(src_rate, dst_rate, amount) {
			base_amount = (1 / src_rate) * amount;
			retval = dst_rate * base_amount;

			return retval.toFixed(2);
		}
	};
}();

//Misc functions
TIMETREX.form = function() {
	return {
		// copy one input to another
		copyElement: function(src_form_element, dst_form_element) {
				switch(dst.type) {
						case 'select-one':
						case 'text':
								src_form_element.value = dst_form_element.value;
								//document.search_form[src].value = dst.value;
								break;
				}
		}
	};
}();

// Search Form
TIMETREX.searchForm = function() {
	var last_selected_tab = null;
	var tabs = new Array();
	tabs['default'] = [ 'basic_search', 'adv_search', 'saved_search' ];

	return {
		toggleTabBlockImage: function( dir ) {
			if ( document.getElementById('tab_hide_img') ) {
				if ( dir == 'up' ) {
					document.getElementById('tab_hide_img').src = '<?php echo Environment::getImagesURL();?>nav_top_sm.gif';
				} else {
					document.getElementById('tab_hide_img').src = '<?php echo Environment::getImagesURL();?>nav_bottom_sm.gif';
				}
			}
		},
		toggleTabBlock: function( tabObjectID, contentObjectID, groupID ) {
			if ( groupID == null ) {
				groupID = 'default';
			}

			if ( last_selected_tab == null ) {
				last_selected_tab = 'basic_search'
			}

			if ( tabObjectID == null ) {
				tabObjectID = last_selected_tab;
			}

			var show_block = true;
			for( var i=0; i < tabs[groupID].length; i++) {
				if ( document.getElementById('tab_'+tabs[groupID][i]).className == 'active' ) {
					//Hide tab block
					show_block = false;
				}
			}

			if ( show_block == true ) {
				TIMETREX.searchForm.showTab( tabObjectID, contentObjectID, groupID );
				TIMETREX.searchForm.toggleTabBlockImage('up');
			} else {
				TIMETREX.searchForm.deactivateTabs();
				TIMETREX.searchForm.hideTabContent();
				TIMETREX.searchForm.toggleTabBlockImage('down');
			}
		},
		deactivateTabs: function( groupID ) {
			if ( groupID == null ) {
				groupID = 'default';
			}

			if(document.getElementById) {
				if ( tabs[groupID] != null ) {
					//Deactivate other tabs
					for( var i=0; i < tabs[groupID].length; i++) {
						if ( document.getElementById('tab_'+tabs[groupID][i]) ) {
							document.getElementById('tab_'+tabs[groupID][i]).className = '';
						}
					}

					document.getElementById('selected_tab').value = '';
				}
			}
		},
		activateTab: function( objectID, groupID ) {
			if ( groupID == null ) {
				groupID = 'default';
			}

			if(document.getElementById) {
				if ( tabs[groupID] != null ) {
					if ( document.getElementById('tab_'+objectID) ) {
						TIMETREX.searchForm.deactivateTabs( groupID );

						//Activate new tab
						document.getElementById('tab_'+objectID).className = 'active';

						TIMETREX.searchForm.toggleTabBlockImage('up');

						if ( objectID != 'saved_search' ) {
							document.getElementById('selected_tab').value = objectID;
						}
						last_selected_tab = objectID;
					}
				}
			}
		},
		hideTabContent: function( groupID ) {
			if ( groupID == null ) {
				groupID = 'default';
			}

			if(document.getElementById) {
				if ( tabs[groupID] != null ) {
					//Hide all other tab content
					for( var i=0; i < tabs[groupID].length; i++) {
						if ( document.getElementById(tabs[groupID][i]) ) {
							document.getElementById(tabs[groupID][i]).style.display = 'none';
						}
					}

					TIMETREX.searchForm.hideTabGlobalContent( groupID );
				}
			}
		},
		showTabContent: function( objectID, groupID ) {
			if ( groupID == null ) {
				groupID = 'default';
			}

			if(document.getElementById) {
				if ( tabs[groupID] != null ) {
					if ( document.getElementById(objectID) ) {
						TIMETREX.searchForm.hideTabContent( groupID );

						document.getElementById(objectID).style.display = '';
					}
				}
			}
		},
		hideTabGlobalContent: function() {
			if ( document.getElementById('tab_global') ) {
				document.getElementById('tab_global').style.display = 'none';
			}
		},
		showTabGlobalContent: function() {
			if ( document.getElementById('tab_global') ) {
				document.getElementById('tab_global').style.display = '';
			}
		},
		filterTabContent: function( tabObjectID ) {
			var table = document.getElementById( 'content_adv_search' );
			var rows = table.getElementsByTagName('tr');
			var row_prefix = 'tab_row_';

			var x=0;
			for (var row=0; row<rows.length; row++) {
				//Skip first row as it contains all other rows.
				if ( x > 0 ) {
					//If row ID matches the tabObjectID we show the row, else we hide the row.
					if ( rows[row].id == row_prefix+'all' || rows[row].id == row_prefix+tabObjectID ) {
						//alert('Showing Row: '+ rows[row].id );
						rows[row].style.display = '';
					} else {
						//alert('i:'+ row +' Hiding Row: '+ rows[row].id );
						rows[row].style.display = 'none';

						if ( tabObjectID == 'basic_search' ) {
							//Need to clear any form elements that aren't in both tabs
							tags = rows[row].getElementsByTagName('input');
							for (var tag=0; tag<tags.length; tag++) {
								TIMETREX.searchForm.clearFormElement( tags[tag].name );
							}

							tags = rows[row].getElementsByTagName('select');
							for (var tag=0; tag<tags.length; tag++) {
								TIMETREX.searchForm.clearFormElement( tags[tag].name );
							}
						}
					}
				}
				x++;
			}
		},
		onLoadShowTab: function() {
			if ( document.getElementById('selected_tab') ) {
				selected_tab = document.getElementById('selected_tab').value;

				if ( selected_tab != '' ) {
					TIMETREX.searchForm.showTab( selected_tab );
				}
			}
		},
		showTab: function( tabObjectID, contentObjectID, groupID) {
			if ( groupID == null ) {
				groupID = 'default';
			}

			if ( tabObjectID == 'basic_search' && ( contentObjectID == null || contentObjectID == '' || contentObjectID == 'undefined' ) ) {
				contentObjectID = 'adv_search';
			}

			if ( contentObjectID == null ) {
				contentObjectID = tabObjectID;
			}

			//alert('Selected Tab: '+ document.getElementById('selected_tab').value );
			//alert('GroupID: '+ groupID +' Selected Tab: '+ tabObjectID +' Tab Content: '+ contentObjectID +' All Tabs: '+ tabs[groupID] );
			if(document.getElementById) {
				if ( tabs[groupID] != null ) {
					//Activate tab
					TIMETREX.searchForm.activateTab( tabObjectID, groupID );

					//Show tab content
					TIMETREX.searchForm.showTabContent( contentObjectID, groupID );

					//Show tab global content
					TIMETREX.searchForm.showTabGlobalContent( contentObjectID, groupID );

					//Filter content within tab
					TIMETREX.searchForm.filterTabContent( tabObjectID );
				}
			}
		},
		clearForm: function() {
			for ( num in document.search_form.elements ) {
				if ( document.search_form.elements[num] ) {
					element = document.search_form.elements[num];
					if( typeof element.type != 'undefined' && typeof element.name != 'undefined'
							&& element.id != 'filter_column'
							&& element.name.indexOf('sort_') == -1
							&& element.name.indexOf('saved_search') == -1  ) {
						TIMETREX.searchForm.clearFormElement( element.name );
					} else {
						//alert('NOT Clearing Form Element Name: '+ element.name );
					}
					document.getElementById('selected_tab').value = '';
				}
			}
		},
		clearFormElement: function( src_name ) {
			if ( typeof document.search_form[src_name] != 'undefined' ) {
				switch(document.search_form[src_name].type) {
					case 'select-one':
						//alert('Clearing Form Element Name: '+ src_name );
						if ( document.search_form[src_name].options[0] ) {
							document.search_form[src_name].options[0].selected = true;
						}
						break;
					case 'checkbox':
						document.search_form[src_name].checked = false;
						break;
					case 'text':
						//alert('Clearing Form Element Name: '+ src_name );
						document.search_form[src_name].value = '';
						break;
				}
			}

			return true;
		},
		copyTabData: function( dst_tab_name ) {
			if ( dst_tab_name == 'basic' ) {
				src_tab_name = 'adv';
			} else {
				src_tab_name = 'basic';
			}

			//alert('Copy Tab Data: SRC: '+ src_tab_name +' DST: '+ dst_tab_name);
			for( num in document.search_form.elements ) {
				if( document.search_form.elements[num] ) {
					element = document.search_form.elements[num];
					if( typeof element.type != 'undefined' && typeof element.name != 'undefined'
						&& element.name.indexOf('['+ src_tab_name +']') != -1 ) {

						//String Replace src tab name with dst tab name.
						dst_name = strReplace( element.name, src_tab_name, dst_tab_name );
						//alert('Found: '+ src_tab_name +' tab elements: '+ element.name +' DST Name: '+ dst_name );

						TIMETREX.searchForm.copyElementByName( element.name, dst_name );
					}
				}
			}

		},
		copyElementByName: function(src_name, dst_name ) {
			if ( typeof document.search_form[src_name] != 'undefined' && typeof document.search_form[dst_name] != 'undefined' ) {
				switch(document.search_form[dst_name].type) {
					case 'select-one':
					case 'text':
						//alert('SRC Name: '+ src_name +' SRC Value: '+ document.search_form[src_name].value +' DST Name: '+ dst_name);
						document.search_form[dst_name].value = document.search_form[src_name].value;
						break;
				}
			}

			return true;
		}
	};
}();