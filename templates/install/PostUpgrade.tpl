{include file="sm_header.tpl" authenticate=FALSE}

{include file="install/Install.js.tpl"}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="editTable">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						{t escape="no" 1=$APPLICATION_NAME 2=$APPLICATION_VERSION}<b>Congratulations!</b> You have successfully upgraded %1 to <b>%2</b>.{/t}
						<br>
						<br>
						{t escape="no" 1=$APPLICATION_NAME}<b>NOTE:</b> In order to access new features you may need to re-apply the <b>Administrator</b> permission preset to each administrator employee in %1.{/t}
						<br>
						<br>
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<input type="submit" id="next_button" name="action:back" value="{t}Back{/t}">
						<input type="submit" id="next_button" name="action:next" value="{t}Next{/t}">
					</td>
				</tr>
				
			</table>
		</form>
		<br>
	</div>
</div>
{include file="footer.tpl"}