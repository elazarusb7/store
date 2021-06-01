'use strict';

// Theme and local dev variables
//---------------------------------------------
var themepath = 'web/themes/omega_samhsa_pep8/';  //The Drupal theme path
var src = themepath+'src/';  //The path to the src files (probably won't need to change)
var localhost = "http://samhsa-pep8.docksal";  //The URL for local development



// Gulp plugins
//---------------------------------------------
var gulp = require('gulp');
var sourcemaps = require('gulp-sourcemaps');
var sass = require('gulp-sass');
var sassGlob = require('gulp-sass-glob');
var del = require('del');
var autoprefixer = require('gulp-autoprefixer');


gulp.task('clean:styles', function () {
  return del([
    'css/**/*.css.map',
    'css/**/*.css'
  ]);
});

gulp.task('sass', function () {
 return gulp.src(src+'scss/**/*.scss')
  .pipe(sassGlob())
  .pipe(sourcemaps.init())
  .pipe(sass().on('error', sass.logError))
  .pipe(autoprefixer({grid: true}))
  .pipe(sourcemaps.write())
  .pipe(gulp.dest(themepath+'css'));
});

gulp.task('publish', gulp.series('clean:styles', function() { 
  return gulp.src(src+'scss/**/*.scss')
  .pipe(sassGlob())
  .pipe(sass().on('error', sass.logError))
  .pipe(autoprefixer({grid: true}))
  .pipe(gulp.dest(themepath+'css'));
}));


gulp.task('compile', gulp.series('publish', function(done){
  done();
}));

gulp.task('watch', function(done){
  gulp.watch(src+'scss/**/*.scss', gulp.parallel('sass'));
    done();
});


gulp.task('default', gulp.series('watch', function(done){
  done();
}));
function onError(err) {
  console.log(err);
  this.emit('end');
}