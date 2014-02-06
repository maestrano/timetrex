{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$epcf->Validator->isValid()}
					{include file="form_errors.tpl" object="epcf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="epcf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr>
					<td colspan="2">
						<table width="100%">
							<tr class="tblHeader">
								<td>
									{t}Active{/t}
								</td>
								<td>
									{t}Code{/t}
								</td>
								<td>
									{t}Name{/t}
								</td>
								<td>
									{t}Severity{/t}
								</td>
{*
								<td>
									{t}Demerits{/t}
								</td>
*}
								<td>
									{t}Grace{/t}
								</td>
								<td>
									{t}Watch Window{/t}
								</td>
								<td>
									{t}Email Notification{/t}
								</td>
							</tr>

							{foreach from=$data.exceptions key=code item=exception}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								<tr class="{$row_class}">
									<td>
										<input type="checkbox" class="checkbox" name="data[exceptions][{$code}][active]" value="1" {if $exception.active == TRUE}checked{/if}>
										<input type="hidden" name="data[exceptions][{$code}][id]" value="{$exception.id}">
									</td>
									<td>
										{$code}
									</td>
									<td>
										{$exception.name}
									</td>
									<td>
										<select id="severity_id" name="data[exceptions][{$code}][severity_id]">
											{html_options options=$data.severity_options selected=$exception.severity_id}
										</select>
									</td>
{*
									<td>
										<input type="text" size="4" name="data[exceptions][{$code}][demerit]" value="{$exception.demerit}">
									</td>
*}
									<td>
										{if $exception.is_enabled_grace == TRUE}
											<input type="text" size="6" name="data[exceptions][{$code}][grace]" value="{gettimeunit value=$exception.grace}">
											<input type="hidden" name="data[exceptions][{$code}][is_enabled_grace]" value="{$exception.is_enabled_grace}">
										{else}
											<br>
										{/if}
									</td>
									<td>
										{if $exception.is_enabled_watch_window == TRUE}
											<input type="text" size="6" name="data[exceptions][{$code}][watch_window]" value="{gettimeunit value=$exception.watch_window}">
											<input type="hidden" name="data[exceptions][{$code}][is_enabled_watch_window]" value="{$exception.is_enabled_watch_window}">
										{else}
											<br>
										{/if}
									</td>
									<td>
										<select id="email_notification_id" name="data[exceptions][{$code}][email_notification_id]">
											{html_options options=$data.email_notification_options selected=$exception.email_notification_id}
										</select>
									</td>
								</tr>

							{/foreach}
						</table>
					</td>
				</tr>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
