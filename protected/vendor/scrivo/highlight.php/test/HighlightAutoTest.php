<?php
/* Copyright (c)
 * - 2013-2014, Geert Bergman (geert@scrivo.nl), highlight.php
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

function custom_warning_handler($errno, $errstr, $errfile, $errline, $errcontext) {
      throw new Exception("GB: $errno, $errstr, $errfile, $errline");
}

set_error_handler("custom_warning_handler");

require_once("../Highlight/Autoloader.php");
spl_autoload_register("Highlight\\Autoloader::load");

class HighlightAutoTest extends PHPUnit_Framework_TestCase
{
    public function testAutoDetection() {

        $hl = new Highlight\Highlighter();
        $lngs = $hl->listLanguages();
        $hl->setAutodetectLanguages($lngs);
        $failed = Array();

        foreach($lngs as $language) {

            $path = __DIR__ . DIRECTORY_SEPARATOR . "detect" . 
                DIRECTORY_SEPARATOR . $language;
            $this->assertTrue(file_exists($path));

            $d = dir($path);
            while (false !== ($entry = $d->read())) {
                if ($entry[0] !== ".") {

                    $filePath = $path . DIRECTORY_SEPARATOR . $entry;
                    $content = file_get_contents($filePath);

                    $expected = $language;
                    $r = $hl->highlightAuto($content);
                    $actual = $r->language;

                    if ($expected !== $actual) {
                        $failed[] = "$expected was detected as $actual";
                    }
                }
            }
            $d->close();
        }
    
        $this->assertEquals(Array(), $failed);
    }
}
