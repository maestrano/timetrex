{include file="header.tpl" enable_calendar=true body_onload="showType(); showPayType(); showAccrualRate(); countAllReportCriteria();"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_branch',
									'filter_department',
									'filter_job_group',
									'filter_job',
									'filter_job_item_group',
									'filter_job_item'
									);

function showType() {
	type_id = document.getElementById('type_id').value;

	hideObject('type_date_time');
	hideObject('type_differential');
	hideObject('type_meal_break');
	hideObject('type_callback');
	hideObject('type_minimum_shift_time');
	hideObject('type_holiday');

	hideObject('filter_branch_on');
	hideObject('filter_branch_off');
	hideObject('filter_department_on');
	hideObject('filter_department_off');
	hideObject('filter_job_group_on');
	hideObject('filter_job_group_off');
	hideObject('filter_job_on');
	hideObject('filter_job_off');
	hideObject('filter_job_item_group_on');
	hideObject('filter_job_item_group_off');
	hideObject('filter_job_item_on');
	hideObject('filter_job_item_off');

	if ( type_id == 10 ) {
		showObject('type_date_time');
	} else if ( type_id == 20 ) {
		showObject('type_differential');

		//Handle sub-rows
		showObject('filter_branch_off');
		showObject('filter_department_off');

		showObject('filter_job_group_off');
		showObject('filter_job_off');
		showObject('filter_job_item_group_off');
		showObject('filter_job_item_off');
	} else if ( type_id == 30 ) {
		showObject('type_meal_break');
	} else if ( type_id == 40 ) {
		showObject('type_callback');
	} else if ( type_id == 50 ) {
		showObject('type_minimum_shift_time');
		showObject('type_differential');

		//Handle sub-rows
		showObject('filter_branch_off');
		showObject('filter_department_off');

		showObject('filter_job_group_off');
		showObject('filter_job_off');
		showObject('filter_job_item_group_off');
		showObject('filter_job_item_off');

	} else if ( type_id == 90 ) {
		showObject('type_holiday');
	} else if ( type_id == 100 ) {
		showObject('type_date_time');
		showObject('type_differential');

		//Handle sub-rows
		showObject('filter_branch_off');
		showObject('filter_department_off');

		showObject('filter_job_group_off');
		showObject('filter_job_off');
		showObject('filter_job_item_group_off');
		showObject('filter_job_item_off');
	}
}

