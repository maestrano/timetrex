{include file="header.tpl" body_onload="filterUserCount();"}

<script	language=JavaScript>

{literal}
function filterUserCount() {
	total = countSelect(document.getElementById('filter_user'));
	writeLayer('filter_user_count', total);
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="edit_message" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$mcf->Validator->isValid()}
					{include file="form_errors.tpl" object="mcf"}
				{/if}

				<table class="editTable">

				{include file="data_saved.tpl" result=$data_saved}

				{if $permission->Check('message','send_to_any') OR $permission->Check('message','send_to_child') }
				<tbody id="filter_employees_on" style="display:none" >
					<tr>
						<td class="cellLeftEditTableHeader" nowrap>
							<b>{t}To:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
						</td>

						<td class="cellRightEditTable" align="center">
							<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
							<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
							<br>
							<select name="src_user_id" id="src_filter_user" style="width:90%px;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
								{html_options options=$data.user_options}
							</select>
						</td>
						<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
							<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
							<br>
							<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
							<br>
							<br>
							<br>
							<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
						</td>
						<td class="cellRightEditTable" align="center">
							<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
							<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
							<br>
							<select name="filter_user_id[]" id="filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
								{html_options options=$filter_user_options selected=$filter_user_id}
							</select>
						</td>
					</tr>
				</tbody>

				<tbody id="filter_employees_off">
				<tr>
					<td class="cellLeftEditTableHeader" nowrap>
						<b>{t}To:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTableHeader" colspan="100">
						<span id="filter_user_count">0</span> {t}Recipients Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				{else}
					<tr onClick="showHelpEntry('to')">
						<td class="{isvalid object="mcf" label="to" value="cellLeftEditTable"}" style="width: 20%;">
							{t}To:{/t}
						</td>
						<td class="cellRightEditTable" colspan="3">
							<select name="filter_user_id[]" id="filter_user">
								{html_options options=$data.user_options}
							</select>
						</td>
					</tr>
				{/if}

				<tr onClick="showHelpEntry('subject')">
					<td class="{isvalid object="mcf" label="subject" value="cellLeftEditTable"}" style="width: 20%;">
						{t}Subject:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<input type="text" size="45" name="data[subject]" value="{if !empty($data.subject)}{$data.subject}{else}{$default_subject}{/if}">
					</td>
				</tr>
				<tr onClick="showHelpEntry('body')">
					<td class="{isvalid object="mcf" label="body" value="cellLeftEditTable"}">
						{t}Body:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<textarea rows="5" cols="50" name="data[body]">{$data.body}</textarea>
					</td>
				</tr>

				{* Disable this feature for the first version of the rewritten message system.
				{if $permission->Check('message','add_advanced')}
				<tr onClick="showHelpEntry('require_ack')">
					<td class="{isvalid object="mcf" label="require_ack" value="cellLeftEditTable"}" style="width: 20%;">
						{t}Requires Acknowledgment:{/t}
					</td>
					<td class="cellRightEditTable" colspan="3">
						<input type="checkbox" class="checkbox" name="data[require_ack]" value="1" {if $data.require_ack == 1}checked{/if}>
					</td>
				</tr>
				{/if}
				*}
				<tr class="tblHeader">
					<td colspan="100">
						<input type="submit" name="action:Submit_Message" value="{t}Submit Message{/t}" onClick="selectAll(document.getElementById('filter_user'))">
					</td>
				</tr>

			</table>
		</div>

		<input type="hidden" name="data[id]" value="{$id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
