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
        },
    },
    test: {
        environment: 'jsdom',
        include: ['protected/humhub/tests/js/**/*.test.js'],
        setupFiles: ['./protected/humhub/tests/js/support/setup.mjs'],
    },
});
