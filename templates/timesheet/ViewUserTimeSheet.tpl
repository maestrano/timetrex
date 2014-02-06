{include file="header.tpl" enable_calendar=true}
<script	language=JavaScript>

{literal}
function editPunch(punchID,punchControlId,userID,date,statusID) {
	try {
		eP=window.open('{/literal}{$BASE_URL}{literal}punch/EditPunch.php?id='+ encodeURI(punchID) +'&punch_control_id='+ encodeURI(punchControlId) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date) +'&status_id='+ encodeURI(statusID),"Edit_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=600,height=470,resizable=1");
		if (window.focus) {
			eP.focus()
		}
	} catch (e) {
		//DN
	}
}
function hourList(userDateID,userID,date) {
	try {
		hL=window.open('{/literal}{$BASE_URL}{literal}punch/UserDateTotalList.php?user_date_id='+ encodeURI(userDateID) +'&filter_user_id='+ encodeURI(userID) +'&filter_date='+ encodeURI(date),"Hours","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=750,height=350,resizable=1");
		if (window.focus) {
			hL.focus()
		}
	} catch (e) {
		//DN
	}
}
function editAbsence(absenceID,userID,date) {
	try {
		eA=window.open('{/literal}{$BASE_URL}{literal}punch/EditUserAbsence.php?id='+ encodeURI(absenceID) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date),"Edit_Absence","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
		if (window.focus) {
			eA.focus()
		}
	} catch (e) {
		//DN
	}
}
function changeDate(date) {
	document.getElementById("filter_date").value = date;
	document.timesheet.submit();
}

function resetAction() {
	action_obj = document.getElementById('select_action');

	if ( action_obj != null && action_obj.value != 0 ) {
		action_obj[0].selected = true;
	}
}

