{include file="header.tpl" enable_calendar=TRUE body_onload="countAllReportCriteria(); showReportDateType();"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_pay_period',
									'filter_user_status',
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_user_title',
									'filter_include_user',
									'filter_exclude_user',
									'filter_currency',
									'filter_column');

var report_date_type_elements = new Array();
report_date_type_elements['date_type_date'] = new Array('start_date', 'end_date');
report_date_type_elements['date_type_pay_period'] = new Array('src_filter_pay_period', 'filter_pay_period');
function showReportDateType() {
	for ( i in report_date_type_elements ) {
		if ( document.getElementById( i ) ) {
			if ( document.getElementById( i ).checked == true ) {
				class_name = '';
			} else {
				class_name = 'DisableFormElement';
			}

			for (var x=0; x < report_date_type_elements[i].length ; x++) {
				document.getElementById( report_date_type_elements[i][x] ).className = class_name;
			}
		}
	}
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<form method="post" name="report" action="{$smarty.server.SCRIPT_NAME}" target="_self">
			<input type="hidden" id="action" name="action" value="">

		    <div id="contentBoxTwoEdit">

				{if !$ugdf->Validator->isValid()}
					{include file="form_errors.tpl" object="ugdf"}
				{/if}

				<table class="editTable">

				<tr class="tblHeader">
					<td colspan="3">
						{t}Saved Reports{/t}
					</td>
				</tr>

				{htmlreportsave generic_data=$generic_data object="ugdf"}

				<tr class="tblHeader">
					<td colspan="3">
						{t}Report Filter Criteria{/t}
					</td>
				</tr>

				<tr>
					<td class="cellReportRadioColumn" rowspan="2">
						<input type="radio" class="checkbox" id="date_type_date" name="filter_data[date_type]" value="date" onClick="showReportDateType();" {if $filter_data.date_type  == '' OR $filter_data.date_type == 'date'}checked{/if}>
					</td>
					<td class="cellLeftEditTableHeader">
						{t}Start Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="filter_data[start_date]" value="{getdate type="DATE" epoch=$filter_data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTableHeader">
						{t}End Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="filter_data[end_date]" value="{getdate type="DATE" epoch=$filter_data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				{capture assign=report_display_name}{t}Pay Period{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Pay Periods{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data date_type=true label='pay_period' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Employee Status{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employee Statuses{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_status' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Group{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Groups{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='group' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Default Branch{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Branches{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='branch' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Default Department{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Departments{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='department' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Employee Title{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Titles{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_title' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Include Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='include_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Exclude Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='exclude_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Currency{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Currencies{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='currency' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Columns{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Columns{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='column' order=TRUE display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{htmlreportgroup filter_data=$filter_data total=4}
				{htmlreportsort filter_data=$filter_data}

				<tr onClick="showHelpEntry('no_data')">
					<td colspan="2" class="{isvalid object="uf" label="no_data" value="cellLeftEditTableHeader"}">
						{t}Include Employees with no data:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="filter_data[include_no_data_users]" value="1">
					</td>
				</tr>

				</table>
			</div>

			<div id="contentBoxFour">
				<input type="submit" name="BUTTON" value="{t}Display Report{/t}" onClick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').name = 'action:Display Report';">
				<input type="submit" name="BUTTON" value="{t}Export{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Export';">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}