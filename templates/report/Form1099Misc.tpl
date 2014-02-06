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
									'filter_exclude_user'
									);

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

				{if !isset($setup_data.4_psea_ids)
						OR !isset($setup_data.6_psea_ids)
						OR !isset($setup_data.7_psea_ids)}
					<tr class="tblDataError">
						<td colspan="4">
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
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Federal Income Tax Withheld (Box 4):{/t}
					</td>
					<td class="cellRightEditTable">
						<table width="60%">
						  <tr class="tblHeader">
							<td>
							  {t}Include{/t}
							</td>
							<td>
							  {t}Exclude{/t}
							</td>
						  </tr>
						  <tr align="center">
							<td>
							  <select id="columns" name="setup_data[4_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.4_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[4_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.4_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Medical and Health Care Payments (Box 6):{/t}
					</td>
					<td class="cellRightEditTable">
						<table width="60%">
						  <tr class="tblHeader">
							<td>
							  {t}Include{/t}
							</td>
							<td>
							  {t}Exclude{/t}
							</td>
						  </tr>
						  <tr align="center">
							<td>
							  <select id="columns" name="setup_data[6_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.6_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[6_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.6_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Nonemployee compensation (Box 7):{/t}
					</td>
					<td class="cellRightEditTable">
						<table width="60%">
						  <tr class="tblHeader">
							<td>
							  {t}Include{/t}
							</td>
							<td>
							  {t}Exclude{/t}
							</td>
						  </tr>
						  <tr align="center">
							<td>
							  <select id="columns" name="setup_data[7_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.7_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[7_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.7_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="3">
						{t}State ID Number{/t}
					</td>
				</tr>

				{foreach from=$setup_data.state_options item=state_options name=state_options}
					{if $smarty.foreach.state_options.first}
					<tr class="tblHeader">
						<td colspan="2" >
							{t}State{/t}
						</td>
						<td>
							{t}Employer State ID Number{/t}
						</td>
					</tr>
					{/if}
					<tr>
						<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
							{$state_options}
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="setup_data[state][{$state_options}][state_id]" size="20" value="{$setup_data.state.$state_options.state_id}">
						</td>
					</tr>
				{/foreach}

				</tbody>

				<tr class="tblHeader">
					<td colspan="3">
						{t}Report Filter Criteria{/t}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTableHeader">
						{t}Transaction Start Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="filter_data[transaction_start_date]" value="{getdate type="DATE" epoch=$filter_data.transaction_start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
						{t}ie:{/t}{$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTableHeader">
						{t}Transaction End Date:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="filter_data[transaction_end_date]" value="{getdate type="DATE" epoch=$filter_data.transaction_end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
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

				<tr onClick="showHelpEntry('sort')">
					<td class="{isvalid object="uf" label="type" value="cellLeftEditTableHeader"}">
						{t}Include Instruction Pages:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<input type="checkbox" class="checkbox" name="filter_data[include_instruction]" value="1" checked>
					</td>
				</tr>

				</table>
			</div>

			<div id="contentBoxFour">
				<input type="submit" name="BUTTON" value="{t}Display Form{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Display Form';this.form.submit()">
				<input type="submit" name="BUTTON" value="{t}Print Form{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Print Form';this.form.submit()">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}