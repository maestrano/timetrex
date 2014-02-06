{include file="header.tpl"}
{include file="print.css.tpl"}
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
					<td colspan="10">
						{t}Pay Period:{/t}
						<select name="pay_period_id" onChange="this.form.submit()">
							{html_options options=$pay_period_options selected=$pay_period_id}
						</select>
					</td>
				</tr>

				{foreach from=$rows item=row name=rows}
					<tr class="tblHeader">
						<td colspan="5">
							{$row.name}
						</td>
					</tr>

					{foreach from=$row.users item=user name=user}
						{if $smarty.foreach.user.first == TRUE}
							<tr class="tblHeader">
								<td>
									{include file="column_sort.tpl" label="Employee" sort_column="last_name" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{include file="column_sort.tpl" label="Total Time" sort_column="advance_pay" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{include file="column_sort.tpl" label="Percent" sort_column="gross_pay" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{include file="column_sort.tpl" label="Gross Pay" sort_column="employee_deductions" current_column="$sort_column" current_order="$sort_order"}
								</td>
{*
								<td>
									{t}Functions{/t}
								</td>
*}
							</tr>
						{/if}
						{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
						<tr class="{$row_class}" {if $smarty.foreach.user.last == TRUE}style="font-weight: bold;"{/if}>
							<td>
								{$user.full_name}
							</td>
							<td>
								{gettimeunit value=$user.total_time default=TRUE}
							</td>
							<td>
								{$user.percent_display}%
							</td>
							<td>
								{$user.gross_pay}
							</td>
{*
							<td>
								{if $smarty.foreach.rows.last == FALSE}

									{if $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_own')}
										{assign var="pay_stub_id" value=$row.id}
										[ <a href="{urlbuilder script="../pay_stub/ViewPayStub.php" values="id=$pay_stub_id" merge="FALSE"}">{t}Pay Stub{/t}</a> ]
									{/if}
									{if $permission->Check('shift','view')}
										{assign var="user_id" value=$row.user_id}
										[ <a href="{urlbuilder script="../timesheet/ViewUserTimeSheet.php" values="filter_user_id=$user_id,pay_period_id=$pay_period_id" merge="FALSE"}">{t}TimeSheet{/t}</a> ]
									{/if}

								{/if}
							</td>
*}
						</tr>

					{/foreach}

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