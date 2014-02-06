{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="showTimeSheetVerificationType(); showType(); changeDailyStartTime(); filterUserCount();"}
<script	language=JavaScript>
{literal}
function filterUserCount() {
	total = countSelect(document.getElementById('filter_user'));
	writeLayer('filter_user_count', total);
}

function showType() {
	hideObject('transaction_date_bd');
	hideObject('display_anchor_date');
	hideObject('type_id-5');
	hideObject('type_id-10');
	hideObject('type_id-30');
	hideObject('type_id-50');

	//alert('Type ID: '+ document.getElementById('type_id').value );
	if ( document.getElementById('type_id').value == 5 ) {
		showObject('type_id-5');
	} else if ( document.getElementById('type_id').value == 10 || document.getElementById('type_id').value == 20 ) {
		showObject('type_id-10');

		showObject('transaction_date_bd');
		showObject('display_anchor_date');
	} else if ( document.getElementById('type_id').value == 30 ) {
		showObject('type_id-30');
		showObject('type_id-50');

		showObject('transaction_date_bd');
		showObject('display_anchor_date');

	} else if ( document.getElementById('type_id').value == 50 ) {
		showObject('type_id-50');

		showObject('transaction_date_bd');
		showObject('display_anchor_date');
	}
}

function showTimeSheetVerificationType() {
	hideObject('timesheet_verify');

	//alert('Type ID: '+ document.getElementById('type_id').value );
	if ( document.getElementById('timesheet_verify_type_id').value > 10 ) {
		showObject('timesheet_verify');
	}
}

