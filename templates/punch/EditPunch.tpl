{include file="sm_header.tpl" enable_ajax=TRUE enable_calendar=true body_onload="fixHeight(); TIMETREX.punch.getJobManualId(); TIMETREX.punch.getJobItemManualId(); TIMETREX.punch.showJobItem( true ); ResizeTextArea( document.getElementById('note'), 1, 4 ); document.getElementById('time_stamp').select()"}
<script language="JavaScript">
var jmido={js_array values=$pc_data.job_manual_id_options name="jmido" assoc=true}
var jimido={js_array values=$pc_data.job_item_manual_id_options name="jimido" assoc=true}

{literal}
function fixHeight() {
	resizeWindowToFit( document.getElementById('body'), 'both', 45);
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

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$pcf->Validator->isValid() OR !$pf->Validator->isValid()}
					{include file="form_errors.tpl" object="pcf,pf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$pc_data.user_full_name}
					</td>
				</tr>

				<tr onClick="showHelpEntry('timestamp')">
					<td class="{isvalid object="pf" label="timestamp" value="cellLeftEditTable"}">
						{t}Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="12" id="time_stamp" name="pc_data[time_stamp]" value="{getdate type="TIME" epoch=$pc_data.time_stamp}">
						{if $pc_data.id != '' }
							({t}Actual Time:{/t} {getdate type="TIME" epoch=$pc_data.actual_time_stamp default=TRUE}{if $pc_data.actual_time_stamp != ''}<img valign="middle" id="cursor-hand" src="{$IMAGES_URL}copy16.gif" onClick="javascript: document.getElementById('time_stamp').value = document.getElementById('actual_time_stamp').value"><input type="hidden" id="actual_time_stamp" name="actual_time_stamp" value="{getdate type="TIME" epoch=$pc_data.actual_time_stamp}">{/if})
						{else}
							{t}ie:{/t} {$current_user_prefs->getTimeFormatExample()}
						{/if}
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="pcf" label="datestamp" value="cellLeftEditTable"}">
						{t}Date:{/t} <a href="javascript:toggleRowObject('repeat');toggleImage(document.getElementById('repeat_img'), '{$IMAGES_URL}/nav_bottom_sm.gif', '{$IMAGES_URL}/nav_top_sm.gif');"><img style="vertical-align: middle" id="repeat_img" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="date_stamp" name="pc_data[date_stamp]" value="{getdate type="DATE" epoch=$pc_data.date_stamp}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_date_stamp" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('date_stamp', 'cal_date_stamp', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>
				<tbody id="repeat" style="display:none">
				<tr>
					<td class="{isvalid object="pcf" label="repeat" value="cellLeftEditTable"}">
						{t}Repeat Punch for:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="3" id="time_stamp" name="pc_data[repeat]" value="0"> {t}day(s) after above date.{/t}
					</td>
				</tr>
				</tbody>
				<tr onClick="showHelpEntry('punch_type')">
					<td class="{isvalid object="pf" label="punch_type" value="cellLeftEditTable"}">
						{t}Punch Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="pc_data[type_id]">
							{html_options options=$pc_data.type_options selected=$pc_data.type_id}
						</select>
						{t}Disable Rounding:{/t} <input type="checkbox" class="checkbox" name="pc_data[disable_rounding]" value="1">
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
							<input type="text" size="4" id="quick_job_id" onKeyUp="TIMETREX.punch.selectJobOption();">
							<select id="job_id" name="pc_data[job_id]" onChange="TIMETREX.punch.getJobManualId(); TIMETREX.punch.showJobItem( true );">
								{html_options options=$pc_data.job_options selected=$pc_data.job_id}
							</select>
						</td>
					</tr>
					{/if}

					{if count($pc_data.job_options) > 1 AND ( count($pc_data.job_item_options) > 1 OR $pc_data.job_item_id != 0) }
					<tr onClick="showHelpEntry('job_item')">
						<td class="{isvalid object="pcf" label="job_item" value="cellLeftEditTable"}">
							{t}Task:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="4" id="quick_job_item_id" onKeyUp="TIMETREX.punch.selectJobItemOption();">
							<select id="job_item_id" name="pc_data[job_item_id]" onChange="TIMETREX.punch.getJobItemManualId();">
								{* {html_options options=$pc_data.job_item_options selected=$pc_data.job_item_id} *}
							</select>
							<input type="hidden" id="selected_job_item" value="{$pc_data.job_item_id}">
						</td>
					</tr>
					{/if}

					{if ( count($pc_data.job_options) > 1 OR $pc_data.job_id != 0 )
							OR ( count($pc_data.job_item_options) > 1 OR $pc_data.job_item_id != 0 ) }
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
						<textarea rows="1" cols="30" name="pc_data[note]" id="note" onKeyUp="ResizeTextArea( document.getElementById('note'), 1, 4 )">{$pc_data.note|escape}</textarea>
					</td>
				</tr>

				{if $pc_data.id != '' }
					<tr onClick="showHelpEntry('station')">
						<td class="{isvalid object="pf" label="station" value="cellLeftEditTable"}">
							{t}Station:{/t} <a href="javascript:toggleRowObject('station');toggleImage(document.getElementById('station_img'), '{$IMAGES_URL}/nav_bottom_sm.gif', '{$IMAGES_URL}/nav_top_sm.gif');"><img style="vertical-align: middle" id="station_img" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTable">
							{assign var="station_id" value=$pc_data.station_data.id}
							{if $station_id != '' AND $permission->Check('station','edit')}<a href="#" onClick="window.opener.location='{urlbuilder script="../station/EditStation.php" values="id=$station_id" merge="FALSE"}';">{/if}
							<b>{$pc_data.station_data.type|default:"N/A"}</b>
							{if $pc_data.station_data.type != ''} - {$pc_data.station_data.description}{/if}
							{if $permission->Check('station','edit')}</a>{/if}
						</td>
					</tr>
				{/if}

				<tbody id="station" style="display:none">
				{if $pc_data.longitude != '' AND $pc_data.latitude != '' }
				<tr onClick="showHelpEntry('created_by')">
					<td class="cellLeftEditTable">
						{t}Location:{/t}
					</td>
					<td class="cellRightEditTable">
						<a href="http://maps.google.com/maps?f=q&hl=en&geocode=&q={$pc_data.latitude},{$pc_data.longitude}&ll={$pc_data.latitude},{$pc_data.longitude}&ie=UTF8&z=16&om=1" target="_blank">{t}Latitude{/t}: {$pc_data.latitude} {t}Longitude{/t}: {$pc_data.longitude}</a>
					</td>
				</tr>
				{/if}
				{if $pc_data.created_date != ''}
				<tr onClick="showHelpEntry('created_by')">
					<td class="cellLeftEditTable">
						{t}Created By:{/t}
					</td>
					<td class="cellRightEditTable">
						{$pc_data.created_by_name|default:"N/A"} @ {getdate type="DATE+TIME" epoch=$pc_data.created_date default=TRUE}
					</td>
				</tr>
				{/if}
				{if $pc_data.updated_date != ''}
				<tr onClick="showHelpEntry('updated_by')">
					<td class="cellLeftEditTable">
						{t}Updated By:{/t}
					</td>
					<td class="cellRightEditTable">
						{$pc_data.updated_by_name|default:"N/A"} @ {getdate type="DATE+TIME" epoch=$pc_data.updated_date default=TRUE}
					</td>
				</tr>
				{/if}
				</tbody>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
			{if $pc_data.punch_id != '' AND ( $permission->Check('punch','delete') OR $permission->Check('punch','delete_own') OR $permission->Check('punch','delete_child') ) }
			<input type="submit" class="btnSubmit" name="action:delete" value="{t}Delete{/t}" onClick="return singleSubmitHandler(this)">
			{/if}
		</div>

		<input type="hidden" name="pc_data[punch_id]" value="{$pc_data.punch_id}">
		<input type="hidden" name="pc_data[id]" value="{$pc_data.id}">
		<input type="hidden" name="pc_data[user_id]" value="{$pc_data.user_id}">
		<input type="hidden" name="pc_data[user_date_id]" value="{$pc_data.user_date_id}">
		<input type="hidden" name="pc_data[user_full_name]" value="{$pc_data.user_full_name}">
		</form>
	</div>
</div>
{include file="sm_footer.tpl"}