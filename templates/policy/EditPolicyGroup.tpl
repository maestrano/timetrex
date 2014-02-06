{include file="header.tpl" body_onload="filterUserCount();"}
<script	language=JavaScript>

{literal}
function filterUserCount() {
	total = countSelect(document.getElementById('filter_user'));
	writeLayer('filter_user_count', total);
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$pgf->Validator->isValid()}
					{include file="form_errors.tpl" object="pgf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="pgf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

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
								<select name="src_user_id" id="src_filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
									{html_options options=$data.user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								<br>
								<br>
								<br>
								<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
								<br>
								<select name="data[user_ids][]" id="filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$filter_user_options}" multiple>
									{html_options options=$filter_user_options selected=$data.user_ids}
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
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				<tr onClick="showHelpEntry('over_time_policy')">
					<td class="{isvalid object="pgf" label="over_time_policy" value="cellLeftEditTable"}">
						{t}Overtime Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="over_time_policy_ids" name="data[over_time_policy_ids][]" size="{select_size array=$data.over_time_policy_options}" multiple>
							{html_options options=$data.over_time_policy_options selected=$data.over_time_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('round_interval_policy')">
					<td class="{isvalid object="pgf" label="round_interval_policy" value="cellLeftEditTable"}">
						{t}Rounding Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="round_interval_policy_ids" name="data[round_interval_policy_ids][]" size="{select_size array=$data.round_interval_policy_options}" multiple>
							{html_options options=$data.round_interval_policy_options selected=$data.round_interval_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('meal_policy')">
					<td class="{isvalid object="pgf" label="meal_policy" value="cellLeftEditTable"}">
						{t}Meal Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="meal_policy_ids" name="data[meal_policy_ids][]" size="{select_size array=$data.meal_options}" multiple>
							{html_options options=$data.meal_options selected=$data.meal_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('break_policy')">
					<td class="{isvalid object="pgf" label="break_policy" value="cellLeftEditTable"}">
						{t}Break Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="break_policy_ids" name="data[break_policy_ids][]" size="{select_size array=$data.break_options}" multiple>
							{html_options options=$data.break_options selected=$data.break_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('accrual_policy')">
					<td class="{isvalid object="pgf" label="accrual_policy" value="cellLeftEditTable"}">
						{t}Accrual Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="accrual_policy_ids" name="data[accrual_policy_ids][]" size="{select_size array=$data.accrual_policy_options}" multiple>
							{html_options options=$data.accrual_policy_options selected=$data.accrual_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('premium_policy')">
					<td class="{isvalid object="pgf" label="premium_policy" value="cellLeftEditTable"}">
						{t}Premium Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="premium_policy_ids" name="data[premium_policy_ids][]" size="{select_size array=$data.premium_policy_options}" multiple>
							{html_options options=$data.premium_policy_options selected=$data.premium_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('holiday_policy')">
					<td class="{isvalid object="pgf" label="holiday_policy" value="cellLeftEditTable"}">
						{t}Holiday Policies:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="holiday_policy_ids" name="data[holiday_policy_ids][]" size="{select_size array=$data.holiday_policy_options}" multiple>
							{html_options options=$data.holiday_policy_options selected=$data.holiday_policy_ids}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('exception_policy')">
					<td class="{isvalid object="pgf" label="exception_policy" value="cellLeftEditTable"}">
						{t}Exception Policy:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="exception_policy_id" name="data[exception_policy_control_id]">
							{html_options options=$data.exception_options selected=$data.exception_policy_control_id}
						</select>
					</td>
				</tr>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_user'))">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
