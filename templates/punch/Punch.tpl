{include file="sm_header.tpl" enable_ajax=TRUE body_onload="fixHeight(); punchTransfer(); TIMETREX.punch.getJobManualId(); TIMETREX.punch.getJobItemManualId(); TIMETREX.punch.showJobItem( false );"}
<script	language=JavaScript>
var jmido={js_array values=$data.job_manual_id_options name="jmido" assoc=true}
var jimido={js_array values=$data.job_item_manual_id_options name="jimido" assoc=true}

{literal}
function fixHeight() {
	resizeWindowToFit(document.getElementById('body'), 'height', 55);
}

function punchTransfer() {
	if(document.getElementById) {
			if ( document.getElementById('transfer').checked == true ) {
				document.getElementById('status_id').value = 10;
				document.getElementById('status_id').disabled = true;
				document.getElementById('type_id').value = 10;
				document.getElementById('type_id').disabled = true;
			} else {
				document.getElementById('status_id').value = document.getElementById('original_status_id').value;
				document.getElementById('status_id').disabled = false;
				document.getElementById('type_id').value = document.getElementById('original_type_id').value;
				document.getElementById('type_id').disabled = false;
			}
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

		<form method="post" name="punch" action="{$smarty.server.SCRIPT_NAME}">
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
						{$data.user_full_name}
					</td>
				</tr>

				{if $station_is_allowed == TRUE}
					<tr onClick="showHelpEntry('timestamp')">
						<td class="{isvalid object="pf" label="timestamp" value="cellLeftEditTable"}">
							{t}Time:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="10" name="data[time_stamp]" value="{getdate type="TIME" epoch=$data.time_stamp}" disabled>
							{t}ie:{/t} {$current_user_prefs->getTimeFormatExample()}
							<input type="hidden" name="data[time_stamp]" value="{$data.time_stamp}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('date_stamp')">
						<td class="{isvalid object="pcf" label="datestamp" value="cellLeftEditTable"}">
							{t}Date:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="15" id="date_stamp" name="data[date_stamp]" value="{getdate type="DATE" epoch=$data.date_stamp}" disabled>
							{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
						</td>
					</tr>

					{if $permission->Check('punch','edit_transfer') OR $permission->Check('punch','default_transfer')}
					<tr onClick="showHelpEntry('transfer')">
						<td class="{isvalid object="pf" label="transfer" value="cellLeftEditTable"}">
							{t}Transfer:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" id="transfer" class="checkbox" name="data[transfer]" value="1" onChange="punchTransfer();" {if $data.transfer == TRUE}checked{/if}>
						</td>
					</tr>
					{/if}

					<tr onClick="showHelpEntry('punch_type')">
						<td class="{isvalid object="pf" label="punch_type" value="cellLeftEditTable"}">
							{t}Punch Type:{/t}
						</td>
						<td class="cellRightEditTable">
							<select id="type_id" name="data[type_id]">
								{html_options options=$data.type_options selected=$data.type_id}
							</select>
							<input type="hidden" id="original_type_id" value="{$data.type_id}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('status')">
						<td class="{isvalid object="pf" label="status" value="cellLeftEditTable"}">
							{t}In/Out:{/t}
						</td>
						<td class="cellRightEditTable">
							<select id="status_id" name="data[status_id]">
								{html_options options=$data.status_options selected=$data.status_id}
							</select>
							<input type="hidden" id="original_status_id" value="{$data.status_id}">
						</td>
					</tr>

					{if count($data.branch_options) > 1 AND $permission->Check('punch','edit_branch') }
					<tr onClick="showHelpEntry('branch')">
						<td class="{isvalid object="pcf" label="branch" value="cellLeftEditTable"}">
							{t}Branch:{/t}
						</td>
						<td class="cellRightEditTable">
							<select id="branch_id" name="data[branch_id]">
								{html_options options=$data.branch_options selected=$data.branch_id}
							</select>
						</td>
					</tr>
					{else}
						<input type="hidden" name="data[branch_id]" value="{$data.branch_id}">
					{/if}

					{if count($data.department_options) > 1 AND $permission->Check('punch','edit_department')}
					<tr onClick="showHelpEntry('department')">
						<td class="{isvalid object="pcf" label="department" value="cellLeftEditTable"}">
							{t}Department:{/t}
						</td>
						<td class="cellRightEditTable">
							<select id="department_id" name="data[department_id]">
								{html_options options=$data.department_options selected=$data.department_id}
							</select>
						</td>
					</tr>
					{else}
						<input type="hidden" name="data[department_id]" value="{$data.department_id}">
					{/if}

					{if $permission->Check('job','enabled') }
						{if count($data.job_options) > 1 AND $permission->Check('punch','edit_job')}
						<tr onClick="showHelpEntry('job')">
							<td class="{isvalid object="pcf" label="job" value="cellLeftEditTable"}">
								{t}Job:{/t}
							</td>
							<td class="cellRightEditTable">
								<input type="text" size="4" id="quick_job_id" onKeyUp="TIMETREX.punch.selectJobOption();">
								<select id="job_id" name="data[job_id]" onChange="TIMETREX.punch.getJobManualId(); TIMETREX.punch.showJobItem( false );">
									{html_options options=$data.job_options selected=$data.job_id}
								</select>
							</td>
						</tr>
						{/if}

						{if count($data.job_options) > 1 AND count($data.job_item_options) > 1 AND $permission->Check('punch','edit_job_item')}
						<tr onClick="showHelpEntry('job_item')">
							<td class="{isvalid object="pcf" label="job_item" value="cellLeftEditTable"}">
								{t}Task:{/t}
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

						{if ( count($data.job_options) > 1 OR count($data.job_item_options) > 1 )
								AND ( $permission->Check('punch','edit_quantity') OR $permission->Check('punch','edit_bad_quantity') )}
						<tr onClick="showHelpEntry('quantity')">
							<td class="{isvalid object="pcf" label="quantity" value="cellLeftEditTable"}">
								{t}Quantity:{/t}
							</td>
							<td class="cellRightEditTable">
								{if $permission->Check('punch','edit_quantity')}<b>{t}Good:{/t} <input type="text" size="4" name="data[quantity]" value="{$data.quantity}">{/if} {if $permission->Check('punch','edit_quantity') AND $permission->Check('punch','edit_bad_quantity')}/{/if} {if $permission->Check('punch','edit_bad_quantity')}{t}Bad{/t}: <input type="text" size="4" name="data[bad_quantity]" value="{$data.bad_quantity}"></b>{/if}
							</td>
						</tr>
						{/if}
					{/if}

					{if isset($data.other_field_names.other_id1) AND $permission->Check('punch','edit_other_id1')}
						<tr onClick="showHelpEntry('other_id1')">
							<td class="{isvalid object="pcf" label="other_id1" value="cellLeftEditTable"}">
								{$data.other_field_names.other_id1}:
							</td>
							<td class="cellRightEditTable">
								<input type="text" name="data[other_id1]" value="{$data.other_id1}">
							</td>
						</tr>
					{/if}

					{if isset($data.other_field_names.other_id2) AND $permission->Check('punch','edit_other_id2')}
					<tr onClick="showHelpEntry('other_id2')">
						<td class="{isvalid object="pcf" label="other_id2" value="cellLeftEditTable"}">
							{$data.other_field_names.other_id2}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="data[other_id2]" value="{$data.other_id2}">
						</td>
					</tr>
					{/if}
					{if isset($data.other_field_names.other_id3) AND $permission->Check('punch','edit_other_id3')}
					<tr onClick="showHelpEntry('other_id3')">
						<td class="{isvalid object="pcf" label="other_id3" value="cellLeftEditTable"}">
							{$data.other_field_names.other_id3}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="data[other_id3]" value="{$data.other_id3}">
						</td>
					</tr>
					{/if}
					{if isset($data.other_field_names.other_id4) AND $permission->Check('punch','edit_other_id4')}
						<tr onClick="showHelpEntry('other_id4')">
							<td class="{isvalid object="pcf" label="other_id4" value="cellLeftEditTable"}">
								{$data.other_field_names.other_id4}:
							</td>
							<td class="cellRightEditTable">
								<input type="text" name="data[other_id4]" value="{$data.other_id4}">
							</td>
						</tr>
					{/if}
					{if isset($data.other_field_names.other_id5) AND $permission->Check('punch','edit_other_id5') }
						<tr onClick="showHelpEntry('other_id5')">
							<td class="{isvalid object="pcf" label="other_id5" value="cellLeftEditTable"}">
								{$data.other_field_names.other_id5}:
							</td>
							<td class="cellRightEditTable">
								<input type="text" name="data[other_id5]" value="{$data.other_id5}">
							</td>
						</tr>
					{/if}

					{if $permission->Check('punch','edit_note')}
					<tr onClick="showHelpEntry('note')">
						<td class="{isvalid object="pcf" label="note" value="cellLeftEditTable"}">
							{t}Note:{/t}
						</td>
						<td class="cellRightEditTable">
							<textarea rows="1" cols="30" name="data[note]">{$data.note|escape}</textarea>
						</td>
					</tr>
					{/if}

				{else}
					<tr id="error">
						<td colspan="2">
							{t}You are not authorized to punch in or out from this station!{/t}
						</td>
					</tr>
				{/if}
			</table>
		</div>
		{if $station_is_allowed == TRUE}
		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>
		{/if}

		<input type="hidden" name="data[punch_control_id]" value="{$data.punch_control_id}">
		<input type="hidden" name="data[user_date_id]" value="{$data.user_date_id}">
		<input type="hidden" name="data[user_id]" value="{$data.user_id}">
		</form>
	</div>
</div>
{include file="sm_footer.tpl"}