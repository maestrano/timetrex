{include file="sm_header.tpl"}
<script	language=JavaScript>
{literal}
function showScheduleDay(epoch) {
	document.getElementById('filter_start_date').value=epoch;
	document.getElementById('day_schedule').style.width='100%';
	document.schedule.submit();
}

{/literal}
</script>
<table class="tblList" id="schedule_table" height="100%">
	<form method="get" target="day_schedule" name="schedule" action="{$BASE_URL}/schedule/ViewScheduleDay.php">
		{assign var="row_class" value="tblDataWhite"}
		{foreach from=$calendar_array item=calendar name=calendar}
			{if $smarty.foreach.calendar.first}
				<tr class="tblHeader">
					{foreach from=$calendar_column_headers item=column_header}
						<td width="10%">
							{$column_header}
						</td>
					{/foreach}
					{if $total_users > 1}
						<td width="30%" rowspan="100" valign="middle" height="100%">
							<iframe style="width:0; height:100%; border: 5px" id="day_schedule" name="day_schedule" src="{$BASE_URL}/blank.html"></iframe>
						</td>
					{/if}
				</tr>
			{/if}

			{if $calendar.isNewMonth == TRUE }
				{cycle assign=row_class values="tblDataGrey,tblDataWhite"}
			{/if}

			{if $calendar.isNewWeek == TRUE }
				</tr>
				<tr>
			{/if}
				{if $total_users <= 1}
					<td {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')}id="cursor-hand" onClick="TIMETREX.schedule.editSchedule('','',{$calendar.epoch})"{/if}
							class="{if $calendar.day_of_month == NULL}tblHeader
								{*{elseif isset($holidays[$calendar.epoch]) }tblDataDeleted*}
								{else}
									{if $calendar.epoch == $current_epoch }
										tblDataHighLight
									{else}
										{$row_class}
									{/if}
								{/if}" valign="top">
						{if $calendar.day_of_month != NULL}
						<table border="0" cellpadding="0" cellspacing="2" width="100%">
							<tr>
								<td align="left" width="50%" >
									{if $calendar.isNewMonth == TRUE }
										<b>{$calendar.month_name}</b>
									{/if}
									<br>
								</td>
								<td align="right" width="50%">
									<b>{$calendar.day_of_month}</b>
								</td>
							</tr>
							{if isset($holidays[$calendar.epoch])}
							<tr>
								<td colspan="2" align="center">
									<b>{$holidays[$calendar.epoch]}</b>
								</td>
							</tr>
							{/if}

							{foreach from=$schedule_shifts[$calendar.date_stamp] item=shifts}
								{if $shifts.start_time}
									{if $shifts.branch_id != 0}
									<tr>
										<td colspan="2" class="tblHeader">
											{$shifts.branch}
										</td>
									</tr>
									{/if}
									{if $shifts.department_id != 0}
									<tr>
										<td colspan="2" class="tblHeader">
											{$shifts.department}
										</td>
									</tr>
									{/if}
									<tr>
										<td colspan="2" align="center" nowrap>
											{if isset($shifts.id) AND ( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child')) }
												<a href="javascript:TIMETREX.schedule.editSchedule({$shifts.id},{$shifts.user_id},{$calendar.epoch})">
											{/if}
											{if $shifts.status_id == 20}<font color="red">{$shifts.absence_policy|default:"N/A"}</font>{else}{getdate type="TIME" epoch=$shifts.start_time default=TRUE}-{getdate type="TIME" epoch=$shifts.end_time default=TRUE}{/if}
											{if isset($shifts.id) AND ( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') OR $permission->Check('schedule','edit_child') ) }</a>{else} {t}[R]{/t}{/if}
										</td>
									<tr>
								{/if}
							{/foreach}
						</table>
						{else}
							<br>
						{/if}
					</td>
				{else}
					<td id="cursor-hand" onClick="showScheduleDay({$calendar.epoch})"
							class="{if $calendar.day_of_month == NULL}cellHL{else}{if $calendar.epoch == $current_epoch }tblDataWarning{else}{$row_class}{/if}{/if}"
							valign="top">
						{if $calendar.day_of_month != NULL}
							<table border="0" cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td align="left" width="50%" >
										{if $calendar.isNewMonth == TRUE }
											<b>{$calendar.month_name}</b>
										{/if}
										<br>
									</td>
									<td align="right" width="50%">
										<b>{$calendar.day_of_month}</b>
									</td>
								</tr>
								<tr>
									<td colspan="2" align="right"> {* Allow wraping for long holiday names *}
										{if isset($holidays[$calendar.epoch])}
											<div align="center">
												<b>{$holidays[$calendar.epoch]}</b>
											</div>
										{/if}

										<span style="white-space:nowrap;">
										{t}Absent:{/t} {$schedule_shift_totals[$calendar.date_stamp].total_absent_users|default:0}<br>
										{t}Scheduled:{/t} {$schedule_shift_totals[$calendar.date_stamp].total_users|default:0}<br>
										{t}Total:{/t} {gettimeunit value=$schedule_shift_totals[$calendar.date_stamp].total_time|default:0}
										</span>
									</td>
								</tr>
							</table>
						{else}
							<br>
						{/if}
					</td>
				{/if}
		{/foreach}
	</table>
<input type="hidden" id="filter_start_date" name="filter_data[start_date]" value="{$filter_data.start_date}">
<input type="hidden" name="serialize_filter_data" value="{$serialize_filter_data}">
{*
{foreach from=$filter_user_id item=user_id}
	<input type="hidden" name="filter_user_id[]" value="{$user_id}">
{/foreach}
*}
</form>
<script language="JavaScript">changeHeight(document.getElementById('body'), parent.document.getElementById('schedule_layer'), 'screen_size' ); {if $do != ''}parent.location.hash = 'schedule';{/if}</script>
</body>
</html>
{*{include file="footer.tpl"} *}