// 'use strict';
/* jshint esversion: 6, node: true */

const gulp = require("gulp");
const sass = require('gulp-sass')(require('sass'));
// Inline source maps are embedded in the source file
const sourcemaps = require("gulp-sourcemaps");
// Parse CSS and add vendor prefixes to CSS rules. Example "-webkit-min-device-pixel-ratio"
const autoprefixer = require("autoprefixer");
// Pipe CSS through several plugins, but parse CSS only once
const postcss = require("gulp-postcss");
const minify = require("gulp-clean-css");

/*
----------------------------------------
PATHS
----------------------------------------
- All paths are relative to the
  project root
- Don't use a trailing `/` for path
  names
----------------------------------------
*/

// Project Sass source directory
const PROJECT_SASS_SRC = "./src/sass/styles.scss";

// Images destination
// const IMG_DEST = "./assets/img";

// Fonts destination
// const FONTS_DEST = "./assets/fonts";

// Javascript destination
// const JS_DEST = "./assets/js";

// Compiled CSS destination
const CSS_DEST = "./css";

// Site CSS destination
// Like the _site/assets/css directory in Jekyll, if necessary.
// If using, uncomment line 106
const SITE_CSS_DEST = "./css";

/*
----------------------------------------
TASKS
----------------------------------------
*/

gulp.task("build-sass", function (done) {
  let plugins = [
    autoprefixer({
      cascade: false, grid: true
    })
  ];
  return (
    gulp.src(`${PROJECT_SASS_SRC}`)
      .pipe(sourcemaps.init({largeFile: true}))
      .pipe(sass.sync().on('error', sass.logError))
      .pipe(postcss(plugins))
      .pipe(minify())
      .pipe(sourcemaps.write())
      .pipe(gulp.dest(`${SITE_CSS_DEST}`))
    );
});

gulp.task("build", gulp.series("build-sass"));

gulp.task("watch-sass", function () {
  gulp.watch("./src/sass/**/*.scss", gulp.series("build-sass"));
});

gulp.task("build", gulp.series("build-sass"));
gulp.task("watch", gulp.series("watch-sass"));
gulp.task("default", gulp.series("build"));

