<?php
if($file->type==='php')
{
	echo '<div class="content">';
	highlight_string($file->content);
	echo '</div>';
}
elseif(in_array($file->type,array('txt','js','css')))
{
	echo '<div class="content">';
	echo nl2br($file->content);
	echo '</div>';
}
else
	echo '<div class="error">Preview is not available for this file type.</div>';
?>