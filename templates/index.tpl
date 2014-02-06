{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>
				<tr class="tblHeader">
					<td colspan="3">
						{t escape="no" 1=$current_user->getFullName()}Recent Activity Summary for %1{/t}<br>
					</td>
				</tr>
				<tr class="tblDataWhiteNH">
					<td valign="top">
						<br>
						<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" onClick="window.location.href='{urlbuilder script="./punch/UserExceptionList.php" values="" merge="FALSE"}'">
							<tr class="tblHeader">
								<td colspan="2">
									{t}Current Exceptions{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}Severity{/t}
								</td>
								<td>
									{t}Exceptions{/t}
								</td>
							</tr>
							<tr class="tblDataGreyNH" style="font-weight: bold; {if $exceptions.30 > 0}background-color: red{/if}">
								<td>
									{t}Critical{/t}
								</td>
								<td>
									{$exceptions.30|default:0}
								</td>
							</tr>
							<tr class="tblDataGreyNH" style="font-weight: bold; {if $exceptions.25 > 0}background-color: orange{/if}">
								<td>
									{t}High{/t}
								</td>
								<td>
									{$exceptions.25|default:0}
								</td>
							</tr>
							<tr class="tblDataWhiteNH" style="font-weight: bold; {if $exceptions.20 > 0}background-color: yellow;{/if}">
								<td>
									{t}Medium{/t}
								</td>
								<td>
									{$exceptions.20|default:0}
								</td>
							</tr>
							<tr class="tblDataGreyNH" style="font-weight: bold">
								<td>
									{t}Low{/t}
								</td>
								<td>
									{$exceptions.10|default:0}
								</td>
							</tr>
						</table>
						<br>
					</td>
					<td valign="top">
						<br>
						<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" onClick="window.location.href='{urlbuilder script="./request/UserRequestList.php" values="" merge="FALSE"}'">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Recent Requests{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}Date{/t}
								</td>
								<td>
									{t}Status{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
							</tr>
							{foreach from=$requests item=request}
								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}">
									<td>
										{getdate type="DATE" epoch=$request.date_stamp}
									</td>
									<td>
										{$request.status}
									</td>
									<td>
										{$request.type}
									</td>
								</tr>
							{foreachelse}
								<tr class="tblDataWhiteNH">
									<td colspan="3">
										{t}No Recent Requests{/t}
									</td>
								</tr>
							{/foreach}
						</table>
						<br>
					</td>
					<td valign="top">
						<br>
						<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" onClick="window.location.href='{urlbuilder script="./message/UserMessageList.php" values="" merge="FALSE"}'">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Recent Messages{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}From{/t}
								</td>
								<td>
									{t}Subject{/t}
								</td>
								<td>
									{t}Date{/t}
								</td>
							</tr>
							{foreach from=$messages item=message}
								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}" {if $message.status_id == 10}style="font-weight: bold;"{/if}>
									<td>
										{$message.user_full_name}
									</td>
									<td>
										{$message.subject}
									</td>
									<td>
										{getdate type="DATE" epoch=$message.created_date}
									</td>
								</tr>
							{foreachelse}
								<tr class="tblDataWhiteNH">
									<td colspan="3">
										{t}No Recent Messages{/t}
									</td>
								</tr>
							{/foreach}
						</table>
						<br>

					</td>
				</tr>
				{if $permission->Check('authorization','enabled') AND $permission->Check('authorization','view') AND $permission->Check('request','authorize')}
				<tr class="tblDataWhiteNH">
					<td colspan="3" valign="top">
						<br>
						<table class="tblList" width="33%" style="cursor: pointer; cursor: hand;" onClick="window.location.href='{urlbuilder script="./authorization/AuthorizationList.php" values="" merge="FALSE"}'">
							<tr class="tblHeader">
								<td colspan="3">
									{t}Pending Requests{/t}
								</td>
							</tr>
							<tr class="tblHeader">
								<td>
									{t}Employee{/t}
								</td>
								<td>
									{t}Type{/t}
								</td>
								<td>
									{t}Date{/t}
								</td>
							</tr>
							{foreach from=$pending_requests item=pending_request}
								{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
								<tr class="{$row_class}" {if $message.status_id == 10}style="font-weight: bold;"{/if}>
									<td>
										{$pending_request.user_full_name}
									</td>
									<td>
										{$pending_request.type}
									</td>
									<td>
										{getdate type="DATE" epoch=$pending_request.date_stamp}
									</td>
								</tr>
							{foreachelse}
								<tr class="tblDataWhiteNH">
									<td colspan="3">
										{t}No Pending Requests{/t}
									</td>
								</tr>
							{/foreach}
						</table>
						<br>
					</td>
				</tr>
				{/if}
			</table>
			<tr>
				<td class="tblPagingLeft" colspan="7" align="right">
					<br>
				</td>
			</tr>

		</form>
	</div>
</div>
{include file="footer.tpl"}