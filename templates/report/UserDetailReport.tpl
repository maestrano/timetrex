{include file="sm_header.tpl"}
{include file="print.css.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="100" align="right">
						<br>{* <a href="javascript: exportReport()"><img src="{$IMAGES_URL}/excel_icon.gif"></a> *}
					</td>
				</tr>

				{foreach from=$rows item=row name=rows}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="tblHeader">
						<td colspan="2">
							{$row.first_name} {$row.last_name}
						</td>
					</tr>

					<tr>
						<td width="50%" valign="top">
							<table class="editTable">
								<tr>
									<td class="cellLeftEditTable">
										{t}Employee:{/t}
									</td>
									<td class="cellRightEditTable">
										{$row.first_name} {$row.last_name}
									</td>
								</tr>
								<tr>
									<td class="cellLeftEditTable">
										{t}City:{/t}
									</td>
									<td class="cellRightEditTable">
										{$row.city}
									</td>
								</tr>
								<tr>
									<td class="cellLeftEditTable">
										{t}Province:{/t}
									</td>
									<td class="cellRightEditTable">
										{$row.province}
									</td>
								</tr>
							</table>
						</td>
						<td valign="top">
							<table class="editTable">
								<tr>
									<td class="cellLeftEditTable">
										{t}Title:{/t}
									</td>
									<td class="cellRightEditTable">
										{$row.title|default:"--"}
									</td>
								</tr>
								<tr>
									<td class="cellLeftEditTable">
										{t}Hired Date:{/t}
									</td>
									<td class="cellRightEditTable">
										{getdate type="DATE" epoch=$row.hire_date defaul=true} ({$row.hire_date_since} {t}ago{/t})
									</td>
								</tr>
								<tr>
									<td class="cellLeftEditTable">
										{t}Termination Date:{/t}
									</td>
									<td class="cellRightEditTable">
										{getdate type="DATE" epoch=$row.termination_date default=true}
									</td>
								</tr>
								<tr>
									<td class="cellLeftEditTable">
										{t}Birth Date:{/t}
									</td>
									<td class="cellRightEditTable">
										{getdate type="DATE" epoch=$row.birth_date default=true} ({$row.birth_date_since} {t}yrs old{/t})
									</td>
								</tr>
							</table>
						</td>
					</tr>

					{* Wage History *}
					{if isset($columns.wage)}
					{foreach from=$row.user_wage_rows item=user_wage name=user_wage}
						{if $smarty.foreach.user_wage.first}
						<tr>
							<td colspan="2">
								<table class="tblList">
									<tr class="tblHeader">
										<td colspan="3">
											{t}Wage History{/t}
										</td>
									</tr>
									<tr class="tblHeader">
										<td>
											{t}Type{/t}
										</td>
										<td>
											{t}Wage{/t}
										</td>
										<td>
											{t}Effective Date{/t}
										</td>
									</tr>
						{/if}

								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								<tr class="{$row_class}">
									<td>
										{$user_wage.type}
									</td>
									<td>
										{$user_wage.currency_symbol}{$user_wage.wage}
									</td>
									<td>
										{getdate type="DATE" epoch=$user_wage.effective_date default=TRUE} ({$user_wage.effective_date_since} {t}ago{/t})
									</td>
								</tr>


						{if $smarty.foreach.user_wage.last}
								</table>
							</td>
						</tr>
						{/if}
					{foreachelse}
						<tr class="tblHeader">
							<td colspan="2">
								{t}No Wage History{/t}
							</td>
						</td>
					{/foreach}
					{/if}

					{* Attendance History *}
					{if isset($columns.attendance)}
						<tr>
							<td colspan="2">
								<table class="tblList">
									<tr class="tblHeader">
										<td colspan="100">
											{t}Attendance History{/t}
										</td>
									</tr>
									<tr class="tblHeader">
										<td rowspan="2">
											{t}Name{/t}
										</td>
										<td colspan="4">
											{t}Per Day{/t}
										</td>
										<td rowspan="100">
											<br>
										</td>
										<td  colspan="4">
											{t}Per Week{/t}
										</td>
										<td rowspan="100">
											<br>
										</td>
										<td  colspan="4">
											{t}Per Month{/t}
										</td>
									</tr>
									<tr class="tblHeader">
										<td>
											{t}Min{/t}
										</td>
										<td>
											{t}Avg{/t}
										</td>
										<td>
											{t}Max{/t}
										</td>
										<td>
											{t}Days{/t}
										</td>

										<td>
											{t}Min{/t}
										</td>
										<td>
											{t}Avg{/t}
										</td>
										<td>
											{t}Max{/t}
										</td>
										<td>
											{t}Weeks{/t}
										</td>

										<td>
											{t}Min{/t}
										</td>
										<td>
											{t}Avg{/t}
										</td>
										<td>
											{t}Max{/t}
										</td>
										<td>
											{t}Months{/t}
										</td>
									</tr>


									{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
									<tr class="{$row_class}">
										<td class="cellLeftEditTable">
											{t}Days Worked:{/t}
										</td>
										<td colspan="4">
											{t}N/A{/t}
										</td>
										<td>
											{$row.user_attendance_rows.days_worked.week.min}
										</td>
										<td>
											{$row.user_attendance_rows.days_worked.week.avg}
										</td>
										<td>
											{$row.user_attendance_rows.days_worked.week.max}
										</td>
										<td>
											--
										</td>

										<td>
											{$row.user_attendance_rows.days_worked.month.min}
										</td>
										<td>
											{$row.user_attendance_rows.days_worked.month.avg}
										</td>
										<td>
											{$row.user_attendance_rows.days_worked.month.max}
										</td>
										<td>
											--
										</td>
									</tr>

									{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
									<tr class="{$row_class}">
										<td class="cellLeftEditTable">
											{t}Regular Time:{/t}
										</td>
										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.day.min|default:0}
										</td>
										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.day.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.day.max|default:0}
										</td>
										<td>
											{$row.user_attendance_rows.hours_worked.regular.0.day.date_units|default:0}
										</td>

										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.week.min|default:0}
										</td>
										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.week.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.week.max|default:0}
										</td>
										<td>
											{$row.user_attendance_rows.hours_worked.regular.0.week.date_units|default:0}
										</td>

										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.month.min|default:0}
										</td>
										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.month.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$row.user_attendance_rows.hours_worked.regular.0.month.max|default:0}
										</td>
										<td>
											{$row.user_attendance_rows.hours_worked.regular.0.month.date_units|default:0}
										</td>
									</tr>

									{foreach from=$row.user_attendance_rows.hours_worked.over_time item=attendance_over_time name=attendance_over_time}
									{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
									<tr class="{$row_class}">
										<td class="cellLeftEditTable">
											{$attendance_over_time.name}:
										</td>

										<td>
											{gettimeunit value=$attendance_over_time.day.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_over_time.day.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_over_time.day.max|default:0}
										</td>
										<td>
											{$attendance_over_time.day.date_units|default:0}
										</td>

										<td>
											{gettimeunit value=$attendance_over_time.week.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_over_time.week.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_over_time.week.max|default:0}
										</td>
										<td>
											{$attendance_over_time.week.date_units|default:0}
										</td>

										<td>
											{gettimeunit value=$attendance_over_time.month.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_over_time.month.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_over_time.month.max|default:0}
										</td>
										<td>
											{$attendance_over_time.month.date_units|default:0}
										</td>
									</tr>
									{/foreach}

									{foreach from=$row.user_attendance_rows.hours_worked.premium item=attendance_premium name=attendance_premium}
									{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
									<tr class="{$row_class}">
										<td class="cellLeftEditTable">
											{$attendance_premium.name}:
										</td>

										<td>
											{gettimeunit value=$attendance_premium.day.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_premium.day.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_premium.day.max|default:0}
										</td>
										<td>
											{$attendance_premium.day.date_units|default:0}
										</td>

										<td>
											{gettimeunit value=$attendance_premium.week.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_premium.week.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_premium.week.max|default:0}
										</td>
										<td>
											{$attendance_premium.week.date_units|default:0}
										</td>

										<td>
											{gettimeunit value=$attendance_premium.month.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_premium.month.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_premium.month.max|default:0}
										</td>
										<td>
											{$attendance_premium.month.date_units|default:0}
										</td>
									</tr>
									{/foreach}

									{foreach from=$row.user_attendance_rows.hours_worked.absence item=attendance_absence name=attendance_absence}
									{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
									<tr class="{$row_class}">
										<td class="cellLeftEditTable">
											{$attendance_absence.name}:
										</td>

										<td>
											{gettimeunit value=$attendance_absence.day.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_absence.day.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_absence.day.max|default:0}
										</td>
										<td>
											{$attendance_absence.day.date_units|default:0}
										</td>

										<td>
											{gettimeunit value=$attendance_absence.week.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_absence.week.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_absence.week.max|default:0}
										</td>
										<td>
											{$attendance_absence.week.date_units|default:0}
										</td>

										<td>
											{gettimeunit value=$attendance_absence.month.min|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_absence.month.avg|default:0}
										</td>
										<td>
											{gettimeunit value=$attendance_absence.month.max|default:0}
										</td>
										<td>
											{$attendance_absence.month.date_units|default:0}
										</td>
									</tr>
									{/foreach}

								</table>
							</td>
						</tr>
					{/if}

					{* Exception History *}
					{if isset($columns.exception)}
						<tr>
							<td colspan="2">
								<table class="tblList">
									{foreach from=$row.user_exception_rows item=exception name=exception}
										{if $smarty.foreach.exception.first}
											<tr class="tblHeader">
												<td colspan="100">
													{t}Exception History{/t}
												</td>
											</tr>
											<tr class="tblHeader">
												<td rowspan="2">
													{t}Exception{/t}
												</td>
												<td rowspan="2">
													{t}Total{/t}
												</td>
												<td rowspan="100">
													<br>
												</td>
												<td colspan="3">
													{t}Per Week{/t}<br>
												</td>
												<td rowspan="100">
													<br>
												</td>
												<td colspan="3">
													{t}Per Month{/t}<br>
												</td>
												<td rowspan="100">
													<br>
												</td>
												<td colspan="7">
													{t}Day of Week{/t}<br>
												</td>
											</tr>

											<tr class="tblHeader">
												<td>
													{t}Min{/t}
												</td>
												<td>
													{t}Avg{/t}
												</td>
												<td>
													{t}Max{/t}
												</td>

												<td>
													{t}Min{/t}
												</td>
												<td>
													{t}Avg{/t}
												</td>
												<td>
													{t}Max{/t}
												</td>

												<td>
													{t}Sun{/t}
												</td>
												<td>
													{t}Mon{/t}
												</td>
												<td>
													{t}Tue{/t}
												</td>
												<td>
													{t}Wed{/t}
												</td>
												<td>
													{t}Thu{/t}
												</td>
												<td>
													{t}Fri{/t}
												</td>
												<td>
													{t}Sat{/t}
												</td>
											</tr>

										{/if}
									{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
									<tr class="{$row_class}">
										<td class="cellLeftEditTable">
											{$exception.week.name}
										</td>
										<td>
											{$exception.week.total}
										</td>

										<td>
											{$exception.week.min}
										</td>
										<td>
											{$exception.week.avg}
										</td>
										<td>
											{$exception.week.max}
										</td>

										<td>
											{$exception.month.min}
										</td>
										<td>
											{$exception.month.avg}
										</td>
										<td>
											{$exception.month.max}
										</td>

										<td {if $exception.dow.max.dow == 0}id="red"{/if}>
											{$exception.dow.0|default:0}
										</td>
										<td {if $exception.dow.min.dow == 1}id="green"{elseif $exception.dow.max.dow == 1}id="red"{/if}>
											{$exception.dow.1|default:0}
										</td>
										<td {if $exception.dow.min.dow == 2}id="green"{elseif $exception.dow.max.dow == 2}id="red"{/if}>
											{$exception.dow.2|default:0}
										</td>
										<td {if $exception.dow.min.dow == 3}id="green"{elseif $exception.dow.max.dow == 3}id="red"{/if}>
											{$exception.dow.3|default:0}
										</td>
										<td {if $exception.dow.min.dow == 4}id="green"{elseif $exception.dow.max.dow == 4}id="red"{/if}>
											{$exception.dow.4|default:0}
										</td>
										<td {if $exception.dow.min.dow == 5}id="green"{elseif $exception.dow.max.dow == 5}id="red"{/if}>
											{$exception.dow.5|default:0}
										</td>
										<td {if $exception.dow.min.dow == 6}id="green"{elseif $exception.dow.max.dow == 6}id="red"{/if}>
											{$exception.dow.6|default:0}
										</td>
									</tr>
									{foreachelse}
										<tr class="tblHeader">
											<td colspan="100">
												{t}No Exception History{/t}
											</td>
										</tr>
									{/foreach}

								</table>
							</td>
						</tr>
					{/if}

					<tr>
						<td>
							<br>
							<br>
						</td>
					</tr>
				{foreachelse}
					<tr class="tblDataWhiteNH">
						<td colspan="100">
							{t}No results match your filter criteria.{/t}
						</td>
					</tr>
				{/foreach}

				<tr>
					<td class="tblHeader" colspan="100" align="center">
						{t}Generated:{/t} {getdate type="DATE+TIME" epoch=$generated_time}
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}