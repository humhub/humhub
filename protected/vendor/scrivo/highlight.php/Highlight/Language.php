<?php
/* Copyright (c)
 * - 2006-2013, Ivan Sagalaev (maniacsoftwaremaniacs.org), highlight.js
 *              (original author)
 * - 2013-2015, Geert Bergman (geertscrivo.nl), highlight.php
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

namespace Highlight;

class Language
{
    public $caseInsensitive = false;
    public $aliases = null;

    public function complete(&$e)
    {
        if (!isset($e)) {
            $e = new \stdClass();
        }
        
        $patch = array(
            "begin" => true,
            "end" => true,
            "lexemes" => true,
            "illegal" => true,
        );

        $def = array(
            "begin" => "",
            "beginRe" => "",
            "beginKeywords" => "",
            "excludeBegin" => "",
            "returnBegin" => "",
            "end" => "",
            "endRe" => "",
            "endsParent" => "",
            "endsWithParent" => "",
            "excludeEnd" => "",
            "returnEnd" => "",
            "starts" => "",
            "terminators" => "",
            "terminatorEnd" => "",
            "lexemes" => "",
            "lexemesRe" => "",
            "illegal" => "",
            "illegalRe" => "",
            "className" => "",
            "contains" => array(),
            "keywords" => null,
            "subLanguage" => null,
            "subLanguageMode" => "",
            "compiled" => false,
            "relevance" => 1);

        foreach ($patch as $k =>  $v) {
            if (isset($e->$k)) {
                $e->$k = str_replace("\\/", "/", $e->$k);
                $e->$k = str_replace("/", "\\/", $e->$k);
            }
        }

        foreach ($def as $k =>  $v) {
            if (!isset($e->$k)) {
                @$e->$k = $v;
            }
        }
    }

    public function __construct($lang)
    {
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "languages" .
            DIRECTORY_SEPARATOR . "{$lang}.json");
        $this->mode = json_decode($json);

        $this->name = $lang;
        $this->aliases =
            isset($this->mode->aliases) ? $this->mode->aliases : null;

        $this->caseInsensitive = isset($this->mode->case_insensitive) ?
            $this->mode->case_insensitive : false;
    }

    private function langRe($value, $global=false)
    {
        return "/{$value}/um" . ($this->caseInsensitive ? "i" : "");
    }

    private function processKeyWords($kw)
    {
        if (is_string($kw)) {
            if ($this->caseInsensitive) {
                $kw = mb_strtolower($kw, "UTF-8");
            }
            $kw = array("keyword" => explode(" ", $kw));
        } else {
            foreach ($kw as $cls=>$vl) {
                if (!is_array($vl)) {
                    if ($this->caseInsensitive) {
                        $vl = mb_strtolower($vl, "UTF-8");
                    }
                    $kw->$cls = explode(" ", $vl);
                }
            }
        }
        return $kw;
    }

    private function compileMode($mode, $parent=null)
    {
        if (isset($mode->compiled)) {
            return;
        }
        $this->complete($mode);
        $mode->compiled = true;

        $mode->keywords =
            $mode->keywords ? $mode->keywords : $mode->beginKeywords;

        /* Note: JsonRef method creates different references as those in the
         * original source files. Two modes may refer to the same keywors
         * set, so only testing if the mode has keywords is not enough: the
         * mode's keywords might be compiled already, so it is necessary
         * to do an 'is_array' check.
         */
        if ($mode->keywords && !is_array($mode->keywords)) {

            $compiledKeywords = array();

            $mode->lexemesRe = $this->langRe($mode->lexemes
                    ? $mode->lexemes : "\b\w+\b", true);

            foreach ($this->processKeyWords($mode->keywords) as $clsNm => $dat) {
                if (!is_array($dat)) {
                    $dat = array($dat);
                }
                foreach ($dat as $kw) {
                    $pair = explode("|", $kw);
                    $compiledKeywords[$pair[0]] =
                        array($clsNm, isset($pair[1]) ? intval($pair[1]) : 1);
                }
            }
            $mode->keywords = $compiledKeywords;
        }

        if ($parent) {
            if ($mode->beginKeywords) {
                $mode->begin = "\\b(" .
                    implode("|",explode(" ", $mode->beginKeywords)) . ")\\b";
            }
            if (!$mode->begin) {
                $mode->begin = "\B|\b";
            }
            $mode->beginRe = $this->langRe($mode->begin);
            if (!$mode->end && !$mode->endsWithParent) {
                $mode->end = "\B|\b";
            }
            if ($mode->end) {
                $mode->endRe = $this->langRe($mode->end);
            }
            $mode->terminatorEnd = $mode->end;
            if ($mode->endsWithParent && $parent->terminatorEnd) {
                $mode->terminatorEnd .=
                    ($mode->end ? "|" : "") . $parent->terminatorEnd;
            }
        }

        if ($mode->illegal) {
            $mode->illegalRe = $this->langRe($mode->illegal);
        }

        $expanded_contains = array();
        for ($i=0; $i<count($mode->contains); $i++) {
            if (isset($mode->contains[$i]->variants)) {
                foreach ($mode->contains[$i]->variants as $v) {
                    $x = (object)((array)$v + (array)$mode->contains[$i]);
                    unset($x->variants);
                    $expanded_contains[] = $x;
                }
            } else {
                $expanded_contains[] = "self" === $mode->contains[$i] ?
                    $mode : $mode->contains[$i];
            }
        }
        $mode->contains = $expanded_contains;

        for ($i=0; $i<count($mode->contains); $i++) {
            $this->compileMode($mode->contains[$i], $mode);
        }

        if ($mode->starts) {
            $this->compileMode($mode->starts, $parent);
        }

        $terminators = array();

        for ($i=0; $i<count($mode->contains); $i++) {
            $terminators[] = $mode->contains[$i]->beginKeywords
                ? "\.?(" . $mode->contains[$i]->begin . ")\.?"
                : $mode->contains[$i]->begin;
        }
        if ($mode->terminatorEnd) {
            $terminators[] = $mode->terminatorEnd;
        }
        if ($mode->illegal) {
            $terminators[] = $mode->illegal;
        }
        $mode->terminators = count($terminators)
            ? $this->langRe(implode("|", $terminators), true) : null;
    }

    public function compile()
    {
        if (!isset($this->mode->compiled)) {
            $jr = new JsonRef();
            $this->mode = $jr->decode($this->mode);
            $this->compileMode($this->mode);
        }
    }
}
