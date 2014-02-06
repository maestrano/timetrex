{include file="header.tpl" enable_calendar=true body_onload="getJobManualId(); getJobItemManualId();"}
<script language="JavaScript">
var jmido={js_array values=$pc_data.job_manual_id_options name="jmido" assoc=true}
var jimido={js_array values=$pc_data.job_item_manual_id_options name="jimido" assoc=true}

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

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$pcf->Validator->isValid() OR !$pf->Validator->isValid()}
					{include file="form_errors.tpl" object="pcf,pf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="pcf" label="user_id" value="cellLeftEditTable"}">					
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
									<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
									<br>
									<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
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

				<tr onClick="showHelpEntry('timestamp')">
					<td class="{isvalid object="pf" label="timestamp" value="cellLeftEditTable"}">
						{t}Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="12" id="time_stamp" name="pc_data[time_stamp]" value="{getdate type="TIME" epoch=$pc_data.time_stamp}">
						{t}ie:{/t} {$current_user_prefs->getTimeFormatExample()}
					</td>
				</tr>

				{if $pc_data.id != '' }
				<tr onClick="showHelpEntry('actual_time_stamp')">
					<td class="{isvalid object="pf" label="actual_time_stamp" value="cellLeftEditTable"}">
						{t}Actual Time:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="TIME" epoch=$pc_data.actual_time_stamp default=TRUE}
						{if $pc_data.actual_time_stamp != ''}
						<input type="hidden" id="actual_time_stamp" name="actual_time_stamp" value="{getdate type="TIME" epoch=$pc_data.actual_time_stamp}">
						<input type="button" value="Use Actual Time" onClick="javascript: document.getElementById('time_stamp').value = document.getElementById('actual_time_stamp').value">
						{/if}
					</td>
				</tr>
				{/if}

				<tr>
					<td class="{isvalid object="pcf" label="datestamp" value="cellLeftEditTable"}">
						{t}Start Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date_stamp" name="pc_data[start_date_stamp]" value="{getdate type="DATE" epoch=$pc_data.start_date_stamp}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date_stamp" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date_stamp', 'cal_start_date_stamp', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="pcf" label="datestamp" value="cellLeftEditTable"}">
						{t}End Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date_stamp" name="pc_data[end_date_stamp]" value="{getdate type="DATE" epoch=$pc_data.end_date_stamp}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date_stamp" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date_stamp', 'cal_end_date_stamp', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="pcf" label="datestamp" value="cellLeftEditTable"}">
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
									<input type="checkbox" class="checkbox" name="pc_data[dow][0]" value="1" {if $pc_data.dow.0 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="pc_data[dow][1]" value="1" {if $pc_data.dow.1 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="pc_data[dow][2]" value="1" {if $pc_data.dow.2 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="pc_data[dow][3]" value="1" {if $pc_data.dow.3 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="pc_data[dow][4]" value="1" {if $pc_data.dow.4 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="pc_data[dow][5]" value="1" {if $pc_data.dow.5 == TRUE}checked{/if}>
								</td>
								<td >
									<input type="checkbox" class="checkbox" name="pc_data[dow][6]" value="1" {if $pc_data.dow.6 == TRUE}checked{/if}>
								</td>
							</tr>
						</table>					
					</td>
				</tr>
							
				<tr onClick="showHelpEntry('disable_rounding')">
					<td class="{isvalid object="pcf" label="disable_rounding" value="cellLeftEditTable"}">
						{t}Disable Rounding:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="pc_data[disable_rounding]" value="1">
					</td>
				</tr>

				<tr onClick="showHelpEntry('punch_type')">
					<td class="{isvalid object="pf" label="punch_type" value="cellLeftEditTable"}">
						{t}Punch Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="pc_data[type_id]">
							{html_options options=$pc_data.type_options selected=$pc_data.type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="pf" label="status" value="cellLeftEditTable"}">
						{t}In/Out:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="pc_data[status_id]">
							{html_options options=$pc_data.status_options selected=$pc_data.status_id}
						</select>
					</td>
				</tr>

				{if count($pc_data.branch_options) > 1 OR $pc_data.branch_id != 0}
				<tr onClick="showHelpEntry('branch')">
					<td class="{isvalid object="pcf" label="branch" value="cellLeftEditTable"}">
						{t}Branch:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="branch_id" name="pc_data[branch_id]">
							{html_options options=$pc_data.branch_options selected=$pc_data.branch_id}
						</select>
					</td>
				</tr>
				{/if}

				{if count($pc_data.department_options) > 1 OR $pc_data.department_id != 0}
				<tr onClick="showHelpEntry('department')">
					<td class="{isvalid object="pcf" label="department" value="cellLeftEditTable"}">
						{t}Department:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="department_id" name="pc_data[department_id]">
							{html_options options=$pc_data.department_options selected=$pc_data.department_id}
						</select>
					</td>
				</tr>
				{/if}

				{if $permission->Check('job','enabled') }
				{if count($pc_data.job_options) > 1 OR $pc_data.job_id != 0}
				<tr onClick="showHelpEntry('job')">
					<td class="{isvalid object="pcf" label="job" value="cellLeftEditTable"}">
						{t}Job:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="4" id="quick_job_id" onKeyUp="selectJobOption();">
						<select id="job_id" name="pc_data[job_id]" onChange="getJobManualId();">
							{html_options options=$pc_data.job_options selected=$pc_data.job_id}
						</select>
					</td>
				</tr>
				{/if}

				{if count($pc_data.job_item_options) > 1 OR $pc_data.job_item_id != 0}
				<tr onClick="showHelpEntry('job_item')">
					<td class="{isvalid object="pcf" label="job_item" value="cellLeftEditTable"}">
						{t}Task:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="4" id="quick_job_item_id" onKeyUp="selectJobItemOption();">
						<select id="job_item_id" name="pc_data[job_item_id]" onChange="getJobItemManualId();">
							{html_options options=$pc_data.job_item_options selected=$pc_data.job_item_id}
						</select>
					</td>
				</tr>
				{/if}

				{if (count($data.job_options) > 1 OR $pc_data.job_id != 0 )
						OR ( count($data.job_item_options) > 1 OR $pc_data.job_item_id != 0 ) }
				<tr onClick="showHelpEntry('quantity')">
					<td class="{isvalid object="pcf" label="quantity" value="cellLeftEditTable"}">
						{t}Quantity:{/t}
					</td>
					<td class="cellRightEditTable">
						<b>{t}Good:{/t} <input type="text" size="4" name="pc_data[quantity]" value="{$pc_data.quantity}"> / Bad: <input type="text" size="4" name="pc_data[bad_quantity]" value="{$pc_data.bad_quantity}"></b>
					</td>
				</tr>
				{/if}

				{/if}

				{if isset($pc_data.other_field_names.other_id1) }
					<tr onClick="showHelpEntry('other_id1')">
						<td class="{isvalid object="jf" label="other_id1" value="cellLeftEditTable"}">
							{$pc_data.other_field_names.other_id1}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="pc_data[other_id1]" value="{$pc_data.other_id1}">
						</td>
					</tr>
				{/if}

				{if isset($pc_data.other_field_names.other_id2) }
				<tr onClick="showHelpEntry('other_id2')">
					<td class="{isvalid object="jf" label="other_id2" value="cellLeftEditTable"}">
						{$pc_data.other_field_names.other_id2}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="pc_data[other_id2]" value="{$pc_data.other_id2}">
					</td>
				</tr>
				{/if}
				{if isset($pc_data.other_field_names.other_id3) }
				<tr onClick="showHelpEntry('other_id3')">
					<td class="{isvalid object="jf" label="other_id3" value="cellLeftEditTable"}">
						{$pc_data.other_field_names.other_id3}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="pc_data[other_id3]" value="{$pc_data.other_id3}">
					</td>
				</tr>
				{/if}
				{if isset($pc_data.other_field_names.other_id4) }
					<tr onClick="showHelpEntry('other_id4')">
						<td class="{isvalid object="jf" label="other_id4" value="cellLeftEditTable"}">
							{$pc_data.other_field_names.other_id4}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="pc_data[other_id4]" value="{$pc_data.other_id4}">
						</td>
					</tr>
				{/if}
				{if isset($pc_data.other_field_names.other_id5) }
					<tr onClick="showHelpEntry('other_id5')">
						<td class="{isvalid object="jf" label="other_id5" value="cellLeftEditTable"}">
							{$pc_data.other_field_names.other_id5}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="pc_data[other_id5]" value="{$pc_data.other_id5}">
						</td>
					</tr>
				{/if}

				<tr onClick="showHelpEntry('note')">
					<td class="{isvalid object="pcf" label="note" value="cellLeftEditTable"}">
						{t}Note:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="2" cols="30" name="pc_data[note]">{$pc_data.note|escape}</textarea>
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