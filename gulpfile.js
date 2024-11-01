const gulp = require('gulp');
const gulpSass = require('gulp-sass');
const rename = require('gulp-rename');
const tildeImporter = require('node-sass-tilde-importer');
const minify = require('gulp-uglify');
const babel = require('gulp-babel');

/**
 * Compile SASS unminified.
 */
gulp.task('sass:dev', () => {
	const sassOptions = {
		outputStyle: 'expanded',
		suffix: '',
		importer: tildeImporter
	};

	return gulp.src('./assets/css/**/*.scss')
		.pipe(gulpSass(sassOptions))
		.pipe(rename(sassOptions))
		.pipe(gulp.dest('./assets/css'));
});

/**
 * Compile SASS minified.
 */
gulp.task('sass:prod', () => {
	const sassOptions = {
		outputStyle: 'compressed',
		suffix: '.min',
		importer: tildeImporter
	};

	return gulp.src('./assets/css/**/*.scss')
		.pipe(gulpSass(sassOptions))
		.pipe(rename(sassOptions))
		.pipe(gulp.dest('./assets/css'));
});

/**
 * Copy libraries from node_modules to assets/js
 */
gulp.task('js:libs', () => {
	return gulp.src([
		'./node_modules/owl.carousel/dist/owl.carousel.min.js',
		'./node_modules/tippy.js/dist/tippy.all.min.js'
	]).pipe(gulp.dest('./assets/js/lib'));
});

/**
 * Minify JS files.
 */
gulp.task('js:minify', () => {
	return gulp.src(['./assets/js/**/*.js', '!./assets/js/**/*.min.js'])
		.pipe(babel({ presets: ['env'] }))
		.pipe(minify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('./assets/js'));
});

// Watch files.
gulp.task('watch', () => {
	gulp.watch('./assets/css/**/*.scss', ['sass:dev', 'sass:prod']);
	gulp.watch('./assets/js/**/*.js', ['js:minify']);
});

// Default task
gulp.task('default', ['js:libs', 'js:minify', 'sass:dev', 'sass:prod', 'watch']);
