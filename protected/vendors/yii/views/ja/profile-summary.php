<!-- start profiling summary -->
<table class="yiiLog" width="100%" cellpadding="2" style="border-spacing:1px;font:11px VVerdana, 'メイリオ', Meiryo, 'ＭＳ Ｐゴシック', 'ヒラギノ角ゴ Pro W3', 'Hiragino Kaku Gothic ProN', sans-serif;background:#EEEEEE;color:#666666;">
	<tr>
		<th style="background:black;color:white;" colspan="6">
			プロファイリング概要レポート
			(時間: <?php echo sprintf('%0.5f',Yii::getLogger()->getExecutionTime()); ?>s,
			メモリ: <?php echo number_format(Yii::getLogger()->getMemoryUsage()/1024); ?>KB)
		</th>
	</tr>
	<tr style="background-color: #ccc;">
	    <th>プロシージャ</th>
		<th>回数</th>
		<th>合計 (s)</th>
		<th>平均 (s)</th>
		<th>最小 (s)</th>
		<th>最大 (s)</th>
	</tr>
<?php
foreach($data as $index=>$entry)
{
	$color=($index%2)?'#F5F5F5':'#FFFFFF';
	$proc=CHtml::encode($entry[0]);
	$min=sprintf('%0.5f',$entry[2]);
	$max=sprintf('%0.5f',$entry[3]);
	$total=sprintf('%0.5f',$entry[4]);
	$average=sprintf('%0.5f',$entry[4]/$entry[1]);

	echo <<<EOD
	<tr style="background:{$color}">
		<td>{$proc}</td>
		<td align="center">{$entry[1]}</td>
		<td align="center">{$total}</td>
		<td align="center">{$average}</td>
		<td align="center">{$min}</td>
		<td align="center">{$max}</td>
	</tr>
EOD;
}
?>
</table>
<!-- end of profiling summary -->