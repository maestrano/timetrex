{include file="header.tpl" enable_calendar=TRUE body_onload="countAllReportCriteria();"}

<script	language=JavaScript>
var jmido={js_array values=$filter_data.job_manual_id_options name="jmido" assoc=true}

{literal}
var report_criteria_elements = new Array(
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_punch_branch',
									'filter_punch_department',
									'filter_user_title',
									'filter_include_user',
									'filter_exclude_user',
									'filter_column',
									'filter_job_group',
									'filter_include_job',
									'filter_exclude_job',
									'filter_job_item'
									);
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

				{capture assign=report_display_name}{t}Group{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Groups{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='group' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Default Branch{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Branches{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='branch' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Default Department{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Departments{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='department' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Punch Branch{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Branches{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='punch_branch' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Punch Department{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Departments{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='punch_department' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Employee Title{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Titles{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_title' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Include Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='include_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Exclude Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='exclude_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{if $current_company->getProductEdition() >= 20}
				{capture assign=report_display_name}{t}Job Group{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Groups{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='job_group' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Include Jobs{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Jobs{/t}{/capture}
				{htmlreportfilter type=job filter_data=$filter_data label='include_job' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Exclude Jobs{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Jobs{/t}{/capture}
				{htmlreportfilter type=job filter_data=$filter_data label='exclude_job' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Task{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Tasks{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='job_item' display_name=$report_display_name display_plural_name=$report_display_plural_name}
				{/if}

				{capture assign=report_display_name}{t}Columns{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Columns{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='column' order=TRUE display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{htmlreportsort filter_data=$filter_data}
{*
				<tr onClick="showHelpEntry('sort')">
					<td colspan="2" class="cellLeftEditTableHeader">
						{t}Automatic Refresh:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="filter_data[refresh]" colspan="3">
							{html_options options=$filter_data.refresh_options selected=$filter_data.refresh}
						</select>
					</td>
				</tr>
*}
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