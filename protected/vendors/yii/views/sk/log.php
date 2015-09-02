<!-- start log messages -->
<table class="yiiLog" width="100%" cellpadding="2" style="border-spacing:1px;font:11px Verdana, Arial, Helvetica, sans-serif;background:#EEEEEE;color:#666666;">
	<tr>
		<th style="background:black;color:white;" colspan="5">
			Log aplikácie
			<?php 
				$t = round(microtime(true) - YII_BEGIN_TIME, 3);
				$m = function_exists('memory_get_usage') ?  '['.round(memory_get_usage()/1024/1024, 3).' MB]' : '';
				echo " * Stránka vygenerovaná za {$t} sek ~ ".round(1/$t)." strán/sek. ".$m;
			?>
		</th>
	</tr>
	<tr style="background-color: #ccc;">
	    <th style="width:120px">Čas</th>
		<th>Úroveň</th>
		<th>Kategória</th>
		<th>Zpráva</th>
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
	$time .= "<br />[+".round(($log[3]-YII_BEGIN_TIME)*1000, 0).'] ms';

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