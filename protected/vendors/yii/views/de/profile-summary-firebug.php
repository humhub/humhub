<script type="text/javascript">
/*<![CDATA[*/
if(typeof(console)=='object')
{
	console.group("Ergebnis der Performance-Analyse");
	console.log(" Anzahl Gesamt   Durschn.   Min      Max   ");
<?php
foreach($data as $index=>$entry)
{
	$proc=CJavaScript::quote($entry[0]);
	$count=sprintf('%5d',$entry[1]); // 5 digits
	$min=sprintf('%0.5f',$entry[2]); // 7 digits
	$max=sprintf('%0.5f',$entry[3]); // 7 digits
	$total=sprintf('%0.5f',$entry[4]); // 7 digits
	$average=sprintf('%0.5f',$entry[4]/$entry[1]);  // 7 digits
	echo "\tconsole.log(\" $count  $total  $average  $min  $max    {$proc}\");\n";

}
?>
	console.groupEnd();
}
/*]]>*/
</script>
