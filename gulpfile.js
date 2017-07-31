var gulp = require('gulp');
var nunjucksRender = require('gulp-nunjucks-render');
var sass = require('gulp-sass');
var browserSync = require('browser-sync').create();
var header = require('gulp-header');
var cleanCSS = require('gulp-clean-css');
var autoprefixer = require('gulp-autoprefixer');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var pkg = require('./package.json');
var gutil = require('gulp-util');
var htmlmin = require('gulp-htmlmin');
var critical = require('critical').stream;
var clean = require('gulp-clean');
var es = require('event-stream');
var notify = require("gulp-notify");


// Set the banner content
var banner = ['/*!\n',
    ' * Bello\n',
    ' * Copyright 2014-' + (new Date()).getFullYear(), ' Bello (Newsio News ApS)\n',
    ' * Henrik Cullen - Head of Product\n',
    ' * https://belloforwork.com\n',
    ' */\n',
    ''
].join('');




gulp.task('nunjucks', function() {
  // Gets .html and .nunjucks files in pages
  return gulp.src('app/pages/**/*.+(html|nunjucks)')
  // Renders template with nunjucks
  .pipe(nunjucksRender({
      path: ['app/templates'],
      data: {
        absolute_url: 'https://belloforwork.com'
      }
    }))
  // output files in app folder
  .pipe(gulp.dest('develop'))
});


// Compile SASS and SCSS files
gulp.task('sass', ['nunjucks'], function () {
  return gulp.src('app/sass/bello.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer({
        browsers: ['last 2 versions'],
        cascade: false
    }))
    .pipe(header(banner, { pkg: pkg }))
    .pipe(gulp.dest('develop/css'))
    .pipe(browserSync.reload({
        stream: true
    }))
});

// Compile SASS and SCSS files
gulp.task('sass_unchained', function () {
  return gulp.src('app/sass/bello.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer({
        browsers: ['last 2 versions'],
        cascade: false
    }))
    .pipe(header(banner, { pkg: pkg }))
    .pipe(gulp.dest('develop/css'))
    .pipe(browserSync.reload({
        stream: true
    }))
});




// Minify compiled CSS
gulp.task('minify-css', ['sass'], function() {
    return gulp.src('develop/css/bello.css')
        .pipe(cleanCSS({ compatibility: '*' }))
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('develop/css'))
        .pipe(browserSync.reload({
            stream: true
        }))
});

// Minify compiled CSS
gulp.task('minify-css_unchained', function() {
    return gulp.src('develop/css/bello.css')
        .pipe(cleanCSS({ compatibility: '*' }))
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('develop/css'))
        .pipe(browserSync.reload({
            stream: true
        }))
});


// Minify JS
gulp.task('minify-js', function() {
    return gulp.src('app/js/*.js')
        .pipe(uglify())
        .pipe(header(banner, { pkg: pkg }))
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('develop/js'))
        .pipe(browserSync.reload({
            stream: true
        }))
});


// gulp.task('critical', function () {
//     return gulp.src('develop/*.html')
//         .pipe(critical({base: 'dist/', inline: true}))
//         .on('error', function(err) { gutil.log(gutil.colors.red(err.message)); })
//         .pipe(gulp.dest('dist'));
// });


// Critical Path Inlining
gulp.task('critical', ['minify-css'], function () {
    return gulp.src('develop/**/*.html')
        .pipe(critical({
          base: "develop/",
          inline: true,
          minify: true,
          dimensions: [{
            height: 800,
            width: 600
          },{
            height: 900,
            width: 1200
          },{
            height: 900,
            width: 1672
          }],
          ignore: ['@font-face',/url\(/],
          include: [/\.nav.*\.logotype/, /\.nav.*\.logomark/]
        }))
        .on('error', function(err) { gutil.log(gutil.colors.red(err.message)); })
        .pipe(htmlmin({collapseWhitespace: true}))
        .pipe(gulp.dest('dist'))
        .pipe(notify({ message: "Critical Build complete.", onLast: true }));
});

// Clean Develop before starting server
gulp.task('dev_clean', function () {
    return gulp.src('develop', {read: false})
        .pipe(clean());
});

// Copies images to Develop
gulp.task('image-move', function() {
    return gulp.src('app/img/**')
        .pipe(gulp.dest('develop/img'))

});

// Cleans Dist before build
gulp.task('build_clean', function () {
    return gulp.src('dist', {read: false})
        .pipe(clean());
});

// Copies relevant assets to Dist
gulp.task('build_copy', ['build_clean'], function () {
  return es.merge(
      gulp.src('app/img/**/*.*').pipe(gulp.dest('dist/img')),
      gulp.src('app/public/**/*.*').pipe(gulp.dest('dist/public')),
      gulp.src('develop/css/**/*.min.css').pipe(gulp.dest('dist/css')),
      gulp.src('develop/js/*.js').pipe(gulp.dest('dist/js'))
    );
});





// Configure the browserSync task
gulp.task('browserSync', function() {
    browserSync.init({
        server: {
            baseDir: 'develop/',
            serveStaticOptions: {
                extensions: ["html"]
            }
        },
    })
})


// Configure the browserSync task
gulp.task('buildtest', ['build'], function() {
    browserSync.init({
        server: {
            baseDir: 'dist/',
            serveStaticOptions: {
                extensions: ["html"]
            }
        },
    })
})



// Production default
gulp.task('default', ['nunjucks', 'sass', 'minify-css', 'minify-js']);

// Production build
gulp.task('build', ['nunjucks', 'sass', 'minify-css', 'minify-js', 'critical', 'build_clean', 'build_copy']);


// Dev task with browserSync
gulp.task('dev', ['browserSync', 'sass', 'minify-css', 'minify-js', 'image-move', 'nunjucks'], function() {
    gulp.watch('app/sass/**/*.scss', ['sass_unchained']);
    gulp.watch('develop/css/*.css', ['minify-css_unchained']);
    gulp.watch('app/js/**/*.js', ['minify-js']);
    gulp.watch('app/img/*', ['image-move']);
    gulp.watch('app/pages/**/*.nunjucks', ['nunjucks']);
    gulp.watch('app/templates/**/*.nunjucks', ['nunjucks']);
    // Reloads the browser whenever HTML or JS files change
    gulp.watch('develop/*.html', browserSync.reload);
    gulp.watch('develop/js/*.js', browserSync.reload);
    // gulp.watch('develop/css/*.min.css', browserSync.reload);
});
