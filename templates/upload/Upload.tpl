{include file="sm_header.tpl" body_onload="$post_js"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<form method="post" name="upload" action="{$smarty.server.SCRIPT_NAME}" enctype="multipart/form-data">
		    <div id="contentBoxTwoEdit">

				<table class="editTable">

				{if $error != ''}
					<div id="rowError">
						{t}Incorrect Input!{/t}
						<br>
						<br>
						{$error}
					</div>
				{elseif $success != ''}
					<tbody id="data_saved">
						<tr class="tblDataWarning">
							<td colspan="100" valign="center">
								<br>
								<b>{$success}</b>
								<br>&nbsp;
							</td>
						</tr>
					</tbody>
				{/if}

				<tr class="tblHeader">
					<td colspan="2">
						{t}Select The File You Wish To Upload{/t}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						File:
					</td>
					<td class="cellRightEditTable">
						<input name="userfile" type="file">
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2">
						<input type="submit" class="btnSubmit" name="action:upload" value="{t}Upload{/t}">
					</td>
				</tr>
			</table>
		</div>
		<input type="hidden" name="object_type" value="{$object_type}">
		<input type="hidden" name="object_id" value="{$object_id}">
		</form>
	</div>
</div>
{include file="sm_footer.tpl"}