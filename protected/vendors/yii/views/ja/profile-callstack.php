<!-- start profiling callstack -->
<table class="yiiLog" width="100%" cellpadding="2" style="border-spacing:1px;font:11px Verdana, 'メイリオ', Meiryo, 'ＭＳ Ｐゴシック', 'ヒラギノ角ゴ Pro W3', 'Hiragino Kaku Gothic ProN', sans-serif; background:#EEEEEE;color:#666666;">
	<tr>
		<th style="background:black;color:white;" colspan="2">
			プロファイリング コールスタック レポート
		</th>
	</tr>
	<tr style="background-color: #ccc;">
	    <th>プロシージャ</th>
		<th>時間 (s)</th>
	</tr>
<?php
foreach($data as $index=>$entry)
{
	$color=($index%2)?'#F5F5F5':'#FFFFFF';
	list($proc,$time,$level)=$entry;
	$proc=CHtml::encode($proc);
	$time=sprintf('%0.5f',$time);
	$spaces=str_repeat('&nbsp;',$level*8);

	echo <<<EOD
	<tr style="background:{$color}">
		<td>{$spaces}{$proc}</td>
		<td align="center">{$time}</td>
	</tr>
EOD;
}
?>
</table>
<!-- end of profiling callstack -->