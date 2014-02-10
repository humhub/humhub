<script type="text/javascript">
/*<![CDATA[*/
if(typeof(console)=='object')
{
	console.group("程序概要分析 - 报告概述");
	console.log("  数量    总计     平均    最小     最大   ");
<?php
foreach($data as $index=>$entry)
{
	$proc=CJavaScript::quote($entry[0]);
	$count=sprintf('%5d',$entry[1]);
	$min=sprintf('%0.5f',$entry[2]);
	$max=sprintf('%0.5f',$entry[3]);
	$total=sprintf('%0.5f',$entry[4]);
	$average=sprintf('%0.5f',$entry[4]/$entry[1]);
	echo "\tconsole.log(\" $count  $total  $average  $min  $max    {$proc}\");\n";
}
?>
	console.groupEnd();
}
/*]]>*/
</script>
