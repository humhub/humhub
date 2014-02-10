<?php

error_reporting(E_ALL);

Yii::import('gii.components.Pear.*');
require_once 'Text/Diff.php';
require_once 'Text/Diff/Renderer.php';
require_once 'Text/Diff/Renderer/inline.php';

class TextDiff extends CComponent
{
	public static function compare($lines1, $lines2)
	{
		if(is_string($lines1))
			$lines1=explode("\n",$lines1);
		if(is_string($lines2))
			$lines2=explode("\n",$lines2);
		$diff = new Text_Diff('auto', array($lines1, $lines2));
		$renderer = new Text_Diff_Renderer_inline();
		return $renderer->render($diff);
	}
}