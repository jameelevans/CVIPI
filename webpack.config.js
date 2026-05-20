const path = require('path');

// Webpack compiles the theme's browser JavaScript entry file into the bundled
// file enqueued by functions.php. Keep feature code in assets/js/scripts.js or
// modules, not directly in scripts-bundled.js.
module.exports = {
  // Main JavaScript entry point for custom theme behavior.
  entry: './assets/js/scripts.js',
  output: {
    // WordPress loads the bundled file from the theme's assets/js directory.
    path: path.resolve(__dirname, './assets/js'),
    filename: 'scripts-bundled.js'
  },
  // Development mode keeps the bundle readable while this theme is in active buildout.
  mode: 'development'
}
