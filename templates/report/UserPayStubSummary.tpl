{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>
				<tr class="tblHeader">
					<td colspan="10" style="vertical-align: top;">
						{t}Employees:{/t}
						<select name="user_ids[]" onChange="this.form.submit()" size="3" multiple>
							{html_options options=$user_options selected=$user_ids}
						</select>
					</td>
				</tr>

				{foreach from=$rows item=row name=rows}
					{if $smarty.foreach.rows.first OR $row.user_changed == TRUE}
						{if $smarty.foreach.rows.first == FALSE}
						<tr>
							<td class="tblPagingLeft" colspan="7" align="right">
								<br>
							</td>
						</tr>
						{/if}

						<tr class="tblHeader">
							<td colspan="10">
								{$row.full_name}
							</td>
						</tr>

						<tr class="tblHeader">
							<td>
								{include file="column_sort.tpl" label="Pay Period" sort_column="pay_period" current_column="$sort_column" current_order="$sort_order"}
							</td>
							<td>
								{include file="column_sort.tpl" label="Advance Pay" sort_column="advance_pay" current_column="$sort_column" current_order="$sort_order"}
							</td>
							<td>
								{include file="column_sort.tpl" label="Gross Pay" sort_column="gross_pay" current_column="$sort_column" current_order="$sort_order"}
							</td>
							<td>
								{include file="column_sort.tpl" label="Employee Deductions" sort_column="employee_deductions" current_column="$sort_column" current_order="$sort_order"}
							</td>
							<td>
								{include file="column_sort.tpl" label="Employer Contributions" sort_column="employer_deductions" current_column="$sort_column" current_order="$sort_order"}
							</td>
							<td>
								{include file="column_sort.tpl" label="Vacation Accrual" sort_column="vacation_accrual" current_column="$sort_column" current_order="$sort_order"}
							</td>
							<td>
								{include file="column_sort.tpl" label="Net Pay" sort_column="net_pay" current_column="$sort_column" current_order="$sort_order"}
							</td>
							<td>
								{t}Functions{/t}
							</td>
						</tr>
					{/if}

					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="{$row_class}" {* {if $smarty.foreach.rows.last}style="font-weight: bold;"{/if} *}>
						<td nowrap>
							{$row.pay_period_name}
						</td>
						<td id="{if $row.entries.advance != ''}{$row.percent_deviation.advance.alert}{else}{$row.percent_deviation.advance_pay.alert}{/if}">
							{if $row.entries.advance != ''}
								{$row.entries.advance|default:"--"} {if $row.entries.advance}({$row.percent_deviation.advance.deviation}%){/if}
							{else}
								{$row.entries.advance_pay|default:"--"} {if $row.entries.advance_pay}({$row.percent_deviation.advance_pay.deviation}%){/if}
							{/if}
						</td>
						<td id="{$row.percent_deviation.gross_pay.alert}">
							{$row.entries.gross_pay} ({$row.percent_deviation.gross_pay.deviation}%)
						</td>
						<td id="{$row.percent_deviation.deductions.alert}">
							{$row.entries.deductions} ({$row.percent_deviation.deductions.deviation}%)
						</td>
						<td id="{$row.percent_deviation.employer_deductions.alert}">
							{$row.entries.employer_deductions} ({$row.percent_deviation.employer_deductions.deviation}%)
						</td>
						<td id="{$row.percent_deviation.vacation_accrual.alert}">
							{$row.entries.vacation_accrual} ({$row.percent_deviation.vacation_accrual.deviation}%)
						</td>
						<td id="{$row.percent_deviation.net_pay.alert}">
							{$row.entries.net_pay} ({$row.percent_deviation.net_pay.deviation}%)
						</td>
						<td nowrap>
							{if $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_own')}
								{assign var="pay_stub_id" value=$row.id}
								[ <a href="{urlbuilder script="../pay_stub/ViewPayStub.php" values="id=$pay_stub_id" merge="FALSE"}">Pay Stub</a> ]
								<br>
							{/if}

							{if $permission->Check('shift','view')}
								{assign var="user_id" value=$row.user_id}
								{assign var="pay_period_id" value=$row.pay_period_id}
								[ <a href="{urlbuilder script="../timesheet/ViewUserTimeSheet.php" values="filter_user_id=$user_id,pay_period_id=$pay_period_id" merge="FALSE"}">{t}TimeSheet{/t}</a> ]
							{/if}
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}