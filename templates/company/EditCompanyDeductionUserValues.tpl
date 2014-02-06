<!-- Start: Dynamic Form Fields -->
{if $page_type == 'mass_user'}
	<tr>
		<td colspan="2">
			<table width="100%">
				{foreach from=$data.users item=row name=users}
					{if $smarty.foreach.users.first}
					<tr class="tblHeader">
						<td>
							{t}#{/t}
						</td>
						<td>
							{t}Employee{/t}
						</td>

						{if $data.combined_calculation_id == ''}
						{elseif $data.combined_calculation_id == 10}
							<td>
								{t}Percent{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}%){/if}
							</td>
						{elseif $data.combined_calculation_id == 15}
							<td>
								{t}Percent{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}%){/if}
							</td>
							<td>
								{t}Annual Wage Base/Maximum Earnings{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
							<td>
								{t}Annual Deduction Amount{/t}
								{if $data.default_user_value3 != ''}<br>({t}Default{/t}: {$data.default_user_value3}){/if}
							</td>
						{elseif $data.combined_calculation_id == 17 OR $data.combined_calculation_id == 19}
							<td>
								{t}Percent{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}%){/if}
							</td>
							<td>
								{t}Annual Amount Greater Than{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
							<td>
								{t}Annual Amount Less Than{/t}
								{if $data.default_user_value3 != ''}<br>({t}Default{/t}: {$data.default_user_value3}){/if}
							</td>
							<td>
								{t}Annual Deduction Amount{/t}
								{if $data.default_user_value4 != ''}<br>({t}Default{/t}: {$data.default_user_value4}){/if}
							</td>
							<td>
								{t}Annual Fixed Amount{/t}
								{if $data.default_user_value5 != ''}<br>({t}Default{/t}: {$data.default_user_value5}){/if}
							</td>
						{elseif $data.combined_calculation_id == 18}
							<td>
								{t}Percent{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}%){/if}
							</td>
							<td>
								{t}Annual Wage Base/Maximum Earnings{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
							<td>
								{t}Annual Exempt Amount{/t}
								{if $data.default_user_value3 != ''}<br>({t}Default{/t}: {$data.default_user_value3}){/if}
							</td>
							<td>
								{t}Annual Deduction Amount{/t}
								{if $data.default_user_value4 != ''}<br>({t}Default{/t}: {$data.default_user_value4}){/if}
							</td>
						{elseif $data.combined_calculation_id == 20}
							<td>
								{t}Amount{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
						{elseif $data.combined_calculation_id == 30}
							<td>
								{t}Amount{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
							<td>
								{t}Annual Amount Greater Than{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
							<td>
								{t}Annual Amount Less Than{/t}
								{if $data.default_user_value3 != ''}<br>({t}Default{/t}: {$data.default_user_value3}){/if}
							</td>
							<td>
								{t}Annual Deduction Amount{/t}
								{if $data.default_user_value4 != ''}<br>({t}Default{/t}: {$data.default_user_value4}){/if}
							</td>
						{elseif $data.combined_calculation_id == 52}
							<td>
								{t}Amount{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
							<td>
								{t}Target Balance/Limit{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == 80}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.us_eic_filing_status_options[$data.default_user_value1]}){/if}
							</td>
						{elseif $data.combined_calculation_id == 82}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.us_medicare_filing_status_options[$data.default_user_value1]}){/if}
							</td>
						{elseif $data.combined_calculation_id == '100-CR'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.federal_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '100-CA'}
							<td>
								{t}Claim Amount{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
						{elseif $data.combined_calculation_id == '100-US'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.federal_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-CA'}
							<td>
								{t}Claim Amount{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-AZ'}
							<td>
								{t}Percent{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}%){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-AL'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_al_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Dependents{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-CT'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_ct_filing_status_options[$data.default_user_value1]}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-DC'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_dc_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-MD'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_dc_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
							<td>
								{t}County Rate{/t}
								{if $data.default_user_value3 != ''}<br>({t}Default{/t}: {$data.default_user_value3}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-DE'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_de_filing_status_options[$data.default_user_value1]}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-NJ'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_nj_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-NC'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_nc_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-MA'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_ma_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-OK'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_ok_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-GA'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_ga_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Employee / Spouse Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
							<td>
								{t}Dependent Allowances{/t}
								{if $data.default_user_value3 != ''}<br>({t}Default{/t}: {$data.default_user_value3}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-IL'}
							<td>
								{t}IL-W-4 Line 1{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
							<td>
								{t}IL-W-4 Line 2{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-OH'}
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-VA'}
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
							<td>
								{t}Age 65/Blind{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-IN'}
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
							<td>
								{t}Dependents{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-LA'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value3 != ''}<br>({t}Default{/t}: {$data.state_la_filing_status_options[$data.default_user_value3]}){/if}
							</td>
							<td>
								{t}Exemptions{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
							<td>
								{t}Dependents{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-ME'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_me_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-WI'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.federal_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '200-US-WV'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_wv_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '300-US'}
							<td>
								{t}Filing Status{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.state_filing_status_options[$data.default_user_value1]}){/if}
							</td>
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
						{elseif $data.combined_calculation_id == '300-US-PERCENT'}
							<td>
								{t}District / County Rate{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}%){/if}
							</td>
						{elseif $data.combined_calculation_id == '300-US-IN'}
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}){/if}
							</td>
							<td>
								{t}Dependents{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
							<td>
								{t}County Rate{/t}
								{if $data.default_user_value3 != ''}<br>({t}Default{/t}: {$data.default_user_value3}%){/if}
							</td>
						{elseif $data.combined_calculation_id == '300-US-MD'}
							<td>
								{t}Allowances{/t}
								{if $data.default_user_value2 != ''}<br>({t}Default{/t}: {$data.default_user_value2}){/if}
							</td>
							<td>
								{t}County Rate{/t}
								{if $data.default_user_value1 != ''}<br>({t}Default{/t}: {$data.default_user_value1}%){/if}
							</td>

						{/if}
					</tr>
					{/if}

					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.users.iteration}
						</td>
						<td>
							{$row.user_full_name}
							<input type="hidden" name="data[users][{$row.user_id}][id]" value="{$row.id}">
							<input type="hidden" name="data[users][{$row.user_id}][user_id]" value="{$row.user_id}">
							<input type="hidden" name="data[users][{$row.user_id}][user_full_name]" value="{$row.user_full_name}">
						</td>
						{if $data.combined_calculation_id == ''}
						{elseif $data.combined_calculation_id == 10}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">%
							</td>
						{elseif $data.combined_calculation_id == 15}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">%
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value3]" value="{$row.user_value3}">
							</td>
						{elseif $data.combined_calculation_id == 17 OR $data.combined_calculation_id == 19}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">%
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value3]" value="{$row.user_value3}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value4]" value="{$row.user_value4}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value5]" value="{$row.user_value5}">
							</td>
						{elseif $data.combined_calculation_id == 18}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">%
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value3]" value="{$row.user_value3}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value4]" value="{$row.user_value4}">
							</td>
						{elseif $data.combined_calculation_id == 20}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
						{elseif $data.combined_calculation_id == 30}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value3]" value="{$row.user_value3}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value4]" value="{$row.user_value4}">
							</td>
						{elseif $data.combined_calculation_id == 52}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == 80}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.us_eic_filing_status_options selected=$row.user_value1}
								</select>
							</td>
						{elseif $data.combined_calculation_id == 82}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.us_medicare_filing_status_options selected=$row.user_value1}
								</select>
							</td>
						{elseif $data.combined_calculation_id == '100-CR'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.federal_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '100-CA'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
						{elseif $data.combined_calculation_id == '100-US'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.federal_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-CA'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
						{elseif $data.combined_calculation_id == '200-US'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-AZ'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">%
							</td>
						{elseif $data.combined_calculation_id == '200-US-AL'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_al_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-CT'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_ct_filing_status_options selected=$row.user_value1}
								</select>
							</td>
						{elseif $data.combined_calculation_id == '200-US-DC'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_dc_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-MD'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_dc_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value3]" value="{$row.user_value3}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-DE'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_de_filing_status_options selected=$row.user_value1}
								</select>
							</td>
						{elseif $data.combined_calculation_id == '200-US-NJ'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_nj_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-NC'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_nc_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-MA'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_ma_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-OK'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_ok_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-GA'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_ga_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value3]" value="{$row.user_value3}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-IL'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-OH'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-VA'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-IN'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-LA'}
							<td>
								<select name="data[users][{$row.user_id}][user_value3]">
									{html_options options=$data.state_la_filing_status_options selected=$row.user_value3}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-ME'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_me_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-WI'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.federal_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '200-US-WV'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_wv_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '300-US'}
							<td>
								<select name="data[users][{$row.user_id}][user_value1]">
									{html_options options=$data.state_filing_status_options selected=$row.user_value1}
								</select>
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
						{elseif $data.combined_calculation_id == '300-US-PERCENT'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">%
							</td>
						{elseif $data.combined_calculation_id == '300-US-IN'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value3]" value="{$row.user_value3}">%
							</td>
						{elseif $data.combined_calculation_id == '300-US-MD'}
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value2]" value="{$row.user_value2}">
							</td>
							<td>
								<input type="text" size="10" name="data[users][{$row.user_id}][user_value1]" value="{$row.user_value1}">%
							</td>
						{/if}
					</tr>
				{foreachelse}
					<tr class="tblHeader">
						<td colspan="2">
							{t}Sorry, no employees are assigned to this Tax / Deduction.{/t}
						</td>
					</tr>
				{/foreach}
			</table>
		</td>
	</tr>
{else}
	<tbody id="10" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Percent{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>%
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}%){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="15" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Percent{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>%
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Annual Wage Base/Maximum Earnings{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Annual Deduction Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value3]" value="{$data.user_value3}" disabled>
				{if $data.default_user_value3 != ''}({t}Default{/t}: {$data.default_user_value3}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="17" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Percent{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>%
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Annual Amount Greater Than{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Annual Amount Less Than{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value3]" value="{$data.user_value3}" disabled>
				{if $data.default_user_value3 != ''}({t}Default{/t}: {$data.default_user_value3}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value4')">
			<td class="{isvalid object="cdf" label="user_value4" value="cellLeftEditTable"}">
				{t}Annual Deduction Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value4]" value="{$data.user_value4}" disabled>
				{if $data.default_user_value4 != ''}({t}Default{/t}: {$data.default_user_value4}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value5')">
			<td class="{isvalid object="cdf" label="user_value5" value="cellLeftEditTable"}">
				{t}Annual Fixed Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value5]" value="{$data.user_value5}" disabled>
				{if $data.default_user_value5 != ''}({t}Default{/t}: {$data.default_user_value5}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="18" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Percent{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>%
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Annual Wage Base/Maximum Earnings{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Annual Exempt Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value3]" value="{$data.user_value3}" disabled>
				{if $data.default_user_value3 != ''}({t}Default{/t}: {$data.default_user_value3}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value4')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Annual Deduction Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value4]" value="{$data.user_value4}" disabled>
				{if $data.default_user_value4 != ''}({t}Default{/t}: {$data.default_user_value4}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="19" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Percent{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>%
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Annual Amount Greater Than{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Annual Amount Less Than{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value3]" value="{$data.user_value3}" disabled>
				{if $data.default_user_value3 != ''}({t}Default{/t}: {$data.default_user_value3}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value4')">
			<td class="{isvalid object="cdf" label="user_value4" value="cellLeftEditTable"}">
				{t}Annual Deduction Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value4]" value="{$data.user_value4}" disabled>
				{if $data.default_user_value4 != ''}({t}Default{/t}: {$data.default_user_value4}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value5')">
			<td class="{isvalid object="cdf" label="user_value5" value="cellLeftEditTable"}">
				{t}Annual Fixed Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value5]" value="{$data.user_value5}" disabled>
				{if $data.default_user_value5 != ''}({t}Default{/t}: {$data.default_user_value5}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="20" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="30" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Annual Amount Greater Than{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Annual Amount Less Than{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value3]" value="{$data.user_value3}" disabled>
				{if $data.default_user_value3 != ''}({t}Default{/t}: {$data.default_user_value3}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value4')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Annual Deduction Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value4]" value="{$data.user_value4}" disabled>
				{if $data.default_user_value4 != ''}({t}Default{/t}: {$data.default_user_value4}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="52" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Target Balance/Limit{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="80" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.us_eic_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.us_eic_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="82" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.us_medicare_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.us_medicare_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="100-CR" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.federal_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.federal_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="100-CA" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Claim Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="100-US" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.federal_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.federal_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-CA" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Claim Amount{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.state_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-AZ" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Percent{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>%
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.default_user_value1}%){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-AL" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_al_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.state_al_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Dependents{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-CT" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_ct_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.state_ct_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-DC" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_dc_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.state_dc_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-MD" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_dc_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.state_dc_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}({t}Default{/t}: {$data.default_user_value2}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}County Rate{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value3]" value="{$data.user_value3}" disabled>
				{if $data.default_user_value3 != ''}({t}Default{/t}: {$data.default_user_value3}){/if}
			</td>
		</tr>

	</tbody>

	<tbody id="200-US-DE" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_de_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}({t}Default{/t}: {$data.state_de_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-NJ" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_nj_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.state_nj_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-NC" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_nc_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.state_nc_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-MA" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_ma_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.state_ma_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-OK" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_ok_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.state_ok_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-GA" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_ga_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.state_ga_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Employee / Spouse Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Dependent Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value3]" value="{$data.user_value3}" disabled>
				{if $data.default_user_value3 != ''}(Default: {$data.default_user_value3}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-IL" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}IL-W-4 Line 1{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}(Default: {$data.default_user_value1}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}IL-W-4 Line 2{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-OH" style="display:none" >
		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-VA" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}(Default: {$data.default_user_value1}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Age 65/Blind{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-IN" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}(Default: {$data.default_user_value1}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Dependents{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-LA" style="display:none" >
		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value3]" disabled>
					{html_options options=$data.state_la_filing_status_options selected=$data.user_value3}
				</select>
				{if $data.default_user_value3 != ''}(Default: {$data.state_la_filing_status_options[$data.default_user_value3]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Exemptions{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}(Default: {$data.default_user_value1}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Dependents{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-ME" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_me_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.state_me_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-WI" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.federal_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.federal_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="200-US-WV" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_wv_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.state_wv_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="300-US" style="display:none" >
		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Filing Status{/t}:
			</td>
			<td class="cellRightEditTable">
				<select name="data[user_value1]" disabled>
					{html_options options=$data.state_filing_status_options selected=$data.user_value1}
				</select>
				{if $data.default_user_value1 != ''}(Default: {$data.state_filing_status_options[$data.default_user_value1]}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="300-US-IN" style="display:none" >
		{if $page_type != 'user'}
			<tr onClick="showHelpEntry('company_value1')">
				<td class="{isvalid object="cdf" label="company_value1" value="cellLeftEditTable"}">
					{t}District / County Name{/t}:
				</td>
				<td class="cellRightEditTable">
					<input type="text" size="25" name="data[company_value1]" value="{$data.company_value1}" disabled>
				</td>
			</tr>
		{/if}

		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>
				{if $data.default_user_value1 != ''}(Default: {$data.default_user_value1}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Dependents{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value3')">
			<td class="{isvalid object="cdf" label="user_value3" value="cellLeftEditTable"}">
				{t}County Rate{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value3]" value="{$data.user_value3}" disabled>%
				{if $data.default_user_value3 != ''}(Default: {$data.default_user_value3}%){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="300-US-MD" style="display:none" >
		{if $page_type != 'user'}
			<tr onClick="showHelpEntry('company_value1')">
				<td class="{isvalid object="cdf" label="company_value1" value="cellLeftEditTable"}">
					{t}District / County Name{/t}:
				</td>
				<td class="cellRightEditTable">
					<input type="text" size="25" name="data[company_value1]" value="{$data.company_value1}" disabled>
				</td>
			</tr>
		{/if}

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}Allowances{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}){/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value1')">
			<td class="{isvalid object="cdf" label="user_value1" value="cellLeftEditTable"}">
				{t}County Rate{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value1]" value="{$data.user_value1}" disabled>%
				{if $data.default_user_value1 != ''}(Default: {$data.default_user_value1}%){/if}
			</td>
		</tr>
	</tbody>

	<tbody id="300-US-PERCENT" style="display:none" >
		<tr onClick="showHelpEntry('company_value1')">
			<td class="{isvalid object="cdf" label="company_value1" value="cellLeftEditTable"}">
				{t}District / County Name{/t}:
			</td>
			<td class="cellRightEditTable">
				{if $page_type == 'user'}
					{$data.company_value1}
				{else}
					<input type="text" size="25" name="data[company_value1]" value="{$data.company_value1}" disabled>
				{/if}
			</td>
		</tr>

		<tr onClick="showHelpEntry('user_value2')">
			<td class="{isvalid object="cdf" label="user_value2" value="cellLeftEditTable"}">
				{t}District / County Rate{/t}:
			</td>
			<td class="cellRightEditTable">
				<input type="text" size="10" name="data[user_value2]" value="{$data.user_value2}" disabled>%
				{if $data.default_user_value2 != ''}(Default: {$data.default_user_value2}%){/if}
			</td>
		</tr>
	</tbody>
{/if}
<!-- End: Dynamic Form Fields -->