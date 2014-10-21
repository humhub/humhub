<!DOCTYPE html PUBLIC
	"-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>400 不正リクエスト</title>
<style type="text/css">
/*<![CDATA[*/
body {font-family:Verdana, 'メイリオ', Meiryo, 'ＭＳ Ｐゴシック', 'ヒラギノ角ゴ Pro W3', 'Hiragino Kaku Gothic ProN', sans-serif; font-weight:normal; color:black; background-color:white;}
h1 {font-weight:normal;font-size:18pt;color:red }
h2 {font-weight:normal;font-size:14pt;color:maroon }
h3 {font-weight:bold;font-size:11pt}
p {font-weight:normal;color:black;font-size:9pt;margin-top: -5px}
.version {color: gray;font-size:8pt;border-top:1px solid #aaaaaa;}
/*]]>*/
</style>
</head>
<body>
<h1>400 不正リクエスト</h1>
<h2><?php echo nl2br(CHtml::encode($data['message'])); ?></h2>
<p>
不正な形式の文法であるために、サーバはリクエストを処理できませんでした。
文法を修正しないうちは同じリクエストを繰り返さないでください。
</p>
<p>
これがサーバの不具合であると思われる場合は、<?php echo $data['admin']; ?>にご連絡ください。
</p>
<div class="version">
<?php echo date('Y-m-d H:i:s',$data['time']) .' '. $data['version']; ?>
</div>
</body>
</html>