<script language="JavaScript">
{literal}
<!--
function fixHeight() {
        var contentObj = document.getElementById('message_table');
        var contentHeight = contentObj.offsetHeight;

        //alert('Height: '+ contentHeight);
        var targetObj = parent.document.getElementById('LayerMessageFactoryFrame');

        var newTargetHeight = contentHeight + 4;

        targetObj.style.height = newTargetHeight + "px";
}

function done() {
	parent.document.getElementById("MessageFactoryLayer").style.display="none"
	//alert('Closing LayerMessageList: '+ parent.document.getElementById("MessageFactoryLayer").style.display);
	parent.document.forms[0].alt_action.value = 'Submit';
	//alert('Action: '+ parent.document.forms[0].alt_action.value);
	parent.document.forms[0].submit();
}
//-->
{/literal}
</script>

<html>
<head>
<link rel="stylesheet" href="{$BASE_URL}global.css.php" type="text/css" />
<SCRIPT language=JavaScript src="{$BASE_URL}global.js.php" type=text/javascript></SCRIPT>
</head>
<body bgcolor="#7a9bbd" {if $template == 1 AND $close == 1}onload="done()"{/if}>


						<div id="rowContentInner">
								<table class="tblList" id="message_table">

								<form method="get" name="message_data" action="{$smarty.server.SCRIPT_NAME}">
									<tr>
										<td class="tblPagingLeft" colspan="7" align="right">
											{include file="pager.tpl" pager_data=$paging_data}
										</td>
									</tr>

								{foreach name="messages" from=$messages item=message}
									{if $smarty.foreach.messages.first}
									<tr class="tblHeader">
										<td>
											{t}Posted By / Date{/t}
										</td>
										<td width="80%">
											{t}Message{/t}
										</td>
									</tr>
									{/if}

									{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
									{if $message.deleted == TRUE}
										{assign var="row_class" value="tblDataDeleted"}
									{/if}

									<tr class="{$row_class}">
										<td style="text-align:left;">
											{$message.created_by_full_name}
										</td>
										<td rowspan="2" style="text-align:left; vertical-align: top;">
											<b>Subject:</b> {$message.subject}<br><br>
											{$message.body|nl2br}
										</td>
									</tr>
									<tr class="{$row_class}">
										<td style="text-align:left;">
											{getdate type="DATE+TIME" epoch=$message.created_date default=TRUE}
										</td>
									</tr>
									<tr>
										<td>
										</td>
									</tr>
								{/foreach}

								{if $permission->Check('message','add')}
									<tr>
									<td colspan="2">
										<table class="editTable">
										{if !$mf->isValid()}
											{include file="form_errors.tpl" object="mf"}
										{/if}

										{if $total_messages == 0 }
										<tr id="warning">
											<td colspan="2">
												{t escape="no" 1=$object_name}Please submit at least one message describing the reason for this %1.{/t}
											</td>
										</tr>
										{/if}
										<tr class="tblHeader">
											<td colspan="2">
												{t}New Message{/t}
											</td>
										</tr>

										<tr onClick="showHelpEntry('subject')">
											<td class="{isvalid object="mf" label="subject" value="cellLeftEditTable"}" style="width: 20%;">
												<a name="form_start"></a>
												{t}Subject:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="text" size="80" name="message_data[subject]" value="{if !empty($message_data.subject)}{$message_data.subject}{else}{$default_subject}{/if}">
											</td>
										</tr>
										<tr onClick="showHelpEntry('body')">
											<td class="{isvalid object="mf" label="body" value="cellLeftEditTable"}">
												{t}Body:{/t}
											</td>
											<td class="cellRightEditTable">
												<textarea rows="5" cols="70" name="message_data[body]">{$message_data.body}</textarea>
											</td>
										</tr>

										<tr class="tblHeader">
											<td colspan="2">
												<input type="submit" name="action" value="Submit Message">
											</td>
										</tr>
										</table>
									</td>
									</tr>
								{/if}
								<tr>
									<td class="tblPagingLeft" colspan="7" align="right">
										{include file="pager.tpl" pager_data=$paging_data}
									</td>
								</tr>

							<input type="hidden" name="parent_id" value="{$parent_id}">
							<input type="hidden" name="object_type_id" value="{$object_type_id}">
							<input type="hidden" name="object_id" value="{$object_id}">
							<input type="hidden" name="template" value="{$template}">
							<input type="hidden" name="total_messages" value="{$total_messages}">
							<input type="hidden" name="sort_column" value="{$sort_column}">
							<input type="hidden" name="sort_order" value="{$sort_order}">
							<input type="hidden" name="page" value="{$paging_data.current_page}">
							</form>
							</table>
						</div>
<script language="JavaScript">fixHeight();</script>
</body>
</html>
