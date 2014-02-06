{include file="header.tpl" body_onload="formChangeDetect()"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="edittax" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$utf->Validator->isValid()}
					{include file="form_errors.tpl" object="utf"}
				{/if}

				<table class="editTable">

				{include file="data_saved.tpl" result=$data_saved}

				<tr class="tblHeader">
					<td colspan="2">
						{t}Employee:{/t}
						<a href="javascript: submitModifiedForm('filter_user', 'prev', document.edittax);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
						<select name="user_id" id="filter_user" onChange="submitModifiedForm('filter_user', '', document.edittax);">
							{html_options options=$tax_data.user_options selected=$user_id}
						</select>
						<input type="hidden" id="old_filter_user" value="{$user_id}">
						<a href="javascript: submitModifiedForm('filter_user', 'next', document.edittax);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$full_name}
					</td>
				</tr>

				{if $tax_data.country == 'CA'}
					<tr onClick="showHelpEntry('federal_claim')">
						<td class="{isvalid object="utf" label="federal_claim" value="cellLeftEditTable"}">
							{t}Federal Claim Amount:{/t}
						</td>
						<td class="cellRightEditTable">
							$<input type="text" size="10" name="tax_data[federal_claim]" value="{$tax_data.federal_claim}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('federal_tax_exempt')">
						<td class="{isvalid object="utf" label="federal_tax_exempt" value="cellLeftEditTable"}">
							{t}Federal Tax Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[federal_tax_exempt]" value="1"{if $tax_data.federal_tax_exempt == TRUE}checked{/if}>
						</td>
					</tr>

					<tr onClick="showHelpEntry('provincial_claim')">
						<td class="{isvalid object="utf" label="provincial_claim" value="cellLeftEditTable"}">
							{t}Provincial Claim Amount:{/t}
						</td>
						<td class="cellRightEditTable">
							$<input type="text" size="10" name="tax_data[provincial_claim]" value="{$tax_data.provincial_claim}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('provincial_tax_exempt')">
						<td class="{isvalid object="utf" label="provincial_tax_exempt" value="cellLeftEditTable"}">
							{t}Provincial Tax Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[provincial_tax_exempt]" value="1"{if $tax_data.provincial_tax_exempt == TRUE}checked{/if}>
						</td>
					</tr>

					<tr onClick="showHelpEntry('federal_additional_deduction')">
						<td class="{isvalid object="utf" label="federal_additional_deduction" value="cellLeftEditTable"}">
							{t}Additional Deduction Amount:{/t}
						</td>
						<td class="cellRightEditTable">
							$<input type="text" size="10" name="tax_data[federal_additional_deduction]" value="{$tax_data.federal_additional_deduction}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('wcb_rate')">
						<td class="{isvalid object="utf" label="wcb_rate" value="cellLeftEditTable"}">
							{t}WCB Rate:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="6" name="tax_data[wcb_rate]" value="{$tax_data.wcb_rate}">%
						</td>
					</tr>

					<tr onClick="showHelpEntry('vacation_rate')">
						<td class="{isvalid object="utf" label="vacation_rate" value="cellLeftEditTable"}">
							{t}Vacation Rate:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="6" name="tax_data[vacation_rate]" value="{$tax_data.vacation_rate}">%
						</td>
					</tr>

					<tr onClick="showHelpEntry('release_vacation')">
						<td class="{isvalid object="utf" label="release_vacation" value="cellLeftEditTable"}">
							{t}Always Release Vacation:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[release_vacation]" value="1"{if $tax_data.release_vacation == TRUE}checked{/if}>
						</td>
					</tr>

					<tr onClick="showHelpEntry('ei_exempt')">
						<td class="{isvalid object="utf" label="ei_exempt" value="cellLeftEditTable"}">
							{t}EI Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[ei_exempt]" value="1"{if $tax_data.ei_exempt == TRUE}checked{/if}>
						</td>
					</tr>

					<tr onClick="showHelpEntry('cpp_exempt')">
						<td class="{isvalid object="utf" label="cpp_exempt" value="cellLeftEditTable"}">
							{t}CPP Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[cpp_exempt]" value="1"{if $tax_data.cpp_exempt == TRUE}checked{/if}>
						</td>
					</tr>
				{elseif ($tax_data.country == 'US')}
					<tr onClick="showHelpEntry('federal_filing_status')">
						<td class="{isvalid object="utf" label="federal_filing_status" value="cellLeftEditTable"}">
							{t}Federal Filing Status:{/t}
						</td>
						<td class="cellRightEditTable">
							<select id="country" name="tax_data[federal_filing_status]">
								{html_options options=$tax_data.federal_filing_status_options selected=$tax_data.federal_filing_status_id}
							</select>
						</td>
					</tr>

					<tr onClick="showHelpEntry('federal_allowance')">
						<td class="{isvalid object="utf" label="federal_allowance" value="cellLeftEditTable"}">
							{t}Federal Allowance:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="4" name="tax_data[federal_allowance]" value="{$tax_data.federal_allowance}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('federal_additional_deduction')">
						<td class="{isvalid object="utf" label="federal_additional_deduction" value="cellLeftEditTable"}">
							{t}Federal Additional Deduction:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="6" name="tax_data[federal_additional_deduction]" value="{$tax_data.federal_additional_deduction}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('federal_tax_exempt')">
						<td class="{isvalid object="utf" label="federal_tax_exempt" value="cellLeftEditTable"}">
							{t}Federal Tax Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[federal_tax_exempt]" value="1"{if $tax_data.federal_tax_exempt == TRUE}checked{/if}>
						</td>
					</tr>

					<tr onClick="showHelpEntry('state_filing_status')">
						<td class="{isvalid object="utf" label="state_filing_status" value="cellLeftEditTable"}">
							{t}State Filing Status:{/t}
						</td>
						<td class="cellRightEditTable">
							<select id="country" name="tax_data[state_filing_status]">
								{html_options options=$tax_data.state_filing_status_options selected=$tax_data.state_filing_status_id}
							</select>
						</td>
					</tr>

					<tr onClick="showHelpEntry('state_allowance')">
						<td class="{isvalid object="utf" label="state_allowance" value="cellLeftEditTable"}">
							{t}State Allowance:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="4" name="tax_data[state_allowance]" value="{$tax_data.state_allowance}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('state_additional_deduction')">
						<td class="{isvalid object="utf" label="state_additional_deduction" value="cellLeftEditTable"}">
							{t}State Additional Deduction:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="6" name="tax_data[state_additional_deduction]" value="{$tax_data.state_additional_deduction}">
						</td>
					</tr>

					<tr onClick="showHelpEntry('state_ui_rate')">
						<td class="{isvalid object="utf" label="state_ui_rate" value="cellLeftEditTable"}">
							{t}State UI Rate:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="6" name="tax_data[state_ui_rate]" value="{$tax_data.state_ui_rate}"> (ie: 3.96%)
						</td>
					</tr>

					<tr onClick="showHelpEntry('state_ui_wage_base')">
						<td class="{isvalid object="utf" label="state_ui_wage_base" value="cellLeftEditTable"}">
							{t}State UI Wage Base:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="6" name="tax_data[state_ui_wage_base]" value="{$tax_data.state_ui_wage_base}"> (ie: $7000)
						</td>
					</tr>

					<tr onClick="showHelpEntry('provincial_tax_exempt')">
						<td class="{isvalid object="utf" label="provincial_tax_exempt" value="cellLeftEditTable"}">
							{t}State Tax Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[provincial_tax_exempt]" value="1"{if $tax_data.provincial_tax_exempt == TRUE}checked{/if}>
						</td>
					</tr>

					<tr onClick="showHelpEntry('social_security_exempt')">
						<td class="{isvalid object="utf" label="social_security_exempt" value="cellLeftEditTable"}">
							{t}Social Security (FICA) Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[social_security_exempt]" value="1"{if $tax_data.social_security_exempt == TRUE}checked{/if}>
						</td>
					</tr>

					<tr onClick="showHelpEntry('ui_exempt')">
						<td class="{isvalid object="utf" label="ui_exempt" value="cellLeftEditTable"}">
							{t}UnEmployment Insurance (FUTA) Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[ui_exempt]" value="1"{if $tax_data.ui_exempt == TRUE}checked{/if}>
						</td>
					</tr>

					<tr onClick="showHelpEntry('medicare_exempt')">
						<td class="{isvalid object="utf" label="medicare_exempt" value="cellLeftEditTable"}">
							{t}Medicare Exempt:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="checkbox" class="checkbox" name="tax_data[medicare_exempt]" value="1"{if $tax_data.medicare_exempt == TRUE}checked{/if}>
						</td>
					</tr>

				{/if}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="tax_data[id]" value="{$tax_data.id}">
		<input type="hidden" name="tax_data[user_id]" value="{$user_id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
