{include file="header.tpl" enable_calendar=TRUE enable_ajax=TRUE body_onload="formChangeDetect(); showProvince()"}

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
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="edituser" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$uf->Validator->isValid()}
					{include file="form_errors.tpl" object="uf"}
				{/if}

			<table class="editTable">

				{include file="data_saved.tpl" result=$data_saved}

				{if $incomplete == 1}
					<tr id="warning">
						<td colspan="7">
							{t escape="no" 1=$APPLICATION_NAME}Welcome to <b>%1</b> since this is your first time logging in, we need you to fill out the following information.{/t}
						</td>
					</tr>
				{/if}
				{if $permission->Check('user','edit_advanced') AND $user_data.id != ''}
				<tr class="tblHeader">
					<td colspan="2">
						{t}Employee:{/t}
						<a href="javascript: submitModifiedForm('filter_user', 'prev', document.edituser);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
						<select name="id" id="filter_user" onChange="submitModifiedForm('filter_user', '', document.edituser);">
							{html_options options=$user_data.user_options selected=$user_data.id}
						</select>
						<input type="hidden" id="old_filter_user" value="{$user_data.id}">
						<a href="javascript: submitModifiedForm('filter_user', 'next', document.edituser);"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

						{assign var="user_id" value=$user_data.id}

						{if $permission->Check('wage','view') OR ( $permission->Check('wage','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('wage','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="UserWageList.php" values="user_id=$user_id,saved_search_id=$saved_search_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Wage{/t}</a> ]
						{/if}

						{if $permission->Check('user_tax_deduction','view') OR ( $permission->Check('user_tax_deduction','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('user_tax_deduction','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="UserDeductionList.php" values="user_id=$user_id,saved_search_id=$saved_search_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Tax{/t}</a> ]
						{/if}

						{if $permission->Check('pay_stub_amendment','view') OR ( $permission->Check('pay_stub_amendment','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('pay_stub_amendment','view_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="../pay_stub_amendment/PayStubAmendmentList.php" values="filter_user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}PS Amendment{/t}</a> ]
						{/if}

						{if $permission->Check('user_preference','edit') OR ( $permission->Check('user_preference','edit_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('user_preference','edit_own') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="EditUserPreference.php" values="user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Prefs{/t}</a> ]
						{/if}

						{if $user_data.country == 'CA' AND ( $permission->Check('roe','view') OR ( $permission->Check('roe','view_child') AND $user_data.is_child === TRUE ) OR ( $permission->Check('roe','view_own') AND $user_data.is_owner === TRUE ) )}
							[ <a href="{urlbuilder script="../roe/ROEList.php" values="user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}ROE{/t}</a> ]
						{/if}

						{if $permission->Check('user','edit_bank') OR ( $permission->Check('user','edit_child_bank') AND $user_data.is_child === TRUE ) OR ( $permission->Check('user','edit_own_bank') AND $user_data.is_owner === TRUE )}
							[ <a href="{urlbuilder script="../bank_account/EditBankAccount.php" values="user_id=$user_id" merge="FALSE"}" onClick="return isModifiedForm();">{t}Bank{/t}</a> ]
						{/if}
					</td>
				</tr>
				{/if}

				<tr>
					<td valign="top">
						<table class="editTable">
							<tr class="tblHeader">
								<td colspan="2">
									{t}Employee Identification{/t}
								</td>
							</tr>

							<tr onClick="showHelpEntry('company')">
								<td class="{isvalid object="uf" label="company" value="cellLeftEditTable"}">
									{t}Company:{/t}
								</td>
								<td class="cellRightEditTable">
									{$user_data.company_options[$user_data.company_id]}
									{if $permission->Check('company','view')}
										<input type="hidden" name="user_data[company_id]" value="{$user_data.company_id}">
										<input type="hidden" name="company_id" value="{$user_data.company_id}">
									{/if}
								</td>
							</tr>

							{if $permission->Check('user','edit_advanced')}
							<tr onClick="showHelpEntry('status')">
								<td class="{isvalid object="uf" label="status" value="cellLeftEditTable"}">
									{t}Status:{/t}
								</td>
								<td class="cellRightEditTable">
									{*
										Don't let the currently logged in user edit their own status,
										this keeps them from accidently locking themselves out of the system.
									*}
									{if $user_data.id != $current_user->getId() AND $permission->Check('user','edit_advanced') AND ( $permission->Check('user','edit') OR $permission->Check('user','edit_own') ) }
										<select name="user_data[status]">
											{html_options options=$user_data.status_options selected=$user_data.status}
										</select>
									{else}
										<input type="hidden" name="user_data[status]" value="{$user_data.status}">
										{$user_data.status_options[$user_data.status]}
									{/if}
								</td>
							</tr>


							<tr onClick="showHelpEntry('permission_control')">
								<td class="{isvalid object="uf" label="permission_control" value="cellLeftEditTable"}">
									{t}Permission Group:{/t}
								</td>
								<td class="cellRightEditTable">
									{*
										Don't let the currently logged in user edit their own permissions from here
										Even if they are a supervisor. This should prevent people from accidently changing
										themselves to a regular employee and locking themselves out.
									*}
									{if $user_data.id != $current_user->getId()
												AND ( $permission->Check('permission','edit') OR $permission->Check('permission','edit_own') OR $permission->Check('user','edit_permission_group') )
												AND $user_data.permission_level <= $permission->getLevel()}
										<select name="user_data[permission_control_id]">
											{html_options options=$user_data.permission_control_options selected=$user_data.permission_control_id}
										</select>
									{else}
										{$user_data.permission_control_options[$user_data.permission_control_id]|default:"N/A"}
										<input type="hidden" name="user_data[permission_control_id]" value="{$user_data.permission_control_id}">
									{/if}
								</td>
							</tr>


							<tr onClick="showHelpEntry('pay_period_schedule_id')">
								<td class="{isvalid object="uf" label="pay_period_schedule_id" value="cellLeftEditTable"}">
									{t}Pay Period Schedule:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('pay_period_schedule','edit') OR $permission->Check('user','edit_pay_period_schedule')}
										<select name="user_data[pay_period_schedule_id]">
											{html_options options=$user_data.pay_period_schedule_options selected=$user_data.pay_period_schedule_id}
										</select>
									{else}
										{$user_data.pay_period_schedule_options[$user_data.pay_period_schedule_id]|default:"N/A"}
										<input type="hidden" name="user_data[pay_period_schedule_id]" value="{$user_data.pay_period_schedule_id}">
									{/if}
								</td>
							</tr>

							<tr onClick="showHelpEntry('policy_group_id')">
								<td class="{isvalid object="uf" label="policy_group_id" value="cellLeftEditTable"}">
									{t}Policy Group:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('policy_group','edit') OR $permission->Check('user','edit_policy_group')}
										<select name="user_data[policy_group_id]">
											{html_options options=$user_data.policy_group_options selected=$user_data.policy_group_id}
										</select>
									{else}
										{$user_data.policy_group_options[$user_data.policy_group_id]|default:"N/A"}
										<input type="hidden" name="user_data[policy_group_id]" value="{$user_data.policy_group_id}">
									{/if}
								</td>
							</tr>

							<tr onClick="showHelpEntry('currency_id')">
								<td class="{isvalid object="uf" label="currency_id" value="cellLeftEditTable"}">
									{t}Currency:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('currency','edit')}
										<select name="user_data[currency_id]">
											{html_options options=$user_data.currency_options selected=$user_data.currency_id}
										</select>
									{else}
										{$user_data.currency_options[$user_data.currency_id]|default:"N/A"}
										<input type="hidden" name="user_data[currency_id]" value="{$user_data.currency_id}">
									{/if}
								</td>
							</tr>

							{/if}

							<tr onClick="showHelpEntry('user_name')">
								<td class="{isvalid object="uf" label="user_name" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}User Name:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[user_name]" value="{$user_data.user_name}">
									{else}
										{$user_data.user_name}
										<input type="hidden" name="user_data[user_name]" value="{$user_data.user_name}">
									{/if}
								</td>
							</tr>

							{if $permission->Check('user','edit_advanced')}
							<tr onClick="showHelpEntry('password')">
								<td class="{isvalid object="uf" label="password" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}Password:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="password" name="user_data[password]" value="{$user_data.password}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('password')">
								<td class="{isvalid object="uf" label="password" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}Password (confirm):{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="password" name="user_data[password2]" value="{$user_data.password2}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('employee_number')">
								<td class="{isvalid object="uf" label="employee_number" value="cellLeftEditTable"}">
									{t}Employee Number:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data.is_child === TRUE) OR ($permission->Check('user','edit_own') AND $user_data.is_owner === TRUE)}
										<input type="text" size="10" name="user_data[employee_number]" value="{$user_data.employee_number|default:$user_data.next_available_employee_number}">
										{if $user_data.next_available_employee_number != ''}
										{t}Next available:{/t} {$user_data.next_available_employee_number}
										{/if}
									{else}
										{$user_data.employee_number|default:"N/A"}
									{/if}
								</td>
							</tr>

							<tr onClick="showHelpEntry('phone_id')">
								<td class="{isvalid object="uf" label="phone_id" value="cellLeftEditTable"}">
									{t}Quick Punch ID:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="15" name="user_data[phone_id]" value="{$user_data.phone_id}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('phone_password')">
								<td class="{isvalid object="uf" label="phone_password" value="cellLeftEditTable"}">
									{t}Quick Punch Password:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="15" name="user_data[phone_password]" value="{$user_data.phone_password}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('default_branch')">
								<td class="{isvalid object="uf" label="default_branch" value="cellLeftEditTable"}">
									{t}Default Branch:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[default_branch_id]">
										{html_options options=$user_data.branch_options selected=$user_data.default_branch_id}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('default_department')">
								<td class="{isvalid object="uf" label="default_department" value="cellLeftEditTable"}">
									{t}Default Department:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[default_department_id]">
										{html_options options=$user_data.department_options selected=$user_data.default_department_id}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('group')">
								<td class="{isvalid object="uf" label="group" value="cellLeftEditTable"}">
									{t}Group:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[group_id]">
										{html_options options=$user_data.group_options selected=$user_data.group_id}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('title')">
								<td class="{isvalid object="uf" label="title" value="cellLeftEditTable"}">
									{t}Title:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('user','add') OR $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data.is_child === TRUE) OR ($permission->Check('user','edit_own') AND $user_data.is_owner === TRUE)}
										<select name="user_data[title_id]">
											{html_options options=$user_data.title_options selected=$user_data.title_id}
										</select>
									{else}
										{$user_data.title|default:"N/A"}
									{/if}
								</td>
							</tr>

							<tr onClick="showHelpEntry('hire_date')">
								<td class="{isvalid object="uf" label="hire_date" value="cellLeftEditTable"}">
									{t}Hire Date:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="10" id="hire_date" name="user_data[hire_date]" value="{getdate type="DATE" epoch=$user_data.hire_date}">
									<img src="{$BASE_URL}/images/cal.gif" id="cal_hire_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('hire_date', 'cal_hire_date', false);">
									{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
								</td>
							</tr>

							<tr onClick="showHelpEntry('termination_date')">
								<td class="{isvalid object="uf" label="termination_date" value="cellLeftEditTable"}">
									{t}Termination Date:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="10" id="termination_date" name="user_data[termination_date]" value="{getdate type="DATE" epoch=$user_data.termination_date}">
									<img src="{$BASE_URL}/images/cal.gif" id="cal_termination_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('termination_date', 'cal_termination_date', false);">
									{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
								</td>
							</tr>

							{if isset($user_data.other_field_names.other_id1) }
								<tr onClick="showHelpEntry('other_id1')">
									<td class="{isvalid object="uf" label="other_id1" value="cellLeftEditTable"}">
										{$user_data.other_field_names.other_id1}:
									</td>
									<td class="cellRightEditTable">
										<input type="text" name="user_data[other_id1]" value="{$user_data.other_id1}">
									</td>
								</tr>
							{/if}

							{if isset($user_data.other_field_names.other_id2) }
							<tr onClick="showHelpEntry('other_id2')">
								<td class="{isvalid object="uf" label="other_id2" value="cellLeftEditTable"}">
									{$user_data.other_field_names.other_id2}:
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[other_id2]" value="{$user_data.other_id2}">
								</td>
							</tr>
							{/if}
							{if isset($user_data.other_field_names.other_id3) }
							<tr onClick="showHelpEntry('other_id3')">
								<td class="{isvalid object="uf" label="other_id3" value="cellLeftEditTable"}">
									{$user_data.other_field_names.other_id3}:
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[other_id3]" value="{$user_data.other_id3}">
								</td>
							</tr>
							{/if}
							{if isset($user_data.other_field_names.other_id4) }
								<tr onClick="showHelpEntry('other_id4')">
									<td class="{isvalid object="uf" label="other_id4" value="cellLeftEditTable"}">
										{$user_data.other_field_names.other_id4}:
									</td>
									<td class="cellRightEditTable">
										<input type="text" name="user_data[other_id4]" value="{$user_data.other_id4}">
									</td>
								</tr>
							{/if}
							{if isset($user_data.other_field_names.other_id5) }
								<tr onClick="showHelpEntry('other_id5')">
									<td class="{isvalid object="uf" label="other_id5" value="cellLeftEditTable"}">
										{$user_data.other_field_names.other_id5}:
									</td>
									<td class="cellRightEditTable">
										<input type="text" name="user_data[other_id5]" value="{$user_data.other_id5}">
									</td>
								</tr>
							{/if}

							{if is_array($user_data.hierarchy_control_options) AND count($user_data.hierarchy_control_options) > 0}
								<tr onClick="showHelpEntry('termination_date')">
									<td class="tblHeader" colspan="2">
										{t}Hierarchies{/t}
									</td>
								</tr>

								{foreach from=$user_data.hierarchy_control_options key=hierarchy_control_object_type_id item=hierarchy_control name=hierarchy_control}
									<tr onClick="showHelpEntry('termination_date')">
										<td class="{isvalid object="uf" label="termination_date" value="cellLeftEditTable"}">
											{$user_data.hierarchy_object_type_options[$hierarchy_control_object_type_id]}:
										</td>
										<td class="cellRightEditTable">
											{if $permission->Check('hierarchy','edit') OR $permission->Check('user','edit_hierarchy')}
												<select name="user_data[hierarchy_control][{$hierarchy_control_object_type_id}]">
													{html_options options=$user_data.hierarchy_control_options[$hierarchy_control_object_type_id] selected=$user_data.hierarchy_control[$hierarchy_control_object_type_id]}
												</select>
											{else}
												{$user_data.hierarchy_control_options[$hierarchy_control_object_type_id].$user_data.hierarchy_control[$hierarchy_control_object_type_id]|default:"N/A"}
												<input type="hidden" name="user_data[hierarchy_control][{$hierarchy_control_object_type_id}]" value="{$user_data.hierarchy_control[$hierarchy_control_object_type_id]}">
											{/if}
										</td>
									</tr>
								{/foreach}
							{/if}

							{/if}
{if $permission->Check('user','edit_advanced') AND ( $permission->Check('user','add') OR ( $permission->Check('user','edit') OR ($permission->Check('user','edit_child') AND $user_data.is_child === TRUE) OR ($permission->Check('user','edit_own') AND $user_data.is_owner === TRUE) ) )}
						</table>
					</td>
					<td valign="top">
						<table class="editTable">
{/if}

							<tr class="tblHeader">
								<td colspan="2">
									{t}Contact Information{/t}
								</td>
							</tr>

							<tr onClick="showHelpEntry('first_name')">
								<td class="{isvalid object="uf" label="first_name" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}First Name:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[first_name]" value="{$user_data.first_name}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('middle_name')">
								<td class="{isvalid object="uf" label="middle_name" value="cellLeftEditTable"}">
									{t}Middle Name:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[middle_name]" value="{$user_data.middle_name}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('last_name')">
								<td class="{isvalid object="uf" label="last_name" value="cellLeftEditTable"}">
									{if $permission->Check('user','edit_advanced')}<font color="red">*</font>{/if}{t}Last Name:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[last_name]" value="{$user_data.last_name}">
								</td>
							</tr>

							{if $current_company->getEnableSecondLastName() == TRUE}
								<tr onClick="showHelpEntry('second_last_name')">
									<td class="{isvalid object="uf" label="second_last_name" value="cellLeftEditTable"}">
										{t}Second Surname:{/t}
									</td>
									<td class="cellRightEditTable">
										<input type="text" name="user_data[second_last_name]" value="{$user_data.second_last_name}">
									</td>
								</tr>
							{/if}

							<tr onClick="showHelpEntry('sex')">
								<td class="{isvalid object="uf" label="sex" value="cellLeftEditTable"}">
									{t}Sex:{/t}
								</td>
								<td class="cellRightEditTable">
									<select name="user_data[sex]">
										{html_options options=$user_data.sex_options selected=$user_data.sex}
									</select>
								</td>
							</tr>

							<tr onClick="showHelpEntry('address1')">
								<td class="{isvalid object="uf" label="address1" value="cellLeftEditTable"}">
									{if $incomplete == 1}<font color="red">*</font>{/if}{t}Home Address (Line 1):{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[address1]" size="30" value="{$user_data.address1}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('address2')">
								<td class="{isvalid object="uf" label="address2" value="cellLeftEditTable"}">
									{t}Home Address (Line 2):{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[address2]" size="30" value="{$user_data.address2}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('city')">
								<td class="{isvalid object="uf" label="city" value="cellLeftEditTable"}">
									{if $incomplete == 1}<font color="red">*</font>{/if}{t}City:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[city]" value="{$user_data.city}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('country')">
								<td class="{isvalid object="uf" label="country" value="cellLeftEditTable"}">
									{t}Country:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('user','edit_advanced')}
										<select id="country" name="user_data[country]" onChange="showProvince()">
											{html_options options=$user_data.country_options selected=$user_data.country}
										</select>
									{else}
										{$user_data.country_options[$user_data.country]}
										<input type="hidden" name="user_data[country]" value="{$user_data.country}">
									{/if}
								</td>
							</tr>

							<tr onClick="showHelpEntry('province')">
								<td class="{isvalid object="uf" label="province" value="cellLeftEditTable"}">
									{t}Province / State:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('user','edit_advanced')}
										<select id="province" name="user_data[province]">
											{* {html_options options=$user_data.province_options selected=$user_data.province} *}
										</select>
									{else}
										{$user_data.province_options[$user_data.province]}
										<input type="hidden" name="user_data[province]" value="{$user_data.province}">
									{/if}
									<input type="hidden" id="selected_province" value="{$user_data.province}">
								</td>
							</tr>
							<tr onClick="showHelpEntry('postal_code')">
								<td class="{isvalid object="uf" label="postal_code" value="cellLeftEditTable"}">
									{if $incomplete == 1}<font color="red">*</font>{/if}{t}Postal / ZIP Code:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[postal_code]" value="{$user_data.postal_code}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('work_phone')">
								<td class="{isvalid object="uf" label="work_phone,work_phone_ext" value="cellLeftEditTable"}">
									{t}Work Phone:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[work_phone]" value="{$user_data.work_phone}">
									{t}Ext:{/t} <input type="text" name="user_data[work_phone_ext]" value="{$user_data.work_phone_ext}" size="6">
								</td>
							</tr>

							<tr onClick="showHelpEntry('home_phone')">
								<td class="{isvalid object="uf" label="home_phone" value="cellLeftEditTable"}">
									{if $incomplete == 1}<font color="red">*</font>{/if}{t}Home Phone:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[home_phone]" value="{$user_data.home_phone}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('mobile_phone')">
								<td class="{isvalid object="uf" label="mobile_phone" value="cellLeftEditTable"}">
									{t}Mobile Phone:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[mobile_phone]" value="{$user_data.mobile_phone}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('fax_phone')">
								<td class="{isvalid object="uf" label="fax_phone" value="cellLeftEditTable"}">
									{t}Fax:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" name="user_data[fax_phone]" value="{$user_data.fax_phone}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('work_email')">
								<td class="{isvalid object="uf" label="work_email" value="cellLeftEditTable"}">
									{t}Work Email:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="30" name="user_data[work_email]" value="{$user_data.work_email}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('home_email')">
								<td class="{isvalid object="uf" label="home_email" value="cellLeftEditTable"}">
									{t}Home Email:{/t}
								</td>
								<td class="cellRightEditTable">
									<input type="text" size="30" name="user_data[home_email]" value="{$user_data.home_email}">
								</td>
							</tr>

							<tr onClick="showHelpEntry('birth_date')">
								<td class="{isvalid object="uf" label="birth_date" value="cellLeftEditTable"}">
									{t}Birth Date:{/t}
								</td>
								<td class="cellRightEditTable">
									{html_select_date field_array="user_data" prefix="birth_" start_year="1930" month_empty="--" day_empty="--" year_empty="--" time=$user_data.birth_date}
								</td>
							</tr>

							<tr onClick="showHelpEntry('sin')">
								<td class="{isvalid object="uf" label="sin" value="cellLeftEditTable"}">
									{t}SIN / SSN:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $permission->Check('user','edit_advanced')}
										<input type="text" name="user_data[sin]" value="{$user_data.sin}" size="15">
									{else}
										{$user_data.sin|default:"N/A"}
										<input type="hidden" name="user_data[sin]" value="{$user_data.sin}">
									{/if}
								</td>
							</tr>

							{if $permission->Check('user','edit_advanced')}
							<tr onClick="showHelpEntry('note')">
								<td class="{isvalid object="uf" label="note" value="cellLeftEditTable"}">
									{t}Note:{/t}
								</td>
								<td class="cellRightEditTable">
									<textarea rows="5" cols="45" name="user_data[note]">{$user_data.note|escape}</textarea>
								</td>
							</tr>
							{/if}
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="user_data[id]" value="{$user_data.id}">
		<input type="hidden" name="incomplete" value="{$incomplete}">
		<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
		</form>
	</div>
</div>
{if $user_data.id != ''
	AND $current_company->getProductEdition() >= 20
	AND ( $permission->Check('document','view') OR $permission->Check('document','view_own') OR $permission->Check('document','view_private') ) }
<br>
<br>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{t}Attachments{/t}</span></div>
</div>
<div id="rowContentInner">
	<div id="contentBoxTwoEdit">
		<table class="tblList">
			<tr>
				<td>
					{embeddeddocumentattachmentlist object_type_id=100 object_id=$user_data.id}
				</td>
			</tr>
		</table>
	</div>
</div>
{/if}
{include file="footer.tpl"}
