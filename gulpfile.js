var gulp 	= require('gulp'),
	sass 	= require('gulp-sass'),
	concat	= require('gulp-concat'),
	watch	= require('gulp-watch'),
	sourcemaps = require('gulp-sourcemaps'),
	uglify	= require('gulp-uglify'),
	autoPrefixer 	= require('gulp-autoprefixer'),
	watch 	= require('gulp-watch');

gulp.task('sass', function() {
	gulp.src("src/sass/*.scss")
	.pipe(sass())
	.on('error', function(error) {
		console.log(error);
		this.emit('end');
	})
	.pipe(autoPrefixer())
	.pipe(gulp.dest("assets/css"));
});

gulp.task('watch', function() {
	gulp.watch("src/sass/**/*", ['sass']);
});
gulp.task('build-all',['sass']);
gulp.task('default',['watch']);