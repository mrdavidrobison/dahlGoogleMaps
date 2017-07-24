var gulp = require('gulp');
var jade = require('gulp-jade');
var stylus = require('gulp-stylus');
var browserSync = require('browser-sync').create();
var reload = browserSync.reload;

gulp.task('jade', function(){
  gulp.src('./src/*.jade')
  .pipe(jade({
    pretty: true
  }))
  .pipe(gulp.dest('./dist'))
  .pipe(browserSync.stream());
})

gulp.task('stylus', function () {
  gulp.src('./css/*.stylus')
  .pipe(stylus())
  .pipe(gulp.dest('./dist'))
  .pipe(browserSync.stream());
});

gulp.task('js', function () {
  gulp.src('./scripts/*.js')
  .pipe(gulp.dest('./dist'))
  .pipe(browserSync.stream());
});

gulp.task('img', function(){
  gulp.src('./images/*')
  .pipe(gulp.dest('./dist'))
  .pipe(browserSync.stream());
});

gulp.task('fonts', function(){
  if ('./dist/*.otf' === false) {
    gulp.src('./fonts/*')
      .pipe(gulp.dest('./dist'))
      .pipe(browserSync.stream());
  }
});

gulp.task('sync', function() {
  browserSync.init({
    server: {
      baseDir: "./dist"
    }
  });
});

gulp.task('default', ['jade', 'stylus', 'js', 'img', 'fonts', 'sync'], function() {
  gulp.watch('./src/*.jade', ['jade']);
  gulp.watch('./css/*.stylus', ['stylus']);
  gulp.watch('./scripts/*.js', ['js']);
  gulp.watch('./images/*', ['img']);
  gulp.watch('./fonts/*', ['fonts']);
  gulp.watch('*.html').on('change', reload);
});