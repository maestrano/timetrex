{if $data.add == 1}
	{include file="header.tpl"}
{else}
	{include file="header.tpl" enable_ajax=TRUE body_onload="showCalculation();"}
	{include file="company/EditCompanyDeduction.js.tpl"}
{/if}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$udf->Validator->isValid()}
					{include file="form_errors.tpl" object="udf"}
				{/if}

				<table class="editTable">

				{if $company_deduction_id == ''}
				<tr onClick="showHelpEntry('user')">
					<td class="{isvalid object="udf" label="user" value="cellLeftEditTable"}">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.user_full_name}
					</td>
				</tr>
				{/if}

				{if $data.add == 1}
					<tr onClick="showHelpEntry('deduction')">
						<td class="{isvalid object="udf" label="deduction" value="cellLeftEditTable"}">
							{t}Add Deductions:{/t}
						</td>
						<td class="cellRightEditTable">
							<select id="deduction_id" name="data[deduction_ids][]" multiple>
								{html_options options=$data.deduction_options selected=$data.deduction_ids}
							</select>
							<input type="hidden" name="data[add]" value="1">
						</td>
					</tr>
				{else}
					<tr onClick="showHelpEntry('status')">
						<td class="{isvalid object="udf" label="status" value="cellLeftEditTable"}">
							{t}Status:{/t}
						</td>
						<td class="cellRightEditTable">
							{$data.status}
						</td>
					</tr>

					<tr onClick="showHelpEntry('type')">
						<td class="{isvalid object="udf" label="type" value="cellLeftEditTable"}">
							{t}Type:{/t}
						</td>
						<td class="cellRightEditTable">
							{$data.type}
						</td>
					</tr>

					<tr onClick="showHelpEntry('name')">
						<td class="{isvalid object="udf" label="name" value="cellLeftEditTable"}">
							{t}Name:{/t}
						</td>
						<td class="cellRightEditTable">
							{$data.name}
						</td>
					</tr>

					<tr onClick="showHelpEntry('calculation')">
						<td class="{isvalid object="udf" label="calculation" value="cellLeftEditTable"}">
							{t}Calculation:{/t}
						</td>
						<td class="cellRightEditTable">
							{$data.calculation}
						</td>
					</tr>

					{if $data.country != '' }
						<tr onClick="showHelpEntry('country')">
							<td class="{isvalid object="udf" label="country" value="cellLeftEditTable"}">
								{t}Country:{/t}
							</td>
							<td class="cellRightEditTable">
								{$data.country}
							</td>
						</tr>
					{/if}

					{if $data.province != '' }
						<tr onClick="showHelpEntry('province')">
							<td class="{isvalid object="udf" label="province" value="cellLeftEditTable"}">
								{t}Province / State:{/t}
							</td>
							<td class="cellRightEditTable">
								{$data.province}
							</td>
						</tr>
					{/if}

					{if $data.district != '' }
						<tr onClick="showHelpEntry('district')">
							<td class="{isvalid object="udf" label="district" value="cellLeftEditTable"}">
								{t}District / County:{/t}
							</td>
							<td class="cellRightEditTable">
								{if $data.district_id == 'ALL' AND $data.default_user_value5 != ''}
									{$data.default_user_value5}
								{else}
									{$data.district}
								{/if}
							</td>
						</tr>
					{/if}

					{if $company_deduction_id != ''}
						{include file="company/EditCompanyDeductionUserValues.tpl" page_type="mass_user"}
					{else}
						{include file="company/EditCompanyDeductionUserValues.tpl" page_type="user"}
					{/if}

				{/if}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_include'))">
		</div>

		<input type="hidden" id="id" name="data[id]" value="{$data.id}">
		<input type="hidden" name="data[user_id]" value="{$data.user_id}">
		<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
		<input type="hidden" name="company_deduction_id" value="{$company_deduction_id}">
		<input type="hidden" id="calculation_id" value="{$data.calculation_id}">
		<input type="hidden" id="combined_calculation_id" value="{$data.combined_calculation_id}">
		<input type="hidden" id="country_id" value="{$data.country_id}">
		<input type="hidden" id="province_id" value="{$data.province_id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
