{include file="sm_header.tpl" enable_calendar=true}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$rf->Validator->isValid()}
					{include file="form_errors.tpl" object="rf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.user_full_name}
					</td>
				</tr>

				<tr onClick="showHelpEntry('date_stamp')">
					<td class="{isvalid object="rf" label="datestamp" value="cellLeftEditTable"}">
						{t}Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="date_stamp" name="data[date_stamp]" value="{getdate type="DATE" epoch=$data.date_stamp}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_date_stamp" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('date_stamp', 'cal_date_stamp', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('type_id')">
					<td class="{isvalid object="rf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>
				{if $data.id == ''}
					<tr>
						<td colspan="2">
							<table>
								<tr onClick="showHelpEntry('message')">
									<td colspan="2" class="tblHeader">
										{t}Message{/t}
									</td>
								</tr>
								<tr onClick="showHelpEntry('message')">
									<td colspan="2" style="text-align: center" class="{isvalid object="rf" label="message" value="cellLeftEditTable"}">
											<textarea rows="5" cols="40" name="data[message]">{$data.message}</textarea>

									</td>
								</tr>
							</table>
						</td>
					</tr>
				{/if}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		<input type="hidden" name="data[user_id]" value="{$data.user_id}">
		</form>
	</div>
</div>
{*
{if $data.id != ''}
<br>
<br>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{t}Messages{/t}</span></div>
</div>
<div id="rowContentInner">
	<div id="contentBoxTwoEdit">
		<table class="tblList">
			<tr>
				<td>
					{embeddedmessagelist object_type_id=50 object_id=$data.id}
				</td>
			</tr>
		</table>
	</div>
</div>
{/if}
*}
{include file="sm_footer.tpl"}