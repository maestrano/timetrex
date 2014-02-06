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
		<form method="post" name="report" action="{$smarty.server.SCRIPT_NAME}">
			<input type="hidden" id="action" name="action" value="">

		    <div id="contentBoxTwoEdit">

				{if !$ugdf->Validator->isValid()}
					{include file="form_errors.tpl" object="ugdf"}
				{/if}

				<table class="editTable">

				<tr class="tblDataError">
					<td colspan="3">
						<br><b>WARNING: THIS REPORT IS OUT OF DATE. UP-TO-DATE TAX REPORTS ARE NOW ONLY AVAILABLE IN THE NEW <a href="{if $smarty.server.HTTP_HOST == 'www.timetrex.com' OR $smarty.server.HTTP_HOST == 'timetrex.com'}http{if $smarty.server.HTTPS == TRUE}s{/if}://{$config_vars.other.hostname}/interface/{/if}../BetaTest.php">v5.0 INTERFACE</a></span></b>.</b><br><br>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="3">
						{t}Saved Reports{/t}
					</td>
				</tr>

				{htmlreportsave generic_data=$generic_data object="ugdf"}

				{if !isset($setup_data.employee_ei_psea_id) OR $setup_data.employee_ei_psea_id == 0
						OR !isset($setup_data.income_psea_ids) OR $setup_data.income_psea_ids == 0
						OR !isset($setup_data.employer_ei_psea_id) OR $setup_data.employer_ei_psea_id == 0
						OR !isset($setup_data.employee_cpp_psea_id) OR $setup_data.employee_cpp_psea_id == 0
						OR !isset($setup_data.employer_cpp_psea_id) OR $setup_data.employer_cpp_psea_id == 0
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
					<td class="cellRightEditTable" >
						{t}Specify which Pay Stub Accounts total for each box in the form. Click arrow to modify.{/t}
					</td>
				</tr>

				<tbody id="setup" style="display:none" >
				<tr class="tblHeader">
					<td colspan="3">
						{t}Company Information{/t}
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="company_name" value="cellLeftEditTable"}">
						{t}Company Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[company_name]" size="25" value="{$setup_data.company_name|default:$current_company->getName()}">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="company_address1" value="cellLeftEditTable"}">
						{t}Address (Line 1):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[company_address1]" size="25" value="{$setup_data.company_address1|default:$current_company->getAddress1()}">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="company_address2" value="cellLeftEditTable"}">
						{t}Address (Line 2):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[company_address2]" size="25" value="{$setup_data.company_address2|default:$current_company->getAddress2()}">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="company_city" value="cellLeftEditTable"}">
						{t}City:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[company_city]" size="25" value="{$setup_data.company_city|default:$current_company->getCity()}">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="company_province" value="cellLeftEditTable"}">
						{t}Province:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[company_province]" size="5" value="{$setup_data.company_province|default:$current_company->getProvince()}">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="company_postal_code" value="cellLeftEditTable"}">
						{t}Postal Code:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[company_postal_code]" size="8" value="{$setup_data.company_postal_code|default:$current_company->getPostalCode()}">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="payroll_account_number" value="cellLeftEditTable"}">
						{t}Payroll Account Number:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[payroll_account_number]" size="25" value="{$setup_data.payroll_account_number|default:$current_company->getBusinessNumber()}">
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="3">
						{t}Form Setup{/t}
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Employment Income (Box: 14):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[income_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.income_psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Income Tax (Box: 22):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[tax_psea_ids][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.tax_psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="employee_cpp" value="cellLeftEditTable"}">
						{t}Employee CPP (Box: 16):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[employee_cpp_psea_id][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.employee_cpp_psea_id}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="employer_cpp" value="cellLeftEditTable"}">
						{t}Employer CPP:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[employer_cpp_psea_id][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.employer_cpp_psea_id}
						</select>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}EI Insurable Earnings (Box: 24):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[ei_earnings_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.ei_earnings_psea_ids}
						</select>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}CPP Pensionable Earnings (Box: 26):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[cpp_earnings_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.cpp_earnings_psea_ids}
						</select>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="employee_ei" value="cellLeftEditTable"}">
						{t}Employee EI (Box: 18):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[employee_ei_psea_id][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.employee_ei_psea_id}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="employer_ei" value="cellLeftEditTable"}">
						{t}Employer EI:{/t}
					</td>
					<td class="cellRightEditTable">
						{* There may be several Employer EI accounts (different rates), so all need to be included, hence the multi-select box. *}
						<select id="columns" name="setup_data[employer_ei_psea_id][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.employer_ei_psea_id}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="union_dues" value="cellLeftEditTable"}">
						{t}Union Dues (Box: 44):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[union_dues_psea_id][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.union_dues_psea_id}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}RPP Contributions (Box: 20):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[rpp_psea_ids][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.rpp_psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Charitable Donations (Box: 46):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[charity_psea_ids][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.charity_psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Pension Adjustment (Box: 52):{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[pension_adjustment_psea_ids][]" size="{select_size array=$filter_data.deduction_pay_stub_entry_account_options}" multiple>
							{html_options options=$filter_data.deduction_pay_stub_entry_account_options selected=$setup_data.pension_adjustment_psea_ids}
						</select>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="3">
						{t}Other Information{/t}
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2">
						{t}Box{/t}
					</td>
					<td>
						{t}Pay Stub Account(s){/t}
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						<input type="text" name="setup_data[other_box][0][box]" size="4" value="{$setup_data.other_box.0.box|default:0}">
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[other_box][0][psea_ids][]" size="5" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.other_box.0.psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						<input type="text" name="setup_data[other_box][1][box]" size="4" value="{$setup_data.other_box.1.box|default:0}">
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[other_box][1][psea_ids][]" size="5" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.other_box.1.psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						<input type="text" name="setup_data[other_box][2][box]" size="4" value="{$setup_data.other_box.2.box|default:0}">
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[other_box][2][psea_ids][]" size="5" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.other_box.2.psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						<input type="text" name="setup_data[other_box][3][box]" size="4" value="{$setup_data.other_box.3.box|default:0}">
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[other_box][3][psea_ids][]" size="5" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.other_box.3.psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						<input type="text" name="setup_data[other_box][4][box]" size="4" value="{$setup_data.other_box.4.box|default:0}">
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[other_box][4][psea_ids][]" size="5" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.other_box.4.psea_ids}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						<input type="text" name="setup_data[other_box][5][box]" size="4" value="{$setup_data.other_box.5.box|default:0}">
					</td>
					<td class="cellRightEditTable">
						<select id="columns" name="setup_data[other_box][5][psea_ids][]" size="5" multiple>
							{html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.other_box.5.psea_ids}
						</select>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="3">
						{t}eFile Information{/t}
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="employee_ei" value="cellLeftEditTable"}">
						{t}Transmitter Number:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="setup_data[transmitter_number]" size="15" value="{$setup_data.transmitter_number}">
					</td>
				</tr>

				</tbody>

				<tr class="tblHeader">
					<td colspan="3">
						{t}Report Filter Criteria{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('sort')">
					<td colspan="2" class="{isvalid object="uf" label="type" value="cellLeftEditTableHeader"}">
						{t}Year:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<select id="year" name="filter_data[year]">
							{html_options options=$filter_data.year_options selected=$filter_data.year}
						</select>
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

				{htmlreportgroup filter_data=$filter_data}
				{htmlreportsort filter_data=$filter_data}

				<tr onClick="showHelpEntry('sort')">
					<td colspan="2" class="{isvalid object="uf" label="type" value="cellLeftEditTableHeader"}">
						{t}Form Type:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<select id="year" name="filter_data[type]">
							{html_options options=$filter_data.type_options selected=$filter_data.type}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('sort')">
					<td colspan="2" class="{isvalid object="uf" label="type" value="cellLeftEditTableHeader"}">
						{t}Include Instruction Page:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<input type="checkbox" class="checkbox" name="filter_data[include_t4_back]" value="1" checked>
					</td>
				</tr>

				</table>
			</div>

			<div id="contentBoxFour">
				<input type="submit" name="BUTTON" value="{t}Display Report{/t}" onClick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').name = 'action:display_report';">
				<input type="submit" name="BUTTON" value="{t}Export{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Export';">
				<input type="submit" name="BUTTON" value="{t}Display T4's{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:display t4s';this.form.submit()">
				<input type="submit" name="BUTTON" value="{t}Export XML{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:export xml';this.form.submit()">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}