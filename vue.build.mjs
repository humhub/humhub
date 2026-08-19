/**
 * Zero-config Vue SFC build for HumHub modules.
 *
 * Compiles a module's Vue sources into a committed IIFE artifact
 * <module>/resources/js/humhub.<id>.vue.js (+ sourcemap). Vue and @humhub/vue
 * are externals mapped onto the globals provided by core asset bundles, so
 * nothing is bundled twice. See docs/develop/ui-js-vuejs.md.
 *
 * Entry resolution (no boilerplate entry file needed in the common case):
 *   - Every `.vue` file directly inside `<module>/vue/` is auto-registered
 *     under its filename (`LikeButton.vue` -> register('LikeButton', ...)).
 *     The filename must be PascalCase and its derived kebab-case tag (see
 *     `toTagName` in humhub.vue.js) must contain a dash — this is validated
 *     at build time and the build fails with a clear message otherwise.
 *   - Files inside subdirectories of `vue/` are internal building blocks.
 *     They are bundled only when imported by a top-level component and are
 *     never auto-registered.
 *   - Escape hatch: if `<module>/vue/index.js` exists, it is used verbatim
 *     as the entry (for custom registration logic) and the auto-registration
 *     scan above does not run at all.
 * The generated entry is a virtual module (never written to disk), so
 * sourcemaps stay free of machine-specific temp paths.
 *
 * Dev-mode publish caching: after every successful build (and every rebuild in
 * `--watch` mode), the module's `resources/` and `resources/js/` directories have
 * their mtime bumped to now - see `touchResourcesDirs()` below for why this is needed
 * (Yii's published-asset hash is keyed off the source directory's mtime, not its
 * files' bytes, so a plain artifact rewrite is otherwise served stale forever).
 *
 * Special case: `--module core` builds the core framework itself (shared, platform-wide
 * components — see docs/develop/ui-js-vuejs.md) from `protected/humhub/vue/` into
 * `protected/humhub/resources/js/humhub.core.vue.js`, rather than a module under
 * `protected/humhub/modules/`.
 *
 * Usage:
 *   node vue.build.mjs --module <core | core-module-id | path-to-module> [--watch] [--minify]
 */