function confirmAction() {
	action = document.getElementById('select_action').value;

	var recalculateCompany = "{/literal}{t}Are you sure you want to recalculate the timesheets of every employee?{/t}{literal}";
	var recalculatePayStub = "{/literal}{t}Are you sure you want to recalculate the pay stub of this employee?{/t}{literal}";

	if ( action == 'recalculate employee' ) {
		confirm_result = true;
	} else if( action == 'recalculate company' ) {
		confirm_result = confirmSubmit(recalculateCompany);
	} else if( action == 'recalculate pay stub' ) {
		confirm_result = confirmSubmit(recalculatePayStub);
	} else {
		confirm_result = true;
	}

	return confirm_result;
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span> </div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form name="timesheet" method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				{if ( $permission->Check('punch','view') OR $permission->Check('punch','view_child') )
					OR ( $permission->Check('punch','add') AND ( $permission->Check('punch','edit') OR $permission->Check('punch','edit_own') OR $permission->Check('punch','edit_child') ) )
					OR ( $permission->Check('absence','add') AND ( $permission->Check('absence','edit') OR $permission->Check('absence','edit_child') OR $permission->Check('absence','edit_own') ) )}
				<tr class="tblHeader">
					<td colspan="8">
						{if $permission->Check('punch','view') OR $permission->Check('punch','view_child')}
							<span style="float:left;">
								&nbsp;
								{if count($group_options) > 2}
								{t}Group:{/t}
								<select name="filter_data[group_ids]" id="filter_branch" onChange="this.form.submit()">
									{html_options options=$group_options selected=$filter_data.group_ids}
								</select>
								{/if}

								{if count($branch_options) > 1}
								{t}Branch:{/t}
								<select name="filter_data[branch_ids]" id="filter_branch" onChange="this.form.submit()">
									{html_options options=$branch_options selected=$filter_data.branch_ids}
								</select>
								{/if}

								{if count($department_options) > 1}
								{t}Dept:{/t}
								<select name="filter_data[department_ids]" id="filter_department" onChange="this.form.submit()">
									{html_options options=$department_options selected=$filter_data.department_ids}
								</select>
								{/if}
								<span style="white-space: nowrap;">
									{t}Employee:{/t}
									<a href="javascript:resetAction();navSelectBox('filter_user', 'prev');document.timesheet.submit()"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
									<select name="filter_data[user_id]" id="filter_user" onChange="this.form.submit()">
										{html_options options=$user_options selected=$filter_data.user_id}
									</select>
									<a href="javascript:resetAction();navSelectBox('filter_user', 'next');document.timesheet.submit()"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
								</span>
							</span>
						{/if}

						{if ( $permission->Check('punch','add') AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))
								OR ( $permission->Check('absence','add') AND ( $permission->Check('absence','edit') OR ($permission->Check('absence','edit_child') AND $is_child === TRUE) OR ($permission->Check('absence','edit_own') AND $is_owner === TRUE )))}
						<span style="float: right">
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							{t}Add:{/t}
							{if ( $permission->Check('punch','add') AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE )))}
							<input type="BUTTON" class="button" name="action" value="{t}Punch{/t}" onClick="editPunch('','',{$filter_data.user_id},{$filter_data.date})">
							{/if}
							{if ( $permission->Check('absence','add') AND ( $permission->Check('absence','edit') OR ($permission->Check('absence','edit_child') AND $is_child === TRUE) OR ($permission->Check('absence','edit_own') AND $is_owner === TRUE )))}
							<input type="BUTTON" class="button" name="action" value="{t}Absence{/t}" onClick="editAbsence('',{$filter_data.user_id},{$filter_data.date})">
							{/if}
						</span>
						{/if}
					</td>
				</tr>
				{/if}

				<tr class="tblHeader">
					<td colspan="8">
						{t}Date:{/t}
						<a href="{urlbuilder script="" values="prev_pp=1" merge="TRUE"}" onClick="resetAction();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first_sm.gif"></a>
						<a href="{urlbuilder script="" values="prev_week=1" merge="TRUE"}" onClick="resetAction();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>

						<input type="text" size="15" id="filter_date" name="filter_data[date]" value="{getdate type="DATE" epoch=$filter_data.date}" onChange="resetAction();this.form.submit()">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_filter_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('filter_date', 'cal_filter_date', false);">
						<a href="{urlbuilder script="" values="next_week=1" merge="TRUE"}" onClick="resetAction();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
						<a href="{urlbuilder script="" values="next_pp=1" merge="TRUE"}" onClick="resetAction();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last_sm.gif"></a>
					</td>
				</tr>

				{if $pay_period_is_locked == TRUE}
					<tr class="tblDataError">
						<td colspan="8">
							<b>{t}NOTICE:{/t}</b> {t}This pay period is currently{/t} {if $pay_period_status_id == 20}{t}closed{/t}{else}{t}locked{/t}{/if}, {t}modifications are not permitted.{/t}
						</td>
					</tr>
				{elseif $pay_period_status_id == 30 }
					<tr class="tblDataWarning">
						<td colspan="8">
							<b>{t}NOTICE:{/t}</b> {t}This pay period is currently in the post adjustment state.{/t}
						</td>
					</tr>
				{/if}

				<tr class="tblHeader">
				{foreach from=$calendar_array item=calendar name=calendar}
					{if $smarty.foreach.calendar.first}
						{assign var="begin_week_epoch" value=$calendar.epoch}
						{assign var="filter_user_id" value=$filter_data.user_id}
						<td>
							<a href="{urlbuilder script="../schedule/ViewSchedule.php" values="filter_data[include_user_ids][]=$filter_user_id,filter_data[start_date]=$begin_week_epoch" merge="FALSE"}"><img src="{$IMAGES_URL}viewSched.gif" alt="{t}View Schedule{/t}"></a>&nbsp;&nbsp;
							<a href="{$BASE_URL}/report/TimesheetDetail.php?action:display_report=1&filter_data[print_timesheet]=1&filter_data[user_id]={$filter_data.user_id}&filter_data[pay_period_ids]={$pay_period_id}"><img src="{$IMAGES_URL}printer.gif" alt="{t}Print Timesheet{/t}"></a>&nbsp;&nbsp;
							<a href="{$BASE_URL}/report/TimesheetDetail.php?action:display_report=1&filter_data[print_timesheet]=2&filter_data[user_id]={$filter_data.user_id}&filter_data[pay_period_ids]={$pay_period_id}"><img src="{$IMAGES_URL}printer_detail.gif" alt="{t}Print Detailed Timesheet{/t}"></a>
						</td>
					{/if}
					<td width="12%" id="cursor-hand" {if $calendar.epoch == $filter_data.date}bgcolor="#33CCFF"{/if} onClick="changeDate('{getdate type="DATE" epoch=$calendar.epoch}')">
						{$calendar.day_of_week}
						<br>
						{$calendar.month_short_name} {$calendar.day_of_month}
						{if isset($holidays[$calendar.epoch])}
							<br>
							({$holidays[$calendar.epoch]})
						{/if}
					</td>
				{/foreach}
				</tr>

				{foreach from=$rows key=row_num item=row name=row}
					{if $row.background == 0}
						{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					{/if}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$row.status}
						</td>
						{foreach from=$row.data key=epoch item=day name=day}
							<td {if $pay_period_locked_rows[$epoch] == FALSE
									AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))}class="cellHL" id="cursor-hand"{/if}
								{if $pay_period_locked_rows[$epoch] == FALSE
									AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))
									AND $day.time_stamp == ''}onClick="editPunch('','{$day.punch_control_id}',{$filter_data.user_id},{$epoch},{$row.status_id});"{/if} nowrap>
								<table align="center" border="0" width="100%">
									<tr>
										<td width="25%" align="left">
											{if isset($punch_exceptions[$day.id]) OR isset($punch_control_exceptions[$day.punch_control_id])}
												{if isset($punch_exceptions[$day.id])}
													{assign var="exception_arr" value=$punch_exceptions[$day.id]}
												{else}
													{assign var="exception_arr" value=$punch_control_exceptions[$day.punch_control_id]}
												{/if}
												{foreach from=$exception_arr key=exception_id item=exception_data name=punch_exception}
													{if $smarty.foreach.punch_exception.first}
														<span style="float: left">
													{/if}
													<font color="{$exception_data.color}">
														<b>{$exception_data.exception_policy_type_id}</b>
													</font>
													{if $smarty.foreach.punch_exception.last}
														</span>
													{/if}
												{/foreach}
											{/if}
										</td>
										<td width="50%" align="center" nowrap>
											{if $day.time_stamp != ''}
												{if $day.has_note == TRUE}*{/if}{if $pay_period_locked_rows[$epoch] == FALSE AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))}<a href="javascript:editPunch({$day.id})">{getdate type="TIME" epoch=$day.time_stamp}</a>{else}{getdate type="TIME" epoch=$day.time_stamp}{/if}
											{else}
												<br>
											{/if}
										</td>
										<td width="25%" align="right">
											{$day.type_code}
										</td>
									</tr>
								</table>
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_break_total_rows item=date_break_total_row name=date_break_total_row}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$date_break_total_row.name}
						</td>
						{foreach from=$date_break_total_row.data key=date_break_total_epoch item=date_break_total_day name=date_break_total_day}
							<td>
								{gettimeunit value=$date_break_total_day.total_time} {if $date_break_total_day.total_breaks > 1}({$date_break_total_day.total_breaks}){/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_break_policy_total_rows item=date_break_policy_total_row name=date_break_policy_total_row}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$date_break_policy_total_row.name}
						</td>
						{foreach from=$date_break_policy_total_row.data key=date_break_policy_total_epoch item=date_break_policy_total_day name=date_break_policy_total_day}
							<td>
								{if $date_break_policy_total_day.total_time < 0}<font color="red">{/if}{gettimeunit value=$date_break_policy_total_day.total_time_display}{if $date_break_policy_total_day.total_time < 0}</font>{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_meal_policy_total_rows item=date_meal_total_row name=date_meal_total_row}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$date_meal_total_row.name}
						</td>
						{foreach from=$date_meal_total_row.data key=date_meal_total_epoch item=date_meal_total_day name=date_meal_total_day}
							<td>
								{if $date_meal_total_day.total_time < 0}<font color="red">{/if}{gettimeunit value=$date_meal_total_day.total_time_display}{if $date_meal_total_day.total_time < 0}</font>{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{if isset($date_exception_total_rows)}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						{foreach from=$date_exception_total_rows item=date_exception_total_row name=date_exception_total_row}

							{if $smarty.foreach.date_exception_total_row.first}
								<td class="tblHeader" style="font-weight: bold; text-align: right">
									{t}Exceptions{/t}
								</td>
							{/if}
								<td>
									<b>
									{foreach from=$date_exception_total_row item=date_exception_total_day name=date_exception_total_day}
										<font color="{$date_exception_total_day.color}">
											{$date_exception_total_day.exception_policy_type_id}
										</font>
									{/foreach}
									</b>
								</td>
						{/foreach}
					</tr>
				{/if}

				{if isset($date_request_total_rows)}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						{foreach from=$date_request_total_rows key=request_epoch item=date_request_total_row name=date_request_total_row}

							{if $smarty.foreach.date_request_total_row.first}
								<td class="tblHeader" style="font-weight: bold; text-align: right">
									{t}Pending Requests{/t}
								</td>
							{/if}
								<td>
									{if isset($date_request_total_row)}
										{assign var="filter_user_id" value=$filter_data.user_id}
										<a href="{urlbuilder script="../request/UserRequestList.php" values="filter_user_id=$filter_user_id,filter_start_date=$request_epoch,filter_end_date=$request_epoch" merge=FALSE}">
											{t}Yes{/t}
										</a>
									{/if}
								</td>
						{/foreach}
					</tr>
				{/if}

				{foreach from=$date_total_rows item=date_total_row name=date_total_row}
					{if $smarty.foreach.date_total_row.first}
						<tr class="tblHeader">
							<td colspan="8">
								{t}Accumulated Time{/t}
							</td>
						</tr>
					{/if}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$date_total_row.name}
						</td>
						{foreach from=$date_total_row.data key=date_total_epoch item=date_total_day name=date_total_day}
							<td {if $date_total_day.type_id == 10 AND $pay_period_locked_rows[$date_total_epoch] == FALSE
								AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))}class="cellHL"{/if}>
								{if $date_total_row.type_and_policy_id == 100}
									{if $pay_period_locked_rows[$date_total_epoch] == FALSE
										AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))}
										<a href="javascript:hourList('','{$filter_data.user_id}','{$date_total_epoch}')">
									{/if}
									{if $date_total_day.override == TRUE}*{/if}{gettimeunit value=$date_total_day.total_time|default:0}
									{if $pay_period_locked_rows[$date_total_epoch] == FALSE
										AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))}</a>{/if}
								{else}
									{gettimeunit value=$date_total_day.total_time}
								{/if}

							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_branch_total_rows item=date_branch_total_row name=date_branch_total_row}
					{if $smarty.foreach.date_branch_total_row.first}
						<tr class="tblHeader">
							<td colspan="100">
								{t}Branch{/t}
							</td>
						</tr>
					{/if}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$date_branch_total_row.name}
						</td>
						{foreach from=$date_branch_total_row.data item=date_branch_total_day name=date_branch_total_day}
							<td>
								{gettimeunit value=$date_branch_total_day.total_time}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_department_total_rows item=date_department_total_row name=date_department_total_row}
					{if $smarty.foreach.date_department_total_row.first}
						<tr class="tblHeader">
							<td colspan="100">
								{t}Department{/t}
							</td>
						</tr>
					{/if}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$date_department_total_row.name}
						</td>
						{foreach from=$date_department_total_row.data item=date_department_total_day name=date_department_total_day}
							<td>
								{gettimeunit value=$date_department_total_day.total_time}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_job_total_rows item=date_job_total_row name=date_job_total_row}
					{if $smarty.foreach.date_job_total_row.first}
						<tr class="tblHeader">
							<td colspan="100">
								{t}Job{/t}
							</td>
						</tr>
					{/if}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{if $permission->Check('job','edit') AND $date_job_total_row.id > 0}
								<a href="../job/EditJob.php?id={$date_job_total_row.id}">{$date_job_total_row.name}</a>
							{else}
								{$date_job_total_row.name}
							{/if}
						</td>
						{foreach from=$date_job_total_row.data item=date_job_total_day name=date_job_total_day}
							<td>
								{gettimeunit value=$date_job_total_day.total_time}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_job_item_total_rows item=date_job_item_total_row name=date_job_item_total_row}
					{if $smarty.foreach.date_job_item_total_row.first}
						<tr class="tblHeader">
							<td colspan="100">
								{t}Task{/t}
							</td>
						</tr>
					{/if}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{if $permission->Check('job_item','edit') AND $date_job_item_total_row.id > 0}
								<a href="../job_item/EditJobItem.php?id={$date_job_item_total_row.id}">{$date_job_item_total_row.name}</a>
							{else}
								{$date_job_item_total_row.name}
							{/if}
						</td>
						{foreach from=$date_job_item_total_row.data item=date_job_item_total_day name=date_job_total_item_day}
							<td>
								{gettimeunit value=$date_job_item_total_day.total_time}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_premium_total_rows item=date_premium_total_row name=date_premium_total_row}
					{if $smarty.foreach.date_premium_total_row.first}
						<tr class="tblHeader">
							<td colspan="100">
								{t}Premium{/t}
							</td>
						</tr>
					{/if}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$date_premium_total_row.name}
						</td>
						{foreach from=$date_premium_total_row.data key=date_premium_total_epoch item=date_premium_total_day name=date_premium_total_day}
							<td>
								{gettimeunit value=$date_premium_total_day.total_time}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				{foreach from=$date_absence_total_rows item=date_absence_total_row name=date_absence_total_row}
					{if $smarty.foreach.date_absence_total_row.first}
						<tr class="tblHeader">
							<td colspan="100">
								{t}Absence{/t}
							</td>
						</tr>
					{/if}
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="tblHeader" style="font-weight: bold; text-align: right">
							{$date_absence_total_row.name}
						</td>
						{foreach from=$date_absence_total_row.data key=date_absence_total_epoch item=date_absence_total_day name=date_absence_total_day}
							<td {if $pay_period_locked_rows[$date_absence_total_epoch] == FALSE AND ( $permission->Check('absence','edit') OR ($permission->Check('absence','edit_child') AND $is_child === TRUE) OR ($permission->Check('absence','edit_own') AND $is_owner === TRUE ))}class="cellHL" id="cursor-hand"{/if} {if $date_absence_total_day.total_time == ''} onClick="editAbsence('','{$filter_data.user_id}', '{$date_absence_total_epoch}')"{/if}>
								{if $pay_period_locked_rows[$date_absence_total_epoch] == FALSE AND ( $permission->Check('absence','edit') OR ($permission->Check('absence','edit_child') AND $is_child === TRUE) OR ($permission->Check('absence','edit_own') AND $is_owner === TRUE ))}
									<a href="javascript: editAbsence({$date_absence_total_day.id});">
								{/if}

								{if $date_absence_total_day.override == TRUE}*{/if}{gettimeunit value=$date_absence_total_day.total_time}

								{if $pay_period_locked_rows[$date_absence_total_epoch] == FALSE AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) OR ($permission->Check('punch','edit_own') AND $is_owner === TRUE ))}
								</a>
								{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}

				<tr class="{if $is_assigned_pay_period_schedule == TRUE}tblHeader{else}tblDataError{/if}">
					<td colspan="8">
						{if $is_assigned_pay_period_schedule == TRUE}
							{t}Pay Period:{/t}
							{if $pay_period_start_date != ''}
								{getdate type="DATE" epoch=$pay_period_start_date} {t}to{/t} {getdate type="DATE" epoch=$pay_period_end_date}
							{else}
								{t}NONE{/t}
							{/if}
						{else}
							<b>{t}Employee is not assigned to a Pay Period Schedule.{/t}</b>
						{/if}
					</td>
				</tr>

				<tr valign="top">
					<td colspan="2">

						{if $permission->Check('punch','verify_time_sheet') AND $pay_period_verify_type_id != 10}
							{if $time_sheet_verify.previous_pay_period_verification_display == TRUE}
							<table class="tblList">
								<tr class="tblDataWarning">
									<td colspan="3">
										<b>{t}Previous pay period is not verified!{/t}</b>
									</td>
								</tr>
							</table>
							{/if}

							{if $pay_period_end_date != ''}
							<table class="tblList">
								<tr class="tblHeader">
									<td>
										{t}Verification{/t}
									</td>
								</tr>
								<tr class="tblDataWhiteNH">
									<td {if $time_sheet_verify.verification_box_color != ''}bgcolor="{$time_sheet_verify.verification_box_color}"{/if}>
										{$time_sheet_verify.verification_status_display}
									</td>
								</tr>

								{if $time_sheet_verify.display_verify_button == TRUE}
									<tr class="tblDataWhiteNH">
										<td colspan="2" {if $time_sheet_verify.verification_box_color != ''}bgcolor="{$time_sheet_verify.verification_box_color}"{/if}>
											<input type="SUBMIT" class="button" name="action:verify" value="{t}Verify{/t}" onClick="return confirmSubmit('{t}By pressing OK, I hereby certify that this timesheet for the pay period of{/t} {getdate type="DATE" epoch=$pay_period_start_date} {t}to{/t} {getdate type="DATE" epoch=$pay_period_end_date} {t}is accurate and correct.{/t}');">
										</td>
									</tr>
								{/if}
							</table>
							{/if}
						{/if}

						<table class="tblList">
							{foreach from=$exception_legend item=exception_legend_row name=exception_legend}
								{if $smarty.foreach.exception_legend.first}
									<tr class="tblHeader">
										<td colspan="2">
											{t}Exception Legend{/t}
										</td>
									</tr>

									<tr class="tblHeader">
										<td>
											{t}Code{/t}
										</td>
										<td>
											{t}Exception{/t}
										</td>
									</tr>
								{/if}
								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}">
									<td>
										<font color="{$exception_legend_row.color}">
											<b>{$exception_legend_row.exception_policy_type_id}</b>
										</font>
									</td>
									<td>
										{$exception_legend_row.name}
									</td>
								</tr>

							{/foreach}
						</table>
					</td>
					<td colspan="3">

						<table class="tblList">
							<tr class="tblHeader">
								<td colspan="2">
									{t}Paid Time{/t}
								</td>
							</tr>

							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr class="{$row_class}" nowrap>
								<td>
									{t}Worked Time{/t}
								</td>
								<td>
									{gettimeunit value=$pay_period_worked_total_time default=TRUE}
								</td>
							</tr>
							{if $pay_period_paid_absence_total_time > 0}
							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr class="{$row_class}" nowrap>
								<td>
									{t}Paid Absences{/t}
								</td>
								<td>
									{gettimeunit value=$pay_period_paid_absence_total_time}
								</td>
							</tr>
							{/if}
							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr class="{$row_class}" style="font-weight: bold;" nowrap>
								<td>
									{t}Total Time{/t}
								</td>
								<td width="75">
									{gettimeunit value=$pay_period_worked_total_time+$pay_period_paid_absence_total_time}
								</td>
							</tr>
						</table>

						{if $pay_period_dock_absence_total_time > 0 }
							<table class="tblList">
								<tr class="tblHeader">
									<td colspan="2">
										{t}Docked Time{/t}
									</td>
								</tr>

								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}" style="font-weight: bold;" nowrap>
									<td>
										{t}Docked Absences{/t}
									</td>
									<td>
										{gettimeunit value=$pay_period_dock_absence_total_time}
									</td>
								</tr>
							</table>
						{/if}
					</td>
					<td colspan="3">
						<table class="tblList">
							<tr class="tblHeader">
								<td colspan="2">
									{t}Accumulated Time{/t}
								</td>
							</tr>

							{foreach from=$pay_period_total_rows item=pay_period_total_row name=pay_period_total_row}
								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}">
									<td>
										{$pay_period_total_row.name}
									</td>
									<td>
										{gettimeunit value=$pay_period_total_row.total_time}
									</td>
								</tr>
							{/foreach}

							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr class="{$row_class}" style="font-weight: bold;">
								<td>
									{t}Total Time{/t}
								</td>
								<td>
									{gettimeunit value=$pay_period_worked_total_time+$pay_period_paid_absence_total_time}
								</td>
							</tr>

							{if $pay_period_is_locked != TRUE AND ( $permission->Check('punch','edit') OR ($permission->Check('punch','edit_child') AND $is_child === TRUE) )}
							<tr>
								<td colspan="2" align="center">
									<select name="action_option" id="select_action">
										{html_options options=$action_options}
									</select>
									<input type="SUBMIT" class="button" name="action:submit" value="{t}Submit{/t}" onClick="return confirmAction();">
								</td>
							</tr>
							{/if}
						</table>

					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
