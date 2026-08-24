import { defineConfig } from '@rspack/cli';

import AskyrBuild from '@askyr/build';

export default defineConfig({
    entry: {
        'app-script': './domain-mapping/ui/index.js',
        'app-style': './domain-mapping/ui/App.css',
    },
    plugins: [
        new AskyrBuild( {
            type: 'wordpress',
            assetMeta: {
                'app-script': {
                    'in_footer': true,
                },
            },
        } ),
    ],
});
