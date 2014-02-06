{include file="header.tpl" enable_calendar=true body_onload="countAllReportCriteria();"}

<script	language=JavaScript>

{literal}
var report_criteria_elements = new Array(
									'filter_group',
									'filter_default_branch',
									'filter_default_department',
									'filter_schedule_branch',
									'filter_schedule_department',
									'filter_user_title',
									'filter_include_user',
									'filter_exclude_user' );

function ViewTypeTarget(obj) {
	if ( !isUndefined(obj) ) {
		if ( obj.value == 10 ) { //Month
			action = '{/literal}{$BASE_URL}{literal}/schedule/ViewScheduleMonth.php';
		} else if ( obj.value == 20 ) { //Week
			action = '{/literal}{$BASE_URL}{literal}/schedule/ViewScheduleWeek.php';
		} else if ( obj.value == 30 ) { //Day
			action = '{/literal}{$BASE_URL}{literal}/schedule/ViewScheduleLinear.php';
		} else if ( obj == 'do:print_schedule' ) {
			action = '{/literal}{$BASE_URL}{literal}/schedule/ViewSchedule.php';
		} else {
			action = '{/literal}{$BASE_URL}{literal}/schedule/ViewSchedule.php';
		}
	} else {
		action = '{/literal}{$BASE_URL}{literal}/schedule/ViewSchedule.php';
	}

	//alert('aValue: '+ obj.value +' Action:'+ action);
	document.getElementById('schedule_form').action = action;

	if ( obj == 'do:print_schedule' ) {
		document.getElementById('schedule_form').target = '';
	} else {
		document.getElementById('schedule_form').target = 'Schedule';
		document.getElementById('schedule_layer').src = action;
	}

	//alert('bSrc:'+document.getElementById('schedule_layer').src);
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
			{*
			If cases where people need to select many employees, the GET URL length can be exceeded. The problem though is
			if we use POST, when editing schedules they can't refresh the page automatically without being prompted to re-submit form data.
			*}
			<form method="get" name="schedule" id="schedule_form">
				<input type="hidden" id="tmp_action" name="do" value="">

					<div id="contentBoxTwoEdit">
						{if !$ugdf->Validator->isValid()}
							{include file="form_errors.tpl" object="ugdf"}
						{/if}

						<table class="editTable">

						{if $permission->Check('schedule','view') OR $permission->Check('schedule','view_child')}
						<tr class="tblHeader">
							<td colspan="3">
								{t}Saved Schedules{/t}
							</td>
						</tr>
						{htmlreportsave generic_data=$generic_data button_prefix="do" action_element_id="tmp_action" onclick="ViewTypeTarget()" object="ugdf"}
						{/if}

						<tr class="tblHeader">
							<td colspan="3">
								{t}Schedule Filter Criteria{/t}
							</td>
						</tr>

						{if $permission->Check('schedule','view') OR $permission->Check('schedule','view_child')}
						{capture assign=report_display_name}{t}Group{/t}{/capture}
						{capture assign=report_display_plural_name}{t}Groups{/t}{/capture}
						{htmlreportfilter filter_data=$filter_data label='group' display_name=$report_display_name display_plural_name=$report_display_plural_name}

						{capture assign=report_display_name}{t}Default Branch{/t}{/capture}
						{capture assign=report_display_plural_name}{t}Branches{/t}{/capture}
						{htmlreportfilter filter_data=$filter_data label='default_branch' display_name=$report_display_name display_plural_name=$report_display_plural_name}

						{capture assign=report_display_name}{t}Default Department{/t}{/capture}
						{capture assign=report_display_plural_name}{t}Departments{/t}{/capture}
						{htmlreportfilter filter_data=$filter_data label='default_department' display_name=$report_display_name display_plural_name=$report_display_plural_name}

						{capture assign=report_display_name}{t}Scheduled Branch{/t}{/capture}
						{capture assign=report_display_plural_name}{t}Branches{/t}{/capture}
						{htmlreportfilter filter_data=$filter_data label='schedule_branch' display_name=$report_display_name display_plural_name=$report_display_plural_name}

						{capture assign=report_display_name}{t}Scheduled Department{/t}{/capture}
						{capture assign=report_display_plural_name}{t}Departments{/t}{/capture}
						{htmlreportfilter filter_data=$filter_data label='schedule_department' display_name=$report_display_name display_plural_name=$report_display_plural_name}

						{capture assign=report_display_name}{t}Employee Title{/t}{/capture}
						{capture assign=report_display_plural_name}{t}Titles{/t}{/capture}
						{htmlreportfilter filter_data=$filter_data label='user_title' display_name=$report_display_name display_plural_name=$report_display_plural_name}

						{capture assign=report_display_name}{t}Include Employees{/t}{/capture}
						{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
						{htmlreportfilter filter_data=$filter_data label='include_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

						{capture assign=report_display_name}{t}Exclude Employees{/t}{/capture}
						{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
						{htmlreportfilter filter_data=$filter_data label='exclude_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}
						{/if}
							<tr>
								<td class="cellLeftEditTableHeader" width="10%" colspan="2" nowrap>
									<b>{t}Start Date:{/t}</b>
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="15" id="start_date" name="filter_data[start_date]" value="{getdate type="DATE" epoch=$filter_data.start_date}">
									<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
									<b>{t}Show:{/t}</b>
									<select name="filter_data[show_days]">
										{html_options options=$filter_data.show_days_options selected=$filter_data.show_days}
									</select>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTableHeader" colspan="2" nowrap>
									{t}View:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="filter_data[view_type_id]" id="filter_view_type">
										{html_options options=$filter_data.view_type_options selected=$filter_data.view_type_id}
									</select>
								</td>
							</tr>

							<tr>
								<td class="tblHeader" colspan="3">
									<a name="schedule"></a>
									<input type="submit" name="do:view_schedule" value="{t}View Schedule{/t}" onClick="ViewTypeTarget(document.getElementById('filter_view_type')); selectAllReportCriteria();">
									<input type="submit" name="do:print_schedule" value="{t}Print Schedule{/t}" onClick="ViewTypeTarget('do:print_schedule'); selectAllReportCriteria();">
									{if $permission->Check('schedule','view') OR $permission->Check('schedule','view_child')}
										{t}Group Schedule{/t}: <input type="checkbox" name="filter_data[group_schedule]" value="1">
									{/if}
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr>
					<td colspan="10">
						<iframe style="width:100%; height:0px; border: 5px" id="schedule_layer" name="Schedule" src="{$BASE_URL}/blank.html"></iframe>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
