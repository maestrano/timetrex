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
 * $Revision: 1981 $
 * $Id: global.css.php 1981 2008-07-10 23:13:44Z ipso $
 * $Date: 2008-07-10 16:13:44 -0700 (Thu, 10 Jul 2008) $
 */
$disable_database_connection=TRUE;
require_once('../includes/global.inc.php');
Header("Content-type: text/css; charset=UTF-8");
forceCacheHeaders();

//This causes iframes to resize themselves (Monthly view, click on a day, the Day iframe resizes to half size
//Look in to fix for it.
//behavior:url("/csshover.htc");
//echo Environment::getBaseURL();
?>
body {background:#fff; color:#000; margin:0; padding:0; font-family:verdana,sans-serif; font-size:11px;}
img {border:0;}

/* container */
/*#container{margin-left:0; margin-right:0; min-width:550px; width:100%;}*/
#container{margin-left:0; margin-right:0;}

/* login page */
#rowHeaderLogin{margin:0; padding:0; border-bottom:5px solid #c30; padding-top:5px; padding-left:12px; padding-bottom:5px;}
#rowContentLogin{margin:0; padding-top:12px; padding-bottom:35px;}
#rowFooterLogin{clear:both; background:#fff; margin:0; padding:0; border-top:1px solid #000; padding-top:5px; text-align:center;}

#contentBox{background:#fff; margin:0 auto; position:relative; left:0; padding:0; width:600px;}
#contentBoxOne{background:transparent url(images/table_top.gif) no-repeat bottom left; margin-top:10px; padding:0; height:9px;}
#contentBoxTwo{background:#779bbe; margin:0px; padding:10px;}
#contentBoxTwoEdit{background:#7a9bbd; margin:20px 0; padding:0;}
#contentBoxThree{background:transparent url(images/table_bottom.gif) no-repeat top left; margin:0; padding:0; height:9px;}
#contentBoxFour{margin:0; padding: 10px; padding-right:100px; float:right;}

.row{background:#779bbe; margin:0; padding:0; height:32px;}
.cell_error{background:#e5e5e5; padding:3px; text-align:right; width:97%; font-weight:bold; border:1px solid #779bbe;}
.cellLeft{float:left; background:#e5e5e5; padding:3px; text-align:right; width:35%; height:23px; font-weight:bold; border:1px solid #779bbe;}
.cellRight{float:left; background:#fff; padding:3px; margin:0; width:62%; height:23px; border:1px solid #779bbe;}

/* fixed height edit rows -- this is replaced by "flex height edit table" (March 18th, 2005) */
.rowEdit{background:#7a9bbd; margin:0; padding:0; height:32px;}
.cellLeftEdit{float:left; background:#e5e5e5; padding:3px; text-align:right; width:35%; height:23px; font-weight:bold; border:1px solid #7a9bbd;}
.cellLeftEdit_error{float:left; background:#FF0000; padding:3px; text-align:right; width:35%; height:23px; font-weight:bold; border:1px solid #7a9bbd;}

.cellRightEdit{float:left; background:#fff; padding:3px; margin:0; width:62%; height:23px; border:1px solid #7a9bbd;}
.cellRightEdit_error{float:left; background:#FF0000; padding:3px; margin:0; width:62%; height:23px; border:1px solid #7a9bbd;}


/* flex height edit table */
.cellLeftEditTableHeader{background-color:#B8CADB; color:#369; font-size:12px; font-weight:bold; padding:5px; vertical-align: top; text-align:right; }
.cellLeftEditTable{background:#e5e5e5; padding:5px; vertical-align: top; text-align:right; width:35%; font-weight:bold; }
.cellLeftEditTable_error{background:#FF0000; padding:5px; vertical-align: top; text-align:right; width:35%; font-weight:bold; }

.cellReportRadioColumn{background-color:#B8CADB; color:#369; font-size:12px; font-weight:bold; padding:5px; vertical-align: middle; text-align:center; }
.disableFormElement{background-color: #eeeeee; color: #CCCCCC;}

.cellLeftBlueEditTable{background:#B8CADB; padding:5px; vertical-align: top; text-align:right; font-weight:bold;}
.cellLeftBlueEditTable_error{background:#FF0000; padding:5px; vertical-align: top; text-align:right; font-weight:bold;}

.cellRightEditTable{background:#fff; padding:5px; margin:0; vertical-align: top;}
.cellRightEditTableHeader{background-color:#B8CADB; color:#369; font-size:12px; font-weight:bold; padding:5px; margin:0; vertical-align: top;}
/*.helpCursor{cursor:help}*/

/* error message */
/* #rowError{margin:15px 30px; padding:5px; border:0px solid #c30; background:#e5e5e5; color:#c30; font-weight:bold;} */
#rowError{margin:15px 30px; padding:5px; border:0px solid #c30; background:#FF0000; font-weight:bold;}
#rowWarning{margin:15px 30px; padding:5px; border:0px solid #c30; background:#FFFF00; font-weight:bold;}

/* generic footer */
#rowFooter{clear:both; background:#fff; margin:0; padding:0; border-top:1px solid #000; padding-top:5px; padding-left:5px; text-align:center;}
.textFooter{font-size:10px; color:#666;}
a.footerLink, a.footerLink:link, a.footerLink:visited, a.footerLink:hover, a.footerLink:active{text-decoration:none; color:#00c;}

/* generic */
.textBold{font-weight:bold;}
.textItalic{font-style:italic;}
.textPaging{color:#036;}

#titleTab{background:#fff; margin:0; height:9px;}
.textTitle{color:#000; font-size:16px; font-weight:bold; padding-top:3px; margin:0; background:#7a9bbd url(images/tab2.gif) no-repeat top right; width:470px; height:9px;}
.textTitleSub{top:82px; background:#7a9bbd; padding-left:10px;}
.textTitle2{color:#036; font-size:16px; font-weight:bold; padding:0; padding-left:10px; margin:0;}

.textSixteenGreyBold{font-size:16px; color:#999; font-weight:bold;}
.textRedBold{color:#c30; font-weight:bold;}
.imgLeft{float:left;}
.imgRight{float:right;}
.imgLock{float:left; padding-right:5px;}

/* template page */
#rowHeader{margin:0; padding:0; height:48px; text-align:left; border-bottom:3px solid #fff;}
#rowMenu{clear:both; margin:0; padding:6px; background:#336699; color:#336699;} /* padding:0; height:25px; */
#rowMenu ul{list-style:none; margin:0; padding:0;}
#rowMenu li{display:inline; margin:0 -3px 0 0;}
#rowBreadcrumbs{margin:0; padding:3px 10px 5px 10px; background:#fff; color:#000; border-top:3px solid #c30;}
#rowBreadcrumbs a, #rowBreadcrumbs a:hover, #rowBreadcrumbs a:link, #rowBreadcrumbs a:visited, #rowBreadcrumbs a:active {text-decoration:none; color:#000;}
#rowBreadcrumbs2{margin:0; padding:3px 10px; background:#EAF0F5; color:#000;}
#rowContent{margin:0; padding:0;}
#rowContentInner{margin:0; padding:2px 0px; background:#7a9bbd;}
#rowContentInner2{margin:0; padding:10px;}
#rowContentInner3{margin:0; padding:0px;}
#rowContentInner4{margin:0; padding:0px 0px; background:#7a9bbd;}

#rowHeaderText{padding:0px; margin:0; font-size:14px; font-weight:bold; color:#999;}
#rowHeaderMenu{margin:0; padding:6px; background:#fff; color:#fff;}
#rowHeaderContainer{margin:0; padding:0;}

/*
a.menuLink, a.menuLink:link, a.menuLink:visited{text-decoration:none; color:#fff; font-weight:bold; font-size:11px; padding:2px 10px 5px 10px; margin:0; background:transparent url(images/menu_separator.gif) no-repeat top right;}
a.menuLink:hover, a.menuLink:active{text-decoration:none; color:#D9D981; font-weight:bold; font-size:11px; padding:2px 10px 5px 10px; margin:0; background:transparent url(images/menu_separator.gif) no-repeat top right;}
*/

.imgClientLogo{float:right; margin:0 10px; padding-top:4px;}

.tblHeader{background-color:#B8CADB; color:#369; font-size:12px; font-weight:bold; padding:3px; vertical-align: middle; text-align:center;}
.tblActionRow{background-color:#B8CADB; text-align:right;}
.tblPaging{color:#369; font-weight:bold; vertical-align:middle; padding:5px;}
.tblPagingLeft{color:#369; font-weight:bold; padding:3px 5px;}
a.pagingLink, a.pagingLink:link, a.pagingLink:visited, a.pagingLink:hover, a.pagingLink:active{text-decoration:none; color:#003366; padding:0 0px;}

.tblDataBlue{background-color:#EAF0F5; color:#000; text-align:center; padding:5px;}
.tblDataBlue a, .tblDataBlue a:link, .tblDataBlue a:visited, .tblDataBlue a:hover, .tblDataBlue a:active{text-decoration:underline; color:#369;}
tr.tblDataBlue:hover { background-color: #33CCFF; }

.tblDataHighLight{background-color:#33CCFF; color:#000; text-align:center; padding:5px;}
.tblDataHighLight a, .tblDataHighLight a:link, .tblDataHighLight a:visited, .tblDataHighLight a:hover, .tblDataHighLight a:active{text-decoration:underline; color:#369;}
tr.tblDataHighLight:hover { background-color: #33CCFF; }

.tblDataWhite{background-color:#fff; color:#000; text-align:center; padding:5px;}
.tblDataWhite a, .tblDataWhite a:link, .tblDataWhite a:visited, .tblDataWhite a:hover, .tblDataWhite a:active{text-decoration:underline; color:#369;}
tr.tblDataWhite:hover { background-color: #33CCFF; }
td.tblDataWhite:hover { background-color: #33CCFF; }

.tblDataGrey{background-color:#eee; color:#000; text-align:center; padding:5px;}
.tblDataGrey a, .tblDataGrey a:link, .tblDataGrey a:visited, .tblDataGrey a:hover, .tblDataGrey a:active{text-decoration:underline; color:#369;}
tr.tblDataGrey:hover { background-color: #33CCFF; }
td.tblDataGrey:hover { background-color: #33CCFF; }
td.cellHL:hover { background-color: #33CCFF; }

.tblDataWhiteNH{background-color:#fff; color:#000; text-align:center; padding:5px;}
.tblDataWhiteNH a, .tblDataWhiteNH a:link, .tblDataWhiteNH a:visited, .tblDataWhiteNH a:hover, .tblDataWhiteNH a:active{text-decoration:underline; color:#369;}

.tblDataGreyNH{background-color:#eee; color:#000; text-align:center; padding:5px;}
.tblDataGreyNH a, .tblDataGreyNH a:link, .tblDataGreyNH a:visited, .tblDataGreyNH a:hover, .tblDataGreyNH a:active{text-decoration:underline; color:#369;}

.tblDataDeleted{background-color:#666666; color:#000; text-align:center; padding:5px;}
.tblDataDeleted a, .tblDataBlue a:link, .tblDataBlue a:visited, .tblDataBlue a:hover, .tblDataBlue a:active{text-decoration:underline; color:#369;}
tr.tblDataDeleted:hover { background-color: #33CCFF; }

.tblDataError{background-color:#FF0000; color:#000; text-align:center; padding:5px;}
.tblDataError a, .tblDataError a:link, .tblDataError a:visited, .tblDataError a:hover, .tblDataError a:active{text-decoration:underline; color:#369;}

.tblDataWarning{background-color:#FFFF00; color:#000; text-align:center; padding:5px;}
.tblDataWarning a, .tblDataWarning a:link, .tblDataWarning a:visited, .tblDataWarning a:hover, .tblDataWarning a:active{text-decoration:underline; color:#369;}

.tblFormLabel{background-color:#e5e5e5; color:#000; text-align:right; padding:5px; font-weight:bold;}
.tblFormValue{background-color:#fff; padding:5px; text-align:left;}
.tblEdit{margin-top:10px;}

/* buttons */
/* .btnSubmit{border: 0; height:22px; width:135px; font-size:10px; margin:4px 5px 4px 0;}*/
.btnSubmit{height:22px; width:88px; font-size:10px; margin:4px 5px 4px 0;}
.btnCalculate{height:22px; width:80px; font-size:10px; margin:4px 5px 4px 0;}
.btnAddShift{height:22px; width:80px; font-size:10px; margin:4px 5px 4px 0;}

.btnAdd{height:22px; width:34px; font-size:10px; margin:4px 5px 4px 0;}
/*.btnAdd{background-image: url(images/btn_submit.gif); height:22px; width:135px;}*/
.btnDelete{height:22px; width:48px; font-size:10px; margin:4px 5px 4px 0;}
.btnUndelete{height:22px; width:64px; font-size:10px; margin:4px 5px 4px 0;}
.btnTimeSheet{height:22px; width:80px; font-size:10px; margin:4px 5px 4px 0;}

.btnCopy{height:22px; width:40px; font-size:10px; margin:4px 5px 4px 0;}
.btnView{height:22px; width:40px; font-size:10px; margin:4px 5px 4px 0;}

/* Tables */
.tblList{background:#7a9bbd; border: 0; border-spacing: 1px;padding: 0px;width: 99%; margin-left:auto; margin-right:auto;}
.tblForm{border: 0; border-spacing: 1px;padding: 0px;width: 99%; margin-left:auto; margin-right:auto;}
.editTable{border:0; border-spacing: 1px; padding: 0px; width:99%; margin-left:auto; margin-right:auto;}

table#form {
	border-spacing: 2px;
	padding: 0px;
	margin: auto;
}

table#head {
	border-spacing: 0px;
	padding: 2px;
	width: 100%;
}

table#sort {
	border-spacing: 0px;
	padding: 0px;
	margin: auto;
}

tr#head {
	background-color: #d3dce3;
	font-weight: bold;
	text-align: center;
}

tr#label {
	background-color: #999999;
	font-weight: bold;
	text-align: center;
}

tr#row {
	background-color: #c0c0c0;
	text-align: center;
}

tr#odd {
	background-color: #c0c0c0;
	text-align: center;
}
tr#odd:hover { background-color: #0099CC; }

tr#even {
	background-color: #d0d0d0;
	text-align: center;
}
tr#even:hover { background-color: #0099CC; }

tr#highlight {
	background-color: #33CCFF;
	text-align: center;
}

tr#error {
	background-color: #FF0000;
	text-align: center;
}

tr#blank {
	background-color: #FFFFFF;
}

tr#warning {
	background-color: #FFFF00;
	text-align: center;
}

tr#deleted {
	background-color: #666666;
	text-align: center;
	text-decoration: line-through;
}

td#label {
	background-color: #999999;
	font-weight: bold;
	text-align: right;
}
td#label_center {
	background-color: #999999;
	font-weight: bold;
	text-align: center;
}
td#label_error {
	background-color: #FF0000;
	font-weight: bold;
	text-align: right;
}

td#value_error {
	background-color: #FF0000;
	text-align: center;
}

td#green {
	background-color: #00FF00;
	text-align: center;
}

td#red {
	background-color: #FF0000;
	text-align: center;
}

td#yellow {
	background-color: #FFFF00;
	text-align: center;
}

td#form {
	text-align: left;
}
td#form_error {
	background-color: #FF0000;
	text-align: left;
}
td#error {
	background-color: #FF0000;
	text-align: center;
}

td#blank {
	background-color: #FFFFFF;
}

td#cursor-hand {
	cursor: pointer;
	cursor: hand;
}

img#cursor-hand {
	cursor: pointer;
	cursor: hand;
}

input.checkbox, input.radio {
        border: 0px;
        margin: 1px 2px 0px;
        padding: 0px;
}

tr.hide {
	display: none;
}
table.hide {
	display: none;
}
tbody.hide {
	display: none;
}


td.tabon {
	background: #438EC5;
}

td.taboff {
	background: #ABC3D4;
}

.invisibleButton {
	border-style:none;
	background-color:transparent;
}
.invisibleButton {
	padding:0px;
	font-size:9px;
	border-width:0px;
	color:#0B63A2;
	vertical-align:middle;
}

/* Tabs */
.tblSearch{background-color:#B8CADB; color:#369; font-size:12px; font-weight:bold; padding:3px; vertical-align: middle; text-align:center;}
.tblSearchMainRow {padding: 4px 0px 4px 0px;}

#tabmenu {
	color: #666;
	border-bottom: 1px solid #B8CADB;
	margin: 0px 0px 0px 0px;
	padding: 0px;
	z-index: 1;
	padding-left: 6px;
	padding-top: 4px;
	height: 21px;
}

#tabmenu li {
	display: inline;
	overflow: hidden;
	list-style-type: none;
	vertical-align: top;
}

#tabmenu a, a.active {
	color: #666;
	background: #7a9bbd;
	font-size: 14px;
	font-weight: bold;
	border: 1px solid #B8CADB;
	padding: 2px 5px 0px 5px;
	margin: 0;
	text-decoration: none;
	padding: 4px;
}

#tabmenu a.active {
	color: #369;
	background: #B8CADB;
	border-bottom: 3px solid #B8CADB;
}

#tabmenu a:hover {
	color: #369;
	background: #B8CADB;
}


#tabmenu a.active:hover {
	color: #369;
	background: #B8CADB;
}

/* JS Calendar special */
.JSCalendarPPStartDate {
  background-color: yellow; color: #fff;
}
.JSCalendarPPTransactionDate {
  background-color: green; color: #fff;
}

/* Used for making SPAN's that don't wrap. */
.nowrap {
	white-space: nowrap;
}