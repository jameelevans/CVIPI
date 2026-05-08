const gulp = require('gulp');
const browserSync = require('browser-sync').create();
const fs = require('fs');
const nodePath = require('path');
const { execFile } = require('child_process');
const dartSass = require('sass');
const gulpSass = require('gulp-sass');
const sass = require('gulp-sass')(require('sass'));
const webpack = require('webpack');
const glob = require('glob');
const log = require('fancy-log');
const PluginError = require('plugin-error');
const postcss = require('gulp-postcss');
const postcssLib = require('postcss');
const cssnano = require('cssnano');
const autoprefixer = require('autoprefixer');

const themeHeader = `!\nTheme Name: CVIPI\nAuthor: Jameel Evans\nDescription: This theme was designed by Laura Myers and Eduardo Minaya. The theme was coded by Jameel Evans.\nVersion: 1.1\nText Domain: cvipi\n`;

const addThemeHeader = () => ({
    postcssPlugin: 'add-theme-header',
    Once(root) {
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
    const parsedPath = nodePath.parse(imagePath);
    return nodePath.join(parsedPath.dir, `${parsedPath.name}.webp`);
}

function shouldConvertImage(imagePath, webpPath) {
    if (!fs.existsSync(webpPath)) {
        return true;
    }

    const sourceStats = fs.statSync(imagePath);
    const webpStats = fs.statSync(webpPath);
    return sourceStats.mtimeMs > webpStats.mtimeMs;
}

function convertImageToWebp(imagePath) {
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
    const plugins = [
        autoprefixer(),
        cssnano(),
        addThemeHeader()
    ];
    return gulp.src(paths.styles.src)
        .pipe(sass({ outputStyle: 'expanded' }).on('error', sass.logError))
        .pipe(postcss(plugins))
        .pipe(gulp.dest(paths.styles.dest)) // Output to the root of the theme
        .pipe(browserSync.stream());
}

// Compile scripts using Webpack
function scripts(callback) {
    log('Starting Webpack...');
    webpack(require('./webpack.config.js'), (err, stats) => {
        if (err) {
            log.error('Webpack error:', err.toString());
            callback(new PluginError('scripts', err));
            return;
        }
        log('Webpack completed.');
        log(stats.toString({ colors: true }));
        browserSync.reload();
        callback();
    });
}

// Watch for changes
function watch() {
    log('Starting BrowserSync...');
    browserSync.init({
        proxy: 'http://cvipi.local', // Update to match your Local environment URL
        notify: false,
        ghostMode: false
    });

    // Watch PHP files
    gulp.watch(paths.php).on('change', browserSync.reload);

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
exports.watch = gulp.series(styles, scripts, watch);
exports.default = exports.watch;
