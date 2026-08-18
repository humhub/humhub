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
 * Usage:
 *   node vue.build.mjs --module <core-module-id | path-to-module> [--watch] [--minify]
 */
import { build } from 'vite';
import vue from '@vitejs/plugin-vue';
import { existsSync, readdirSync } from 'node:fs';
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
    console.error('Usage: node vue.build.mjs --module <core-module-id | path-to-module> [--watch] [--minify]');
    process.exit(1);
}

const coreModulePath = resolve(root, 'protected/humhub/modules', moduleArg);
const modulePath = existsSync(coreModulePath) ? coreModulePath : resolve(moduleArg);
const moduleId = modulePath.split(/[\\/]/).filter(Boolean).pop();
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

try {
    await build({
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
} catch (error) {
    console.error(error.message);
    process.exit(1);
}
