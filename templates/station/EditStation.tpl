{include file="header.tpl" enable_ajax=TRUE body_onload="showType(); countAllReportCriteria(); TIMETREX.punch.showJobItem( false );"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_include_user',
									'filter_exclude_user'
									);
function showType() {
	type_id = document.getElementById('type_id').value;

	document.getElementById('timeclock').style.display = 'none';
	document.getElementById('type_id-100').style.display = 'none';
	document.getElementById('type_id-150').style.display = 'none';

	if ( type_id == 100 || type_id == 120 || type_id == 200 ) {
		document.getElementById('timeclock').className = '';
		document.getElementById('timeclock').style.display = '';

		document.getElementById('type_id-100').className = '';
		document.getElementById('type_id-100').style.display = '';
	} else if ( type_id == 150 ) {
		document.getElementById('timeclock').className = '';
		document.getElementById('timeclock').style.display = '';

		document.getElementById('type_id-150').className = '';
		document.getElementById('type_id-150').style.display = '';
	}
}

if ( TTProductEdition >= 20 ) {
	var loading = false;
	var hwCallback = {
			getJobItemOptions: function(result) {
				if ( result != false ) {
					TIMETREX.punch.getJobItemOptionsCallBack( result );
				}
				loading = false;
			}
		}

	var remoteHW = new AJAX_Server(hwCallback);
}
{/literal}
</script>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$sf->Validator->isValid()}
					{include file="form_errors.tpl" object="sf"}
				{/if}

				{if $time_clock_command_result != ''}
				<div id="rowWarning" align="center">
					<br><b>{$time_clock_command_result}</b><br><br>
				</div>
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="sf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[status]">
							{html_options options=$data.status_options selected=$data.status}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="sf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[type]" id="type_id" onChange="showType();">
							{html_options options=$data.type_options selected=$data.type}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('station')">
					<td class="{isvalid object="sf" label="station" value="cellLeftEditTable"}">
						{t}Station ID:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[station]" value="{$data.station}" size="35">
					</td>
				</tr>

				<tr onClick="showHelpEntry('source')">
					<td class="{isvalid object="sf" label="source" value="cellLeftEditTable"}">
						{t}Source:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="40" name="data[source]" value="{$data.source}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('description')">
					<td class="{isvalid object="sf" label="description" value="cellLeftEditTable"}">
						{t}Description:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[description]" value="{$data.description}" size="45">
					</td>
				</tr>

				<tr>
					<td colspan="2" class="tblHeader">
						{t}Default Punch Settings{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('branch_id')">
					<td class="{isvalid object="sf" label="branch_id" value="cellLeftEditTable"}">
						{t}Branch{/t}:
					</td>
					<td class="cellRightEditTable">
						<select name="data[branch_id]">
							{html_options options=$data.branch_options selected=$data.branch_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('department_id')">
					<td class="{isvalid object="sf" label="department_id" value="cellLeftEditTable"}">
						{t}Department{/t}:
					</td>
					<td class="cellRightEditTable">
						<select name="data[department_id]">
							{html_options options=$data.department_options selected=$data.department_id}
						</select>
					</td>
				</tr>

				{if $current_company->getProductEdition() >= 20}
					{if count($data.job_options) > 1}
					<tr onClick="showHelpEntry('job_id')">
						<td class="{isvalid object="sf" label="job_id" value="cellLeftEditTable"}">
							{t}Job{/t}:
						</td>
						<td class="cellRightEditTable">
							<select id="job_id" name="data[job_id]" onChange="TIMETREX.punch.showJobItem( false );">
								{html_options options=$data.job_options selected=$data.job_id}
							</select>
						</td>
					</tr>
					{/if}

					{if count($data.job_item_options) > 1}
					<tr onClick="showHelpEntry('job_item_id')">
						<td class="{isvalid object="sf" label="job_item_id" value="cellLeftEditTable"}">
							{t}Task{/t}:
						</td>
						<td class="cellRightEditTable">
							<select id="job_item_id" name="data[job_item_id]">
							</select>
							<input type="hidden" id="selected_job_item" value="{$data.job_item_id}">
						</td>
					</tr>
					{/if}
				{/if}

				<tbody id="timeclock" style="display:none" >
				<tr>
					<td colspan="2" class="tblHeader">
						{t}Time Clock Configuration{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('password')">
					<td class="{isvalid object="sf" label="password" value="cellLeftEditTable"}">
						{t}Password{/t}/{t}COMM Key{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[password]" value="{$data.password}" size="25">
					</td>
				</tr>

				<tr onClick="showHelpEntry('port')">
					<td class="{isvalid object="sf" label="port" value="cellLeftEditTable"}">
						{t}Port:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[port]" value="{$data.port}" size="8">
					</td>
				</tr>

				<tr onClick="showHelpEntry('time_zone')">
					<td class="{isvalid object="sf" label="time_zone" value="cellLeftEditTable"}">
						{t}Force Time Zone:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[time_zone_id]">
							{html_options options=$data.time_zone_options selected=$data.time_zone_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('enable_auto_punch_status')">
					<td class="{isvalid object="apf" label="enable_auto_punch_status" value="cellLeftEditTable"}">
						{t}Enable Automatic Punch Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[enable_auto_punch_status]" value="1" {if $data.enable_auto_punch_status == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('mode_flag')">
					<td class="{isvalid object="sf" label="mode_flag" value="cellLeftEditTable"}">
						{t}Configuration Modes:{/t}
					</td>
					<td class="cellRightEditTable" valign="top">
						<select name="data[mode_flag][]" multiple>
							{html_options options=$data.mode_flag_options selected=$data.mode_flag}
						</select>
					</td>
				</tr>

				{if $data.id != '' AND ( $data.type == 100 OR $data.type == 120 OR $data.type == 200)}
				<tr onClick="showHelpEntry('time_clock_commands')">
					<td class="{isvalid object="sf" label="time_clock_commands" value="cellLeftEditTable"}">
						{t}Manual Command:{/t}
					</td>
					<td class="cellRightEditTable" valign="top">
						<select name="data[time_clock_command]">
							{html_options options=$data.time_clock_command_options selected=$data.time_clock_command}
						</select>
						<input type="submit" name="action:time_clock_command" value="{t}Run Now{/t}">
					</td>
				</tr>
				{/if}

				<tr>
					<td colspan="2" class="tblHeader">
						{t}Time Clock Synchronization{/t}
					</td>
				</tr>

				<tbody id="type_id-100" style="display:none" >
					<tr onClick="showHelpEntry('poll_frequency')">
						<td class="{isvalid object="sf" label="poll_frequency" value="cellLeftEditTable"}">
							{t}Download Frequency:{/t}
						</td>
						<td class="cellRightEditTable">
							<select id="100_poll_frequency" name="data[poll_frequency]" onChange="document.getElementById('150_poll_frequency').value = document.getElementById('100_poll_frequency').value;">
								{html_options options=$data.poll_frequency_options selected=$data.poll_frequency}
							</select>
							({t}Last Download{/t}: {getdate type="DATE+TIME" default=TRUE epoch=$data.last_poll_date})
						</td>
					</tr>

					<tr onClick="showHelpEntry('push_frequency')">
						<td class="{isvalid object="sf" label="push_frequency" value="cellLeftEditTable"}">
							{t}Full Upload Frequency:{/t}
						</td>
						<td class="cellRightEditTable">
							<select name="data[push_frequency]">
								{html_options options=$data.push_frequency_options selected=$data.push_frequency}
							</select>
							({t}Last Upload{/t}: {getdate type="DATE+TIME" default=TRUE epoch=$data.last_push_date})
						</td>
					</tr>

					<tr onClick="showHelpEntry('partial_push_frequency')">
						<td class="{isvalid object="sf" label="partial_push_frequency" value="cellLeftEditTable"}">
							{t}Partial Upload Frequency:{/t}
						</td>
						<td class="cellRightEditTable">
							<select name="data[partial_push_frequency]">
								{html_options options=$data.push_frequency_options selected=$data.partial_push_frequency}
							</select>
							({t}Last Upload{/t}: {getdate type="DATE+TIME" default=TRUE epoch=$data.last_partial_push_date})
						</td>
					</tr>

					</tbody>
				</tbody>

				<tbody id="type_id-150" style="display:none" >
					<td class="{isvalid object="sf" label="poll_frequency" value="cellLeftEditTable"}">
						{t}Synchronize Frequency:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="150_poll_frequency" name="data[poll_frequency]" onChange="document.getElementById('100_poll_frequency').value = document.getElementById('150_poll_frequency').value;">
							{html_options options=$data.poll_frequency_options selected=$data.poll_frequency}
						</select>
					</td>
				</tbody>

				<tr onClick="showHelpEntry('last_punch_time_stamp')">
					<td class="{isvalid object="sf" label="poll_frequency" value="cellLeftEditTable"}">
						{t}Last Downloaded Punch:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="DATE+TIME" default=TRUE epoch=$data.last_punch_time_stamp}
					</td>
				</tr>

				{if $permission->Check('station','assign')}
				<tr>
					<td colspan="2" class="tblHeader">
						{t}Employee Criteria{/t}
					</td>
				</tr>

				<tbody id="filter_group_on" style="display:none" >
				<tr>
					<td class="{isvalid object="sf" label="group" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employee Groups{/t}:</b><a href="javascript:toggleRowObject('filter_group_on');toggleRowObject('filter_group_off');filterCountSelect( 'filter_group' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td colspan="3">
								{t}Selection Type{/t}:
								<select id="type_id" name="data[group_selection_type_id]">
									{html_options options=$data.group_selection_type_options selected=$data.group_selection_type_id}
								</select>
							</td>
						</td>
						<tr class="tblHeader">
							<td>
								{t}UnSelected Groups{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Selected Groups{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_group'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_group'))">
								<br>
								<select name="src_group_id" id="src_filter_group" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_group_options array2=$data.selected_group_options}" multiple>
									{html_options options=$data.src_group_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_group'), document.getElementById('filter_group')); uniqueSelect(document.getElementById('filter_group')); sortSelect(document.getElementById('filter_group'));resizeSelect(document.getElementById('src_filter_group'), document.getElementById('filter_group'), {select_size array=$data.src_group_options array2=$data.selected_group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_group'), document.getElementById('src_filter_group')); uniqueSelect(document.getElementById('src_filter_group')); sortSelect(document.getElementById('src_filter_group'));resizeSelect(document.getElementById('src_filter_group'), document.getElementById('filter_group'), {select_size array=$data.src_group_options array2=$data.selected_group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_group'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_group'))">
								<br>
								<select name="data[group_ids][]" id="filter_group" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_group_options array2=$data.selected_group_options}" multiple>
									{html_options options=$data.selected_group_options selected=$data.group_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_group_off">
				<tr>
					<td class="{isvalid object="sf" label="group" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employee Groups{/t}:</b><a href="javascript:toggleRowObject('filter_group_on');toggleRowObject('filter_group_off');uniqueSelect(document.getElementById('filter_group'), document.getElementById('src_filter_group')); sortSelect(document.getElementById('filter_group'));resizeSelect(document.getElementById('src_filter_group'), document.getElementById('filter_group'), {select_size array=$data.group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_group_count">0</span> {t}Employee Groups Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				<tbody id="filter_branch_on" style="display:none" >
				<tr>
					<td class="{isvalid object="sf" label="branch" value="cellLeftEditTable"}" nowrap>
						<b>{t}Branches{/t}:</b><a href="javascript:toggleRowObject('filter_branch_on');toggleRowObject('filter_branch_off');filterCountSelect( 'filter_branch' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td colspan="3">
								{t}Selection Type{/t}:
								<select id="type_id" name="data[branch_selection_type_id]">
									{html_options options=$data.branch_selection_type_options selected=$data.branch_selection_type_id}
								</select>
							</td>
						</td>
						<tr class="tblHeader">
							<td>
								{t}UnSelected Branches{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Selected Branches{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_branch'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_branch'))">
								<br>
								<select name="src_branch_id" id="src_filter_branch" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_branch_options array2=$data.selected_branch_options}" multiple>
									{html_options options=$data.src_branch_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_branch'), document.getElementById('filter_branch')); uniqueSelect(document.getElementById('filter_branch')); sortSelect(document.getElementById('filter_branch'));resizeSelect(document.getElementById('src_filter_branch'), document.getElementById('filter_branch'), {select_size array=$data.src_branch_options array2=$data.selected_branch_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_branch'), document.getElementById('src_filter_branch')); uniqueSelect(document.getElementById('src_filter_branch')); sortSelect(document.getElementById('src_filter_branch'));resizeSelect(document.getElementById('src_filter_branch'), document.getElementById('filter_branch'), {select_size array=$data.src_branch_options array2=$data.selected_branch_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_branch'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_branch'))">
								<br>
								<select name="data[branch_ids][]" id="filter_branch" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_branch_options array2=$data.selected_branch_options}" multiple>
									{html_options options=$data.selected_branch_options selected=$data.branch_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_branch_off">
				<tr>
					<td class="{isvalid object="sf" label="branch" value="cellLeftEditTable"}" nowrap>
						<b>{t}Branches{/t}:</b><a href="javascript:toggleRowObject('filter_branch_on');toggleRowObject('filter_branch_off');uniqueSelect(document.getElementById('filter_branch'), document.getElementById('src_filter_branch')); sortSelect(document.getElementById('filter_branch'));resizeSelect(document.getElementById('src_filter_branch'), document.getElementById('filter_branch'), {select_size array=$data.branch_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_branch_count">0</span> {t}Branches Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				<tbody id="filter_department_on" style="display:none" >
				<tr>
					<td class="{isvalid object="sf" label="department" value="cellLeftEditTable"}" nowrap>
						<b>{t}Departments{/t}:</b><a href="javascript:toggleRowObject('filter_department_on');toggleRowObject('filter_department_off');filterCountSelect( 'filter_department' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td colspan="3">
								{t}Selection Type{/t}:
								<select id="type_id" name="data[department_selection_type_id]">
									{html_options options=$data.department_selection_type_options selected=$data.department_selection_type_id}
								</select>
							</td>
						</td>
						<tr class="tblHeader">
							<td>
								{t}UnSelected Departments{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Selected Departments{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_department'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_department'))">
								<br>
								<select name="src_department_id" id="src_filter_department" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_department_options array2=$data.selected_department_options}" multiple>
									{html_options options=$data.src_department_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_department'), document.getElementById('filter_department')); uniqueSelect(document.getElementById('filter_department')); sortSelect(document.getElementById('filter_department'));resizeSelect(document.getElementById('src_filter_department'), document.getElementById('filter_department'), {select_size array=$data.src_department_options array2=$data.selected_department_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_department'), document.getElementById('src_filter_department')); uniqueSelect(document.getElementById('src_filter_department')); sortSelect(document.getElementById('src_filter_department'));resizeSelect(document.getElementById('src_filter_department'), document.getElementById('filter_department'), {select_size array=$data.src_department_options array2=$data.selected_department_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_department'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_department'))">
								<br>
								<select name="data[department_ids][]" id="filter_department" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_department_options array2=$data.selected_department_options}" multiple>
									{html_options options=$data.selected_department_options selected=$data.department_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_department_off">
				<tr>
					<td class="{isvalid object="sf" label="department" value="cellLeftEditTable"}" nowrap>
						<b>{t}Departments{/t}:</b><a href="javascript:toggleRowObject('filter_department_on');toggleRowObject('filter_department_off');uniqueSelect(document.getElementById('filter_department'), document.getElementById('src_filter_department')); sortSelect(document.getElementById('filter_department'));resizeSelect(document.getElementById('src_filter_department'), document.getElementById('filter_department'), {select_size array=$data.department_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_department_count">0</span> {t}Departments Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				<tbody id="filter_include_user_on" style="display:none" >
				<tr>
					<td class="{isvalid object="sf" label="include_user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Include Employees{/t}:</b><a href="javascript:toggleRowObject('filter_include_user_on');toggleRowObject('filter_include_user_off');filterCountSelect( 'filter_include_user' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
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
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_include_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_include_user'))">
								<br>
								<select name="src_include_user_id" id="src_filter_include_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_include_user_options array2=$data.selected_include_user_options}" multiple>
									{html_options options=$data.src_include_user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_include_user'), document.getElementById('filter_include_user')); uniqueSelect(document.getElementById('filter_include_user')); sortSelect(document.getElementById('filter_include_user'));resizeSelect(document.getElementById('src_filter_include_user'), document.getElementById('filter_include_user'), {select_size array=$data.src_include_user_options array2=$data.selected_include_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_include_user'), document.getElementById('src_filter_include_user')); uniqueSelect(document.getElementById('src_filter_include_user')); sortSelect(document.getElementById('src_filter_include_user'));resizeSelect(document.getElementById('src_filter_include_user'), document.getElementById('filter_include_user'), {select_size array=$data.src_include_user_options array2=$data.selected_include_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_include_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_include_user'))">
								<br>
								<select name="data[include_user_ids][]" id="filter_include_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_include_user_options array2=$data.selected_include_user_options}" multiple>
									{html_options options=$data.selected_include_user_options selected=$data.include_user_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_include_user_off">
				<tr>
					<td class="{isvalid object="sf" label="include_user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Include Employees{/t}:</b><a href="javascript:toggleRowObject('filter_include_user_on');toggleRowObject('filter_include_user_off');uniqueSelect(document.getElementById('filter_include_user'), document.getElementById('src_filter_include_user')); sortSelect(document.getElementById('filter_include_user'));resizeSelect(document.getElementById('src_filter_include_user'), document.getElementById('filter_include_user'), {select_size array=$data.include_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_include_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				<tbody id="filter_exclude_user_on" style="display:none" >
				<tr>
					<td class="{isvalid object="sf" label="exclude_user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Exclude Employees{/t}:</b><a href="javascript:toggleRowObject('filter_exclude_user_on');toggleRowObject('filter_exclude_user_off');filterCountSelect( 'filter_exclude_user' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
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
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_exclude_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_exclude_user'))">
								<br>
								<select name="src_exclude_user_id" id="src_filter_exclude_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_exclude_user_options array2=$data.selected_exclude_user_options}" multiple>
									{html_options options=$data.src_exclude_user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_exclude_user'), document.getElementById('filter_exclude_user')); uniqueSelect(document.getElementById('filter_exclude_user')); sortSelect(document.getElementById('filter_exclude_user'));resizeSelect(document.getElementById('src_filter_exclude_user'), document.getElementById('filter_exclude_user'), {select_size array=$data.src_exclude_user_options array2=$data.selected_exclude_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_exclude_user'), document.getElementById('src_filter_exclude_user')); uniqueSelect(document.getElementById('src_filter_exclude_user')); sortSelect(document.getElementById('src_filter_exclude_user'));resizeSelect(document.getElementById('src_filter_exclude_user'), document.getElementById('filter_exclude_user'), {select_size array=$data.src_exclude_user_options array2=$data.selected_exclude_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_exclude_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_exclude_user'))">
								<br>
								<select name="data[exclude_user_ids][]" id="filter_exclude_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_exclude_user_options array2=$data.selected_exclude_user_options}" multiple>
									{html_options options=$data.selected_exclude_user_options selected=$data.exclude_user_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_exclude_user_off">
				<tr>
					<td class="{isvalid object="sf" label="exclude_user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Exclude Employees{/t}:</b><a href="javascript:toggleRowObject('filter_exclude_user_on');toggleRowObject('filter_exclude_user_off');uniqueSelect(document.getElementById('filter_exclude_user'), document.getElementById('src_filter_exclude_user')); sortSelect(document.getElementById('filter_exclude_user'));resizeSelect(document.getElementById('src_filter_exclude_user'), document.getElementById('filter_exclude_user'), {select_size array=$data.exclude_user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_exclude_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>
				{/if}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAllReportCriteria(); return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
