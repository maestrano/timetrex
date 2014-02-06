{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">

					<tr>
						<td colspan="10">
							<br>
						</td>
					</tr>

					{foreach from=$pay_periods item=pay_period name="pay_periods"}
						{if $smarty.foreach.pay_periods.first}
							<tr class="tblHeader">
								<td colspan="8">
									{t}Step 1: Confirm all requests are authorized, and exceptions are handled.{/t}
								</td>
							</tr>

							<tr class="tblHeader">
								<td>
									{t}Name{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
								<td colspan="2">
									{t}Pending Requests{/t}
								</td>
								<td colspan="1">
									{t}Exceptions{/t}<br>
									{t}Low{/t} / {t}Medium{/t} / {t}High{/t} / {t}Critical{/t}
								</td>
								<td colspan="1">
									{t}Verified TimeSheets{/t}<br>
									{t}Pending{/t} / {t}Verified{/t} / {t}Total{/t}
								</td>
								<td colspan="2">
									{t}Functions{/t}
								</td>
							</tr>
						{/if}
						{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
						{if $pay_period.deleted == TRUE}
							{assign var="row_class" value="tblDataDeleted"}
						{/if}
						<tr class="{$row_class}">
							<td>
								{$pay_period.name}
							</td>
							<td>
								{$pay_period.type}
							</td>
							<td colspan="2">
								<table width="100%" align="center" >
									<tr>
										<td width="20" style="background: {if $pay_period.pending_requests > 0}red{else}green{/if};">
											<br>
										</td>
										<td align="center">
											<b>
											{$pay_period.pending_requests}
											</b>
										</td>
									</tr>
								</table>
							</td>

							<td colspan="1">
								<table width="100%" align="center" >
									<tr>
										<td width="20" style="background: {if $pay_period.critical_severity_exceptions > 0}red{else}green{/if};">
											<br>
										</td>
										<td align="center">
											<b>
											{$pay_period.low_severity_exceptions}
											/ <font color="blue">{$pay_period.med_severity_exceptions}</font>
											/ <font color="orange">{$pay_period.high_severity_exceptions}</font>
											/ <font color="red">{$pay_period.critical_severity_exceptions}</font>
											</b>
										</td>
									</tr>
								</table>
							</td>

							<td colspan="1">
								<table width="100%" align="center" >
									<tr>
										<td width="20" style="background: {if $pay_period.verified_time_sheets >= $pay_period.total_worked_users}green{elseif ($pay_period.verified_time_sheets+$pay_period.pending_time_sheets) >= $pay_period.total_worked_users}yellow{else}red{/if};">
											<br>
										</td>
										<td align="center">
											<b>
											{$pay_period.pending_time_sheets}
											/ {$pay_period.verified_time_sheets}
											/ {$pay_period.total_worked_users}
											</b>
										</td>
									</tr>
								</table>
							</td>

							<td colspan="2">
								<span style="white-space: nowrap;">[ <a href="{urlbuilder script="../punch/UserExceptionList.php" merge="FALSE"}">{t}Exceptions{/t}</a> ]</span>
								<span style="white-space: nowrap;">[ <a href="{urlbuilder script="../authorization/AuthorizationList.php" merge="FALSE"}">{t}Requests{/t}</a> ]</span>
								{assign var="pay_period_id" value=$pay_period.id}
								<span style="white-space: nowrap;">[ <a href="{urlbuilder script="../report/TimesheetSummary.php" values="filter_data[pay_period_ids][0]=$pay_period_id,filter_data[columns][99]=verified_time_sheet,filter_data[primary_sort]=verified_time_sheet" merge="FALSE"}">{t}Verifications{/t}</a> ]</span>
							</td>
						</tr>
					{/foreach}

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="10" align="right">
						<br>
					</td>
				</tr>

				{if $open_pay_periods == FALSE }
					<tr class="tblHeader">
						<td colspan="8">
							{if $total_pay_periods == 0}
								{t}There are no Pay Periods past their end date yet.{/t}
							{else}
								{t}All pay periods are currently closed.{/t}
							{/if}
						</td>
					</tr>

				{else}
					{foreach from=$pay_periods item=pay_period name="pay_periods"}
						{if $smarty.foreach.pay_periods.first}
							<tr class="tblHeader">
								<td colspan="8">
									{t}Step 2: Lock Pay Period to prevent changes.{/t}
								</td>
							</tr>

							<tr class="tblHeader">
								<td>
									{t}Name{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
								<td>
									{t}Status{/t}
								</td>
								<td>
									{t}Start{/t}
								</td>
								<td>
									{t}End{/t}
								</td>
								<td>
									{t}Transaction{/t}
								</td>
								<td>
									{t}Functions{/t}
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
								</td>
							</tr>
						{/if}
						{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
						{if $pay_period.deleted == TRUE}
							{assign var="row_class" value="tblDataDeleted"}
						{/if}
						<tr class="{$row_class}">
							<td>
								{$pay_period.name}
							</td>
							<td>
								{$pay_period.type}
							</td>
							<td>
								{$pay_period.status}
							</td>
							<td>
								{$pay_period.start_date}
							</td>
							<td>
								{$pay_period.end_date}
							</td>
							<td>
								{$pay_period.transaction_date}
							</td>
							<td>
								{if $pay_period.id}
									{assign var="pay_period_id" value=$pay_period.id}
									[ <a href="{urlbuilder script="ViewPayPeriod.php" values="pay_period_id=$pay_period_id" merge="FALSE"}">{t}View{/t}</a> ]
								{/if}
							</td>
							<td>
								<input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{$pay_period.id}" checked>
							</td>
						</tr>
					{/foreach}
					<tr class="tblHeader">
						<td colspan="6">
							<br>
						</td>
						<td colspan="2" align="center">
							<input type="submit" name="action:lock" value="{t}Lock{/t}">
							<input type="submit" name="action:unlock" value="{t}UnLock{/t}">
						</td>
					</tr>
		</form>

					<tr>
						<td colspan="10">
							<br>
						</td>
					</tr>

					{foreach from=$pay_periods item=pay_period name="pay_periods"}
						{if $smarty.foreach.pay_periods.first}
							<tr class="tblHeader">
								<td colspan="8">
									{t}Step 3: Submit all Pay Stub Amendments.{/t}
								</td>
							</tr>

							<tr class="tblHeader">
								<td>
									{t}Name{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
								<td colspan="4">
									{t}Pay Stub Amendments{/t}
								</td>
								<td colspan="2">
									{t}Functions{/t}
								</td>
							</tr>
						{/if}
						{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
						{if $pay_period.deleted == TRUE}
							{assign var="row_class" value="tblDataDeleted"}
						{/if}
						<tr class="{$row_class}">
							<td>
								{$pay_period.name}
							</td>
							<td>
								{$pay_period.type}
							</td>
							<td colspan="4">
								{$pay_period.total_ps_amendments}
							</td>
							<td colspan="2">
								[ <a href="{urlbuilder script="../pay_stub_amendment/PayStubAmendmentList.php" values="" merge="FALSE"}">{t}View{/t}</a> ]
							</td>
						</tr>
					{/foreach}

					<tr>
						<td colspan="10">
							<br>
						</td>
					</tr>
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
					{foreach from=$pay_periods item=pay_period name="pay_periods"}
						{if $smarty.foreach.pay_periods.first}
							<tr class="tblHeader">
								<td colspan="8">
									{t}Step 4: Generate and Review Pay Stubs.{/t}
								</td>
							</tr>

							<tr class="tblHeader">
								<td>
									{t}Name{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
								<td colspan="4">
									{t}Pay Stubs{/t}
								</td>
								<td>
									{t}Functions{/t}
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
								</td>
							</tr>
						{/if}
						{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
						{if $pay_period.deleted == TRUE}
							{assign var="row_class" value="tblDataDeleted"}
						{/if}
						<tr class="{$row_class}">
							<td>
								{$pay_period.name}
							</td>
							<td>
								{$pay_period.type}
							</td>
							<td colspan="4">
								{$pay_period.total_pay_stubs}
							</td>
							<td>
								{if $pay_period.id}
									{assign var="pay_period_id" value=$pay_period.id}
									[ <a href="{urlbuilder script="../pay_stub/PayStubList.php" values="filter_pay_period_id=$pay_period_id" merge="FALSE"}">{t}View{/t}</a> ]
									[ <a href="{urlbuilder script="../report/PayStubSummary.php" values="pay_period_id=$pay_period_id" merge="FALSE"}">{t}Summary{/t}</a> ]
								{/if}
							</td>
							<td>
								<input type="checkbox" class="checkbox" name="pay_stub_pay_period_ids[]" value="{$pay_period.id}" checked>
							</td>
						</tr>
					{/foreach}
					<tr class="tblHeader">
						<td colspan="6">
							<br>
						</td>
						<td colspan="2" align="center">
							<input type="submit" name="action:generate_pay_stubs" value="{t}Generate Pay Stubs{/t}">
						</td>
					</tr>
		</form>

					<tr>
						<td colspan="10">
							<br>
						</td>
					</tr>
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
					{foreach from=$pay_periods item=pay_period name="pay_periods"}
						{if $smarty.foreach.pay_periods.first}
							<tr class="tblHeader">
								<td colspan="8">
									{t}Step 5: Transfer Funds or Write Checks.{/t}
								</td>
							</tr>

							<tr class="tblHeader">
								<td>
									{t}Name{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
								<td>
									{t}Status{/t}
								</td>
								<td>
									{t}Start{/t}
								</td>
								<td>
									{t}End{/t}
								</td>
								<td>
									{t}Transaction{/t}
								</td>
								<td>
									{t}Functions{/t}
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
								</td>
							</tr>
						{/if}
						{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
						{if $pay_period.deleted == TRUE}
							{assign var="row_class" value="tblDataDeleted"}
						{/if}
						<tr class="{$row_class}">
							<td>
								{$pay_period.name}
							</td>
							<td>
								{$pay_period.type}
							</td>
							<td>
								{$pay_period.status}
							</td>
							<td>
								{$pay_period.start_date}
							</td>
							<td>
								{$pay_period.end_date}
							</td>
							<td>
								{$pay_period.transaction_date}
							</td>
							<td>
								{if $pay_period.id}
									{assign var="pay_period_id" value=$pay_period.id}
									[ <a href="{urlbuilder script="../pay_stub/PayStubList.php" values="filter_pay_period_id=$pay_period_id" merge="FALSE"}">{t}View{/t}</a> ]
									[ <a href="{urlbuilder script="../report/PayStubSummary.php" values="pay_period_id=$pay_period_id" merge="FALSE"}">{t}Summary{/t}</a> ]
								{/if}
							</td>
							<td>
								<input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{$pay_period.id}" checked>
							</td>
						</tr>
					{/foreach}
		</form>
					<tr>
						<td colspan="10">
							<br>
						</td>
					</tr>
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
					{foreach from=$pay_periods item=pay_period name="pay_periods"}
						{if $smarty.foreach.pay_periods.first}
							<tr class="tblHeader">
								<td colspan="8">
									{t}Step 6: Close Pay Period.{/t}
								</td>
							</tr>

							<tr class="tblHeader">
								<td>
									{t}Name{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
								<td>
									{t}Status{/t}
								</td>
								<td>
									{t}Start{/t}
								</td>
								<td>
									{t}End{/t}
								</td>
								<td>
									{t}Transaction{/t}
								</td>
								<td>
									{t}Functions{/t}
								</td>
								<td>
									<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)" checked/>
								</td>
							</tr>
						{/if}
						{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
						{if $pay_period.deleted == TRUE}
							{assign var="row_class" value="tblDataDeleted"}
						{/if}
						<tr class="{$row_class}">
							<td>
								{$pay_period.name}
							</td>
							<td>
								{$pay_period.type}
							</td>
							<td>
								{$pay_period.status}
							</td>
							<td>
								{$pay_period.start_date}
							</td>
							<td>
								{$pay_period.end_date}
							</td>
							<td>
								{$pay_period.transaction_date}
							</td>
							<td>
								{if $pay_period.id}
									{assign var="pay_period_id" value=$pay_period.id}
									[ <a href="{urlbuilder script="ViewPayPeriod.php" values="pay_period_id=$pay_period_id" merge="FALSE"}">{t}View{/t}</a> ]
								{/if}
							</td>
							<td>
								<input type="checkbox" class="checkbox" name="pay_period_ids[]" value="{$pay_period.id}" checked>
							</td>
						</tr>
					{/foreach}
					<tr class="tblHeader">
						<td colspan="6">
							<br>
						</td>
						<td colspan="2" align="center">
							<input type="submit" name="action:close" value="{t}Close{/t}">
						</td>
					</tr>
			{/if}
				</table>
				<br>
		</form>
	</div>
</div>
{include file="footer.tpl"}
