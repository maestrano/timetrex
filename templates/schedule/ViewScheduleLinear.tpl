{include file="sm_header.tpl"}
<table class="tblList" id="schedule_table" border="0">
<form method="get" name="schedule" action="{$smarty.server.SCRIPT_NAME}">
		<tr>{* This ensures that all hour columns are divided in to 4 columns *}
			<td colspan="2"></td>
			{foreach from=$header_hours item=header_hour}
				<td width="{$column_widths}%"></td>
				<td width="{$column_widths}%"></td>
				<td width="{$column_widths}%"></td>
				<td width="{$column_widths}%"></td>
			{/foreach}
		</tr>

		{foreach from=$calendar_array item=calendar name=calendar}
			<tr class="tblHeader">
				<td colspan="{$total_span_columns}" {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}id="cursor-hand" onClick="TIMETREX.schedule.editSchedule('','',{$calendar.epoch})"{/if}>
					{getdate type="DATE" epoch=$calendar.epoch}

					{if isset($holidays[$calendar.epoch])}
						<br>
						({$holidays[$calendar.epoch]})
					{/if}
				</td>
			</tr>

			{foreach from=$schedule_shifts[$calendar.date_stamp] key=branch item=branches name=branches}
				{if $branch != '--'}
				<tr class="tblHeader">
					<td colspan="{$total_span_columns}" {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}id="cursor-hand" onClick="TIMETREX.schedule.editSchedule('','',{$calendar.epoch})"{/if}>
						{$branch}
					</td>
				</tr>
				{/if}
				{foreach from=$branches key=department item=departments}
						{if $department != '--'}
						<tr class="tblHeader">
							<td colspan="{$total_span_columns}" id="cursor-hand" {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}onClick="TIMETREX.schedule.editSchedule('','',{$calendar.epoch})"{/if}>
								{$department}
							</td>
						</tr>
						{/if}

						<tr class="tblHeader" id="cursor-hand" {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}onClick="TIMETREX.schedule.editSchedule('','',{$calendar.epoch})"{/if}>
							<td>
								{t}Employee{/t}
							</td>
							{foreach from=$header_hours item=header_hour}
								<td class="tblHeader" colspan="4" nowrap>
									{getdate type="TIME" epoch=$header_hour.hour default=TRUE}
								</td>
							{/foreach}
						</tr>

						{foreach from=$schedule_shifts[$calendar.date_stamp][$branch][$department] item=shifts name=shifts}
							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr>
								{foreach from=$shifts item=shift name=shift}
									{if $smarty.foreach.shift.first}
									{*<td class="{$row_class}" id="cursor-hand" {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}onClick="TIMETREX.schedule.editSchedule('',{$shift.user_id},{$calendar.epoch})"{/if} nowrap>*}
									<td class="{if $shift.user_id == 0 }tblDataWarning{else}{$row_class}{/if}" {if $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shift.is_child === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shift.is_owner === TRUE ) }id="cursor-hand" onClick="TIMETREX.schedule.editSchedule('',{$shift.user_id},{$calendar.epoch})"{/if} nowrap>
										<b>{$shift.user_full_name}</b>
									</td>
									{/if}
									{if $shift.off_duty > 0}<td colspan="{$shift.off_duty}"></td>{/if}
									{if $shift.on_duty > 0}
									<td class="{if $shift.user_id == 0 }tblDataWarning{else}{$row_class}{/if}" colspan="{$shift.on_duty}" {if $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shift.is_child === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shift.is_owner === TRUE ) }id="cursor-hand" onClick="TIMETREX.schedule.editSchedule('{$shift.id}',{$shift.user_id},{$calendar.epoch},{$shift.status_id},{$shift.start_time},{$shift.end_time},'{$shift.schedule_policy_id}','{$shift.absence_policy_id}')"{/if} nowrap>
										{if $shift.note != ''}*{/if}{if isset($shift.id) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shift.is_child === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shift.is_owner === TRUE ) )}<a href="javascript:TIMETREX.schedule.editSchedule({$shift.id},{$shift.user_id},{$calendar.epoch})">{/if}{if $shift.status_id == 20}<font color="red">{$shift.absence_policy|default:"N/A"}</font>{else}{if $shift.span_day == TRUE}{if $shift.span_day_split == TRUE}{getdate type="TIME" epoch=$shift.start_time default=TRUE}-{else}-{getdate type="TIME" epoch=$shift.end_time default=TRUE}{/if}{else}{getdate type="TIME" epoch=$shift.start_time default=TRUE}-{getdate type="TIME" epoch=$shift.end_time default=TRUE}{/if}{/if}
										{if isset($shift.id) AND ( $permission->Check('schedule','edit') OR ( $permission->Check('schedule','edit_child') AND $shift.is_child === TRUE ) OR ( $permission->Check('schedule','edit_own') AND $shift.is_owner === TRUE ) )}</a>{/if}
										{if !isset($shift.id)}{t}[R]{/t}{/if}
										<br>
									</td>
									{/if}
								{/foreach}
							</tr>
						{/foreach}
				{/foreach}
			{/foreach}

			<tr class="tblHeader">
				<td id="cursor-hand" colspan="{$total_span_columns}" {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}onClick="TIMETREX.schedule.editSchedule('','',{$calendar.epoch})"{/if} nowrap>
					{$schedule_shift_totals[$calendar.date_stamp].total_users|default:0} {t}Employees{/t} -
					{gettimeunit value=$schedule_shift_totals[$calendar.date_stamp].total_time|default:0}
				</td>
			</tr>

			<tr>
				<td bgcolor="#000000" colspan="{$total_span_columns}"></td>
			</td>
		{/foreach}
	</table>
</form>
<script language="JavaScript">changeHeight(document.getElementById('body'), parent.document.getElementById('schedule_layer'), 'screen_size' ); {if $do != ''}parent.location.hash = 'schedule';{/if}</script>
{*{include file="footer.tpl"}*}
</body>
</html>
