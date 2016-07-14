<?php
/* Copyright (c)
 * - 2013-2015, Geert Bergman (geert@scrivo.nl), highlight.php
 * - 2014,      Daniel Lynge, highlight.php (contributor)
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. Neither the name of "highlight.js", "highlight.php", nor the names of its
 *    contributors may be used to endorse or promote products derived from this
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */
$start = microtime(true);

require_once("../Highlight/Autoloader.php");
spl_autoload_register("Highlight\\Autoloader::load");

$hl = new Highlight\Highlighter();
$hl->setAutodetectLanguages($hl->listLanguages());

?>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="../styles/default.css">
    <script src="highlight.pack.js"></script>
    <script>
        
function testDetection() {

    var table = document.getElementById('test');
    var rws = table.getElementsByTagName('TR');

    for (var i = 1; i < rws.length; i++) {
        var tds = rws[i].getElementsByTagName('TD');

        var p1a = tds[1].getElementsByTagName('P')[0]; 
        var p1b = tds[1].getElementsByTagName('P')[1]; 
        var p2a = tds[2].getElementsByTagName('P')[0];
        var p2b = tds[2].getElementsByTagName('P')[1];
        
        var code1 = tds[1].getElementsByTagName('DIV')[0]; 
        var code2 = tds[2].getElementsByTagName('CODE')[0];

        p2a.innerHTML = code2.result.language +    ": " + code2.result.re;
        p2b.innerHTML = code2.second_best.language +    ": " + 
            code2.second_best.re;
        
        if (code1.innerHTML != code2.innerHTML
                 || p1a.innerHTML != p2a.innerHTML
//                 || p1b.innerHTML != p2b.innerHTML
        ) {
            console.log(code1.innerHTML);
            console.log(code2.innerHTML);

            rws[i].style.backgroundColor = "#ffcccc"; 
            tds[0].style.backgroundColor = "#ffcccc";
            tds[0].lastChild.innerHTML = "failed"; 
        }
    }
}

hljs.tabReplace = '    ';
hljs.initHighlightingOnLoad();

window.addEventListener("load", testDetection );  // capture phase  

    </script>
    
    <style type="text/css">

table { font-family: sans-serif; }
table { border-spacing: 0px; border-collapse:collapse; width: 100%}
th, td { border: solid grey 1px; overflow: auto; max-width:500px}
td p { margin: 0px; }
pre code, pre div { padding: 0.5em; background: #F0F0F0; }
td.signal { padding: 0.5em; background-color: #ccffcc; }
pre { margin: 0px; }

    </style>
    
  </head>
  <body>
    <table id="test">
    <tr>
      <th>result</th>
      <th>highlight.php</th>
      <th>highlight.js</th>
    </tr>
<?php
foreach ($hl->listLanguages() as $languageId) {
  $snippet = file_get_contents("../test/detect/{$languageId}/default.txt");
  $r = $hl->highlightAuto($snippet);
?>
    <tr>
      <td class="signal"><p><?=$r->language?></p><p>pass</p></td>
      <td>
        <p><?=$r->language?>: <?=$r->relevance?></p>
        <p><?=$r->secondBest->language?>: <?=$r->secondBest->relevance?></p>
        <pre><div class=" hljs <?=$r->language?>"><?= $r->value?></div></pre>
      </td>
      <td class="js">
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <pre><code><?=htmlentities($snippet)?></code></pre>
      </td>
    </tr>
<?php
} 
?>  
  </body>
</html>
<?php 
error_log("Highlighting took ".(microtime(true)-$start)." seconds");
?>