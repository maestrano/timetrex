{include file="header.tpl" body_onload="showType(); showAverageDays();"}
<script	language=JavaScript>
{literal}
function showType() {
	document.getElementById('type_id-10_and_20').style.display = 'none';
	document.getElementById('type_id-10_and_20_minimum_time').disabled = true;
	document.getElementById('type_id-20').style.display = 'none';
	document.getElementById('type_id-30').style.display = 'none';

	if ( document.getElementById('type_id').value == 10 ) {
		document.getElementById('type_id-10_and_20_minimum_time').disabled = false;
		document.getElementById('type_id-10_and_20').className = '';
		document.getElementById('type_id-10_and_20').style.display = '';
	} else if (document.getElementById('type_id').value == 20) {
		document.getElementById('type_id-10_and_20_minimum_time').disabled = false;
		document.getElementById('type_id-10_and_20').className = '';
		document.getElementById('type_id-10_and_20').style.display = '';

		document.getElementById('type_id-20').className = '';
		document.getElementById('type_id-20').style.display = '';
	} else if (document.getElementById('type_id').value == 30) {
		document.getElementById('type_id-20').className = '';
		document.getElementById('type_id-20').style.display = '';

		document.getElementById('type_id-30').className = '';
		document.getElementById('type_id-30').style.display = '';
	}
}

