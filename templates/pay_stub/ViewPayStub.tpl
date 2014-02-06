{include file="header.tpl"}
			<table id="main">
				<tr>
					<td colspan="8">
						<table width="100%">
							<tr id="label">
								<td width="33%" style="text-align: left">
									<h2>{$APPLICATION_NAME}</h2>
								</td>
								<td width="33%">
									{$company_obj->getName()}<br>
									{$company_obj->getAddress1()}<br>
									{if $company_obj->getAddress2()}
										{$company_obj->getAddress2()}<br>
									{/if}
									{$company_obj->getCity()}, {$company_obj->getProvince()}<br>
									{$company_obj->getPostalCode()}<br>
								</td>
								<td width="33%" style="text-align: right">
									{t}Pay Period{/t}<br>
									{t}Start Date:{/t} {$pay_period_data.start_date}<br>
									{t}End Date:{/t} {$pay_period_data.end_date}<br>
									{t}Payment Date:{/t} {$pay_period_data.transaction_date}
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr id="label">
					<td colspan="8">
						<h3>{t}STATEMENT OF EARNINGS AND DEDUCTIONS{/t}</h3>
					</td>
				</tr>

				{* Earnings *}
				{foreach name=earnings from=$pay_stub.entries.10 item=pay_stub_entry}
					{if $smarty.foreach.earnings.first}
						<tr id="label">
							<td>
								{t}Earnings{/t}
							</td>
							<td>
								{t}Rate{/t}
							</td>
							<td>
								{t}Hours{/t}
							</td>
							<td>
								{t}Amount{/t}
							</td>
							<td>
								{t}YTD Hours{/t}
							</td>
							<td>
								{t}YTD Amount{/t}
							</td>
						</tr>
					{/if}

					{cycle assign=row_class values="even,odd"}
					<tr id="{$row_class}" {if $pay_stub_entry.type == 40}style="font-weight: bold;"{/if}>
						<td style="text-align: left;">
							{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						</td>
						<td style="text-align: right">
							{if $pay_stub_entry.type == 10}
								{$pay_stub_entry.rate}
							{else}
								<br>
							{/if}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.units}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.amount}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.ytd_units}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.ytd_amount}
						</td>
					</tr>
					{if $smarty.foreach.earnings.last}
						<tr>
							<td>
								<br>
							</td>
						</tr>
					{/if}
				{/foreach}


				{* Advance Pay *}
				{foreach name=advance_pay from=$pay_stub.entries.15 item=pay_stub_entry}
					{if $smarty.foreach.advance_pay.first}
						<tr id="label">
							<td colspan="4">
								{t}Advance{/t}
							</td>
							<td>
								{t}Amount{/t}
							</td>
							<td>
								{t}YTD Amount{/t}
							</td>
						</tr>
					{/if}

					{cycle assign=row_class values="even,odd"}
					<tr id="{$row_class}" {if $pay_stub_entry.type == 40}style="font-weight: bold;"{/if}>
						<td colspan="4" style="text-align: left;">
							{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.amount}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.ytd_amount}
						</td>
					</tr>
					{if $smarty.foreach.advance_pay.last}
						<tr>
							<td>
								<br>
							</td>
						</tr>
					{/if}
				{/foreach}

				{* Advance Deduction *}
				{foreach name=advance_deduction from=$pay_stub.entries.16 item=pay_stub_entry}
					{if $smarty.foreach.advance_deduction.first}
						<tr id="label">
							<td colspan="4">
								{t}Less Advance{/t}
							</td>
							<td>
								{t}Amount{/t}
							</td>
							<td>
								{t}YTD Amount{/t}
							</td>
						</tr>
					{/if}

					{cycle assign=row_class values="even,odd"}
					<tr id="{$row_class}" {if $pay_stub_entry.type == 40}style="font-weight: bold;"{/if}>
						<td colspan="4" style="text-align: left;">
							{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.amount}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.ytd_amount}
						</td>
					</tr>

					{if $smarty.foreach.advance_deduction.last}
						<tr>
							<td>
								<br>
							</td>
						</tr>
					{/if}
				{/foreach}


				{* Deductions *}
				{foreach name=deductions from=$pay_stub.entries.20 item=pay_stub_entry}
					{if $smarty.foreach.deductions.first}
						<tr id="label">
							<td colspan="4">
								{t}Deductions{/t}
							</td>
							<td>
								{t}Amount{/t}
							</td>
							<td>
								{t}YTD Amount{/t}
							</td>
						</tr>
					{/if}

					{cycle assign=row_class values="even,odd"}
					<tr id="{$row_class}" {if $pay_stub_entry.type == 40}style="font-weight: bold;"{/if}>
						<td colspan="4" style="text-align: left;">
							{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.amount}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.ytd_amount}
						</td>
					</tr>
					{if $smarty.foreach.deductions.last}
						<tr>
							<td>
								<br>
							</td>
						</tr>
					{/if}
				{/foreach}

				{if $pay_stub.advance == FALSE}
					<tr id="label" style="text-align: right;">
						<td colspan="4" style="text-align: left;">
							{t}Net{/t}
						</td>
						<td>
							{$pay_stub.entries.40.0.amount}
						</td>
						<td>
							{$pay_stub.entries.40.0.ytd_amount}
						</td>
					</tr>

					<tr>
						<td>
							<br>
						</td>
					</tr>
				{/if}

				{* Employer Deductions *}
				{foreach name=employer_deductions from=$pay_stub.entries.30 item=pay_stub_entry}
					{if $smarty.foreach.employer_deductions.first}
						<tr id="label">
							<td colspan="4">
								{t}Employer Deductions{/t}
							</td>
							<td>
								{t}Amount{/t}
							</td>
							<td>
								{t}YTD Amount{/t}
							</td>
						</tr>
					{/if}

					{cycle assign=row_class values="even,odd"}
					<tr id="{$row_class}" {if $pay_stub_entry.type == 40}style="font-weight: bold;"{/if}>
						<td colspan="4" style="text-align: left;">
							{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.amount}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.ytd_amount}
						</td>
					</tr>
					{if $smarty.foreach.employer_deductions.last}
						<tr>
							<td>
								<br>
							</td>
						</tr>
					{/if}
				{/foreach}

				{* Other *}
				{foreach name=other from=$pay_stub.entries.35 item=pay_stub_entry}
					{if $smarty.foreach.other.first}
						<tr id="label">
							<td colspan="4">
								{t}Other{/t}
							</td>
							<td>
								{t}Amount{/t}
							</td>
							<td>
								{t}YTD Amount{/t}
							</td>
						</tr>
					{/if}

					{cycle assign=row_class values="even,odd"}
					<tr id="{$row_class}" {if $pay_stub_entry.type == 40}style="font-weight: bold;"{/if}>
						<td colspan="4" style="text-align: left;">
							{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.amount}
						</td>
						<td style="text-align: right">
							{$pay_stub_entry.ytd_amount}
						</td>
					</tr>
					{if $smarty.foreach.other.last}
						<tr>
							<td>
								<br>
							</td>
						</tr>
					{/if}
				{/foreach}

				{* Descriptions *}
				{foreach name=description from=$pay_stub_entry_descriptions item=description}
					{if $smarty.foreach.description.first}
						<tr id="label">
							<td colspan="6">
								{t}Notes{/t}
							</td>
						</tr>
					{/if}

					{cycle assign=row_class values="even,odd"}
					<tr id="{$row_class}">
						<td colspan="6" style="text-align: left;">
							[{$description.subscript}] {$description.description}
						</td>
					</tr>
					{if $smarty.foreach.description.last}
						<tr>
							<td>
								<br>
							</td>
						</tr>
					{/if}
				{/foreach}

				<tr id="label">
					<td colspan="6">
						{t}NON NEGOTIABLE{/t}
					</td>
				</tr>
				<tr id="even" style="text-align: right;">
					<td colspan="3" style="text-align: center;">
						<b>{t}CONFIDENTIAL{/t}</b><br>
						{$user_obj->getFullName()}<br>
						{$user_obj->getAddress1()}<br>
						{if $user_obj->getAddress2()}
							{$user_obj->getAddress2()}<br>
						{/if}
						{$user_obj->getCity()}, {$user_obj->getProvince()}<br>
						{$user_obj->getPostalCode()}
					</td>
					<td colspan="3" style="text-align: right;">
						{* {t escape="no" 1=$pay_period_number 2=$annual_pay_periods}Pay Period %1 of %2{/t}<Br> *}
						<br>
						<b>{t}Net:{/t} ${if $pay_stub.advance == TRUE}{$pay_stub.entries.15.0.amount}{else}{$pay_stub.entries.40.0.amount}{/if}</b>
					</td>

				</tr>
			</table>
{include file="footer.tpl"}
