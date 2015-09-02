<!-- start log messages -->
<table width="100%" cellpadding="2" style="border-spacing:1px;font:11px Verdana, Arial, Helvetica, sans-serif;background:#EEEEEE;color:#666666;">
	<tr>
		<th style="background:black;color:white;" colspan="5">
			Дневник на приложението
		</th>
	</tr>
	<tr style="background-color: #ccc;">
	  <th style="width:120px">Време</th>
		<th>Ниво</th>
		<th>Категория</th>
		<th>Съобщение</th>
	</tr>
<?php
foreach($data as $index=>$log)
{
	$color=($index%2)?'#F5F5F5':'#EBF8FE';
	$message='<pre>'.CHtml::encode(wordwrap($log[0])).'</pre>';
	$time=date('H:i:s.',$log[3]).(int)(($log[3]-(int)$log[3])*1000000);

	echo <<<EOD
	<tr style="background:{$color}">
		<td align="center">{$time}</td>
		<td>{$log[1]}</td>
		<td>{$log[2]}</td>
		<td>{$message}</td>
	</tr>
EOD;
}
?>
</table>
<!-- end of log messages -->
