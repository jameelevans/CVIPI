const base = require('./webpack.config');

module.exports = {
  ...base,
  entry: './assets/js/modules/TtaCalendar.js',
  output: { ...base.output, filename: 'tta-calendar-bundled.js' },
  mode: 'production',
  devtool: false,
};
