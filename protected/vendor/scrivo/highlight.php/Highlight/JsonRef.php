<?php
/* Copyright (c)
 * - 2014,      Geert Bergman (geert@scrivo.nl), highlight.php
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
 * Implementation of the \Highlight\JsonRef class.
 */

namespace Highlight;

/**
 * Class to decode JSON data that contains path-based references.
 *
 * The language data file for highlight.js are written as JavaScript classes
 * and therefore may contain variables. This allows for inner references in
 * the language data. This kind of data can be converterd to JSON using the
 * path based references. This class can be used to decode such JSON
 * structures. It follows the conventions for path based referencing as
 * used in dojox.json.ref form the Dojo toolkit (Javascript). A typical
 * example of such a structure is as follows:
 *
 * {
 *   "name":"Kris Zyp",
 *   "children":[{"name":"Jennika Zyp"},{"name":"Korban Zyp"}],
 *   "spouse":{
 *     "name":"Nicole Zyp",
 *     "spouse":{"$ref":"#"},
 *     "children":{"$ref":"#children"}
 *   },
 *   "oldestChild":{"$ref":"#children.0"}
 * }
 *
 * Usage example:
 *
 * $jr = new JsonRef();
 * $data = $jr->decode(file_get_contents("data.json"));
 * echo $data->spouse->spouse->name; // echos 'Kris Zyp'
 * echo $data->oldestChild->name; // echos 'Jennika Zyp'
 *
 */
class JsonRef
{
    /**
     * Array to hold all data paths in the given JSON data.
     * @var array
     */
    private $paths = null;

    /**
     * Recurse through the data tree and fill an array of paths that reference
     * the nodes in the decoded JSON data structure.
     *
     * @param mixed $s
     *     Decoded JSON data (decoded with json_decode).
     * @param string $r
     *     The current path key (for example: '#children.0').
     */
    private function getPaths(&$s, $r="#")
    {
        $this->paths[$r] = &$s;
        if (is_array($s) || is_object($s)) {
            foreach ($s as $k => &$v) {
                if ($k !== "\$ref") {
                    $this->getPaths($v, $r=="#"?"#{$k}":"{$r}.{$k}");
                }
            }
        }
    }

    /**
     * Recurse through the data tree and resolve all path references.
     *
     * @param mixed $s
     *     Decoded JSON data (decoded with json_decode).
     */
    private function resolvePathReferences(&$s)
    {
        if (is_array($s) || is_object($s)) {
            foreach ($s as $k => &$v) {
                if ($k === "\$ref") {
                    $s = $this->paths[$v];
                } else {
                    $this->resolvePathReferences($v);
                }
            }
        }
    }

    /**
     * Decode JSON data that may contain path based references.
     *
     * @param string|object $json
     *     JSON data string or JSON data object.
     * @return mixed
     *     The decoded JSON data.
     */
    public function decode($json)
    {
        // Clear the path array.
        $this->paths = array();
        // Decode the given JSON data if necessary.
        $x = is_string($json) ? json_decode($json) : $json;
        // Get all data paths.
        $this->getPaths($x);
        // Resolve all path references.
        $this->resolvePathReferences($x);
        // Return the data.
        return $x;
    }
}
