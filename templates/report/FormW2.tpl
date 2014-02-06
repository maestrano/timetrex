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
					<td colspan="4">
						{t}Saved Reports{/t}
					</td>
				</tr>

				{htmlreportsave generic_data=$generic_data object="ugdf"}

				{if !isset($setup_data.1_psea_ids)
					OR !isset($setup_data.2_psea_ids)
					OR !isset($setup_data.3_psea_ids)
					OR !isset($setup_data.4_psea_ids)
					OR !isset($setup_data.5_psea_ids)
					OR !isset($setup_data.6_psea_ids)}
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
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Wages, Tips, Other Compensation (Box 1):{/t}
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
							  <select id="columns" name="setup_data[1_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.1_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[1_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.1_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Federal Income Tax Withheld (Box 2):{/t}
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
							  <select id="columns" name="setup_data[2_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.2_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[2_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.2_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Social Security Wages (Box 3):{/t}
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
							  <select id="columns" name="setup_data[3_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.3_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[3_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.3_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Social Security Tax Withheld (Box 4):{/t}
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
						{t}Medicare Wages and Tips (Box 5):{/t}
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
							  <select id="columns" name="setup_data[5_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.5_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[5_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.5_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Medicare Tax Withheld (Box 6):{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
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
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Social Security Tips (Box 7):{/t}
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
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Allocated Tips (Box 8):{/t}
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
							  <select id="columns" name="setup_data[8_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.8_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[8_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.8_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Advance EIC Payment (Box 9):{/t}
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
							  <select id="columns" name="setup_data[9_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.9_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[9_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.9_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Dependent Care Benefits (Box 10):{/t}
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
							  <select id="columns" name="setup_data[10_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.10_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[10_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.10_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Nonqualified Plans (Box 11):{/t}
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
							  <select id="columns" name="setup_data[11_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.11_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[11_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.11_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Box 12a:{/t} [{t}Code{/t}:<input type="text" name="setup_data[12a_code]" size="5" value="{$setup_data.12a_code}">]
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
							  <select id="columns" name="setup_data[12a_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.12a_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[12a_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.12a_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Box 12b:{/t} [{t}Code{/t}:<input type="text" name="setup_data[12b_code]" size="5" value="{$setup_data.12b_code}">]
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
							  <select id="columns" name="setup_data[12b_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.12b_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[12b_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.12b_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Box 12c:{/t} [{t}Code{/t}:<input type="text" name="setup_data[12c_code]" size="5" value="{$setup_data.12c_code}">]
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
							  <select id="columns" name="setup_data[12c_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.12c_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[12c_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.12c_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Box 12d:{/t} [{t}Code{/t}:<input type="text" name="setup_data[12d_code]" size="5" value="{$setup_data.12d_code}">]
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
							  <select id="columns" name="setup_data[12d_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.12d_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[12d_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.12d_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Box 14 (Other):{/t} [{t}Name{/t}:<input type="text" name="setup_data[14a_name]" size="5" value="{$setup_data.14a_name}">]
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
							  <select id="columns" name="setup_data[14a_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.14a_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[14a_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.14a_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Box 14 (Other):{/t} [{t}Name{/t}:<input type="text" name="setup_data[14b_name]" size="5" value="{$setup_data.14b_name}">]
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
							  <select id="columns" name="setup_data[14b_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.14b_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[14b_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.14b_exclude_psea_ids}
							  </select>
							</td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="tax" value="cellLeftEditTable"}">
						{t}Box 14 (Other):{/t} [{t}Name{/t}:<input type="text" name="setup_data[14c_name]" size="5" value="{$setup_data.14c_name}">]
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
							  <select id="columns" name="setup_data[14c_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.14c_psea_ids}
							  </select>
							</td>
							<td>
							  <select id="columns" name="setup_data[14c_exclude_psea_ids][]" size="{select_size array=$filter_data.pay_stub_entry_account_options}" multiple>
								  {html_options options=$filter_data.pay_stub_entry_account_options selected=$setup_data.14c_exclude_psea_ids}
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
						<td colspan="2">
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
					<td colspan="5">
						{t}Report Filter Criteria{/t}
					</td>
				</tr>

				<tr>
					<td colspan="2" class="{isvalid object="ugdf" label="quarter" value="cellLeftEditTableHeader"}">
						{t}Year:{/t}
					</td>
					<td colspan="3" class="cellRightEditTable">
						<select id="columns" name="filter_data[year]">
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

				{htmlreportsort filter_data=$filter_data}

				</table>
			</div>

			<div id="contentBoxFour">
			<input type="submit" name="BUTTON" value="{t}Display Form{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Display Form';this.form.submit()">
			<input type="submit" name="BUTTON" value="{t}Print Form{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Print Form';this.form.submit()">
			<input type="submit" name="BUTTON" value="{t}Display Report{/t}" onClick="selectAllReportCriteria(); this.form.target = '_blank'; document.getElementById('action').name = 'action:Display Report';">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}