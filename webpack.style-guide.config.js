const base = require('./webpack.config');

module.exports = {
  ...base,
  entry: './assets/js/modules/DigitalStyleGuide.js',
  output: { ...base.output, filename: 'digital-style-guide-bundled.js' },
  mode: 'production',
  devtool: false,
};
