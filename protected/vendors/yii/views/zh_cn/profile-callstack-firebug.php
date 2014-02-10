<script type="text/javascript">
/*<![CDATA[*/
if(typeof(console)=='object')
{
	console.group("程序概要分析 - 堆栈调用报告");
<?php
foreach($data as $index=>$entry)
{
	list($proc,$time,$level)=$entry;
	$proc=CJavaScript::quote($proc);
	$time=sprintf('%0.5f',$time);
	$spaces=str_repeat(' ',$level*8);
	echo "\tconsole.log(\"[$time]{$spaces}{$proc}\");\n";
}
?>
	console.groupEnd();
}
/*]]>*/
</script>