function changeDailyStartTime() {
	daily_start_time = document.getElementById('daily_start_time').value
	document.getElementById('start_day_of_week_start_time').innerHTML = daily_start_time;
	document.getElementById('primary_day_of_month_start_time').innerHTML = daily_start_time;
	document.getElementById('secondary_day_of_month_start_time').innerHTML = daily_start_time;

}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$ppsf->Validator->isValid()}
					{include file="form_errors.tpl" object="ppsf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="ppsf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" name="pay_period_schedule_data[name]" value="{$pay_period_schedule_data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('description')">
					<td class="{isvalid object="ppsf" label="description" value="cellLeftEditTable"}">
						{t}Description:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="40" name="pay_period_schedule_data[description]" value="{$pay_period_schedule_data.description}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_week_day')">
					<td class="{isvalid object="ppsf" label="start_week_day" value="cellLeftEditTable"}">
						{t}Overtime Week:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[start_week_day_id]">
							{html_options options=$pay_period_schedule_data.start_week_day_options selected=$pay_period_schedule_data.start_week_day_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="ppsf" label="time_zone" value="cellLeftEditTable"}">
						Time Zone:
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[time_zone]">
							{html_options options=$pay_period_schedule_data.time_zone_options selected=$pay_period_schedule_data.time_zone}
						</select>
					</td>
				</tr>

				{if $pay_period_schedule_data.day_start_time != ''}
				<tr onClick="showHelpEntry('day_start_time')">
					<td class="{isvalid object="ppsf" label="day_start_time" value="cellLeftEditTable"}">
						{t}Pay Period Daily Start Time:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="7" id="daily_start_time" name="pay_period_schedule_data[day_start_time]" value="{gettimeunit value=$pay_period_schedule_data.day_start_time}" onChange="changeDailyStartTime()"> {$current_user_prefs->getTimeUnitFormatExample()} {t}(Hours from Midnight){/t}
					</td>
				</tr>
				{else}
					<input type="hidden" id="daily_start_time" name="pay_period_schedule_data[day_start_time]" value="{gettimeunit value=$pay_period_schedule_data.day_start_time}">
				{/if}

				<tr onClick="showHelpEntry('new_day_trigger_time')">
					<td class="{isvalid object="ppsf" label="new_day_trigger_time" value="cellLeftEditTable"}">
						{t}Minimum Time-Off Between Shifts:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="7" name="pay_period_schedule_data[new_day_trigger_time]" value="{gettimeunit value=$pay_period_schedule_data.new_day_trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
						({t}Only for shifts that span midnight{/t})
					</td>
				</tr>

				<tr onClick="showHelpEntry('maximum_shift_time')">
					<td class="{isvalid object="ppsf" label="maximum_shift_time" value="cellLeftEditTable"}">
						{t}Maximum Shift Time:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="7" name="pay_period_schedule_data[maximum_shift_time]" value="{gettimeunit value=$pay_period_schedule_data.maximum_shift_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('shift_assigned_day')">
					<td class="{isvalid object="ppsf" label="shift_assigned_day" value="cellLeftEditTable"}">
						{t}Assign Shifts To:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[shift_assigned_day_id]">
							{html_options options=$pay_period_schedule_data.shift_assigned_day_options selected=$pay_period_schedule_data.shift_assigned_day_id}
						</select>
					</td>
				</tr>

				<tr class="cellLeftEditTable" style="text-align:center">
					<td colspan="4">
						{t}TimeSheet Verification{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('shift_assigned_day')">
					<td class="{isvalid object="ppsf" label="timesheet_verify_type_id" value="cellLeftEditTable"}">
						{t}TimeSheet Verification:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[timesheet_verify_type_id]" id="timesheet_verify_type_id" onChange="showTimeSheetVerificationType()">
							{html_options options=$pay_period_schedule_data.timesheet_verify_type_options selected=$pay_period_schedule_data.timesheet_verify_type_id}
						</select>
					</td>
				</tr>

				<tbody id="timesheet_verify" style="display:none" >
				<tr onClick="showHelpEntry('timesheet_verify_before_end_date')">
					<td class="{isvalid object="ppsf" label="timesheet_verify_before_end_date" value="cellLeftEditTable"}">
						{t}Verification Window Starts:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="3" name="pay_period_schedule_data[timesheet_verify_before_end_date]" value="{$pay_period_schedule_data.timesheet_verify_before_end_date}">
						{t}Day(s){/t} (<b>{t}Before Pay Period End Date{/t}</b>)
					</td>
				</tr>
				<tr onClick="showHelpEntry('timesheet_verify_before_transaction_date')">
					<td class="{isvalid object="ppsf" label="timesheet_verify_before_transaction_date" value="cellLeftEditTable"}">
						{t}Verification Window Ends:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="3" name="pay_period_schedule_data[timesheet_verify_before_transaction_date]" value="{$pay_period_schedule_data.timesheet_verify_before_transaction_date}">
						{t}Day(s){/t} (<b>{t}Before Pay Period Transaction Date{/t}</b>)
					</td>
				</tr>
				</tbody>

				<tr class="cellLeftEditTable" style="text-align:center">
					<td colspan="4">
						{t}Pay Period Dates{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="ppsf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select id="type_id" name="pay_period_schedule_data[type]" onChange="showType();">
							{html_options options=$pay_period_schedule_data.type_options selected=$pay_period_schedule_data.type}
						</select>
					</td>
				</tr>

				<tbody id="type_id-5" style="display:none" >
				<tr onClick="showHelpEntry('annual_pay_periods')">
					<td class="{isvalid object="ppsf" label="annual_pay_periods" value="cellLeftEditTable"}">
						{t}Annual Pay Periods:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="7" id="annual_pay_periods" name="pay_period_schedule_data[annual_pay_periods]" value="{$pay_period_schedule_data.annual_pay_periods}">
					</td>
				</tr>
				</tbody>
				<tbody id="type_id-10" style="display:none" >
				<tr onClick="showHelpEntry('start_day_of_week')">
					<td class="{isvalid object="ppsf" label="start_day_of_week" value="cellLeftEditTable"}">
						{t}Pay Period Starts On:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[start_day_of_week]">
							{html_options options=$pay_period_schedule_data.day_of_week_options selected=$pay_period_schedule_data.start_day_of_week}
						</select>
						{t}at{/t} <b><span id="start_day_of_week_start_time">00:00</span></b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('transaction_date')">
					<td class="{isvalid object="ppsf" label="transaction_date" value="cellLeftEditTable"}">
						{t}Transaction Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[transaction_date]">
							{html_options options=$pay_period_schedule_data.transaction_date_options selected=$pay_period_schedule_data.transaction_date}
						</select>
						{t}(days after end of pay period){/t}
					</td>
				</tr>
				</tbody>

				<tbody id="type_id-50" style="display:none" >
				<tr class="cellLeftEditTable" style="text-align:center">
					<td colspan="4">
						{t}Primary{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('primary_day_of_month')">
					<td class="{isvalid object="ppsf" label="primary_day_of_month" value="cellLeftEditTable"}">
						{t}Pay Period Start Day Of Month:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[primary_day_of_month]">
							{html_options options=$pay_period_schedule_data.day_of_month_options selected=$pay_period_schedule_data.primary_day_of_month}
						</select>
						{t}at{/t} <b><span id="primary_day_of_month_start_time">00:00</span></b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('primary_transaction_day_of_month')">
					<td class="{isvalid object="ppsf" label="primary_transaction_day_of_month" value="cellLeftEditTable"}">
						{t}Transaction Day Of Month:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[primary_transaction_day_of_month]">
							{html_options options=$pay_period_schedule_data.day_of_month_options selected=$pay_period_schedule_data.primary_transaction_day_of_month}
						</select>
					</td>
				</tr>
				</tbody>

				<tbody id="type_id-30" style="display:none" >
				<tr class="cellLeftEditTable" style="text-align:center">
					<td colspan="4">
						{t}Secondary{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('secondary_day_of_month')">
					<td class="{isvalid object="ppsf" label="secondary_day_of_month" value="cellLeftEditTable"}">
						{t}Pay Period Start Day Of Month:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[secondary_day_of_month]">
							{html_options options=$pay_period_schedule_data.day_of_month_options selected=$pay_period_schedule_data.secondary_day_of_month}
						</select>
						at <b><span id="secondary_day_of_month_start_time">00:00</span></b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('secondary_transaction_day_of_month')">
					<td class="{isvalid object="ppsf" label="secondary_transaction_day_of_month" value="cellLeftEditTable"}">
						{t}Transaction Day Of Month:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[secondary_transaction_day_of_month]">
							{html_options options=$pay_period_schedule_data.day_of_month_options selected=$pay_period_schedule_data.secondary_transaction_day_of_month}
						</select>
					</td>
				</tr>
				</tbody>

				<tbody id="transaction_date_bd" style="display:none" >
				<tr onClick="showHelpEntry('transaction_date_bd')">
					<td class="{isvalid object="ppsf" label="transaction_date_bd" value="cellLeftEditTable"}">
						{t}Transaction Always on Business Day:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select name="pay_period_schedule_data[transaction_date_bd]">
							{html_options options=$pay_period_schedule_data.transaction_date_bd_options selected=$pay_period_schedule_data.transaction_date_bd}
						</select>

					</td>
				</tr>
				</tbody>

				{if $pay_period_schedule_data.id == ''}
				<tbody id="display_anchor_date" style="display:none" >
				<tr onClick="showHelpEntry('anchor_date')">
					<td class="{isvalid object="ppsf" label="anchor_date" value="cellLeftEditTable"}">
						{t}Create Initial Pay Periods From:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="15" id="anchor_date" onFocus="showHelpEntry('anchor_date')" name="pay_period_schedule_data[anchor_date]" value="{getdate type="DATE" epoch=$pay_period_schedule_data.anchor_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_anchor_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('anchor_date', 'cal_anchor_date', false);">
					</td>
				</tr>
				</tbody>
				{/if}

				<tbody id="filter_employees_on" style="display:none" >
				<tr>
					<td class="{isvalid object="ppsf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td>
								{t}UnAssigned Employees{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Assigned Employees{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
								<br>
								<select name="src_user_id" id="src_filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$pay_period_schedule_data.user_options}" multiple>
									{html_options options=$pay_period_schedule_data.user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$pay_period_schedule_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$pay_period_schedule_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								<br>
								<br>
								<br>
								<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
								<br>
								<select name="pay_period_schedule_data[user_ids][]" id="filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$filter_user_options}" multiple>
									{html_options options=$filter_user_options selected=$pay_period_schedule_data.user_options}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_employees_off">
				<tr>
					<td class="{isvalid object="ppsf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$pay_period_schedule_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_user'))">
		</div>

		<input type="hidden" name="pay_period_schedule_data[id]" value="{$pay_period_schedule_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}