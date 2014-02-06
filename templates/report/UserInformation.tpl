{include file="header.tpl" body_onload="countAllReportCriteria();"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_user_status',
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_user_title',
									'filter_include_user',
									'filter_exclude_user',
									'filter_column');

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

				{capture assign=report_display_name}{t}Columns{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Columns{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='column' order=TRUE display_name=$report_display_name display_plural_name=$report_display_plural_name}
				
				{htmlreportsort filter_data=$filter_data}
				
				</table>
			</div>

			<div id="contentBoxFour">
				<input type="BUTTON" id="display_report" name="action" value="{t}Display Report{/t}" onClick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').name = 'action:Display Report'; this.form.submit();">
				<input type="submit" name="BUTTON" value="{t}Export{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Export';">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}