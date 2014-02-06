{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="editTable">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				<tr class="tblDataWhiteNH">
					<td colspan="2">
						<br>
						<a href="http://{$ORGANIZATION_URL}"><img src="{$IMAGES_URL}timetrex_logo_wbg_small2.jpg" width="167" height="42" alt="{$APPLICATION_NAME}"></a>
						<br>
						<br>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="cellLeftEditTable">
						<div align="center">{t}Version 3.0.0{/t} - 03-Sep-2009</div>
					</td>
				</tr>
				<tr>
					<td class="cellRightEditTable">
						<b>{t}Features{/t}:</b>
						<ul>

							<li>Wages:
								<ul>
									<li>Added support for wage groups, so specific rates of pay can be defined for different purposes and referenced directly by policies.</li>
								</ul>
							</li>
							<br>
							<li>Pay Period Schedules:
								<ul>
									<li>Added option to define how shifts are assigned to days, possible options are assigning shifts to the day they start on, day they end on, day with most time worked, or split shifts at midnight.</li>
									<li>Added option to automatically move transaction dates backward/forward or to the nearest business day.</li>
								</ul>
							</li>
							<br>
							<li>Hierarchies:
								<ul>
									<li>Redesigned hierachy functionality entirely to better support advanced hierarchy functionality and easier administration, including support for supervisors to be in multiple hierarchies.</li>
									<li>Added support to assign employees to multiple hierarchies directly from the Edit Employee page.</li>
								</ul>
							</li>
							<br>
							<li>Break Policies:
								<ul>
									<li>Added normal, auto-deduct and auto-add break policies.</li>
									<li>Added option to auto-detect break policies based on Time Window or Punch Time.</li>
									<li>Added functionality to add multiple break policies to policy groups.</li>
								</ul>
							</li>
							<br>
							<li>Meal Policies:
								<ul>
									<li>Added setting to auto-detect meal policies based on Time Window or Punch Time.</li>
									<li>Added functionality to add multiple meal policies to policy groups.</li>
								</ul>
							</li>
							<br>
							<li>Holiday Policies:
								<ul>
									<li>Added new type of holiday policies, Advanced Fixed for easily defining a fixed amount of time to award employees who are eligible.</li>
									<li>Added new type of holiday policies, Advanced Average for defining a time averaging formula to award employees who are eligible.</li>
									<li>Added additional holiday eligibility criteria to support checking calendar or scheduled time worked before and after the holiday.</li>
									<li>Added support for multiple holidays policies to be assigned to a policy group.</li>
								</ul>
							</li>
							<br>
							<li>Schedules:
								<ul>
									<li>Added option to filter schedules based on which branch and department the employee is scheduled to work in.</li>
								</ul>
							</li>
							<br>
							<li>Tax / Deductions:
								<ul>
									<li>Added eligibility critiera to define start/end dates as well as minimum/maximum length of service.</li>
									<li>Added support for more advanced calculations by using different amounts for included/excluded pay stub accounts, including Year To Date (YTD) amounts and Units/Hours.</li>
									<li>Added additional calculation formulas.</li>
								</ul>
							</li>
							<br>
							<li>Stations:
								<ul>
									<li>Each station can specify a default branch,department,job and/or task to be used when employees punch in/out from it.</li>
								</ul>
							</li>
							<br>
							<li>Pay Stubs:
								<ul>
									<li>Added support for rate/units to support precision up to 4 decimal places.</li>
								</ul>
							</li>
							<br>
							<li>Pay Stub Amendments:
								<ul>
									<li>Added support to add pay stub amendments for multiple employees in a single operation.</li>
								</ul>
							</li>
							{if DEPLOYMENT_ON_DEMAND == FALSE}
							<br>
							<li>Maintenance Jobs:
								<ul>
									<li>Added support to automatically rotate log files each day to prevent excessive disk space usage.</li>
									<li>Added support to automatically backup the database each day and maintain a set number of backups.</li>
								</ul>
							</li>
							{/if}
							{if $current_company->getProductEdition() >= 20}
							<br>
							<li>Employees:
								<ul>
									<li>Added support to upload document attachments to be assigned to individual employees.</li>
								</ul>
							</li>
							<br>
							<li>Jobs:
								<ul>
									<li>Added criteria to define eligible employees by group, branch, department, and individually included/excluded employees.</li>
									<li>Added criteria to define eligible tasks by group and individually included/excluded tasks.</li>
								</ul>
							</li>
							{/if}
						</ul>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="cellLeftEditTable">
						<div align="center">{t}Version 2.2.0{/t} - 11-Jan-2008</div>
					</td>
				</tr>
				<tr>
					<td class="cellRightEditTable">
						<b>{t}Features{/t}:</b>
						<ul>
							<li>Tax Formulas/Tables:
								<ul>
									<li><b>Updated United States and Canadian tax formulas and tables for 2008.</b></li>
								</ul>
							</li>
							<br>
							<li>OverTime Policies:
								<ul>
									<li>Added consecutive day overtime policy types.</li>
								</ul>
							</li>
							<br>
							<li>Exception Policy:
								<ul>
									<li>Added pre-mature exceptions that alert supervisors to exceptions that may need to be corrected by the end of the day.</li>
									<li>Added search/filter capability to exception list page.</li>
								</ul>
							</li>
							<br>
							<li>Premium Policies:
								<ul>
									<li>Added meal/break policy types.</li>
								</ul>
							</li>
							<br>
							<li>Accrual Policies:
								<ul>
									<li>Added hour based accrual policy type.</li>
									<li>Added functionality to show accrual balances on pay stubs.</li>
								</ul>
							</li>
							<br>
							<li>Schedules:
								<ul>
									<li>Added additional filter criteria and saved report functionality to schedules.</li>
								</ul>
							</li>
							<br>
							<li>Employees:
								<ul>
									<li>Added several status options for employee leaves.</li>
									<li>Added email notifications for exceptions, requests and messages.</li>
								</ul>
							</li>
							<br>
							<li>Company:
								<ul>
									<li>Added functionality to allow uploading of custom company logos.</li>
								</ul>
							</li>
							<br>
							<li>Permissions:
								<ul>
									<li>Added permissions to mask employee SIN/SSN.</li>
								</ul>
							</li>
							<br>
							<li>Tax Reports:
								<ul>
									<li>Added additional support for Canada's Record of Employment form, including XML export functionality compatible with WebROE.<li>
									<li>Updated several US tax reports.</li>
								</ul>
							</li>
							{if $current_company->getProductEdition() >= 20}
							<br>
							<li>
								Jobs:
									<ul>
										<li>Added additional fields/search criteria to job list page.</li>
										<li>Added job selection by code where job drop down boxes are shown.</li>
									</ul>
							</li>
							{/if}
						</ul>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="cellLeftEditTable">
						<div align="center">{t}Version 2.1.0{/t} - 25-Oct-2007</div>
					</td>
				</tr>
				<tr>
					<td class="cellRightEditTable">
						<b>{t}Features{/t}:</b>
						<ul>
							<li>Permissions:
								<ul>
									<li>Added permission groups.</li>
									<li>Added additional permissions to display/hide each field on the employees punch window, as well as default the transfer flag on/off.</li>
								</ul>
							</li>
							<br>
							<li>Schedules:
								<ul>
									<li>Scheduled absences now show on each employees schedule with the name of the absence policy, ie: Vacation/Jury Duty.</li>
									<li>Scheduled absences automatically roll-over to employee's TimeSheets.</li>
								</ul>
							</li>
							<br>
							<li>TimeSheet:
								<ul>
									<li>Added multi-level timesheet verification, with a separate timesheet hierarchy.</li>
								</ul>
							</li>
							<br>
							<li>Stations:
								<ul>
									<li>Added advanced employee selection criteria by employee group, branch, department, individual include/exclude.</li>
								</ul>
							</li>
							<br>
							<li>Meal Policies: Auto-Deduct/Auto-Add types can now take into account lunch punches.
								<ul>
									<li>Auto-Deduct: If the auto-deduct policy is set for one hour, and the employee takes a 45 minute lunch, it will automatically deduct the remaining 15 minutes.</li>
									<li>Auto-Add: If the auto-add policy is set for 30 minutes, and the employee takes a 45 minute lunch, it will automatically pay them for up 30 minutes.</li>
								</ul>
							</li>
							<br>
							<li>Premium Policies:
								<ul>
									<li>Added flat rate pay type.</li>
									<li>Added minimum/maximum hours.</li>
									<li>Added active after Daily/Weekly hours.</li>
									<li>Added Branch/Department/Job/Task differentials.</li>
								</ul>
							</li>
							<br>
							<li>Exception Policy:
								<ul>
									{if $current_company->getProductEdition() >= 20}
									<li>Added No Job/Task exception.</li>
									{/if}
									<li>Added Long/Short Lunch exceptions with grace time.</li>
									<li>Added grace time to Over/Under Scheduled Time exceptions.</li>
									<li>Exceptions are now shown beside each punch on Employee TimeSheets.</li>
								</ul>
							</li>
							<br>
							<li>Reports: Added additional columns to:
								<ul>
									<li>Employee Information Report: iButton/Fingerprint enrolled status, Employee Note columns.<li>
									<li>TimeSheet Summary Report: Employee Number, Worked Days columns.</li>
									{if $current_company->getProductEdition() >= 20}
									<li>Job Summary Report: Regular Time, Over Time, Premium Time columns.</li>
									{/if}
									<li>Added Pay Period Schedule names to drop down lists where multiple pay period schedules exist.</li>
								</ul>
							</li>
							{if $current_company->getProductEdition() >= 20}
							<br>
							<li>
								Jobs:
									<ul>
										<li>Added job supervisor option to jobs.</li>
										<li>Added copy job functionality.</li>
										<li>Added import_job script.</li>
									</ul>
							</li>
							<br>
							<li>
								Invoice:
									<ul>
										<li>Added invoice status (#invoice_status#) variable when emailing invoices to clients.</li>
									</ul>
							</li>
							<br>
							<li>
								Products:
									<ul>
										<li>Added Fixed, Bracket, Progressive price types to Products.</li>
									</ul>
							</li>
							{/if}
						</ul>
					</td>
				</tr>

				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}