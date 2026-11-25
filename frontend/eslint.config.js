
import js from '@eslint/js';
import globals from 'globals';
import tseslint from 'typescript-eslint';
import reactHooks from 'eslint-plugin-react-hooks';
import reactRefresh from 'eslint-plugin-react-refresh';
import stylistic from '@stylistic/eslint-plugin';

export default tseslint.config(
    {
        ignores: ['dist', 'coverage'],
    },
    {
        files: ['**/*.{js,jsx,ts,tsx}'],
        extends: [
            js.configs.recommended,
            ...tseslint.configs.recommended,
        ],
        languageOptions: {
            ecmaVersion: 'latest',
            globals: globals.browser,
        },
        plugins: {
            'react-hooks': reactHooks,
            'react-refresh': reactRefresh,
            '@stylistic': stylistic,
        },
        rules: {
            ...reactHooks.configs.flat.recommended.rules,

            'react-refresh/only-export-components': [
                'warn',
                {
                    allowConstantExport: true,
                },
            ],

            // Indentation: 4 spaces
            '@stylistic/indent': [
                'error',
                4,
            ],

            // K&R brace style
            '@stylistic/brace-style': [
                'error',
                '1tbs',
                {
                    allowSingleLine: false,
                },
            ],
        },
    },
);
