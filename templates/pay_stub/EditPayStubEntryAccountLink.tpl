{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$psealf->Validator->isValid()}
					{include file="form_errors.tpl" object="psealf"}
				{/if}

				<table class="editTable">

				{include file="data_saved.tpl" result=$data_saved}

				<tr onClick="showHelpEntry('total_gross')">
					<td class="{isvalid object="psealf" label="total_gross" value="cellLeftEditTable"}">
						{t}Total Gross:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[total_gross]">
							{html_options options=$data.total_account_options selected=$data.total_gross}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('total_employee_deduction')">
					<td class="{isvalid object="psealf" label="total_employee_deduction" value="cellLeftEditTable"}">
						{t}Total Employee Deduction:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[total_employee_deduction]">
							{html_options options=$data.total_account_options selected=$data.total_employee_deduction}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('total_employer_deduction')">
					<td class="{isvalid object="psealf" label="total_employer_deduction" value="cellLeftEditTable"}">
						{t}Total Employer Deduction:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[total_employer_deduction]">
							{html_options options=$data.total_account_options selected=$data.total_employer_deduction}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('total_net_pay')">
					<td class="{isvalid object="psealf" label="total_net_pay" value="cellLeftEditTable"}">
						{t}Total Net Pay:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[total_net_pay]">
							{html_options options=$data.total_account_options selected=$data.total_net_pay}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('regular_Time')">
					<td class="{isvalid object="psealf" label="regular_time" value="cellLeftEditTable"}">
						{t}Regular Time Earnings:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[regular_time]">
							{html_options options=$data.earning_account_options selected=$data.regular_time}
						</select>
					</td>
				</tr>

				{if $current_company->getCountry() == 'CA'}
					<tr onClick="showHelpEntry('employee_cpp')">
						<td class="{isvalid object="psealf" label="employee_cpp" value="cellLeftEditTable"}">
							{t}Employee CPP:{/t}
						</td>
						<td class="cellRightEditTable">
							<select name="data[employee_cpp]">
								{html_options options=$data.employee_deduction_account_options selected=$data.employee_cpp}
							</select>
						</td>
					</tr>

					<tr onClick="showHelpEntry('employee_ei')">
						<td class="{isvalid object="psealf" label="employee_ei" value="cellLeftEditTable"}">
							{t}Employee EI:{/t}
						</td>
						<td class="cellRightEditTable">
							<select name="data[employee_ei]">
								{html_options options=$data.employee_deduction_account_options selected=$data.employee_ei}
							</select>
						</td>
					</tr>
				{/if}

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
