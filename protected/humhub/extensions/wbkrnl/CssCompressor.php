<?php
/*
 * Copyright (c) 2012, "Klaas Sangers"<klaas@webkernel.nl>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this 
 * list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, 
 * this list of conditions and the following disclaimer in the documentation 
 * and/or other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF 
 * THE POSSIBILITY OF SUCH DAMAGE.
 */

class CssCompressor {

	/**
	 * @param string $inflatedCss
	 * @return string $deflatedCss
	 */
	public static final function deflate($inflatedCss) {
		if (!is_string($inflatedCss))
			trigger_error(__METHOD__ . "() - input is not a string");
		$o = "";
		$isComment = false;
		foreach (explode("\n", $inflatedCss) as $l) {
			$commentStart = strpos($l, "/*");
			if (!$isComment && $commentStart !== false && strpos($l, "*/") !== false)
				$l = preg_replace("/\/\*.*\*\//", "", $l);
			if (!$isComment && $commentStart !== false && ($pos = strpos($l, "/*")) !== false) {
				$isComment = true;
				$l = substr($l, 0, $pos);
			} elseif ($isComment && ($pos = strpos($l, "*/")) !== false) {
				$isComment = false;
				$l = substr($l, $pos + 2);
			} elseif ($isComment) {
				continue;
			}
			$o .= preg_replace("/\s*(;|:|}|{|,)\s*/", "\\1", trim(str_replace("\t", " ", $l)));
		}
		return $o;
	}

}
