<script	language=JavaScript>

{literal}
function editSchedule(scheduleID,userID,date) {
	try {
		eS=window.open('{/literal}{$BASE_URL}{literal}schedule/EditSchedule.php?id='+ encodeURI(scheduleID) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date),"Edit_Schedule","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
	} catch (e) {
		//DN
	}
}
{/literal}
</script>
		<table class="tblList">
		{*<form method="get" name="schedule" action="{$smarty.server.SCRIPT_NAME}">*}
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				{assign var="row_class" value="tblDataWhite"}
				{foreach from=$calendar_array item=calendar name=calendar}
					{if $smarty.foreach.calendar.first}
						<tr class="tblHeader">
							{if $calendar.start_day_of_week == 0}
								<td width="14%">
									{t}Sunday{/t}
								</td>
							{/if}
							<td width="14%">
								{t}Monday{/t}
							</td>
							<td width="14%">
								{t}Tuesday{/t}
							</td>
							<td width="14%">
								{t}Wednesday{/t}
							</td>
							<td width="14%">
								{t}Thursday{/t}
							</td>
							<td width="14%">
								{t}Friday{/t}
							</td>
							<td width="14%">
								{t}Saturday{/t}
							</td>
							{if $calendar.start_day_of_week == 1}
								<td width="14%">
									{t}Sunday{/t}
								</td>
							{/if}
						</tr>
					{/if}

					{if $calendar.isNewMonth == TRUE }
						{cycle assign=row_class values="tblDataGrey,tblDataWhite"}
					{/if}

					{if $calendar.isNewWeek == TRUE }
						{if !$smarty.foreach.calendar.first}
							</td>
						{/if}
						<tr>
					{/if}

						<td {if $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own')}onClick="editSchedule('',{$filter_user_id},{$calendar.epoch})"{/if}
								class="{if $calendar.day_of_month == NULL}tblHeader
									{elseif isset($holidays[$calendar.epoch]) }tblDataDeleted
									{else}
										{if $calendar.epoch == $current_epoch }
											tblDataHighLight
										{else}
											{$row_class}
										{/if}
									{/if}" valign="top">
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
									<td colspan="2" nowrap>
										{if isset($holidays[$calendar.epoch])}
											<div align="center">
												<b>{$holidays[$calendar.epoch]}</b>
											</div>
										{/if}

										{foreach from=$schedule_shifts[$calendar.epoch] item=shifts}
											{if $shifts.start_time}
												{if isset($shifts.id) AND ( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') ) }
													<a href="javascript:editSchedule({$shifts.id},{$filter_user_id},{$calendar.epoch})">
												{/if}
													{if $shifts.status_id == 20}<font color="red">{/if}
													{getdate type="TIME" epoch=$shifts.start_time default=TRUE}-{getdate type="TIME" epoch=$shifts.end_time default=TRUE}<br>
													{if $shifts.status_id == 20}</font>{/if}
												{if isset($shifts.id) AND ( $permission->Check('schedule','edit') OR $permission->Check('schedule','edit_own') ) }
													</a>
												{/if}
											{else}
												<br>
											{/if}

										{/foreach}
										<br>
									</td>
								</tr>
							</table>
							{else}
								<br>
							{/if}
						</td>
					{if $smarty.foreach.calendar.last}
						</td>
					{/if}
				{/foreach}

				<tr class="tblHeader">
					<td colspan="7">
						{t}Schedule is subject to change without notice.{/t}
					</td>
				</tr>
				<tr>
				  <td class="tblPagingLeft" colspan="7" align="right">
					  <br>
				  </td>
				</tr>
			</table>
{*
		</form>
	</div>
</div>
{include file="footer.tpl"}
*}