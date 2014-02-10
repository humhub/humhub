<!-- start log messages -->
<table class="yiiLog" width="100%" cellpadding="2" style="border-spacing:1px;font:11px Verdana, Arial, Helvetica, sans-serif;background:#EEEEEE;color:#666666;">
	<tr>
		<th style="background:black;color:white;" colspan="5">
			Log aplicatie
		</th>
	</tr>
	<tr style="background-color: #ccc;">
	    <th style="width:120px">Timestamp</th>
		<th>Nivel</th>
		<th>Categorie</th>
		<th>Mesaj</th>
	</tr>
<?php
$colors=array(
	CLogger::LEVEL_PROFILE=>'#DFFFE0',
	CLogger::LEVEL_INFO=>'#FFFFDF',
	CLogger::LEVEL_WARNING=>'#FFDFE5',
	CLogger::LEVEL_ERROR=>'#FFC0CB',
);
foreach($data as $index=>$log)
{
	$color=($index%2)?'#F5F5F5':'#FFFFFF';
	if(isset($colors[$log[1]]))
		$color=$colors[$log[1]];
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