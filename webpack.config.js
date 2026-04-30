const path = require('path');

module.exports = {
  entry: './assets/js/scripts.js',
  output: {
    path: path.resolve(__dirname, './assets/js'),
    filename: 'scripts-bundled.js'
  },
  mode: 'development'
}