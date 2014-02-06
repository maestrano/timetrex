{include file="sm_header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="fixHeight(); showAbsencePolicy(); getAbsencePolicyBalance(); getScheduleTotalTime(); TIMETREX.punch.getJobManualId(); TIMETREX.punch.getJobItemManualId(); TIMETREX.punch.showJob(); TIMETREX.punch.showJobItem(); "}

<script language="JavaScript">
var jmido={js_array values=$data.job_manual_id_options name="jmido" assoc=true}
var jimido={js_array values=$data.job_item_manual_id_options name="jimido" assoc=true}

{literal}
function fixHeight() {
	resizeWindowToFit(document.getElementById('body'), 'height', 100);
}

function showAbsencePolicy() {
	status_obj = document.getElementById('status_id');
	absence_obj = document.getElementById('absence');
	if ( status_obj[status_obj.selectedIndex].value == 10 ) {
		absence_obj.className = '';
		absence_obj.style.display = 'none';
	} else {
		absence_obj.className = '';
		absence_obj.style.display = '';
	}

	fixHeight();
}

function getAbsencePolicyBalance() {
	document.getElementById('accrual_policy_name').innerHTML = 'None';
	document.getElementById('accrual_policy_balance').innerHTML = 'N/A';

	if ( document.getElementById('absence_policy_id').value != 0 ) {
		remoteHW.getAbsencePolicyBalance( document.getElementById('absence_policy_id').value, document.getElementById('user_id').value);
		remoteHW.getAbsencePolicyData( document.getElementById('absence_policy_id').value );
	}
}

var loading = false;
var hwCallback = {
	getScheduleTotalTime: function(result) {
		if ( result != false ) {
			//alert('aWeek Row: '+ week_row);
			document.getElementById('total_time').innerHTML = result;
		}
	},
	getJobOptions: function(result) {
		if ( result != false ) {
			TIMETREX.punch.getJobOptionsCallBack( result );
		}
		loading = false;
	},
	getJobItemOptions: function(result) {
		if ( result != false ) {
			TIMETREX.punch.getJobItemOptionsCallBack( result );
		}
		loading = false;
	},
	getAbsencePolicyBalance: function(result) {
		if ( result == false ) {
			result = 'N/A';
		}
		document.getElementById('accrual_policy_balance').innerHTML = result;
	},
	getAbsencePolicyData: function(result) {
		if ( result == false ) {
			result = 'None';
		} else {
			result = result.accrual_policy_name;
		}
		document.getElementById('accrual_policy_name').innerHTML = result;
	}
}

var remoteHW = new AJAX_Server(hwCallback);

function getScheduleTotalTime() {
	start_time = document.getElementById('date').value +' '+ document.getElementById('start_time').value;
	end_time = document.getElementById('date').value +' '+ document.getElementById('end_time').value;
	schedule_policy_obj = document.getElementById('schedule_policy_id');
	schedule_policy_id = schedule_policy_obj[schedule_policy_obj.selectedIndex].value;


	if ( start_time != '' && end_time != '' ) {
		remoteHW.getScheduleTotalTime( start_time, end_time, schedule_policy_id );
	}
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>

<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$sf->Validator->isValid()}
					{include file="form_errors.tpl" object="sf"}
				{/if}

				<table class="editTable">
				{if $data.pay_period_is_locked == TRUE}
					<tr class="tblDataError">
						<td colspan="2">
							{t escape="no"}<b>NOTICE:</b> This pay period is currently locked, modifications are not permitted.{/t}
						</td>
					</tr>
				{/if}

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="user_id" name="data[user_id]" onChange="TIMETREX.punch.showJob(); getAbsencePolicyBalance()">
							{html_options options=$data.user_options selected=$data.user_id}
						</select>
{*
						{if $data.user_id == ''}
						{else}
							{$data.user_full_name}
							<input type="hidden" id="user_id" name="data[user_id]" value="{$data.user_id}">
							<input type="hidden" name="data[user_full_name]" value="{$data.user_full_name}">
						{/if}
*}
					</td>
				</tr>

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="sf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="data[status_id]" onChange="showAbsencePolicy();">
							{html_options options=$data.status_options selected=$data.status_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('date_stamp')">
					<td class="{isvalid object="sf" label="date_stamp" value="cellLeftEditTable"}">
						<a href="javascript:toggleRowObject('repeat');toggleImage(document.getElementById('repeat_img'), '{$IMAGES_URL}/nav_bottom_sm.gif', '{$IMAGES_URL}/nav_top_sm.gif'); fixHeight(); "><img style="vertical-align: middle" id="repeat_img" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						{t}Date:{/t}
					</td>
					<td class="cellRightEditTable">
						 {* Must use the date of the start_time, as date_stamp is the date that the schedule will be displayed on (based on PP schedule Assign Shifts too setting *}
						<input type="text" size="15" id="date" name="data[date_stamp]" value="{getdate type="DATE" epoch=$data.start_time}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('date', 'cal_date', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				<tbody id="repeat" style="display:none">
				<tr>
					<td class="{isvalid object="pcf" label="repeat" value="cellLeftEditTable"}">
						{t}Repeat Schedule for:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="3" id="time_stamp" name="data[repeat]" value="0"> {t}day(s) after above date.{/t}
					</td>
				</tr>
				</tbody>

				<tr onClick="showHelpEntry('start_time')">
					<td class="{isvalid object="sf" label="start_time" value="cellLeftEditTable"}">
						{t}In:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" id="start_time" name="data[start_time]" value="{getdate type="TIME" epoch=$data.parsed_start_time}" onChange="getScheduleTotalTime();">
						{t}ie:{/t} {$current_user_prefs->getTimeFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('end_time')">
					<td class="{isvalid object="sf" label="end_time" value="cellLeftEditTable"}">
						{t}Out:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" id="end_time" name="data[end_time]" value="{getdate type="TIME" epoch=$data.parsed_end_time}" onChange="getScheduleTotalTime();">
						{t}ie:{/t} {$current_user_prefs->getTimeFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('total_time')">
					<td class="{isvalid object="sf" label="total_time" value="cellLeftEditTable"}">
						{t}Total:{/t}
					</td>
					<td class="cellRightEditTable">
						<span id="total_time">
							{gettimeunit value=$data.total_time default=true}
						</span>
					</td>
				</tr>

				<tr onClick="showHelpEntry('schedule_policy')">
					<td class="{isvalid object="sf" label="schedule_policy" value="cellLeftEditTable"}">
						{t}Schedule Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="schedule_policy_id" name="data[schedule_policy_id]" onChange="getScheduleTotalTime();">
							{html_options options=$data.schedule_policy_options selected=$data.schedule_policy_id}
						</select>
					</td>
				</tr>

				<tbody id="absence" style="display:none">
				<tr onClick="showHelpEntry('absence_policy')">
					<td class="{isvalid object="sf" label="absence_policy" value="cellLeftEditTable"}">
						{t}Absence Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="absence_policy_id" name="data[absence_policy_id]" onChange="getAbsencePolicyBalance();">
							{html_options options=$data.absence_policy_options selected=$data.absence_policy_id}
						</select>
						<br>
						{t}Accrual Policy:{/t} <span id="accrual_policy_name">{t}None{/t}</span><br>
						{t}Available Balance:{/t} <span id="accrual_policy_balance">{t}N/A{/t}</span><br>
					</td>
				</tr>
				</tbody>

				<tr onClick="showHelpEntry('branch')">
					<td class="{isvalid object="sf" label="branch" value="cellLeftEditTable"}">
						{t}Branch:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="branch_id" name="data[branch_id]">
							{html_options options=$data.branch_options selected=$data.branch_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('department')">
					<td class="{isvalid object="sf" label="department" value="cellLeftEditTable"}">
						{t}Department:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="department_id" name="data[department_id]">
							{html_options options=$data.department_options selected=$data.department_id}
						</select>
					</td>
				</tr>

				{if $current_company->getProductEdition() >= 20 AND $permission->Check('job','enabled') }
				<tr onClick="showHelpEntry('job')">
					<td class="{isvalid object="sf" label="job" value="cellLeftEditTable"}">
						{t}Job:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="4" id="quick_job_id" onKeyUp="TIMETREX.punch.selectJobOption();">
						<select id="job_id" name="data[job_id]" onChange="TIMETREX.punch.getJobManualId(); TIMETREX.punch.showJobItem();">
							{html_options options=$data.job_options selected=$data.job_id}
						</select>
						<input type="hidden" id="selected_job" value="{$data.job_id}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('job_item')">
					<td class="{isvalid object="sf" label="job_item" value="cellLeftEditTable"}">
						Task:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="4" id="quick_job_item_id" onKeyUp="TIMETREX.punch.selectJobItemOption();">
						<select id="job_item_id" name="data[job_item_id]" onChange="TIMETREX.punch.getJobItemManualId();">
							{* {html_options options=$data.job_item_options selected=$data.job_item_id} *}
						</select>
						<input type="hidden" id="selected_job_item" value="{$data.job_item_id}">
					</td>
				</tr>
				{/if}

				<tr onClick="showHelpEntry('note')">
					<td class="{isvalid object="sf" label="note" value="cellLeftEditTable"}">
						{t}Note:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="1" cols="30" name="data[note]" id="note" onKeyUp="ResizeTextArea( document.getElementById('note'), 1, 4 )">{$data.note|escape}</textarea>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" {if $data.pay_period_is_locked == TRUE}disabled="true"{/if} onClick="return singleSubmitHandler(this)">
			{if $data.id != '' AND ( $permission->Check('schedule','delete') OR ( $permission->Check('schedule','delete_child') AND $data.is_child === TRUE ) OR ( $permission->Check('schedule','delete_own') AND $data.is_owner === TRUE ) )}
				<input type="submit" class="btnSubmit" name="action:delete" value="{t}Delete{/t}" {if $data.pay_period_is_locked == TRUE}disabled="true"{/if} onClick="return singleSubmitHandler(this)">
			{/if}
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		<input type="hidden" name="data[user_date_id]" value="{$data.user_date_id}">
		</form>
	</div>
</div>
{include file="sm_footer.tpl"}