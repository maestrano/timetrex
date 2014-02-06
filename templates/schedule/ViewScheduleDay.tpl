{include file="sm_header.tpl"}
<table style="width:100%; background:#7a9bbd" id="schedule_table">
<form method="get" name="schedule" action="{$smarty.server.SCRIPT_NAME}">
		<tr class="tblHeader" height="1">
		{foreach from=$calendar_array item=calendar name=calendar}
			<td {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}id="cursor-hand" onClick="TIMETREX.schedule.editSchedule('','',{$calendar.epoch})"{/if} >
				{$calendar.day_of_week}, {$calendar.month_short_name} {$calendar.day_of_month}
				{if isset($holidays[$calendar.epoch])}
					<br>
					({$holidays[$calendar.epoch]})
				{/if}
			</td>
		{/foreach}
		</tr>

		<tr class="tblDataWhiteNH">
		{foreach from=$calendar_array item=calendar name=calendar}
			<td valign="top">
				<table width="100%">
				{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH" reset=true}
				{foreach from=$schedule_shifts[$calendar.date_stamp] key=branch item=branches name=branches}
					{if $branch != '--'}
					<tr class="tblHeader">
						<td>
							{$branch}
						</td>
					</tr>
					{/if}
					{foreach from=$branches key=department item=departments}
						{if $department != '--'}
						<tr class="tblHeader">
							<td>
								{$department}
							</td>
						</tr>
						{/if}
						{foreach from=$departments item=shifts}
							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr class="{if $shifts.user_id == 0 }tblDataWarning{else}{$row_class}{/if}">
								<td class="cellHL" {if $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts.is_child === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts.is_owner === TRUE ) }id="cursor-hand" onClick="TIMETREX.schedule.editSchedule('{$shifts.id}',{$shifts.user_id},{$calendar.epoch},{$shifts.status_id},{$shifts.start_time},{$shifts.end_time},'{$shifts.schedule_policy_id}','{$shifts.absence_policy_id}')"{/if} nowrap>
									{if $shifts.start_time}
										<b>{$shifts.user_full_name}</b><br>
										{if $shifts.note != ''}*{/if}{if isset($shifts.id) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts.is_child === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts.is_owner === TRUE ) )}<a href="javascript:TIMETREX.schedule.editSchedule({$shifts.id},{$shifts.user_id},{$calendar.epoch})">{/if}{if $shifts.status_id == 20}<font color="red">{$shifts.absence_policy|default:"N/A"}</font>{else}{getdate type="TIME" epoch=$shifts.start_time default=TRUE}-{getdate type="TIME" epoch=$shifts.end_time default=TRUE}{/if}
										{if isset($shifts.id) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shifts.is_child === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shifts.is_owner === TRUE ) )}</a>{/if}
										{if !isset($shifts.id)}{t}[R]{/t}{/if}
									{else}
										<br>
									{/if}
								</td>
							</tr>
						{/foreach}
					{/foreach}
				{/foreach}
				</table>
			</td>
		{/foreach}
		</tr>
		<tr class="tblHeader" height="1">
		{foreach from=$calendar_array item=calendar name=calendar}
			<td {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}onClick="TIMETREX.schedule.editSchedule('','',{$calendar.epoch})"{/if}  nowrap>
				{$schedule_shift_totals[$calendar.date_stamp].total_users|default:0} {t}Employees{/t} -
				{gettimeunit value=$schedule_shift_totals[$calendar.date_stamp].total_time|default:0}
			</td>
		{/foreach}
		</tr>
	</table>
</form>
{*<script language="JavaScript">changeHeight(document.getElementById('body'), parent.document.getElementById('day_schedule'), parent.scrollHeight );</script>*}
{*{include file="footer.tpl"}*}
</body>
</html>