function showAverageDays() {
	document.getElementById('average_days').disabled = false;
	if ( document.getElementById('average_time_worked_days').checked == true ) {
		document.getElementById('average_days').disabled = true;
		document.getElementById('average_days').value = 0;
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
				{if !$hpf->Validator->isValid()}
					{include file="form_errors.tpl" object="hpf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="hpf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="hpf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]" onChange="showType();">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('default_schedule_status')">
					<td class="{isvalid object="hpf" label="default_schedule_status" value="cellLeftEditTable"}">
						{t}Default Schedule Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[default_schedule_status_id]">
							{html_options options=$data.schedule_status_options selected=$data.default_schedule_status_id}
						</select>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2" >
						{t}Holiday Eligibility{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_employed_days')">
					<td class="{isvalid object="hpf" label="minimum_employed_days" value="cellLeftEditTable"}">
						{t}Minimum Employed Days:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="6" type="text" name="data[minimum_employed_days]" value="{$data.minimum_employed_days}">
					</td>
				</tr>

				<tbody id="type_id-20" {if $data.type_id != 20}style="display:none"{/if}>
				<tr onClick="showHelpEntry('minimum_worked_days')">
					<td class="{isvalid object="hpf" label="minimum_worked_days" value="cellLeftEditTable"}">
						{t}Employee Must Work at Least:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[minimum_worked_days]" value="{$data.minimum_worked_days}">
						{t}of the:{/t} <input size="3" type="text" name="data[minimum_worked_period_days]" value="{$data.minimum_worked_period_days}">
						<select id="worked_scheduled_days" name="data[worked_scheduled_days]">
							{html_options options=$data.scheduled_day_options selected=$data.worked_scheduled_days}
						</select>
						{t}prior to the holiday{/t}.
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_worked_after_days')">
					<td class="{isvalid object="hpf" label="minimum_worked_after_days" value="cellLeftEditTable"}">
						{t}Employee Must Work at Least:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[minimum_worked_after_days]" value="{$data.minimum_worked_after_days}">
						{t}of the:{/t} <input size="3" type="text" name="data[minimum_worked_after_period_days]" value="{$data.minimum_worked_after_period_days}">
						<select id="worked_after_scheduled_days" name="data[worked_after_scheduled_days]">
							{html_options options=$data.scheduled_day_options selected=$data.worked_after_scheduled_days}
						</select>
						{t}following the holiday{/t}.
					</td>
				</tr>
				</tbody>

				<tr class="tblHeader">
					<td colspan="2" >
						{t}Holiday Time Calculation{/t}
					</td>
				</tr>

				<tbody id="type_id-30" {if $data.type_id != 30}style="display:none"{/if}>
				<tr onClick="showHelpEntry('average_time_days')">
					<td class="{isvalid object="hpf" label="average_time_days" value="cellLeftEditTable"}">
						{t}Total Time over:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[average_time_days]" value="{$data.average_time_days}"> {t}days{/t}
					</td>
				</tr>
				<tr onClick="showHelpEntry('average_days')">
					<td class="{isvalid object="hpf" label="average_days" value="cellLeftEditTable"}">
						{t}Average Time over:{/t}
					</td>
					<td class="cellRightEditTable">
						{t}Worked Days Only:{/t} <input type="checkbox" class="checkbox" id="average_time_worked_days" name="data[average_time_worked_days]" value="1" onclick="showAverageDays()"{if $data.average_time_worked_days == TRUE}checked{/if}> {t}or{/t}
						<input size="3" type="text" id="average_days" name="data[average_days]" value="{$data.average_days}"> {t}days{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_time')">
					<td class="{isvalid object="hpf" label="minimum_time" value="cellLeftEditTable"}">
						{t}Minimum Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="8" type="text" name="data[minimum_time]" value="{gettimeunit value=$data.minimum_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('maximum_time')">
					<td class="{isvalid object="hpf" label="maximum_time" value="cellLeftEditTable"}">
						{t}Maximum Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="8" type="text" name="data[maximum_time]" value="{gettimeunit value=$data.maximum_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('force_over_time_policy')">
					<td class="{isvalid object="hpf" label="force_over_time_policy" value="cellLeftEditTable"}">
						{t}Always Apply Over Time/Premium Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[force_over_time_policy]" value="1"{if $data.force_over_time_policy == TRUE}checked{/if}> {t}(Even if they are not eligible for holiday pay){/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('include_over_time')">
					<td class="{isvalid object="hpf" label="include_over_time" value="cellLeftEditTable"}">
						{t}Include Over Time in Average:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[include_over_time]" value="1"{if $data.include_over_time == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('include_paid_absence_time')">
					<td class="{isvalid object="hpf" label="include_paid_absence_time" value="cellLeftEditTable"}">
						{t}Include Paid Absence Time in Average:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[include_paid_absence_time]" value="1"{if $data.include_paid_absence_time == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('round_interval_policy')">
					<td class="{isvalid object="hpf" label="round_interval_policy" value="cellLeftEditTable"}">
						{t}Rounding Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="round_interval_policy_id" name="data[round_interval_policy_id]">
							{html_options options=$data.round_interval_options selected=$data.round_interval_policy_id}
						</select>
					</td>
				</tr>
				</tbody>

				<tbody id="type_id-10_and_20" {if $data.type_id == 30}style="display:none"{/if}>
				<tr onClick="showHelpEntry('minimum_time')">
					<td class="{isvalid object="hpf" label="minimum_time" value="cellLeftEditTable"}">
						{t}Holiday Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input id="type_id-10_and_20_minimum_time" size="8" type="text" name="data[minimum_time]" value="{gettimeunit value=$data.minimum_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>
				</tbody>

				<tr onClick="showHelpEntry('absence_policy_id')">
					<td class="{isvalid object="hpf" label="absence_policy_id" value="cellLeftEditTable"}">
						{t}Absence Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="absence_policy_id" name="data[absence_policy_id]">
							{html_options options=$data.absence_options selected=$data.absence_policy_id}
						</select>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2" >
						{t}Recurring Holidays{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('recurring_holiday')">
					<td class="{isvalid object="hpf" label="recurring_holiday" value="cellLeftEditTable"}">
						{t}Recurring Holidays:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="recurring_holiday_id" name="data[recurring_holiday_ids][]" size="{select_size array=$data.recurring_holiday_options}" multiple>
							{html_options options=$data.recurring_holiday_options selected=$data.recurring_holiday_ids}
						</select>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
