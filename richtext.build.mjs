/**
 * Builds the richtext editor (ProseMirror) from protected/humhub/resources/js/richtext/ into the
 * committed artifact protected/humhub/resources/js/humhub.richtext.editor.js, which
 * `humhub\assets\ProsemirrorEditorAsset` publishes. See docs/develop/internals-richtext-editor.md.
 *
 *   node richtext.build.mjs [--watch] [--minify]
 *
 * The artifact is a self-executing script that exports the editor as `window.prosemirror`
 * and the `ui.richtext` module; jQuery and the HumHub client are the page's globals.
 */
import { build } from 'vite';
import { utimesSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = dirname(fileURLToPath(import.meta.url));
const resourcesDir = resolve(root, 'protected/humhub/resources');
const outDir = resolve(resourcesDir, 'js');
const sourceDir = resolve(outDir, 'richtext');
const args = process.argv.slice(2);

// Yii keys a published asset directory off its source directory's mtime, not its files'
// bytes: without this the rebuilt artifact is served stale (see vue.build.mjs).
const touchResourcesDirs = () => {
    const now = new Date();
    for (const dir of [resourcesDir, outDir]) {
        utimesSync(dir, now, now);
    }
};

const result = await build({
    configFile: false,
    root: sourceDir,
    logLevel: 'info',
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
    },
    build: {
        outDir,
        emptyOutDir: false,
        sourcemap: false,
        minify: args.includes('--minify') ? 'esbuild' : false,
        watch: args.includes('--watch') ? {} : null,
        // A source file written as CommonJS (core/plugins/image/markdownit_imsize.js)
        commonjsOptions: {
            include: [/node_modules/, /protected\/humhub\/resources\/js\/richtext\//],
        },
        lib: {
            entry: resolve(sourceDir, 'index.js'),
            formats: ['iife'],
            name: 'humhubRichtextEditor',
            fileName: () => 'humhub.richtext.editor.js',
        },
        rollupOptions: {
            output: {
                // A "legal comment", kept by --minify and the core uglify pipeline. No
                // timestamp: the artifact must stay byte-reproducible.
                banner: [
                    '/*!',
                    ' * AUTO-GENERATED FILE — do not edit.',
                    ' * Compiled from protected/humhub/resources/js/richtext/ via `npm run build:richtext`.',
                    ' * See docs/develop/internals-richtext-editor.md',
                    ' */',
                ].join('\n'),
            },
        },
    },
});

if (args.includes('--watch')) {
    result.on('event', (event) => {
        if (event.code === 'END') {
            touchResourcesDirs();
        }
    });
} else {
    touchResourcesDirs();
}
