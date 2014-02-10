<?php
/**
 * The view file for CFlexWidget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package system.web.widgets.views
 * @since 1.0
 */
?>
<script type="text/javascript">
/*<![CDATA[*/
// Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
var hasProductInstall = DetectFlashVer(6, 0, 65);

// Version check based upon the values defined in globals
var hasRequestedVersion = DetectFlashVer(9, 0, 0);

// Check to see if a player with Flash Product Install is available and the version does not meet the requirements for playback
if ( hasProductInstall && !hasRequestedVersion ) {
	// MMdoctitle is the stored document.title value used by the installation process to close the window that started the process
	// This is necessary in order to close browser windows that are still utilizing the older version of the player after installation has completed
	// DO NOT MODIFY THE FOLLOWING FOUR LINES
	// Location visited after installation is complete if installation is required
	var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
	var MMredirectURL = window.location;
	document.title = document.title.slice(0, 47) + " - Flash Player Installation";
	var MMdoctitle = document.title;

	AC_FL_RunContent(
		"src", "<?php echo $this->baseUrl ?>/playerProductInstall",
		"FlashVars", "MMredirectURL="+MMredirectURL+'&MMplayerType='+MMPlayerType+'&MMdoctitle='+MMdoctitle+"",
		"width", "<?php echo $this->width; ?>",
		"height", "<?php echo $this->height; ?>",
		"align", "<?php echo $this->align; ?>",
		"id", "<?php echo $this->name; ?>",
		"quality", "<?php echo $this->quality; ?>",
		"bgcolor", "<?php echo $this->bgColor; ?>",
		"name", "<?php echo $this->name; ?>",
		"allowScriptAccess","<?php echo $this->allowScriptAccess ?>",
		"allowFullScreen","<?php echo $this->allowFullScreen ?>",
		"type", "application/x-shockwave-flash",
		"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
} elseif (hasRequestedVersion) {
	// if we've detected an acceptable version
	// embed the Flash Content SWF when all tests are passed
	AC_FL_RunContent(
		"src", "<?php echo $this->baseUrl ?>/<?php echo $this->name ?>",
		"width", "<?php echo $this->width ?>",
		"height", "<?php echo $this->height ?>",
		"align", "<?php echo $this->align ?>",
		"id", "<?php echo $this->name ?>",
		"quality", "<?php echo $this->quality ?>",
		"bgcolor", "<?php echo $this->bgColor ?>",
		"name", "<?php echo $this->name ?>",
		"flashvars","<?php echo $this->flashVarsAsString; ?>",
		"allowScriptAccess","<?php echo $this->allowScriptAccess ?>",
		"allowFullScreen","<?php echo $this->allowFullScreen ?>",
		"type", "application/x-shockwave-flash",
		"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
} else {  // flash is too old or we can't detect the plugin
	var alternateContent = '<?php echo CJavaScript::quote($this->altHtmlContent); ?>';
	document.write(alternateContent);  // insert non-flash content
}
/*]]>*/
</script>
<noscript>
	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
		id="<?php echo $this->name ?>"
		width="<?php echo $this->width ?>"
		height="<?php echo $this->height ?>"
		codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
			<param name="movie" value="<?php echo $this->baseUrl ?>/<?php echo $this->name ?>.swf" />
			<param name="quality" value="<?php echo $this->quality ?>" />
			<param name="bgcolor" value="<?php echo $this->bgColor ?>" />
			<param name="flashVars" value="<?php echo $this->flashVarsAsString ?>" />
			<param name="allowScriptAccess" value="<?php echo $this->allowScriptAccess ?>" />
			<param name="allowFullScreen" value="<?php echo $this->allowFullScreen ?>" />
			<embed src="<?php echo $this->baseUrl ?>/<?php echo $this->name ?>.swf"
				quality="<?php echo $this->quality ?>"
				bgcolor="<?php echo $this->bgColor ?>"
				width="<?php echo $this->width ?>"
				height="<?php echo $this->height ?>"
				name="<?php echo $this->name ?>"
				align="<?php echo $this->align ?>"
				play="true"
				loop="false"
				quality="<?php echo $this->quality ?>"
				allowScriptAccess="<?php echo $this->allowScriptAccess ?>"
				allowFullScreen="<?php echo $this->allowFullScreen ?>"
				type="application/x-shockwave-flash"
				pluginspage="http://www.adobe.com/go/getflashplayer">
			</embed>
	</object>
</noscript>