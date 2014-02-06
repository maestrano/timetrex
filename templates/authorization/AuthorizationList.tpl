{include file="header.tpl"}
<script	language=JavaScript>

{literal}
function viewRequest(requestID,level) {
	try {
		window.open('{/literal}{$BASE_URL}{literal}request/ViewRequest.php?id='+ encodeURI(requestID) +'&selected_level='+ encodeURI(level),"Request_"+ requestID,"toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
	} catch (e) {
		//DN
	}
}
function viewTimeSheetVerification(timesheet_verify_id,level) {
	try {
		//window.open('{/literal}{$BASE_URL}{literal}timesheet/ViewTimeSheetVerification.php?id='+ encodeURI(timesheet_verify_id) +'&selected_level='+ encodeURI(level),"TimeSheet_"+ timesheet_verify_ID,"toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
		window.open('{/literal}{$BASE_URL}{literal}timesheet/ViewTimeSheetVerification.php?id='+ encodeURI(timesheet_verify_id) +'&selected_level='+ encodeURI(level),"TimeSheet_"+ timesheet_verify_id,"toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
	} catch (e) {
		//DN
	}
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="5" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>

				{if $permission->Check('request','authorize')}
					{*
					//
					//Missed Punch: request_punch
					//
					*}
					{if is_array($hierarchy_levels.request_punch)}
						<tr class="tblHeader">
							<td colspan="5">
								{t}Pending Requests: Missed Punch{/t}
								[ {foreach from=$hierarchy_levels.request_punch key=request_level_display item=request_level name=request_levels}
									{if $selected_level_arr.request_punch == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_punch]=$request_level_display" merge="FALSE"}">{t}Level{/t} {$request_level_display}</a>{if $selected_level_arr.request_punch == $request_level}</span>{/if}
									{if !$smarty.foreach.request_levels.last}
										|
									{/if}
								{/foreach} ]
							</td>
						</tr>
						{foreach from=$requests.request_punch item=request_punch name=request_punch}
							{if $smarty.foreach.request_punch.first}
							<tr class="tblHeader">
								<td>
									{capture assign=label}{t}Employee{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Request Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Submitted Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{t}Functions{/t}
								</td>
							</tr>
							{/if}
							{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
							<tr class="{$row_class}">
								<td>
									{$request_punch.user_full_name}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_punch.date_stamp}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_punch.created_date}
								</td>
								<td>
									{assign var="request_punch_id" value=$request_punch.id}
									{assign var="selected_level" value=$selected_levels.request_punch}
									<a href="javascript:viewRequest({$request_punch_id},{$selected_level|default:0})">{t}View{/t}</a>
								</td>
							</tr>
						{foreachelse}
							<tr class="tblDataWhite">
								<td colspan="5">
									{t}0 Requests found.{/t}
								</td>
							</tr>
						{/foreach}

						<tr>
							<td colspan="6">
								<br>
							</td>
						</tr>
					{/if}


					{*
					//
					//Missed Punch: request_punch_adjust
					//
					*}
					{if is_array($hierarchy_levels.request_punch_adjust)}
						<tr class="tblHeader">
							<td colspan="5">
								{t}Pending Requests: Punch Adjustment{/t}

								[ {foreach from=$hierarchy_levels.request_punch_adjust key=request_level_display item=request_level name=request_levels}
									{if $selected_level_arr.request_punch_adjust == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_punch_adjust]=$request_level_display" merge="FALSE"}">{t}Level{/t} {$request_level_display}</a>{if $selected_level_arr.request_punch_adjust == $request_level}</span>{/if}
									{if !$smarty.foreach.request_levels.last}
										|
									{/if}
								{/foreach} ]

							</td>
						</tr>
						{foreach from=$requests.request_punch_adjust item=request_punch_adjust name=request_punch_adjust}
							{if $smarty.foreach.request_punch_adjust.first}
							<tr class="tblHeader">
								<td>
									{capture assign=label}{t}Employee{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Request Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Submitted Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{t}Functions{/t}
								</td>
							</tr>
							{/if}
							{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
							<tr class="{$row_class}">
								<td>
									{$request_punch_adjust.user_full_name}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_punch_adjust.date_stamp}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_punch_adjust.created_date}
								</td>
								<td>
									{assign var="request_punch_adjust_id" value=$request_punch_adjust.id}
									{assign var="selected_level" value=$selected_levels.request_punch_adjust}
									<a href="javascript:viewRequest({$request_punch_adjust_id},{$selected_level|default:0})">{t}View{/t}</a>
								</td>
							</tr>
						{foreachelse}
							<tr class="tblDataWhite">
								<td colspan="5">
									{t}0 Requests found.{/t}
								</td>
							</tr>
						{/foreach}

						<tr>
							<td colspan="6">
								<br>
							</td>
						</tr>
					{/if}

					{*
					//
					//Missed Punch: request_absence
					//
					*}
					{if is_array($hierarchy_levels.request_absence)}
						<tr class="tblHeader">
							<td colspan="5">
								{t}Pending Requests: Absence{/t}
								[ {foreach from=$hierarchy_levels.request_absence key=request_level_display item=request_level name=request_levels}
									{if $selected_level_arr.request_absence == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_absence]=$request_level_display" merge="FALSE"}">{t}Level{/t} {$request_level_display}</a>{if $selected_level_arr.request_absence == $request_level}</span>{/if}
									{if !$smarty.foreach.request_levels.last}
										|
									{/if}
								{/foreach} ]
							</td>
						</tr>
						{foreach from=$requests.request_absence item=request_absence name=request_absence}
							{if $smarty.foreach.request_absence.first}
							<tr class="tblHeader">
								<td>
									{capture assign=label}{t}Employee{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Request Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Submitted Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{t}Functions{/t}
								</td>
							</tr>
							{/if}
							{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
							<tr class="{$row_class}">
								<td>
									{$request_absence.user_full_name}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_absence.date_stamp}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_absence.created_date}
								</td>
								<td>
									{assign var="request_absence_id" value=$request_absence.id}
									{assign var="selected_level" value=$selected_levels.request_absence}
									<a href="javascript:viewRequest({$request_absence_id},{$selected_level|default:0})">{t}View{/t}</a>
								</td>
							</tr>
						{foreachelse}
							<tr class="tblDataWhite">
								<td colspan="5">
									{t}0 Requests found.{/t}
								</td>
							</tr>
						{/foreach}

						<tr>
							<td colspan="6">
								<br>
							</td>
						</tr>
					{/if}

					{*
					//
					//Missed Punch: request_schedule
					//
					*}
					{if is_array($hierarchy_levels.request_schedule)}
						<tr class="tblHeader">
							<td colspan="5">
								{t}Pending Requests: Schedule Adjustment{/t}
								[ {foreach from=$hierarchy_levels.request_schedule key=request_level_display item=request_level name=request_levels}
									{if $selected_level_arr.request_schedule == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_schedule]=$request_level_display" merge="FALSE"}">{t}Level{/t} {$request_level_display}</a>{if $selected_level_arr.request_schedule == $request_level}</span>{/if}
									{if !$smarty.foreach.request_levels.last}
										|
									{/if}
								{/foreach} ]
							</td>
						</tr>

						{foreach from=$requests.request_schedule item=request_schedule name=request_schedule}
							{if $smarty.foreach.request_schedule.first}
							<tr class="tblHeader">
								<td>
									{capture assign=label}{t}Employee{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Request Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Submitted Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{t}Functions{/t}
								</td>
							</tr>
							{/if}
							{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
							<tr class="{$row_class}">
								<td>
									{$request_schedule.user_full_name}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_schedule.date_stamp}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_schedule.created_date}
								</td>
								<td>
									{assign var="request_schedule_id" value=$request_schedule.id}
									{assign var="selected_level" value=$selected_levels.request_schedule}
									<a href="javascript:viewRequest({$request_schedule_id},{$selected_level|default:0})">{t}View{/t}</a>
								</td>
							</tr>
						{foreachelse}
							<tr class="tblDataWhite">
								<td colspan="5">
									{t}0 Requests found.{/t}
								</td>
							</tr>
						{/foreach}

						<tr>
							<td colspan="6">
								<br>
							</td>
						</tr>
					{/if}

					{*
					//
					//Missed Punch: request_other
					//
					*}
					{if is_array($hierarchy_levels.request_other)}
						<tr class="tblHeader">
							<td colspan="5">
								{t}Pending Requests: Other{/t}
								[ {foreach from=$hierarchy_levels.request_other key=request_level_display item=request_level name=request_levels}
									{if $selected_level_arr.request_other == $request_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[request_other]=$request_level_display" merge="FALSE"}">{t}Level{/t} {$request_level_display}</a>{if $selected_level_arr.request_other == $request_level}</span>{/if}
									{if !$smarty.foreach.request_levels.last}
										|
									{/if}
								{/foreach} ]
							</td>
						</tr>

						{foreach from=$requests.request_other item=request_other name=request_other}
							{if $smarty.foreach.request_other.first}
							<tr class="tblHeader">
								<td>
									{capture assign=label}{t}Employee{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Request Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="date_stamp" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{capture assign=label}{t}Submitted Date{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="created_date" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{t}Functions{/t}
								</td>
							</tr>
							{/if}
							{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
							<tr class="{$row_class}">
								<td>
									{$request_other.user_full_name}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_other.date_stamp}
								</td>
								<td>
									{getdate type="DATE" epoch=$request_other.created_date}
								</td>
								<td>
									{assign var="request_other_id" value=$request_other.id}
									{assign var="selected_level" value=$selected_levels.request_other}
									<a href="javascript:viewRequest({$request_other_id},{$selected_level|default:0})">{t}View{/t}</a>
								</td>
							</tr>
						{foreachelse}
							<tr class="tblDataWhite">
								<td colspan="5">
									{t}0 Requests found.{/t}
								</td>
							</tr>
						{/foreach}

						<tr>
							<td colspan="6">
								<br>
							</td>
						</tr>
					{/if}

				{/if}


				{if $permission->Check('punch','authorize')}
					{if is_array($hierarchy_levels.timesheet)}
						<tr class="tblHeader">
							<td colspan="5">
								{t}Pending TimeSheets{/t}
								[ {foreach from=$hierarchy_levels.timesheet key=timesheet_level_display item=timesheet_level name=timesheet_levels}
									{if $selected_level_arr.timesheet == $timesheet_level}<span style="background-color:#33CCFF">{/if}<a href="{urlbuilder script="AuthorizationList.php" values="selected_levels[timesheet]=$timesheet_level_display" merge="FALSE"}">{t}Level{/t} {$timesheet_level_display}</a>{if $selected_level_arr.timesheet == $timesheet_level}</span>{/if}
									{if !$smarty.foreach.timesheet_levels.last}
										|
									{/if}
								{/foreach} ]
							</td>
						</tr>
						{foreach from=$timesheets item=timesheet name=timesheets}
							{if $smarty.foreach.timesheets.first}
							<tr class="tblHeader">
								<td>
									{capture assign=label}{t}Employee{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="user_id" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td colspan="2">
									{capture assign=label}{t}Pay Period{/t}{/capture}
									{include file="column_sort.tpl" label=$label sort_column="start_date" current_column="$sort_column" current_order="$sort_order"}
								</td>
								<td>
									{t}Functions{/t}
								</td>
							</tr>
							{/if}
							{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
							<tr class="{$row_class}">
								<td>
									{$timesheet.user_full_name}
								</td>
								<td colspan="2">
									{getdate type="DATE" epoch=$timesheet.pay_period_start_date} - {getdate type="DATE" epoch=$timesheet.pay_period_end_date}
								</td>
								<td>
									{assign var="timesheet_id" value=$timesheet.id}
									{assign var="selected_level" value=$selected_levels.timesheet}
									<a href="javascript:viewTimeSheetVerification({$timesheet_id},{$selected_level|default:0})">{t}View{/t}</a>
								</td>
							</tr>
						{foreachelse}
							<tr class="tblDataWhite">
								<td colspan="5">
									{t}0 TimeSheets found.{/t}
								</td>
							</tr>
						{/foreach}
					{/if}
				{/if}

				{if !is_array($hierarchy_levels.request_punch)
						AND !is_array($hierarchy_levels.request_punch_adjust)
						AND !is_array($hierarchy_levels.request_absence)
						AND !is_array($hierarchy_levels.request_schedule)
						AND !is_array($hierarchy_levels.request_other)
						AND !is_array($hierarchy_levels.timesheet)}
					<tr class="tblDataWhite">
						<td colspan="5">
							{t}No hierarchies are defined, therefore there are no authorizations pending.{/t}
						</td>
					</tr>
				{/if}

				<tr>
					<td class="tblPagingLeft" colspan="5" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
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