<h5>{L_STATUSINFORMATIONEN}</h5>
<!-- BEGIN DIRECTORY_WARNINGS -->
	{DIRECTORY_WARNINGS.MSG}
<!-- END DIRECTORY_WARNINGS -->

<!-- BEGIN DIRECTORY_PROTECTION_STATUS -->
	{DIRECTORY_PROTECTION_STATUS.MSG}<br>
<!-- END DIRECTORY_PROTECTION_STATUS -->

<!-- BEGIN DIRECTORY_PROTECTION_STATUS_ERROR -->
	<span class="error">{DIRECTORY_PROTECTION_STATUS_ERROR.MSG}</span><br>
<!-- END DIRECTORY_PROTECTION_STATUS_ERROR -->

<!-- BEGIN HTACCESS_EXISTS -->
	<br>
	<a href="main.php?action=edithtaccess" class="Formbutton">{L_HTACC_EDIT}</a>&nbsp;
	<a href="main.php?action=deletehtaccess" class="Formbutton" onclick="if (!confirm('{L_HTACC_CONFIRM_DELETE}')) return false;">{L_DELETE_HTACCESS}</a><br>
<!-- END HTACCESS_EXISTS -->

<!-- BEGIN HTACCESS_DOESNT_EXISTS -->
	<br>
	<a href="main.php?action=schutz" class="Formbutton">{L_HTACC_CREATE}</a><br>
<!-- END HTACCESS_DOESNT_EXISTS -->

<h6>{L_VERSIONSINFORMATIONEN}</h6>
<img src="css/{THEME}/pics/loveyourdata.gif" align="right" alt="love your data" title="love your data">
{L_MOD_VERSION}: <strong>{MOD_VERSION}</strong><br>
{L_OS}: <strong>{OS}</strong> ({OS_EXT})<br>
{L_MYSQL_VERSION}: <strong>{MYSQL_VERSION}</strong><br>
{L_PHP_VERSION}: <strong>{PHP_VERSION}</strong>&nbsp;&nbsp;{L_MEMORY}: <strong>{MEMORY}</strong>&nbsp;&nbsp;
{L_MAX_EXECUTION_TIME}: <strong>{MAX_EXECUTION_TIME} {L_SECONDS}</strong>&nbsp;&nbsp;
<a href="main.php?action=phpinfo" class="Formbutton">PHP-Info</a><br>

<!-- BEGIN NO_FTP -->
	<span class="error">{L_NOFTPPOSSIBLE}</span><br>
<!-- END NO_FTP -->

<!-- BEGIN NO_ZLIB -->
	<span class="error">{L_NOGZPOSSIBLE}</span><br>
<!-- END NO_ZLIB -->

{L_PHP_EXTENSIONS}: <span class="small">{PHP_EXTENSIONS}</span><br>
<!-- BEGIN DISABLED_FUNCTIONS -->
	<br>
	{L_DISABLEDFUNCTIONS}: <span class="small">{DISABLED_FUNCTIONS.PHP_DISABLED_FUNCTIONS}</span><br>
<!-- END DISABLED_FUNCTIONS -->

<br clear="all">

<h6>{L_MOD_INFO}</h6>
{L_INFO_LOCATION} "<b>{SERVER_NAME}</b>" ({MOD_PATH})<br>
{L_INFO_ACTDB}: <strong>{DB}</strong><br>
{L_BACKUPFILESANZAHL} <strong>{NR_OF_BACKUP_FILES}</strong>
{L_BACKUPS} (<strong>{SIZE_BACKUPS}</strong>)<br>
{L_FM_FREESPACE}: <strong>{FREE_DISKSPACE}</strong><br>

<!-- BEGIN LAST_BACKUP -->
	{L_LASTBACKUP} {L_VOM} <strong><span class="small">{LAST_BACKUP.LAST_BACKUP_INFO}</span></strong><br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{LAST_BACKUP.LAST_BACKUP_LINK}" target="_blank"><strong>{LAST_BACKUP.LAST_BACKUP_NAME}</strong></a><br>
<!-- END LAST_BACKUP -->
