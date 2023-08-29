require("dotenv").config();

const gulp = require("gulp");
const sass = require("gulp-sass")(require("sass"));
const concat = require("gulp-concat");
const postcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");
const cssnano = require("cssnano");
const terser = require("gulp-terser");
const gulpIf = require("gulp-if");

const isProduction = process.env.NODE_ENV === "production";

gulp.task("styles", function() {
  return gulp
    .src("./includes/static/sass/**/*.scss")
    .pipe(sass().on("error", sass.logError))
    .pipe(
      postcss(
        [autoprefixer(), isProduction ? cssnano() : false].filter(Boolean)
      )
    )
    .pipe(gulp.dest("./includes/static/css"));
});

gulp.task("scripts", function() {
  return gulp
    .src("./includes/static/js/src/**/*.js")
    .pipe(concat("bundle.js"))
    .pipe(gulpIf(isProduction, terser()))
    .pipe(gulp.dest("./includes/static/js"));
});

// Adding new task for admin scripts
gulp.task("admin-scripts", function() {
  return gulp
    .src("./includes/static/js/src-admin/**/*.js") // Update the source directory for admin scripts
    .pipe(concat("bundle-admin.js")) // Name of the output bundle
    .pipe(gulpIf(isProduction, terser()))
    .pipe(gulp.dest("./includes/static/js")); // Pointing to the same output directory as other JS files
});

gulp.task("watch", function() {
  gulp.watch("./includes/static/sass/**/*.scss", gulp.series("styles"));
  gulp.watch("./includes/static/js/src/**/*.js", gulp.series("scripts"));
  gulp.watch("./includes/static/js/src-admin/**/*.js", gulp.series("admin-scripts")); // Add a watch for admin-scripts
});

gulp.task("default", gulp.series("styles", "scripts", "admin-scripts", "watch")); // Add admin-scripts to default series