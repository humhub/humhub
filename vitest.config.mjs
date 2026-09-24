import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'node:url';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            // Mirrors the production build, where `@humhub/vue` is external and
            // mapped onto the humhub.modules.vue global (see vue.build.mjs).
            '@humhub/vue': fileURLToPath(new URL('./protected/humhub/tests/js/support/humhubVueShim.mjs', import.meta.url)),
            // CSP forbids the template compiler in production (see
            // docs/develop/ui-js-vuejs.md, "Constraints") — test against the
            // same runtime-only build so a template accidentally sneaking into
            // a test component fails here instead of only in the browser.
            vue: 'vue/dist/vue.runtime.esm-bundler.js',
        },
    },
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
    },
    test: {
        environment: 'jsdom',
        include: ['protected/humhub/tests/js/**/*.test.js'],
        setupFiles: ['./protected/humhub/tests/js/support/setup.mjs'],
    },
});
