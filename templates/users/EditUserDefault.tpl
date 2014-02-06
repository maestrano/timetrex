{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="formChangeDetect(); showProvince(); showDateFormat();"}
<script	language=JavaScript>

{literal}
var loading = false;
var hwCallback = {
		getProvinceOptions: function(result) {
			if ( result != false ) {
				province_obj = document.getElementById('province');
				selected_province = document.getElementById('selected_province').value;

				populateSelectBox( province_obj, result, selected_province);
			}
			loading = false;
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function showProvince() {
	country = document.getElementById('country').value;
	remoteHW.getProvinceOptions( country );
}

function showDateFormat() {
	var lang = document.getElementById('language').value;

	if (lang == 'en'){
		document.getElementById('DateFormat').style.display = '';
		document.getElementById('otherDateFormat').style.display = 'none';
	}else{
		document.getElementById('otherDateFormat').style.display = '';
		document.getElementById('DateFormat').style.display = 'none';
	}
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="edituser" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$udf->Validator->isValid()}
					{include file="form_errors.tpl" object="udf"}
				{/if}

			<table class="editTable">

				{include file="data_saved.tpl" result=$data_saved}

				<tr>
					<td valign="top">
						<table class="editTable">
							<tr class="tblHeader">
								<td colspan="2">
									{t}Employee Identification{/t}
								</td>
							</tr>

							<tr onClick="showHelpEntry('company')">
								<td class="{isvalid object="udf" label="company" value="cellLeftEditTable"}">
									{t}Company:{/t}
								</td>
								<td class="cellRightEditTable">
									{$user_data.company}
								</td>
							</tr>

							<tr onClick="showHelpEntry('permission_control')">
								<td class="{isvalid object="uf" label="permission_control" value="cellLeftEditTable"}">
									{t}Permission Group:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('permission','edit')}
									<select name="user_data[permission_control_id]">
										{html_options options=$user_data.permission_control_options selected=$user_data.permission_control_id}
									</select>
									{else}
										{$user_data.permission_control_options[$user_data.permission_control_id]}
										<input type="hidden" name="user_data[permission_control_id]" value="{$user_data.permission_control_id}">
									{/if}
								</td>
							</tr>

							<tr onClick="showHelpEntry('pay_period_schedule')">
								<td class="{isvalid object="udf" label="pay_period_schedule" value="cellLeftEditTable"}">
									{t}Pay Period Schedule:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[pay_period_schedule_id]">
										{html_options options=$user_data.pay_period_schedule_options selected=$user_data.pay_period_schedule_id}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('policy_group')">
								<td class="{isvalid object="udf" label="policy_group" value="cellLeftEditTable"}">
									{t}Policy Group:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[policy_group_id]">
										{html_options options=$user_data.policy_group_options selected=$user_data.policy_group_id}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('currency')">
								<td class="{isvalid object="udf" label="currency" value="cellLeftEditTable"}">
									{t}Currency:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[currency_id]">
										{html_options options=$user_data.currency_options selected=$user_data.currency_id}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('title')">
								<td class="{isvalid object="udf" label="title" value="cellLeftEditTable"}">
									{t}Title:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[title_id]">
										{html_options options=$user_data.title_options selected=$user_data.title_id}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('employee_number')">
								<td class="{isvalid object="udf" label="employee_number" value="cellLeftEditTable"}">
									{t}Employee Number:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[employee_number]" value="{$user_data.employee_number}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('hire_date')">
								<td class="{isvalid object="udf" label="hire_date" value="cellLeftEditTable"}">
									{t}Hire Date:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="15" id="hire_date" name="user_data[hire_date]" value="{getdate type="DATE" epoch=$user_data.hire_date}">
									<img src="{$BASE_URL}/images/cal.gif" id="cal_hire_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('hire_date', 'cal_hire_date', false);">
									{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
								</td>
							</tr>

							<tr onClick="showHelpEntry('default_branch')">
								<td class="{isvalid object="udf" label="default_branch" value="cellLeftEditTable"}">
									{t}Default Branch:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[default_branch_id]">
										{html_options options=$user_data.branch_options selected=$user_data.default_branch_id}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('default_department')">
								<td class="{isvalid object="udf" label="default_department" value="cellLeftEditTable"}">
									{t}Default Department:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[default_department_id]">
										{html_options options=$user_data.department_options selected=$user_data.default_department_id}
									</select>
								</td>
							</tr>

							<tr class="tblHeader">
								<td colspan="2">
									{t}Contact Information{/t}
								</td>
							</tr>

							<tr onClick="showHelpEntry('city')">
								<td class="{isvalid object="udf" label="city" value="cellLeftEditTable"}">
									{t}City:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[city]" value="{$user_data.city}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('country')">
								<td class="{isvalid object="udf" label="country" value="cellLeftEditTable"}">
									{t}Country:{/t}
								</td>
								<td class="cellRightEditTable">
									<select id="country" name="user_data[country]" onChange="showProvince()">
										{html_options options=$user_data.country_options selected=$user_data.country}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('province')">
								<td class="{isvalid object="udf" label="province" value="cellLeftEditTable"}">
									{t}Province / State:{/t}
								</td>
								<td class="cellRightEditTable">
									<select id="province" name="user_data[province]">
									</select>
									<input type="hidden" id="selected_province" value="{$user_data.province}">
								</td>
							</tr>
							<tr onClick="showHelpEntry('work_phone')">
								<td class="{isvalid object="udf" label="work_phone,work_phone_ext" value="cellLeftEditTable"}">
									{t}Work Phone:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[work_phone]" value="{$user_data.work_phone}">
									{t}Ext:{/t} <input type="text" name="user_data[work_phone_ext]" value="{$user_data.work_phone_ext}" size="6">
								</td>
							</tr>

							<tr onClick="showHelpEntry('work_email')">
								<td class="{isvalid object="udf" label="work_email" value="cellLeftEditTable"}">
									{t}Work Email:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="30" name="user_data[work_email]" value="{$user_data.work_email}">
								</td>
							</tr>

						</table>
					</td>
					<td valign="top">
						<table class="editTable">

							<tr class="tblHeader">
								<td colspan="2">
									{t}Employee Preferences{/t}
								</td>
							</tr>

							<tr onClick="showHelpEntry('language')">
								<td class="{isvalid object="udf" label="language" value="cellLeftEditTable"}">
									{t}Language:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[language]" onchange = "showDateFormat();" id="language">
										{html_options options=$user_data.language_options selected=$user_data.language}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('date_format')">
								<td class="{isvalid object="udf" label="date_format" value="cellLeftEditTable"}">
									{t}Date Format:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[date_format]" id="DateFormat" style="display:none">
										{html_options options=$user_data.date_format_options selected=$user_data.date_format}
									</select>

									<select name="user_data[other_date_format]" id="otherDateFormat">
										{html_options options=$user_data.other_date_format_options selected=$user_data.other_date_format}
									</select>
								</td>

							</tr>

							<tr onClick="showHelpEntry('time_format')">
								<td class="{isvalid object="udf" label="time_format" value="cellLeftEditTable"}">
									{t}Time Format:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[time_format]">
										{html_options options=$user_data.time_format_options selected=$user_data.time_format}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('time_unit_format')">
								<td class="{isvalid object="udf" label="time_unit_format" value="cellLeftEditTable"}">
									{t}Time Units:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[time_unit_format]">
										{html_options options=$user_data.time_unit_format_options selected=$user_data.time_unit_format}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('time_zone')">
								<td class="{isvalid object="udf" label="time_zone" value="cellLeftEditTable"}">
									{t}Time Zone:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[time_zone]">
										{html_options options=$user_data.time_zone_options selected=$user_data.time_zone}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('start_week_day')">
								<td class="{isvalid object="udf" label="start_week_day" value="cellLeftEditTable"}">
									{t}Start Weeks on:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[start_week_day]">
										{html_options options=$user_data.start_week_day_options selected=$user_data.start_week_day}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('items_per_page')">
								<td class="{isvalid object="udf" label="items_per_page" value="cellLeftEditTable"}">
									{t}Rows per Page:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="5" name="user_data[items_per_page]" value="{$user_data.items_per_page}">
								</td>
							</tr>

							<tr class="tblHeader">
								<td colspan="2">
									{t}Email Notifications{/t}
								</td>
							</tr>

							<tr onClick="showHelpEntry('email_notification_exception')">
								<td class="{isvalid object="udf" label="email_notification_exception" value="cellLeftEditTable"}">
									{t}Exceptions:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="checkbox" name="user_data[enable_email_notification_exception]" value="1" {if $user_data.enable_email_notification_exception == TRUE}checked{/if}>
								</td>
							</tr>

							<tr onClick="showHelpEntry('email_notification_message')">
								<td class="{isvalid object="udf" label="email_notification_message" value="cellLeftEditTable"}">
									{t}Messages:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="checkbox" name="user_data[enable_email_notification_message]" value="1" {if $user_data.enable_email_notification_message == TRUE}checked{/if}>
								</td>
							</tr>

							<tr onClick="showHelpEntry('email_notification_home')">
								<td class="{isvalid object="udf" label="email_notification_home" value="cellLeftEditTable"}">
									{t}Send Notifications to Home Email:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="checkbox" name="user_data[enable_email_notification_home]" value="1" {if $user_data.enable_email_notification_home == TRUE}checked{/if}>
								</td>
							</tr>

							<tr class="tblHeader">
								<td colspan="2">
									{t}Employee Tax / Deductions{/t}
								</td>
							</tr>

							<tr onClick="showHelpEntry('company_deduction')">
								<td class="{isvalid object="udf" label="company_deduction" value="cellLeftEditTable"}">
									{t}Deductions:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[company_deduction_ids][]" multiple>
										{html_options options=$user_data.company_deduction_options selected=$user_data.company_deduction_ids}
									</select>
								</td>
							</tr>

{*
							<tr onClick="showHelpEntry('federal_claim')">
								<td class="{isvalid object="udf" label="federal_claim" value="cellLeftEditTable"}">
									{t}Federal Claim Amount:{/t}
								</td>
								<td class="cellRightEditTable">
									$<input type="text" size="10" name="user_data[federal_claim]" value="{$user_data.federal_claim}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('federal_tax_exempt')">
								<td class="{isvalid object="udf" label="federal_tax_exempt" value="cellLeftEditTable"}">
									{t}Federal Tax Exempt:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="checkbox" class="checkbox" name="user_data[federal_tax_exempt]" value="1"{if $user_data.federal_tax_exempt == TRUE}checked{/if}>
								</td>
							</tr>

							<tr onClick="showHelpEntry('provincial_claim')">
								<td class="{isvalid object="udf" label="provincial_claim" value="cellLeftEditTable"}">
									{t}Provincial Claim Amount:{/t}
								</td>
								<td class="cellRightEditTable">
									$<input type="text" size="10" name="user_data[provincial_claim]" value="{$user_data.provincial_claim}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('provincial_tax_exempt')">
								<td class="{isvalid object="udf" label="provincial_tax_exempt" value="cellLeftEditTable"}">
									{t}Provincial Tax Exempt:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="checkbox" class="checkbox" name="user_data[provincial_tax_exempt]" value="1"{if $user_data.provincial_tax_exempt == TRUE}checked{/if}>
								</td>
							</tr>

							<tr onClick="showHelpEntry('federal_additional_deduction')">
								<td class="{isvalid object="udf" label="federal_additional_deduction" value="cellLeftEditTable"}">
									{t}Additional Deduction Amount:{/t}
								</td>
								<td class="cellRightEditTable">
									$<input type="text" size="10" name="user_data[federal_additional_deduction]" value="{$user_data.federal_additional_deduction}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('wcb_rate')">
								<td class="{isvalid object="udf" label="wcb_rate" value="cellLeftEditTable"}">
									{t}WCB Rate:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="6" name="user_data[wcb_rate]" value="{$user_data.wcb_rate}">%
								</td>
							</tr>

							<tr onClick="showHelpEntry('vacation_rate')">
								<td class="{isvalid object="udf" label="vacation_rate" value="cellLeftEditTable"}">
									{t}Vacation Rate:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="6" name="user_data[vacation_rate]" value="{$user_data.vacation_rate}">%
								</td>
							</tr>

							<tr onClick="showHelpEntry('release_vacation')">
								<td class="{isvalid object="udf" label="release_vacation" value="cellLeftEditTable"}">
									{t}Always Release Vacation:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="checkbox" class="checkbox" name="user_data[release_vacation]" value="1"{if $user_data.release_vacation == TRUE}checked{/if}>
								</td>
							</tr>

							<tr onClick="showHelpEntry('ei_exempt')">
								<td class="{isvalid object="udf" label="ei_exempt" value="cellLeftEditTable"}">
									{t}EI Exempt:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="checkbox" class="checkbox" name="user_data[ei_exempt]" value="1"{if $user_data.ei_exempt == TRUE}checked{/if}>
								</td>
							</tr>

							<tr onClick="showHelpEntry('cpp_exempt')">
								<td class="{isvalid object="udf" label="cpp_exempt" value="cellLeftEditTable"}">
									{t}CPP Exempt:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="checkbox" class="checkbox" name="user_data[cpp_exempt]" value="1"{if $user_data.cpp_exempt == TRUE}checked{/if}>
								</td>
							</tr>
*}
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="user_data[id]" value="{$user_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}