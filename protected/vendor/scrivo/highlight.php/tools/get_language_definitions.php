<?php 
/* Copyright (c)
 * - 2013-2014, Geert Bergman (geert@scrivo.nl), highlight.php
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

/**
 * Extract language definitions (JSON strings) from the large file that was
 * created using 'get_language_definitions.php' and create a JSON file for 
 * each language.
 */

$f = file("languages.dat");

$patches = Array(
    // Somehwere somehow the first percentage sign was lost
    "dos" => Array(Array("\"%[^ ]", "\"%%[^ ]")),
    // WTF, any ideas anyone?
    "mercury" => Array(Array("\\\\\\\/", "\\\\\\\\\\\/")),
    // The expression [^] is not allowed in PREG
    "lisp" => Array(Array("[^]", "[^|]")),
    // Just being plain lazy
    "xml" => Array(Array("subLanguage\":\"\"", "subLanguage\":\"javascript\"")),
    
);

for ($i=0; $i<count($f); $i+=2) {
    if (isset($f[$i+1])) {

        $fl = trim($f[$i]);
        $json = $f[$i+1];

        if (!$fl) {
            die("ERROR: No language name on line ".($i+1).".<br />\n");
        }
        if (!@json_decode($json)) {
            die("ERROR: invalid JSON data on line ".($i+2).".<br />\n");
        }

        if (isset($patches[$fl])) {
            foreach ($patches[$fl] as $patch) {
                $json = str_replace($patch[0], $patch[1], $json);
                echo "{$patch[0]}, {$patch[1]}\n{$json}";
            }
        }

        echo "Creating language file '{$fl}.json'.<br />\n";
        if (!file_put_contents("../Highlight/languages/{$fl}.json", $json)) {
            die("ERROR: Couldn't write to file.<br />\n");;
        }
    }
}
