import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import { createExtensionConfig } from '../reno-cms/tools/vite/createExtensionConfig.mjs';

const packageDirectory = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig(
    createExtensionConfig({
        packageDirectory,
        base: '/js/reno/cms-user-settings/build/',
        entryDefinitions: [
            {
                type: 'file',
                name: 'components/user-settings/UserSettingPage',
                relativePath: 'components/user-settings/UserSettingPage.vue',
            },
        ],
        externalizeCmsRuntime: true,
    }),
);