import { build } from 'vite';
import vue from '@vitejs/plugin-vue';
import { existsSync, readdirSync, utimesSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = dirname(fileURLToPath(import.meta.url));
const args = process.argv.slice(2);
const getArg = (name) => {
    const index = args.indexOf(name);
    if (index === -1) {
        return null;
    }
    const value = args[index + 1];
    // A missing value or the next flag being mistaken for this one's value
    // (e.g. `--module --watch`) must not silently swallow that flag.
    return value === undefined || value.startsWith('--') ? null : value;
};

const moduleArg = getArg('--module');
if (!moduleArg) {
    console.error('Usage: node vue.build.mjs --module <core | core-module-id | path-to-module> [--watch] [--minify]');
    process.exit(1);
}

// `core` is not a core MODULE (there is no protected/humhub/modules/core/) — it is the core
// framework itself, hosting components shared platform-wide (see docs/develop/ui-js-vuejs.md).
// Special-cased to protected/humhub/ rather than falling through to the generic
// path-or-core-module resolution below.
const isCore = moduleArg === 'core';
const coreModulePath = resolve(root, 'protected/humhub/modules', moduleArg);
const modulePath = isCore
    ? resolve(root, 'protected/humhub')
    : (existsSync(coreModulePath) ? coreModulePath : resolve(moduleArg));
const moduleId = isCore ? 'core' : modulePath.split(/[\\/]/).filter(Boolean).pop();
const vueDir = resolve(modulePath, 'vue');
const indexPath = resolve(vueDir, 'index.js');

// Mirrors toTagName()/NAME_PATTERN in protected/humhub/resources/js/humhub/humhub.vue.js
// so a file that would fail registration at runtime is caught here instead.
const NAME_PATTERN = /^[A-Z][A-Za-z0-9]*$/;
const toTagName = (name) => name
    .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
    .replace(/([A-Z])([A-Z][a-z])/g, '$1-$2')
    .toLowerCase();

// Virtual entry module id (never written to disk — see module header). The
// '\0' prefix is the Rollup convention marking an id as virtual, so no other
// plugin tries to load or transform it as a real file.
const VIRTUAL_ENTRY_ID = '\0virtual:humhub-vue-entry';

function virtualEntryPlugin(code) {
    return {
        name: 'humhub-vue-virtual-entry',
        resolveId(id) {
            return id === VIRTUAL_ENTRY_ID ? VIRTUAL_ENTRY_ID : null;
        },
        load(id) {
            return id === VIRTUAL_ENTRY_ID ? code : null;
        },
    };
}

let entryInput;
let extraPlugins = [];

if (existsSync(indexPath)) {
    entryInput = indexPath;
} else {
    const topLevelVueFiles = existsSync(vueDir)
        ? readdirSync(vueDir, { withFileTypes: true })
            .filter((dirent) => dirent.isFile() && dirent.name.endsWith('.vue'))
            .map((dirent) => dirent.name)
            .sort()
        : [];

    if (topLevelVueFiles.length === 0) {
        console.error(
            `Nothing to build for module "${moduleId}": no vue/index.js and no top-level ` +
            `.vue file in ${vueDir}`,
        );
        process.exit(1);
    }

    const invalid = topLevelVueFiles
        .map((fileName) => ({ fileName, name: fileName.slice(0, -'.vue'.length) }))
        .filter(({ name }) => !NAME_PATTERN.test(name) || !toTagName(name).includes('-'));

    if (invalid.length > 0) {
        console.error(`Invalid top-level Vue component file name(s) in ${vueDir}:`);
        for (const { fileName, name } of invalid) {
            console.error(`  - ${fileName} (registers as "${name}" -> <${toTagName(name)}>)`);
        }
        console.error(
            'Every top-level .vue file in vue/ is auto-registered under its filename, so the ' +
            `name must be PascalCase (${NAME_PATTERN}) and its derived kebab-case tag must ` +
            'contain a dash (e.g. LikeButton.vue -> "LikeButton" -> <like-button>). Move ' +
            'internal-only components into a subdirectory of vue/, or add a vue/index.js to ' +
            'opt out of auto-registration.',
        );
        process.exit(1);
    }

    const entryCode = [
        "import { register } from '@humhub/vue';",
        ...topLevelVueFiles.map((fileName, i) => {
            const name = fileName.slice(0, -'.vue'.length);
            const absPath = resolve(vueDir, fileName);
            return `import C${i} from ${JSON.stringify(absPath)};\nregister(${JSON.stringify(name)}, C${i});`;
        }),
    ].join('\n');

    entryInput = VIRTUAL_ENTRY_ID;
    extraPlugins = [virtualEntryPlugin(entryCode)];
}

/**
 * Root-causes a dev-mode DX trap (see module header): Yii's published-asset hash is
 * derived from the source DIRECTORY's path + mtime, not from the bytes of the files
 * inside it - rebuilding just the artifact FILES never changes the directory's own
 * mtime, so `AssetManager` keeps serving the stale already-published copy forever,
 * even though the on-disk artifact is fresh. Touching the directory's mtime after
 * every successful build makes the next request publish a fresh hash directory
 * automatically. Purely a metadata write - the artifact bytes (and therefore
 * `grunt build-vue`'s byte-reproducibility guarantee) are untouched.
 */
function touchResourcesDirs() {
    const now = new Date();
    for (const dir of [resolve(modulePath, 'resources'), resolve(modulePath, 'resources/js')]) {
        try {
            utimesSync(dir, now, now);
        } catch (error) {
            // Nothing to touch yet (e.g. a from-scratch build whose resources/js the
            // build call below is about to create for the first time) - not fatal.
        }
    }
}

try {
    const result = await build({
        configFile: false,
        root: modulePath,
        logLevel: 'info',
        plugins: [vue(), ...extraPlugins],
        define: {
            // Vue itself is external; this only affects dev-mode branches in
            // compiled component code and small helper imports.
            'process.env.NODE_ENV': JSON.stringify('production'),
        },
        build: {
            outDir: resolve(modulePath, 'resources/js'),
            emptyOutDir: false,
            sourcemap: true,
            minify: args.includes('--minify') ? 'esbuild' : false,
            cssCodeSplit: false,
            watch: args.includes('--watch') ? {} : null,
            lib: {
                // Never actually read: `rollupOptions.input` below takes
                // precedence over `lib.entry` in Vite's own resolution, which
                // is what lets a virtual module id serve as the real entry
                // (Vite would otherwise path-resolve `lib.entry` itself and
                // never call our resolveId hook). Kept as a real, existing
                // path purely so nothing downstream trips over a bogus value.
                entry: vueDir,
                formats: ['iife'],
                name: `humhubVue_${moduleId.replace(/[^A-Za-z0-9_]/g, '_')}`,
                fileName: () => `humhub.${moduleId}.vue.js`,
            },
            rollupOptions: {
                input: entryInput,
                external: ['vue', '@humhub/vue'],
                output: {
                    // `/*!` marks a "legal comment", which survives --minify
                    // (esbuild) and the core uglify pipeline. Deliberately no
                    // timestamp — the artifact must stay byte-reproducible.
                    banner: [
                        '/*!',
                        ' * AUTO-GENERATED FILE — do not edit.',
                        ` * Compiled from ${moduleId}/vue/ via \`grunt build-vue --module=${moduleId}\`.`,
                        ' * See docs/develop/ui-js-vuejs.md',
                        ' */',
                    ].join('\n'),
                    globals: {
                        'vue': 'Vue',
                        '@humhub/vue': 'humhub.modules.vue',
                    },
                    assetFileNames: `humhub.${moduleId}.vue.[ext]`,
                },
            },
        },
    });

    if (args.includes('--watch')) {
        // In watch mode `build()` resolves immediately with a RollupWatcher, well before
        // the first bundle is even written - 'event' with code 'END' fires once per
        // completed build (the initial one AND every rebuild on save), which is exactly
        // when a republish should happen. See touchResourcesDirs()'s own docblock for why
        // this matters in watch mode just as much as (arguably more than) a one-shot build.
        result.on('event', (event) => {
            if (event.code === 'END') {
                touchResourcesDirs();
            }
        });
    } else {
        touchResourcesDirs();
    }
} catch (error) {
    console.error(error.message);
    process.exit(1);
}
