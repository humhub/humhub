import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import {dirname, resolve} from 'path'

const entryPath = process.argv[process.argv.indexOf('entry') + 1]
const distPath = process.argv[process.argv.indexOf('dist') + 1]

if (!entryPath) {
    console.error('Error: Please provide entry path')
    process.exit(1)
}

if (!distPath) {
    console.error('Error: Please provide dist path')
    process.exit(1)
}

const entry = resolve(entryPath)
const entryDir = dirname(entry)
const outDir = dirname(distPath)

export default defineConfig({
    plugins: [vue()],
    root: entryDir,

    build: {
        outDir,
        emptyOutDir: true,
        assetsDir: '',
        rollupOptions: {
            input: entry,
            output: {
                entryFileNames: 'entry.js'
            }
        },
    },

    resolve: {
        alias: {
            '@': resolve(entryDir, '.'),
            '~': entryDir,
        },
    },

    configFile: false
})
