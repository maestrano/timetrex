{include file="header.tpl" body_onload="countAllReportCriteria();"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_user_status',
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_user_title',
									'filter_pay_period',
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

				{if !isset($setup_data.ei_psea_ids)
						OR !isset($setup_data.cpp_psea_ids)
						OR !isset($setup_data.tax_psea_ids)}
					<tr class="tblDataError">
						<td colspan="3">
							<b>{t}ERROR: Report has not been setup yet! Please click the arrow below to do so now.{/t}</b>
						</td>
					</tr>
				{/if}

				<tr>
					<td colspan="2" class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
						<a href="javascript:toggleRowObject('setup');toggleImage(document.getElementById('setup_img'), '{$IMAGES_URL}/nav_bottom_sm.gif', '{$IMAGES_URL}/nav_top_sm.gif');"><img style="vertical-align: middle" id="setup_img" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a><b> {t}Report Setup:{/t}</b>
					</td>
					<td class="cellRightEditTable">
						{t}Specify which Pay Stub Accounts total for each box in the form. Click arrow to modify.{/t}
					</td>
				</tr>

				<tbody id="setup" style="display:none" >
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="ei" value="cellLeftEditTable"}">
						{t}Employee/Employer EI Accounts:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[ei_psea_ids][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.ei_psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="cpp" value="cellLeftEditTable"}">
						{t}Employee/Employer CPP Accounts:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[cpp_psea_ids][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.cpp_psea_ids}
						</select>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Income Tax Accounts:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[tax_psea_ids][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.tax_psea_ids}
						</select>
					</td>
				</tr>
				</tbody>

				<tr class="tblHeader">
					<td colspan="3">
						{t}Report Filter Criteria{/t}
					</td>
				</tr>

				{capture assign=report_display_name}{t}Pay Period{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Pay Periods{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='pay_period' display_name=$report_display_name display_plural_name=$report_display_plural_name}

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

				{htmlreportgroup filter_data=$filter_data}
				{htmlreportsort filter_data=$filter_data}

				</table>
			</div>

			<div id="contentBoxFour">
				<input type="submit" name="BUTTON" value="{t}Display Report{/t}" onClick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').name = 'action:Display Report';">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}