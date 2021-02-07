module.exports = {
  root: true,

  env: {
    node: true
  },

  plugins: [
    'vuetify',
    'vue'
  ],

  extends: [
    'plugin:vue/recommended',
    'airbnb-base'
  ],

  rules: {
    'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    'space-before-function-paren': 'off',
    'vuetify/no-deprecated-classes': 'error',
    'linebreak-style': 'off',
    'vue/html-self-closing': ['error', {
      html: {
        void: 'any',
        normal: 'any',
        component: 'any'
      },
      svg: 'always',
      math: 'always'
    }],
    'import/no-unresolved': 0,
    'no-param-reassign': 0,
    'no-plusplus': 0,
    'no-restricted-syntax': 0,
    semi: ['error', 'never'],
    'func-names': 0,
    'guard-for-in': 0,
    'no-restricted-globals': ['error', 'event', 'fdescribe'],
    'comma-dangle': ['error', 'never'],
    'prefer-destructuring': ['error', { object: true, array: false }],
    'arrow-parens': ['error', 'as-needed'],
    "import/extensions": ['error', "never" ],
    'prefer-default-export': 0
  },

  parserOptions: {
    parser: 'babel-eslint'
  },

  overrides: [
    {
      files: [
        '**/__tests__/*.{j,t}s?(x)',
        '**/tests/unit/**/*.spec.{j,t}s?(x)'
      ],
      env: {
        jest: true
      }
    }
  ]
}
