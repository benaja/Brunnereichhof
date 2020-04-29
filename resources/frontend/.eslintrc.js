module.exports = {
  root: true,
  env: {
    node: true
  },
  plugins: [
    'vuetify',
    'vue'
  ],
  'extends': [
    'plugin:vue/recommended',
    'airbnb-base'
  ],
  rules: {
    'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
    'space-before-function-paren': 'off',
    'vuetify/no-deprecated-classes': 'error',
    'linebreak-style': ['error', 'windows'],
    "vue/html-self-closing": ["error", {
      "html": {
        "void": "any",
        "normal": "any",
        "component": "any"
      },
      "svg": "always",
      "math": "always"
    }],
    'import/no-unresolved': 0,
    'no-param-reassign': 0
  },
  parserOptions: {
    parser: 'babel-eslint'
  },
}
