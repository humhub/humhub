/**
 * Zero-config Vue SFC build for HumHub modules.
 *
 * Compiles <module>/resources/vue/index.js into a committed IIFE artifact
 * <module>/resources/js/humhub.<id>.vue.js (+ sourcemap). Vue and @humhub/vue
 * are externals mapped onto the globals provided by core asset bundles, so
 * nothing is bundled twice. See docs/develop/ui-js-vuejs.md.
 *
 * Usage:
 *   node vue.build.mjs --module <core-module-id | path-to-module> [--watch] [--minify]
 */
import { build } from 'vite';
import vue from '@vitejs/plugin-vue';
import { existsSync } from 'node:fs';
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
const entry = resolve(modulePath, 'resources/vue/index.js');

if (!existsSync(entry)) {
    console.error(`Entry not found: ${entry}`);
    process.exit(1);
}

try {
    await build({
        configFile: false,
        root: modulePath,
        logLevel: 'info',
        plugins: [vue()],
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
                entry,
                formats: ['iife'],
                name: `humhubVue_${moduleId.replace(/[^A-Za-z0-9_]/g, '_')}`,
                fileName: () => `humhub.${moduleId}.vue.js`,
            },
            rollupOptions: {
                external: ['vue', '@humhub/vue'],
                output: {
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
