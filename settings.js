/** 
 * Settings for project
 *
 * ! What the code does:
 * *    1. Stores all useful file paths
 * *    2. Stores the best Wordpress browsers to prefix
 *      
 *  TODO: Update the urlToPreview path to your projects path
 *  * Everything else can stay the same 
 *
 */

//* Project options
exports.urlToPreview =  'http://cvipi.local/'; // Local project URL of your already running WordPress site. Could be something like wpgulp.local or localhost:3000 depending upon your local WordPress setup.
exports.productURL = './'; // Theme URL. Leave it like it is, since gulpfile.js lives in the root folder.

//* Style options
exports.styleSRC = './assets/css/style.scss'; // Path to main .scss file 
exports.styleMain = './style.css'; // Path to main .scss file
exports.styleDestination = './'; // Path to place the compiled CSS file. Default set to root folder
exports.outputStyle = 'expanded'; // Expanded so that our CSS is readable to wordpress
exports.precision = 10;

//* JS options
exports.jsWebpack = './webpack.config.js'; // Path to Webpack config file 
exports.jsMain = "./assets/js/scripts.js"; // Path to main js file
exports.jsBundled = "./assets/js/scripts-bundled.js"; // Path to bundled js file

//* Images options
exports.imgSRC = './assets/img/raw/**/*'; // Source folder of images which should be optimized and watched
exports.imgDST = './assets/img/'; // Destination folder of optimized images

//* Watch files paths
exports.watchStyles = './assets/css/**/*.scss'; // Path to all *.scss files inside css folder and inside them
exports.watchJsModules = './assets/js/modules/*.js'; // Path to all JS modules
exports.watchJsMain = './assets/js/scripts.js'; // Path to all main JS file
exports.watchPhp = './**/*.php'; // Path to all PHP files

//* Browsers you care about for autoprefixing
exports.BROWSERS_LIST = [
    'last 2 version',
    '> 1%',
    'ie >= 11',
    'last 1 Android versions',
    'last 1 ChromeAndroid versions',
    'last 2 Chrome versions',
    'last 2 Firefox versions',
    'last 2 Safari versions',
    'last 2 iOS versions',
    'last 2 Edge versions',
    'last 2 Opera versions'
];
