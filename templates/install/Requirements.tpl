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
						{t escape="no" 1=$APPLICATION_NAME}In order for your %1 installation to function properly, please ensure all of the system check items listed below are marked as <b>OK</b>. If any are red, please take the necessary steps to fix them.{/t}
					</td>
				</tr>

				{if $install_obj->checkAllRequirements() != 0}
				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						<div id="rowWarning">
						{t escape="no" 1='<a href="http://forums.timetrex.com" target="_blank">' 2='</a>' 3='<a href="http://www.timetrex.com/setup_support.php" target="_blank">' 4='</a>'}For installation support, please join our community %1forums%2 or contact a TimeTrex support expert for %3Implementation Support Services%4.{/t}
						</div>
					</td>
				</tr>
				{/if}

				<tr>
					<td class="cellLeftEditTable">
						{t}TimeTrex Version:{/t}
					</td>
					<td class="cellRightEditTable">
						{assign var="check_timetrex_version" value=$install_obj->checkTimeTrexVersion()}
						{if $check_timetrex_version == 0}
							<span class="">{t}OK{/t}
							(v{$install_obj->getCurrentTimeTrexVersion()})
						{elseif $check_timetrex_version == 1}
							<span class="tblDataWarning">{t}Unable to Check Latest Version{/t}
						{elseif $check_timetrex_version == 2}
							<span class="tblDataWarning">A Newer Version of TimeTrex is Available.
							<a href="http://www.timetrex.com/download.php">{t escape="no" 1=$install_obj->getLatestTimeTrexVersion()}Download v%1 Now{/t}</a>
						{/if}
						</span>
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}PHP Version:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $install_obj->checkPHPVersion() == 0}
							<span class="">{t}OK{/t}
						{elseif $install_obj->checkPHPVersion() == 1}
							<span class="tblDataError">{t}Invalid{/t}
						{elseif $install_obj->checkPHPVersion() == 2}
							<span class="tblDataWarning">{t}Unsupported{/t}
						{/if}
						(v{$install_obj->getPHPVersion()})
						</span>
					</td>
				</tr>

				<tr>
					<td valign="top" width="50%">
						<table class="editTable">
							<tr>
								<td colspan="2" class="cellLeftEditTable">
									<div align="center">
										{t}PHP Requirements{/t}
									</div>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}Database Engine:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkDatabaseType() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkDatabaseType() == 1}
										<span class="tblDataError">{t}Invalid, PGSQL or MySQLi PHP extensions are required{/t}
									{elseif $install_obj->checkDatabaseType() == 2}
										<span class="tblDataWarning">{t}Unsupported, upgrade to MySQLi PHP extension instead.{/t}
									{/if}
									</span>
								</td>
							</tr>
			{*
							<tr>
								<td class="cellLeftEditTable">
									{t}Database Version:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkDatabaseVersion() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkDatabaseVersion() == 1}
										<span class="tblDataError">{t}Invalid{/t}
									{elseif $install_obj->checkDatabaseVersion() == 2}
										<span class="tblDataWarning">{t}Unsupported{/t}
									{/if}
									({$install_obj->getDatabaseVersion()})
									</span>
								</td>
							</tr>
			*}
							<tr>
								<td class="cellLeftEditTable">
									{t}BCMATH Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkBCMATH() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkBCMATH() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (BCMATH extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}MBSTRING Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkMBSTRING() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkMBSTRING() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (MBSTRING extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}GETTEXT Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkGETTEXT() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkGETTEXT() > 0}
										<span class="tblDataWarning">{t}Warning: Not Installed. (GETTEXT extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>
							{*
							<tr>
								<td class="cellLeftEditTable">
									{t}CALENDAR Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkCALENDAR() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkCALENDAR() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (CALENDAR extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>
							*}

							<tr>
								<td class="cellLeftEditTable">
									{t}SOAP Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkSOAP() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkSOAP() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (SOAP extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}GD Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkGD() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkGD() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (GD extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}JSON Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkJSON() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkJSON() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (JSON extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>

							{if $install_obj->getTTProductEdition() >= 20}
							<tr>
								<td class="cellLeftEditTable">
									{t}MCRYPT Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkMCRYPT() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkMCRYPT() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (MCRYPT extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>
							{/if}

							<tr>
								<td class="cellLeftEditTable">
									{t}SimpleXML Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkSimpleXML() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkSimpleXML() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (SimpleXML extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}ZIP Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkZIP() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkZIP() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (ZIP extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>
							<tr>
								<td class="cellLeftEditTable">
									{t}MAIL Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkMAIL() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkMAIL() == 1}
										<span class="tblDataError">{t}Warning: Not Installed. (MAIL extension must be enabled){/t}
									{/if}
									</span>
								</td>
							</tr>
							<tr>
								<td class="cellLeftEditTable">
									{t}PEAR Enabled:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkPEAR() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkPEAR() == 1}
										<span class="tblDataError">{t escape="no"}Warning: Not Installed.{/t} {if PHP_OS == 'WINNT'}{t escape="no"}(try running: "<b>go-pear.bat</b>"){/t}{else}{t escape="no" 1="<a href=\"http://pear.php.net\">http://pear.php.net</a>"}(install the PEAR RPM or package from %1){/t}{/if}
									{/if}
									</span>
								</td>
							</tr>
							<tr>
								<td class="cellLeftEditTable">
									{t}Safe Mode Turned Off:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkPHPSafeMode() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkPHPSafeMode() == 1}
										<span class="tblDataError">{t}Safe Mode is On. (Please disable it in php.ini){/t}
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}Magic Quotes GPC Turned Off:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkPHPMagicQuotesGPC() == 0}
										<span class="">{t}OK{/t}
									{elseif $install_obj->checkPHPMagicQuotesGPC() == 1}
										<span class="tblDataError">{t}magic_quotes_gpc is On. (Please disable it in php.ini){/t}
									{/if}
									</span>
								</td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table class="editTable">
							<tr>
								<td colspan="2" class="cellLeftEditTable">
									<div align="center">
										{t}Other Requirements{/t}
									</div>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}Memory Limit:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkPHPMemoryLimit() == 0}
										<span class="">{t}OK{/t}{if $install_obj->getMemoryLimit() > 0}({$install_obj->getMemoryLimit()}M){/if}
									{elseif $install_obj->checkPHPMemoryLimit() == 1}
										<span class="tblDataError">{t escape="no" 1=$install_obj->getMemoryLimit()}Warning: %1M (Set this to 128M or higher){/t}
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}Base URL:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkBaseURL() == 0}
										<span class="">{t}OK{/t}
									{else}
										<span class="tblDataError"><b>{t escape="no" 1=$install_obj->getRecommendedBaseURL()}Warning: base_url in timetrex.ini.php is incorrect, perhaps it should be "%1" instead.{/t}</b>
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}PHP Open BaseDir:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkPHPOpenBaseDir() == 0}
										<span class="">{t}OK{/t}
									{else}
										<span class="tblDataError"><b>{t escape="no" 1=$install_obj->getPHPOpenBaseDir() 2=$install_obj->getPHPCLIDirectory()}Warning: PHP open_basedir setting (%1) does not include directory of PHP CLI binary (%2).{/t}</b>
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}PHP CLI Executable:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkPHPCLIBinary() == 0}
										<span class="">{t}OK{/t}
									{else}
										<span class="tblDataError"><b>{t escape="no" 1=$install_obj->getPHPCLI()}Warning: PHP CLI (%1) does not exist or is not executable.{/t}</b>
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}PHP CLI Requirements:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkPHPCLIRequirements() == 0}
										<span class="">{t}OK{/t}
									{else}
										<span class="tblDataError"><b>{t escape="no" 1=$install_obj->getPHPCLIRequirementsCommand()}Warning: PHP CLI requirements failed while executing <br>"%1"<br> Likely caused by having two PHP.INI files with different settings.{/t}</b>
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t escape="no" 1=$APPLICATION_NAME}Writable %1 Configuration File (timetrex.ini.php):{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkWritableConfigFile() == 0}
										<span class="">{t}OK{/t}
									{else}
										<span class="tblDataError">{t}Warning: Not writable{/t}
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}Writable Cache Directory:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkWritableCacheDirectory() == 0}
										<span class="">{t}OK{/t}
									{else}
										<span class="tblDataError">{t}Warning: Not writable{/t} ({$install_obj->config_vars.cache.dir})
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}Writable Storage Directory:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkWritableStorageDirectory() == 0}
										<span class="">{t}OK{/t}
									{else}
										<span class="tblDataError">{t}Warning: Not writable{/t} ({$install_obj->config_vars.path.storage})
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									{t}Writable Log Directory:{/t}
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkWritableLogDirectory() == 0}
										<span class="">{t}OK{/t}
									{else}
										<span class="tblDataError">{t}Warning: Not writable{/t} ({$install_obj->config_vars.path.log})
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									Empty Cache Directory:
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkCleanCacheDirectory() == 0}
										<span class="">OK
									{else}
										<span class="tblDataError">Warning: Please delete all files/directories in: <b>{$install_obj->config_vars.cache.dir}</b>
									{/if}
									</span>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTable">
									File Permissions:
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkFilePermissions() == 0}
										<span class="">OK
									{else}
										<span class="tblDataError">Warning: File permissions are invalid, some {$APPLICATION_NAME} files are not readable/writable. See detailed error messages below.</b>
									{/if}
									</span>
								</td>
							</tr>
							<tr>
								<td class="cellLeftEditTable">
									File CheckSums:
								</td>
								<td class="cellRightEditTable">
									{if $install_obj->checkFileChecksums() == 0}
										<span class="">OK
									{else}
										<span class="tblDataError">Warning: File checksums do not match, some {$APPLICATION_NAME} files may be corrupted, missing, or not installed properly. See detailed error messages below.</b>
									{/if}
									</span>
								</td>
							</tr>

						</table>
					</td>
				</tr>

				{if count($install_obj->getExtendedErrorMessage()) > 0}
				<tr>
					<td class="tblDataError" colspan="2">
					  <b>Detailed Error Messages</b><br><br>
					  {foreach from=$install_obj->getExtendedErrorMessage() key=key item=errors name=errors}
						{foreach from=$errors item=error_msg name=error_msg}
						  {$error_msg}<br>
						{/foreach}
					  {/foreach}
					</td>
				</tr>
				{/if}

				<tr>
					<td class="tblDataWhiteNH" colspan="2">
						<ul>
						<li>{t escape="no" 1=$APPLICATION_NAME 2=$install_obj->getConfigFile()}Your %1 configuration file (timetrex.ini.php) is located at:<br> <b>%2</b>{/t}</li>
						<br>
						<li>{t escape="no" 1=$install_obj->getPHPConfigFile() 2=$install_obj->getPHPIncludePath()}Your PHP configuration file (php.ini) is located at:<br> <b>%1</b>, the include path is: "<b>%2</b>"{/t}</li>
						<br>
						<li>{t}Detailed{/t} <a href={$smarty.server.SCRIPT_NAME}?action:phpinfo=phpinfo>{t}PHP Information{/t}</a></li>
						</ul>
					</td>
				</tr>

				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<input type="submit" class="btnSubmit" id="next_button" name="action:back" value="{t}Back{/t}">
						<input type="submit" class="btnSubmit" id="next_button" name="action:re-check" value="{t}Re-Check{/t}">
						<input type="submit" class="btnSubmit" id="next_button" name="action:next" value="{t}Next{/t}" {if $install_obj->checkAllRequirements() == 1}disabled{/if}>
					</td>
				</tr>
			</table>
			<input type="hidden" name="external_installer" value="{$external_installer}">
		</form>
	</div>
</div>
{include file="footer.tpl"}