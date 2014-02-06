{include file="header.tpl" enable_calendar=true body_onload="filterUserCount();"}
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
				{if !$rscf->Validator->isValid()}
					{include file="form_errors.tpl" object="rscf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('recurring_schedule_template_control')">
					<td class="{isvalid object="rscf" label="recurring_schedule_template_control_id" value="cellLeftEditTable"}">
						{t}Template:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="template_id" name="data[template_id][]" multiple>
							{html_options options=$data.template_options selected=$data.template_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_week')">
					<td class="{isvalid object="rscf" label="start_week" value="cellLeftEditTable"}">
						{t}Start Week:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="4" name="data[start_week]" value="{$data.start_week}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_date')">
					<td class="{isvalid object="rscf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="data[start_date]" value="{getdate type="DATE" epoch=$data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('end_date')">
					<td class="{isvalid object="rscf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="data[end_date]" value="{getdate type="DATE" epoch=$data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()} <b>{t}(Leave blank for no end date){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('auto_fill')">
					<td class="{isvalid object="ripf" label="auto_fill" value="cellLeftEditTable"}">
						{t}Auto-Pilot:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[auto_fill]" value="1"{if $data.auto_fill == TRUE}checked{/if}>
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
								<select name="src_user_id" id="src_filter_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
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
								<select name="data[user_ids][]" id="filter_user" style="width:200px;margin:5px 0 5px 0;" size="{select_size array=$filter_user_options}" multiple>
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
