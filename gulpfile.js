const gulp = require('gulp');
const fs = require('fs');
const nodePath = require('path');
const { execFile } = require('child_process');

// Gulp handles local theme build tasks: Sass compilation, Webpack bundling,
// optional BrowserSync reloads, and source image conversion to WebP.
let browserSync;

// WordPress requires this theme header at the top of the compiled root style.css.
const themeHeader = `!\nTheme Name: CVIPI\nAuthor: Jameel Evans\nDescription: This theme was designed by Laura Myers and Eduardo Minaya. The theme was coded by Jameel Evans.\nVersion: 1.1\nText Domain: cvipi\n`;

// PostCSS plugin that prepends the WordPress theme header after CSS processing.
const addThemeHeader = () => ({
    postcssPlugin: 'add-theme-header',
    Once(root) {
        const postcssLib = require('postcss');
        root.prepend(postcssLib.comment({ text: themeHeader }));
    }
});
addThemeHeader.postcss = true;

// File paths
const paths = {
    styles: {
        src: 'assets/css/style.scss',
        dest: './', // Write style.css to the root of the theme directory
        watch: 'assets/css/**/*.scss'
    },
    scripts: {
        src: ['./assets/js/modules/*.js', './assets/js/scripts.js'],
        watch: ['./assets/js/modules/*.js', './assets/js/scripts.js']
    },
    images: {
        src: 'assets/img/**/*.{jpg,jpeg,png}',
        watch: 'assets/img/**/*.{jpg,jpeg,png}'
    },
    php: './**/*.php'
};

function webpPathFor(imagePath) {
    // Output the optimized WebP beside the original source image.
    const parsedPath = nodePath.parse(imagePath);
    return nodePath.join(parsedPath.dir, `${parsedPath.name}.webp`);
}

function shouldConvertImage(imagePath, webpPath) {
    // Skip conversion when the existing WebP is newer than its source image.
    if (!fs.existsSync(webpPath)) {
        return true;
    }

    const sourceStats = fs.statSync(imagePath);
    const webpStats = fs.statSync(webpPath);
    return sourceStats.mtimeMs > webpStats.mtimeMs;
}

function convertImageToWebp(imagePath) {
    // Convert one image with cwebp. This expects cwebp to be installed locally.
    const PluginError = require('plugin-error');
    const log = require('fancy-log');
    const webpPath = webpPathFor(imagePath);

    if (!shouldConvertImage(imagePath, webpPath)) {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        execFile('cwebp', ['-quiet', '-q', '85', imagePath, '-o', webpPath], (err) => {
            if (err) {
                reject(new PluginError('images', err));
                return;
            }

            log(`Created ${nodePath.relative(__dirname, webpPath)}`);
            resolve();
        });
    });
}

function images(done) {
    const glob = require('glob');

    glob(paths.images.src, { nodir: true }, (err, files) => {
        if (err) {
            done(err);
            return;
        }

        Promise.all(files.map(convertImageToWebp)).then(() => done(), done);
    });
}

// Compile SCSS into CSS with PostCSS
function styles() {
    const sass = require('gulp-sass')(require('sass'));
    const postcss = require('gulp-postcss');
    const cssnano = require('cssnano');
    const autoprefixer = require('autoprefixer');
    const plugins = [
        autoprefixer(),
        cssnano(),
        addThemeHeader()
    ];

    let stream = gulp.src(paths.styles.src)
        .pipe(sass({ outputStyle: 'expanded' }).on('error', sass.logError))
        .pipe(postcss(plugins))
        .pipe(gulp.dest(paths.styles.dest)); // Output to the root of the theme

    if (browserSync && browserSync.active) {
        stream = stream.pipe(browserSync.stream());
    }

    return stream;
}

// Compile scripts using Webpack
function scripts(callback) {
    const webpack = require('webpack');
    const log = require('fancy-log');
    const PluginError = require('plugin-error');

    log('Starting Webpack...');
    webpack(require('./webpack.config.js'), (err, stats) => {
        if (err) {
            log.error('Webpack error:', err.toString());
            callback(new PluginError('scripts', err));
            return;
        }
        log('Webpack completed.');
        log(stats.toString({ colors: true }));
        if (browserSync && browserSync.active) {
            browserSync.reload();
        }
        callback();
    });
}

// Watch for changes
function watch() {
    const log = require('fancy-log');
    const useBrowserSync = process.argv.includes('--sync');

    if (useBrowserSync) {
        log('Starting BrowserSync...');
        browserSync = require('browser-sync').create();
        browserSync.init({
            proxy: 'http://cvipi.local', // Update to match your Local environment URL
            listen: '127.0.0.1',
            host: '127.0.0.1',
            open: false,
            notify: false,
            ghostMode: false
        });
    } else {
        log('BrowserSync disabled. Use `npm run gulpwatch:sync` to enable browser reloads.');
    }

    styles();
    scripts(() => {});

    // Watch PHP files
    gulp.watch(paths.php).on('change', () => {
        if (browserSync && browserSync.active) {
            browserSync.reload();
        }
    });

    // Watch SCSS files
    gulp.watch(paths.styles.watch, styles);

    // Watch JS files
    gulp.watch(paths.scripts.watch, scripts);

    // Watch source images and create matching WebP files
    gulp.watch(paths.images.watch).on('add', convertImageToWebp).on('change', convertImageToWebp);
}

// Default task
exports.styles = styles;
exports.scripts = scripts;
exports.images = images;
exports.watch = watch;
exports.default = exports.watch;
