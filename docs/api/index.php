<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Landing page of the HTTP API reference, reachable at `/docs/api/` on any installation.
 *
 * Lists the rendered documents next to this file (see `build.sh`), so a new API document shows
 * up here by being rendered — no list to maintain. Titles come from each document's own
 * `<title>`, i.e. from the `info.title` of its OpenAPI source.
 *
 * Deliberately standalone: no HumHub bootstrap, no database, no assets. It has to work on an
 * installation that is not set up yet, and it must not become one more thing that breaks when
 * the application does.
 */

$documents = [];

foreach (glob(__DIR__ . '/*.html') ?: [] as $path) {
    $file = basename($path);
    $name = basename($file, '.html');

    // The document's own <title>, e.g. "HumHub - Comment API (v2)". Read from the head only:
    // a rendered reference is a megabyte of markup, and the title is in the first few hundred
    // bytes of it.
    $head = (string)file_get_contents($path, false, null, 0, 4096);
    $title = preg_match('/<title>(.*?)<\/title>/is', $head, $match) === 1
        ? html_entity_decode(trim($match[1]), ENT_QUOTES | ENT_HTML5)
        : ucfirst($name);

    $documents[$name] = [
        'file' => $file,
        'title' => $title,
        'source' => is_file(__DIR__ . '/src/' . $name . '.yaml') ? 'src/' . $name . '.yaml' : null,
    ];
}

ksort($documents);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>HumHub HTTP API reference</title>
    <style>
        :root { color-scheme: light dark; }
        body {
            margin: 0 auto;
            padding: 2.5rem 1.25rem 4rem;
            max-width: 46rem;
            font: 16px/1.6 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        h1 { font-size: 1.6rem; margin: 0 0 .25rem; }
        p.lead { margin: 0 0 2rem; opacity: .75; }
        ul { list-style: none; margin: 0; padding: 0; }
        li { padding: .9rem .25rem; border-top: 1px solid rgba(128, 128, 128, .35); }
        li:last-child { border-bottom: 1px solid rgba(128, 128, 128, .35); }
        a.doc { display: inline-block; text-decoration: none; font-weight: 600; }
        a.doc:hover, a.doc:focus { text-decoration: underline; }
        a.src { display: block; margin-top: .15rem; font-size: .875rem; opacity: .7; }
        footer { margin-top: 2.5rem; font-size: .875rem; opacity: .75; }
        code { font-size: .9em; }
    </style>
</head>
<body>
    <h1>HumHub HTTP API</h1>
    <p class="lead">
        Endpoint reference for <code>/api/v2</code>, the API this installation serves.
    </p>

    <?php if ($documents === []) : ?>
        <p>
            No rendered documents found. Run <code>docs/api/build.sh</code> to render the
            OpenAPI sources in <code>docs/api/src/</code>.
        </p>
    <?php else : ?>
        <ul>
            <?php foreach ($documents as $document) : ?>
                <li>
                    <a class="doc" href="<?= htmlspecialchars($document['file'], ENT_QUOTES) ?>"
                    ><?= htmlspecialchars($document['title'], ENT_QUOTES) ?></a>
                    <?php if ($document['source'] !== null) : ?>
                        <a class="src" href="<?= htmlspecialchars($document['source'], ENT_QUOTES) ?>"
                        ><?= htmlspecialchars($document['source'], ENT_QUOTES) ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <footer>
        <p>
            The references render with <a href="https://redocly.com/redoc/">Redoc</a>, which the
            pages load from Redocly's CDN — a page stays empty without internet access.
        </p>
        Token authentication for these endpoints comes from the
        <a href="https://github.com/humhub/rest">REST API module</a>, which also documents the
        older <code>/api/v1</code> surface. The design of this stack is described in
        <code>docs/develop/concept-api.md</code>.
    </footer>
</body>
</html>