function showPayType() {
	pay_type_id = document.getElementById('pay_type_id').value;

	document.getElementById('pay_type_10_desc').style.display = 'none';
	document.getElementById('pay_type_20_desc').style.display = 'none';
	document.getElementById('pay_type_30_desc').style.display = 'none';

	document.getElementById('pay_type_10_help').style.display = 'none';
	document.getElementById('pay_type_20_help').style.display = 'none';
	document.getElementById('pay_type_30_help').style.display = 'none';

	document.getElementById('wage_group_desc').style.display = 'none';

	if ( pay_type_id == 10 ) {
		document.getElementById('pay_type_10_desc').className = '';
		document.getElementById('pay_type_10_desc').style.display = '';

		document.getElementById('pay_type_10_help').className = '';
		document.getElementById('pay_type_10_help').style.display = '';

		document.getElementById('wage_group_desc').className = '';
		document.getElementById('wage_group_desc').style.display = '';
	} else if ( pay_type_id == 20 ) {
		document.getElementById('pay_type_20_desc').className = '';
		document.getElementById('pay_type_20_desc').style.display = '';

		document.getElementById('pay_type_20_help').className = '';
		document.getElementById('pay_type_20_help').style.display = '';
	} else if ( pay_type_id == 30 ) {
		document.getElementById('pay_type_30_desc').className = '';
		document.getElementById('pay_type_30_desc').style.display = '';

		document.getElementById('pay_type_30_help').className = '';
		document.getElementById('pay_type_30_help').style.display = '';

		document.getElementById('wage_group_desc').className = '';
		document.getElementById('wage_group_desc').style.display = '';
	} else if ( pay_type_id == 32 ) {
		document.getElementById('pay_type_30_desc').className = '';
		document.getElementById('pay_type_30_desc').style.display = '';

		document.getElementById('pay_type_30_help').className = '';
		document.getElementById('pay_type_30_help').style.display = '';

		document.getElementById('wage_group_desc').className = '';
		document.getElementById('wage_group_desc').style.display = '';
	} else if ( pay_type_id == 40 ) {
		document.getElementById('pay_type_30_desc').className = '';
		document.getElementById('pay_type_30_desc').style.display = '';

		document.getElementById('pay_type_30_help').className = '';
		document.getElementById('pay_type_30_help').style.display = '';

		document.getElementById('wage_group_desc').className = '';
		document.getElementById('wage_group_desc').style.display = '';
	}
}
function showAccrualRate() {
	accrual_policy_id = document.getElementById('accrual_policy_id').value;

	if ( accrual_policy_id == 0 ) {
		document.getElementById('accrual_rate').style.display = 'none';
	} else {
		document.getElementById('accrual_rate').className = '';
		document.getElementById('accrual_rate').style.display = '';
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
				{if !$ppf->Validator->isValid()}
					{include file="form_errors.tpl" object="ppf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="ppf" label="name" value="cellLeftEditTable"}">
						{t}Name{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="ppf" label="type" value="cellLeftEditTable"}">
						{t}Type{/t}:
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]" onChange="showType()">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>

				<tbody id="type_date_time" style="display:none" >
				<tr>
					<td colspan="2" class="tblHeader">
						{t}Date/Time Criteria{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_date')">
					<td class="{isvalid object="ppf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="data[start_date]" value="{getdate type="DATE" epoch=$data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
						{t}ie{/t}: {$current_user_prefs->getDateFormatExample()} <b>{t}(Leave blank for no start date){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('end_date')">
					<td class="{isvalid object="ppf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="data[end_date]" value="{getdate type="DATE" epoch=$data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
						{t}ie{/t}: {$current_user_prefs->getDateFormatExample()} <b>{t}(Leave blank for no end date){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_time')">
					<td class="{isvalid object="ppf" label="start_time" value="cellLeftEditTable"}">
						{t}Start Time{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" name="data[start_time]" value="{getdate type="TIME" epoch=$data.start_time}">
						{t}ie{/t}: {$current_user_prefs->getTimeFormatExample()} <b>{t}(Leave blank for no start time){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('end_time')">
					<td class="{isvalid object="ppf" label="end_time" value="cellLeftEditTable"}">
						{t}End Time{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" name="data[end_time]" value="{getdate type="TIME" epoch=$data.end_time}">
						{t}ie{/t}: {$current_user_prefs->getTimeFormatExample()} <b>{t}(Leave blank for no end time){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('include_partial_punch')">
					<td class="{isvalid object="ppf" label="include_partial_punch" value="cellLeftEditTable"}">
						{t}Include Partial Punches{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[include_partial_punch]" value="1" {if $data.include_partial_punch == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('daily_trigger_time')">
					<td class="{isvalid object="ppf" label="daily_trigger_time" value="cellLeftEditTable"}">
						{t}Daily Time{/t}:
					</td>
					<td class="cellRightEditTable">
						{t}Active After{/t}: <input type="text" size="8" name="data[daily_trigger_time]" value="{gettimeunit value=$data.daily_trigger_time}">
						{t}Active Before{/t}: <input type="text" size="8" name="data[maximum_daily_trigger_time]" value="{gettimeunit value=$data.maximum_daily_trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>


				<tr onClick="showHelpEntry('weekly_trigger_time')">
					<td class="{isvalid object="ppf" label="weekly_trigger_time" value="cellLeftEditTable"}">
						{t}Weekly Time{/t}:
					</td>
					<td class="cellRightEditTable">
						{t}Active After{/t}: <input type="text" size="8" name="data[weekly_trigger_time]" value="{gettimeunit value=$data.weekly_trigger_time}">
						{t}Active Before{/t}: <input type="text" size="8" name="data[maximum_weekly_trigger_time]" value="{gettimeunit value=$data.maximum_weekly_trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('effective_days')">
					<td class="{isvalid object="ppf" label="effective_days" value="cellLeftEditTable"}">
						{t}Effective Days{/t}:
					</td>
					<td class="cellRightEditTable">
						<table width="280">
							<tr align="center" style="font-weight: bold">
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
							<tr align="center">
								<td>
									<input type="checkbox" class="checkbox" name="data[sun]" value="1" {if $data.sun == TRUE}checked{/if}>
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="data[mon]" value="1" {if $data.mon == TRUE}checked{/if}>
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="data[tue]" value="1" {if $data.tue == TRUE}checked{/if}>
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="data[wed]" value="1" {if $data.wed == TRUE}checked{/if}>
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="data[thu]" value="1" {if $data.thu == TRUE}checked{/if}>
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="data[fri]" value="1" {if $data.fri == TRUE}checked{/if}>
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="data[sat]" value="1" {if $data.sat == TRUE}checked{/if}>
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr onClick="showHelpEntry('include_holiday_type')">
					<td class="{isvalid object="ppf" label="type" value="cellLeftEditTable"}">
						{t}Holidays{/t}:
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[include_holiday_type_id]">
							{html_options options=$data.include_holiday_type_options selected=$data.include_holiday_type_id}
						</select>
					</td>
				</tr>

				</tbody>

				<tbody id="type_differential" style="display:none" >
				<tr>
					<td colspan="2" class="tblHeader">
						{t}Differential Criteria{/t}
					</td>
				</tr>

				<tbody id="filter_branch_on" style="display:none" >
				<tr>
					<td class="{isvalid object="ppf" label="branch" value="cellLeftEditTable"}" nowrap>
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
								{t}Exclude Default Branch{/t}:
								<input type="checkbox" class="checkbox" name="data[exclude_default_branch]" value="1" {if $data.exclude_default_branch == TRUE}checked{/if}>
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
				<tbody id="filter_branch_off" style="display:none">
				<tr>
					<td class="{isvalid object="ppf" label="branch" value="cellLeftEditTable"}" nowrap>
						<b>{t}Branches{/t}:</b><a href="javascript:toggleRowObject('filter_branch_on');toggleRowObject('filter_branch_off');uniqueSelect(document.getElementById('filter_branch'), document.getElementById('src_filter_branch')); sortSelect(document.getElementById('filter_branch'));resizeSelect(document.getElementById('src_filter_branch'), document.getElementById('filter_branch'), {select_size array=$data.branch_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_branch_count">0</span> {t}Branches Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				<tbody id="filter_department_on" style="display:none" >
				<tr>
					<td class="{isvalid object="ppf" label="department" value="cellLeftEditTable"}" nowrap>
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
								{t}Exclude Default Department{/t}:
								<input type="checkbox" class="checkbox" name="data[exclude_default_department]" value="1" {if $data.exclude_default_department == TRUE}checked{/if}>
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
				<tbody id="filter_department_off" style="display:none">
				<tr>
					<td class="{isvalid object="ppf" label="department" value="cellLeftEditTable"}" nowrap>
						<b>{t}Departments{/t}:</b><a href="javascript:toggleRowObject('filter_department_on');toggleRowObject('filter_department_off');uniqueSelect(document.getElementById('filter_department'), document.getElementById('src_filter_department')); sortSelect(document.getElementById('filter_department'));resizeSelect(document.getElementById('src_filter_department'), document.getElementById('filter_department'), {select_size array=$data.department_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_department_count">0</span> {t}Departments Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				{if $current_company->getProductEdition() >= 20}
					<tbody id="filter_job_group_on" style="display:none" >
					<tr>
						<td class="{isvalid object="ppf" label="job_group" value="cellLeftEditTable"}" nowrap>
							<b>{t}Job Groups{/t}:</b><a href="javascript:toggleRowObject('filter_job_group_on');toggleRowObject('filter_job_group_off');filterCountSelect( 'filter_job_group' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Selection Type{/t}:
									<select id="type_id" name="data[job_group_selection_type_id]">
										{html_options options=$data.job_group_selection_type_options selected=$data.job_group_selection_type_id}
									</select>
								</td>
							</td>
							<tr class="tblHeader">
								<td>
									{t}UnSelected Job Groups{/t}
								</td>
								<td>
									<br>
								</td>
								<td>
									{t}Selected Job Groups{/t}
								</td>
							</tr>
							<tr>
								<td class="cellRightEditTable" width="49%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_job_group'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_job_group'))">
									<br>
									<select name="src_job_group_id" id="src_filter_job_group" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_job_group_options array2=$data.selected_job_group_options}" multiple>
										{html_options options=$data.src_job_group_options}
									</select>
								</td>
								<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
									<a href="javascript:moveItem(document.getElementById('src_filter_job_group'), document.getElementById('filter_job_group')); uniqueSelect(document.getElementById('filter_job_group')); sortSelect(document.getElementById('filter_job_group'));resizeSelect(document.getElementById('src_filter_job_group'), document.getElementById('filter_job_group'), {select_size array=$data.src_job_group_options array2=$data.selected_job_group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
									<br>
									<a href="javascript:moveItem(document.getElementById('filter_job_group'), document.getElementById('src_filter_job_group')); uniqueSelect(document.getElementById('src_filter_job_group')); sortSelect(document.getElementById('src_filter_job_group'));resizeSelect(document.getElementById('src_filter_job_group'), document.getElementById('filter_job_group'), {select_size array=$data.src_job_group_options array2=$data.selected_job_group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								</td>
								<td class="cellRightEditTable" width="49%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_job_group'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_job_group'))">
									<br>
									<select name="data[job_group_ids][]" id="filter_job_group" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.selected_job_group_options}" multiple>
										{html_options options=$data.selected_job_group_options selected=$data.job_group_ids}
									</select>
								</td>
							</tr>
							</table>
						</td>
					</tr>
					</tbody>
					<tbody id="filter_job_group_off">
					<tr>
						<td class="{isvalid object="ppf" label="job_group" value="cellLeftEditTable"}" nowrap>
							<b>{t}Job Groups{/t}:</b><a href="javascript:toggleRowObject('filter_job_group_on');toggleRowObject('filter_job_group_off');uniqueSelect(document.getElementById('filter_job_group'), document.getElementById('src_filter_job_group')); sortSelect(document.getElementById('filter_job_group'));resizeSelect(document.getElementById('src_filter_job_group'), document.getElementById('filter_job_group'), {select_size array=$data.src_job_group_options array2=$data.selected_job_group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTable" colspan="100">
							<span id="filter_job_group_count">0</span> {t}Job Groups Currently Selected, Click the arrow to modify.{/t}
						</td>
					</tr>
					</tbody>

					<tbody id="filter_job_on" style="display:none" >
					<tr>
						<td class="{isvalid object="ppf" label="job" value="cellLeftEditTable"}" nowrap>
							<b>{t}Jobs{/t}:</b><a href="javascript:toggleRowObject('filter_job_on');toggleRowObject('filter_job_off');filterCountSelect( 'filter_job' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Selection Type{/t}:
									<select id="type_id" name="data[job_selection_type_id]">
										{html_options options=$data.job_selection_type_options selected=$data.job_selection_type_id}
									</select>
								</td>
							</td>
							<tr class="tblHeader">
								<td>
									{t}UnSelected Jobs{/t}
								</td>
								<td>
									<br>
								</td>
								<td>
									{t}Selected Jobs{/t}
								</td>
							</tr>
							<tr>
								<td class="cellRightEditTable" width="49%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_job'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_job'))">
									<br>
									<select name="src_job_id" id="src_filter_job" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_job_options array2=$data.selected_job_options}" multiple>
										{html_options options=$data.src_job_options}
									</select>
								</td>
								<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
									<a href="javascript:moveItem(document.getElementById('src_filter_job'), document.getElementById('filter_job')); uniqueSelect(document.getElementById('filter_job')); sortSelect(document.getElementById('filter_job'));resizeSelect(document.getElementById('src_filter_job'), document.getElementById('filter_job'), {select_size array=$data.src_job_options array2=$data.selected_job_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
									<br>
									<a href="javascript:moveItem(document.getElementById('filter_job'), document.getElementById('src_filter_job')); uniqueSelect(document.getElementById('src_filter_job')); sortSelect(document.getElementById('src_filter_job'));resizeSelect(document.getElementById('src_filter_job'), document.getElementById('filter_job'), {select_size array=$data.src_job_options array2=$data.selected_job_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								</td>
								<td class="cellRightEditTable" width="49%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_job'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_job'))">
									<br>
									<select name="data[job_ids][]" id="filter_job" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_job_options array2=$data.selected_job_options}" multiple>
										{html_options options=$data.selected_job_options selected=$data.job_ids}
									</select>
								</td>
							</tr>
							</table>
						</td>
					</tr>
					</tbody>
					<tbody id="filter_job_off">
					<tr>
						<td class="{isvalid object="ppf" label="job" value="cellLeftEditTable"}" nowrap>
							<b>{t}Jobs{/t}:</b><a href="javascript:toggleRowObject('filter_job_on');toggleRowObject('filter_job_off');uniqueSelect(document.getElementById('filter_job'), document.getElementById('src_filter_job')); sortSelect(document.getElementById('filter_job'));resizeSelect(document.getElementById('src_filter_job'), document.getElementById('filter_job'), {select_size array=$data.job_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTable" colspan="100">
							<span id="filter_job_count">0</span> {t}Jobs Currently Selected, Click the arrow to modify.{/t}
						</td>
					</tr>
					</tbody>

					<tbody id="filter_job_item_group_on" style="display:none" >
					<tr>
						<td class="{isvalid object="ppf" label="job_item_group" value="cellLeftEditTable"}" nowrap>
							<b>{t}Task Groups{/t}:</b><a href="javascript:toggleRowObject('filter_job_item_group_on');toggleRowObject('filter_job_item_group_off');filterCountSelect( 'filter_job_item_group' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Selection Type{/t}:
									<select id="type_id" name="data[job_item_group_selection_type_id]">
										{html_options options=$data.job_item_group_selection_type_options selected=$data.job_item_group_selection_type_id}
									</select>
								</td>
							</td>
							<tr class="tblHeader">
								<td>
									{t}UnSelected Task Groups{/t}
								</td>
								<td>
									<br>
								</td>
								<td>
									{t}Selected Task Groups{/t}
								</td>
							</tr>
							<tr>
								<td class="cellRightEditTable" width="49%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_job_item_group'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_job_item_group'))">
									<br>
									<select name="src_job_item_group_id" id="src_filter_job_item_group" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_job_item_group_options array2=$data.selected_job_item_group_options}" multiple>
										{html_options options=$data.src_job_item_group_options}
									</select>
								</td>
								<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
									<a href="javascript:moveItem(document.getElementById('src_filter_job_item_group'), document.getElementById('filter_job_item_group')); uniqueSelect(document.getElementById('filter_job_item_group')); sortSelect(document.getElementById('filter_job_item_group'));resizeSelect(document.getElementById('src_filter_job_item_group'), document.getElementById('filter_job_item_group'), {select_size array=$data.src_job_item_group_options array2=$data.selected_job_item_group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
									<br>
									<a href="javascript:moveItem(document.getElementById('filter_job_item_group'), document.getElementById('src_filter_job_item_group')); uniqueSelect(document.getElementById('src_filter_job_item_group')); sortSelect(document.getElementById('src_filter_job_item_group'));resizeSelect(document.getElementById('src_filter_job_item_group'), document.getElementById('filter_job_item_group'), {select_size array=$data.src_job_item_group_options array2=$data.selected_job_item_group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								</td>
								<td class="cellRightEditTable" width="49%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_job_item_group'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_job_item_group'))">
									<br>
									<select name="data[job_item_group_ids][]" id="filter_job_item_group" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_job_item_group_options array2=$data.selected_job_item_group_options}" multiple>
										{html_options options=$data.selected_job_item_group_options selected=$data.job_item_group_ids}
									</select>
								</td>
							</tr>
							</table>
						</td>
					</tr>
					</tbody>
					<tbody id="filter_job_item_group_off">
					<tr>
						<td class="{isvalid object="ppf" label="job_item_group" value="cellLeftEditTable"}" nowrap>
							<b>{t}Task Groups{/t}:</b><a href="javascript:toggleRowObject('filter_job_item_group_on');toggleRowObject('filter_job_item_group_off');uniqueSelect(document.getElementById('filter_job_item_group'), document.getElementById('src_filter_job_item_group')); sortSelect(document.getElementById('filter_job_item_group'));resizeSelect(document.getElementById('src_filter_job_item_group'), document.getElementById('filter_job_item_group'), {select_size array=$data.job_item_group_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTable" colspan="100">
							<span id="filter_job_item_group_count">0</span> {t}Task Groups Currently Selected, Click the arrow to modify.{/t}
						</td>
					</tr>
					</tbody>

					<tbody id="filter_job_item_on" style="display:none" >
					<tr>
						<td class="{isvalid object="ppf" label="job_item" value="cellLeftEditTable"}" nowrap>
							<b>{t}Tasks{/t}:</b><a href="javascript:toggleRowObject('filter_job_item_on');toggleRowObject('filter_job_item_off');filterCountSelect( 'filter_job_item' );"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>
						<td colspan="3">
							<table class="editTable">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Selection Type{/t}:
									<select id="type_id" name="data[job_item_selection_type_id]">
										{html_options options=$data.job_item_selection_type_options selected=$data.job_item_selection_type_id}
									</select>
								</td>
							</td>
							<tr class="tblHeader">
								<td>
									{t}UnSelected Tasks{/t}
								</td>
								<td>
									<br>
								</td>
								<td>
									{t}Selected Tasks{/t}
								</td>
							</tr>
							<tr>
								<td class="cellRightEditTable" width="49%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_job_item'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_job_item'))">
									<br>
									<select name="src_job_item_id" id="src_filter_job_item" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_job_item_options array2=$data.selected_job_item_options}" multiple>
										{html_options options=$data.src_job_item_options}
									</select>
								</td>
								<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
									<a href="javascript:moveItem(document.getElementById('src_filter_job_item'), document.getElementById('filter_job_item')); uniqueSelect(document.getElementById('filter_job_item')); sortSelect(document.getElementById('filter_job_item'));resizeSelect(document.getElementById('src_filter_job_item'), document.getElementById('filter_job_item'), {select_size array=$data.src_job_item_options array2=$data.selected_job_item_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
									<br>
									<a href="javascript:moveItem(document.getElementById('filter_job_item'), document.getElementById('src_filter_job_item')); uniqueSelect(document.getElementById('src_filter_job_item')); sortSelect(document.getElementById('src_filter_job_item'));resizeSelect(document.getElementById('src_filter_job_item'), document.getElementById('filter_job_item'), {select_size array=$data.src_job_item_options array2=$data.selected_job_item_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								</td>
								<td class="cellRightEditTable" width="49%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_job_item'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_job_item'))">
									<br>
									<select name="data[job_item_ids][]" id="filter_job_item" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.src_job_item_options array2=$data.selected_job_item_options}" multiple>
										{html_options options=$data.selected_job_item_options selected=$data.job_item_ids}
									</select>
								</td>
							</tr>
							</table>
						</td>
					</tr>
					</tbody>
					<tbody id="filter_job_item_off">
					<tr>
						<td class="{isvalid object="ppf" label="job_item" value="cellLeftEditTable"}" nowrap>
							<b>{t}Tasks{/t}:</b><a href="javascript:toggleRowObject('filter_job_item_on');toggleRowObject('filter_job_item_off');uniqueSelect(document.getElementById('filter_job_item'), document.getElementById('src_filter_job_item')); sortSelect(document.getElementById('filter_job_item'));resizeSelect(document.getElementById('src_filter_job_item'), document.getElementById('filter_job_item'), {select_size array=$data.job_item_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
						</td>
						<td class="cellRightEditTable" colspan="100">
							<span id="filter_job_item_count">0</span> {t}Tasks Currently Selected, Click the arrow to modify.{/t}
						</td>
					</tr>
					</tbody>
				{/if}
				</tbody>

				<tbody id="type_meal_break" style="display:none" >
				<tr>
					<td colspan="2" class="tblHeader">
						{t}Meal/Break Criteria{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('daily_trigger_time')">
					<td class="{isvalid object="ppf" label="daily_trigger_time" value="cellLeftEditTable"}">
						{t}Active After Daily Hours{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[daily_trigger_time2]" value="{gettimeunit value=$data.daily_trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('maximum_no_break_time')">
					<td class="{isvalid object="ppf" label="maximum_no_break_time" value="cellLeftEditTable"}">
						{t}Maximum Time Without A Break{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[maximum_no_break_time]" value="{gettimeunit value=$data.maximum_no_break_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('mimimum_break_time')">
					<td class="{isvalid object="ppf" label="minimum_break_time" value="cellLeftEditTable"}">
						{t}Minimum Time Recognized As Break{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[minimum_break_time]" value="{gettimeunit value=$data.minimum_break_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>
				<tbody>

				<tbody id="type_callback" style="display:none" >
				<tr>
					<td colspan="2" class="tblHeader">
						{t}Callback Criteria{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_time_between_shifts')">
					<td class="{isvalid object="ppf" label="minimum_time_between_shift" value="cellLeftEditTable"}">
						{t}Minimum Time Between Shifts{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[minimum_time_between_shift]" value="{gettimeunit value=$data.minimum_time_between_shift}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_first_shift_time')">
					<td class="{isvalid object="ppf" label="minimum_first_shift_time" value="cellLeftEditTable"}">
						{t}First Shift Must Be At Least{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[minimum_first_shift_time]" value="{gettimeunit value=$data.minimum_first_shift_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>
				<tbody>

				<tbody id="type_minimum_shift_time" style="display:none" >
				<tr>
					<td colspan="2" class="tblHeader">
						{t}Minimum Shift Time Criteria{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_shift_time')">
					<td class="{isvalid object="ppf" label="minimum_shift_time" value="cellLeftEditTable"}">
						{t}Minimum Shift Time{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[minimum_shift_time]" value="{gettimeunit value=$data.minimum_shift_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_time_between_shifts')">
					<td class="{isvalid object="ppf" label="minimum_time_between_shift" value="cellLeftEditTable"}">
						{t}Minimum Time-Off Between Shifts{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[minimum_time_between_shift]" value="{gettimeunit value=$data.minimum_time_between_shift}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tbody>

				<tbody id="type_holiday" style="display:none" >
				<tr>
					<td colspan="2" class="tblHeader">
						{t}Holiday Criteria{/t}
					</td>
				</tr>
				<tr onClick="showHelpEntry('include_partial_punch')">
					<td class="{isvalid object="ppf" label="include_partial_punch" value="cellLeftEditTable"}">
						{t}Include Partial Punches{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[holiday_include_partial_punch]" value="1" {if $data.include_partial_punch == TRUE}checked{/if}>
					</td>
				</tr>
				</tbody>

				<tr>
					<td colspan="2" class="tblHeader">
						{t}Hours/Pay Criteria{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_time')">
					<td class="{isvalid object="hpf" label="minimum_time" value="cellLeftEditTable"}">
						{t}Minimum Time{/t}:
					</td>
					<td class="cellRightEditTable">
						<input size="8" type="text" name="data[minimum_time]" value="{gettimeunit value=$data.minimum_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
						<b>{t}(Use 0 for no minimum){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('maximum_time')">
					<td class="{isvalid object="hpf" label="maximum_time" value="cellLeftEditTable"}">
						{t}Maximum Time{/t}:
					</td>
					<td class="cellRightEditTable">
						<input size="8" type="text" name="data[maximum_time]" value="{gettimeunit value=$data.maximum_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
						<b>{t}(Use 0 for no maximum){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('include_meal_policy')">
					<td class="{isvalid object="hpf" label="include_meal_policy" value="cellLeftEditTable"}">
						{t}Include Meal Policy in Calculation{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[include_meal_policy]" value="1" {if $data.include_meal_policy == TRUE}checked{/if}>
					</td>
				</tr>
				<tr onClick="showHelpEntry('include_break_policy')">
					<td class="{isvalid object="hpf" label="include_break_policy" value="cellLeftEditTable"}">
						{t}Include Break Policy in Calculation{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[include_break_policy]" value="1" {if $data.include_break_policy == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('pay_type')">
					<td class="{isvalid object="ppf" label="pay_type" value="cellLeftEditTable"}">
						{t}Pay Type{/t}:
					</td>
					<td class="cellRightEditTable">
						<select id="pay_type_id" name="data[pay_type_id]" onChange="showPayType()">
							{html_options options=$data.pay_type_options selected=$data.pay_type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('rate')">
					<td class="{isvalid object="ppf" label="rate" value="cellLeftEditTable"}">
						<span id="pay_type_10_desc" style="display:none">{t}Rate{/t}</span><span id="pay_type_20_desc" style="display:none">{t}Premium{/t}</span><span id="pay_type_30_desc" style="display:none">{t}Hourly Rate{/t}</span>:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[rate]" value="{$data.rate}"> ({t}ie:{/t} <span id="pay_type_10_help" style="display:none">{t}1.5 for time and a half{/t}</span><span id="pay_type_20_help" style="display:none">{t}0.75 for 75 cents/hr{/t}</span><span id="pay_type_30_help" style="display:none">{t}10.00/hr{/t}</span>)
					</td>
				</tr>

				<tbody id="wage_group_desc" style="display:none">
				<tr onClick="showHelpEntry('wage_group')">
					<td class="{isvalid object="ppf" label="rate" value="cellLeftEditTable"}">
						{t}Wage Group:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="wage_group" name="data[wage_group_id]">
							{html_options options=$data.wage_group_options selected=$data.wage_group_id}
						</select>
					</td>
				</tr>
				</tbody>

				<tr onClick="showHelpEntry('pay_stub_entry')">
					<td class="{isvalid object="ppf" label="pay_stub_entry_account_id" value="cellLeftEditTable"}">
						{t}Pay Stub Account{/t}:
					</td>
					<td class="cellRightEditTable">
						<select id="pay_stub_entry_name" name="data[pay_stub_entry_account_id]">
							{html_options options=$data.pay_stub_entry_options selected=$data.pay_stub_entry_account_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('accrual_policy')">
					<td class="{isvalid object="otpf" label="accrual_policy" value="cellLeftEditTable"}">
						{t}Accrual Policy{/t}:
					</td>
					<td class="cellRightEditTable">
						<select id="accrual_policy_id" name="data[accrual_policy_id]" onChange="showAccrualRate()">
							{html_options options=$data.accrual_options selected=$data.accrual_policy_id}
						</select>
					</td>
				</tr>

				<tbody id="accrual_rate" style="display:none">
				<tr onClick="showHelpEntry('accural_rate')">
					<td class="{isvalid object="otpf" label="accrual_rate" value="cellLeftEditTable"}">
						{t}Accrual Rate{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[accrual_rate]" value="{$data.accrual_rate}">
					</td>
				</tr>
				</tbody>

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
