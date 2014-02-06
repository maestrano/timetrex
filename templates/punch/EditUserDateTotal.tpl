{include file="sm_header.tpl" body_onload="fixHeight(); showPolicy();"}

<script	language=JavaScript>

{literal}
function fixHeight() {
	resizeWindowToFit(document.getElementById('body'), 'height', 65);
}

function showPolicy() {
	document.getElementById('absence_policy').style.display = 'none';
	document.getElementById('over_time_policy').style.display = 'none';
	document.getElementById('premium_policy').style.display = 'none';
	document.getElementById('meal_policy').style.display = 'none';

	if ( document.getElementById('status_id').value == 10 ) {
		document.getElementById('type').className = '';
		document.getElementById('type').style.display = '';

		if ( document.getElementById('type_id').value == 30 ) { //OT
			document.getElementById('over_time_policy').className = '';
			document.getElementById('over_time_policy').style.display = '';
		} else if ( document.getElementById('type_id').value == 40 ) { //Prem
			document.getElementById('premium_policy').className = '';
			document.getElementById('premium_policy').style.display = '';
		} else if ( document.getElementById('type_id').value == 100 ) { //Lunch
			document.getElementById('meal_policy').className = '';
			document.getElementById('meal_policy').style.display = '';
		}
	} else if (document.getElementById('status_id').value == 20) {
		document.getElementById('type').style.display = 'none';
		document.getElementById('type_id').value = 10;
	} else if (document.getElementById('status_id').value == 30) {
		document.getElementById('type').style.display = 'none';
		document.getElementById('type_id').value = 10;

		document.getElementById('absence_policy').className = '';
		document.getElementById('absence_policy').style.display = '';
	}
}

function setOverride() {
	if ( document.getElementById('status_id').value == 10 || document.getElementById('punch_control_id').value > 0 ) {
		document.getElementById('override').checked = true;
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
				{if !$udtf->Validator->isValid()}
					{include file="form_errors.tpl" object="udtf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$udt_data.user_full_name}
						<input type="hidden" name="udt_data[user_full_name]" value="{$udt_data.user_full_name}">
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Date:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="DATE" epoch=$udt_data.date_stamp}
						<input type="hidden" name="udt_data[date_stamp]" value="{$udt_data.date_stamp}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('total_time')">
					<td class="{isvalid object="udtf" label="total_time" value="cellLeftEditTable"}">
						{t}Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="udt_data[total_time]" value="{gettimeunit value=$udt_data.total_time}" onChange="setOverride()">
						{t}ie:{/t} {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="udtf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="udt_data[status_id]" onChange="showPolicy()">
							{html_options options=$udt_data.status_options selected=$udt_data.status_id}
						</select>
					</td>
				</tr>

				<tbody id="type" style="display:none" >
				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="udtf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="udt_data[type_id]" onChange="showPolicy()">
							{html_options options=$udt_data.type_options selected=$udt_data.type_id}
						</select>
					</td>
				</tr>
				</tbody>

				<tbody id="absence_policy" style="display:none" >
				<tr onClick="showHelpEntry('absence_policy')">
					<td class="{isvalid object="udtf" label="absence_policy" value="cellLeftEditTable"}">
						{t}Absence Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="udt_data[absence_policy_id]">
							{html_options options=$udt_data.absence_policy_options selected=$udt_data.absence_policy_id}
						</select>
					</td>
				</tr>

				<tbody id="over_time_policy" style="display:none" >
				<tr onClick="showHelpEntry('over_time_policy')">
					<td class="{isvalid object="udtf" label="over_time_policy" value="cellLeftEditTable"}">
						{t}Overtime Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="udt_data[over_time_policy_id]">
							{html_options options=$udt_data.over_time_policy_options selected=$udt_data.over_time_policy_id}
						</select>
					</td>
				</tr>
				</tbody>

				<tbody id="premium_policy" style="display:none" >
				<tr onClick="showHelpEntry('premium_policy')">
					<td class="{isvalid object="udtf" label="premium_policy" value="cellLeftEditTable"}">
						{t}Premium Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="udt_data[premium_policy_id]">
							{html_options options=$udt_data.premium_policy_options selected=$udt_data.premium_policy_id}
						</select>
					</td>
				</tr>
				</tbody>

				<tbody id="meal_policy" style="display:none" >
				<tr onClick="showHelpEntry('meal_policy')">
					<td class="{isvalid object="udtf" label="meal_policy" value="cellLeftEditTable"}">
						{t}Meal Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="udt_data[meal_policy_id]">
							{html_options options=$udt_data.meal_policy_options selected=$udt_data.meal_policy_id}
						</select>
					</td>
				</tr>
				</tbody>

				<tr onClick="showHelpEntry('branch')">
					<td class="{isvalid object="udtf" label="branch" value="cellLeftEditTable"}">
						{t}Branch:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="branch_id" name="udt_data[branch_id]">
							{html_options options=$udt_data.branch_options selected=$udt_data.branch_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('department')">
					<td class="{isvalid object="udtf" label="department" value="cellLeftEditTable"}">
						{t}Department:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="department_id" name="udt_data[department_id]">
							{html_options options=$udt_data.department_options selected=$udt_data.department_id}
						</select>
					</td>
				</tr>

				{if $permission->Check('job','enabled') }
				<tr onClick="showHelpEntry('job')">
					<td class="{isvalid object="udtf" label="job" value="cellLeftEditTable"}">
						{t}Job:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="job_id" name="udt_data[job_id]">
							{html_options options=$udt_data.job_options selected=$udt_data.job_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('job_item')">
					<td class="{isvalid object="udtf" label="job_item" value="cellLeftEditTable"}">
						{t}Task:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="job_item_id" name="udt_data[job_item_id]">
							{html_options options=$udt_data.job_item_options selected=$udt_data.job_item_id}
						</select>
					</td>
				</tr>
				<tr onClick="showHelpEntry('quantity')">
					<td class="{isvalid object="udtf" label="quantity" value="cellLeftEditTable"}">
						{t}Quantity:{/t}
					</td>
					<td class="cellRightEditTable">
						<b>{t}Good:{/t} <input type="text" size="4" name="udt_data[quantity]" value="{$udt_data.quantity}"> / Bad: <input type="text" size="4" name="udt_data[bad_quantity]" value="{$udt_data.bad_quantity}"></b>
					</td>
				</tr>
				{/if}

				<tr onClick="showHelpEntry('override')">
					<td class="{isvalid object="udtf" label="override" value="cellLeftEditTable"}">
						{t}Override:{/t}
					</td>
					<td class="cellRightEditTable">
						<input id="override" type="checkbox" class="checkbox" name="udt_data[override]" value="1" {if $udt_data.override == TRUE}checked{/if}> {t}(Must override to modify){/t}
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" id="udt_id" name="udt_data[id]" value="{$udt_data.id}">
		<input type="hidden" name="udt_data[user_id]" value="{$udt_data.user_id}">
		<input type="hidden" id="punch_control_id" name="udt_data[punch_control_id]" value="{$udt_data.punch_control_id}">
		<input type="hidden" name="udt_data[user_date_id]" value="{$udt_data.user_date_id}">
		</form>
	</div>
</div>
{include file="sm_footer.tpl"}