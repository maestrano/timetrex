{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="showAbsencePolicy(); getScheduleTotalTime(); getJobManualId(); getJobItemManualId();"}

<script language="JavaScript">
var jmido={js_array values=$data.job_manual_id_options name="jmido" assoc=true}
var jimido={js_array values=$data.job_item_manual_id_options name="jimido" assoc=true}

{literal}
function selectJobOption() {
	quick_job_id = document.getElementById('quick_job_id').value;

	if ( jmido[quick_job_id] != null ) {
		return selectOptionByValue( document.getElementById('job_id'), jmido[quick_job_id] );
	} else {
		selectOptionByValue( document.getElementById('job_id'), 0 );
	}

}

function getJobManualId() {
	if ( document.getElementById('job_id') ) {
		selected_job_id = document.getElementById('job_id').value;

		for ( x in jmido ) {
			if ( jmido[x] == selected_job_id ) {
				document.getElementById('quick_job_id').value = x;

				return true;
			}
		}
	}

	return false;
}

function selectJobItemOption() {
	quick_job_item_id = document.getElementById('quick_job_item_id').value;

	if ( jimido[quick_job_item_id] != null ) {
		return selectOptionByValue( document.getElementById('job_item_id'), jimido[quick_job_item_id] );
	} else {
		selectOptionByValue( document.getElementById('job_item_id'), 0 );
	}
}

function getJobItemManualId() {
	if ( document.getElementById('job_id') ) {
		selected_job_item_id = document.getElementById('job_item_id').value;

		for ( x in jimido ) {
			if ( jimido[x] == selected_job_item_id ) {
				document.getElementById('quick_job_item_id').value = x;

				return true;
			}
		}
	}

	return false;
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
	}
}

var remoteHW = new AJAX_Server(hwCallback);

function getScheduleTotalTime() {
	start_time = document.getElementById('start_time').value;
	end_time = document.getElementById('end_time').value;
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

		<form method="post" name="mass_schedule" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$sf->Validator->isValid()}
					{include file="form_errors.tpl" object="sf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="sf" label="user_id" value="cellLeftEditTable"}">
						{t}Employee(s):{/t}
					</td>
					<td>
						<table class="editTable">
							<tr class="tblHeader">
								<td>
									{t}UnSelected Employees{/t}
								</td>
								<td>
									<br>
								</td>
								<td>
									{t}Selected Employees{/t}
								</td>
							</tr>
							<tr>
								<td class="cellRightEditTable" width="50%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
									<br>
									<select name="src_user_id" id="src_filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$user_options}" multiple>
										{html_options options=$user_options}
									</select>
								</td>
								<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
									<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('filter_user'), document.getElementById('filter_user'), {select_size array=$user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
									<br>
									<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('src_filter_user'), {select_size array=$filter_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
									<br>
									<br>
									<br>
									<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
								</td>
								<td class="cellRightEditTable" width="50%"  align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
									<br>
									<select name="filter_user_id[]" id="filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$user_options}" multiple>
										{html_options options=$filter_user_options selected=$filter_user_id}
									</select>
								</td>
							</tr>
						</table>
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

				<tr>
					<td class="{isvalid object="sf" label="date_stamp" value="cellLeftEditTable"}">
						{t}Start Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date_stamp" name="data[start_date_stamp]" value="{getdate type="DATE" epoch=$data.start_date_stamp}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date_stamp" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date_stamp', 'cal_start_date_stamp', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="sf" label="date_stamp" value="cellLeftEditTable"}">
						{t}End Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date_stamp" name="data[end_date_stamp]" value="{getdate type="DATE" epoch=$data.end_date_stamp}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date_stamp" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date_stamp', 'cal_end_date_stamp', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				<tr>
					<td class="{isvalid object="sf" label="datestamp" value="cellLeftEditTable"}">
						{t}Only These Day(s):{/t}
					</td>
					<td class="cellRightEditTable">
						<table width="1">
						<table width="280">
							<tr style="text-align:center; font-weight: bold">
								<td>
									{t}Sun{/t}
								</td>
								<td>
									{t}Mon{/t}
								</td>
								<td>
									{t}Tue{/t}
								</td>
								<td>
									{t}Wed{/t}
								</td>
								<td>
									{t}Thu{/t}
								</td>
								<td>
									{t}Fri{/t}
								</td>
								<td>
									{t}Sat{/t}
								</td>
							</tr>
							<tr style="text-align:center;">
								<td >
									<input type="checkbox" class="checkbox" name="data[dow][0]" value="1" {if $data.dow.0 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="data[dow][1]" value="1" {if $data.dow.1 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="data[dow][2]" value="1" {if $data.dow.2 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="data[dow][3]" value="1" {if $data.dow.3 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="data[dow][4]" value="1" {if $data.dow.4 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="data[dow][5]" value="1" {if $data.dow.5 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="data[dow][6]" value="1" {if $data.dow.6 == TRUE}checked{/if}>
								</td>
							</tr>
						</table>
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
						<select id="absence_policy_id" name="data[absence_policy_id]">
							{html_options options=$data.absence_policy_options selected=$data.absence_policy_id}
						</select>
					</td>
				</tr>
				</tbody>

				{if count($data.branch_options) > 1 OR $data.branch_id != 0}
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
				{/if}

				{if count($data.department_options) > 1 OR $data.department_id != 0}
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
				{/if}

				{if $permission->Check('job','enabled') }
				{if count($data.job_options) > 1 OR $data.job_id != 0}
				<tr onClick="showHelpEntry('job')">
					<td class="{isvalid object="sf" label="job" value="cellLeftEditTable"}">
						{t}Job:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="4" id="quick_job_id" onKeyUp="selectJobOption();">
						<select id="job_id" name="data[job_id]" onChange="getJobManualId();">
							{html_options options=$data.job_options selected=$data.job_id}
						</select>
					</td>
				</tr>
				{/if}

				{if count($data.job_item_options) > 1 OR $data.job_item_id != 0}
				<tr onClick="showHelpEntry('job_item')">
					<td class="{isvalid object="sf" label="job_item" value="cellLeftEditTable"}">
						{t}Task:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="4" id="quick_job_item_id" onKeyUp="selectJobItemOption();">
						<select id="job_item_id" name="data[job_item_id]" onChange="getJobItemManualId();">
							{html_options options=$data.job_item_options selected=$data.job_item_id}
						</select>
					</td>
				</tr>
				{/if}

				{/if}

				<tr onClick="showHelpEntry('overwrite')">
					<td class="{isvalid object="sf" label="overwrite" value="cellLeftEditTable"}">
						{t}Overwrite Existing Shifts:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[overwrite]" value="1" {if $data.overwrite == TRUE}checked{/if}>
					</td>
				</tr>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_user'))">
		</div>

		</form>
	</div>
</div>
{include file="footer.tpl"}