var gulp = require('gulp');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var cleanCSS = require('gulp-clean-css');
var sass = require('gulp-sass');
var notify = require('gulp-notify');
var print = require('gulp-print');

var modules = [
    {
        name: 'requirejs',
        globs: ['node_modules/requirejs/require.js'],
        exportPath: 'public/static/component/requirejs/'
    },
    {
        name: 'jquery',
        globs: ['node_modules/jquery/dist/**', '!node_modules/jquery/dist/*.map', '!node_modules/jquery/dist/*.min.*'],
        exportPath: 'public/static/component/jquery/dist/'
    },
    {
        name: 'jqueryValidation',
        globs: ['node_modules/jquery-validation/dist/**', '!node_modules/jquery-validation/dist/*.min.*'],
        exportPath: 'public/static/component/jquery-validation/dist/'
    },
    {
        name: 'bootstrap3',
        globs: ['node_modules/bootstrap3/dist/**', '!node_modules/bootstrap3/dist/**/*.map', '!node_modules/bootstrap3/dist/**/*.min.*'],
        exportPath: 'public/static/component/bootstrap3/dist/'
    },
    {
        name: 'bootstrap3Dialog',
        globs: ['node_modules/bootstrap3-dialog/dist/?(css|js)/**', '!node_modules/bootstrap3-dialog/dist/**/*.map', '!node_modules/bootstrap3-dialog/dist/**/*.min.*'],
        exportPath: 'public/static/component/bootstrap3-dialog/dist/'
    },
    {
        name: 'bootstrapNotify',
        globs: ['node_modules/bootstrap-notify/*.js', '!node_modules/bootstrap-notify/*.min.*'],
        exportPath: 'public/static/component/bootstrap-notify/dist/'
    },
    {
        name: 'fontAwesome',
        globs: ['node_modules/font-awesome/?(css|fonts)/**', '!node_modules/font-awesome/css/*.map', '!node_modules/font-awesome/css/*.min.*'],
        exportPath: 'public/static/component/font-awesome/'
    },
    {
        name: 'plupload',
        globs: ['node_modules/plupload_2.1.9/**'],
        exportPath: 'public/static/component/plupload/js/'
    },
    {
        name: 'qiniu',
        globs: ['node_modules/qiniu-js/dist/qiniu.js'],
        exportPath: 'public/static/component/qiniu-js/dist/'
    },
    {
        name: 'wangEditor',
        globs: ['node_modules/wangEditor/dist/**', '!node_modules/wangEditor/dist/**/*.map', '!node_modules/wangEditor/dist/**/*.min.*', '!node_modules/wangEditor/dist/js/lib/*'],
        exportPath: 'public/static/component/wangEditor/dist/'
    }
];

var compression = {
    js: ['public/static/component/**/*.js', '!public/static/component/**/*.min.js'],
    css: ['public/static/component/**/*.css', '!public/static/component/**/*.min.css']
};

gulp.task('test', function() {
    gulp.src('')
        .pipe(notify( {
            message: "Gulp stand by."
        }));
});

gulp.task('build-dist', function(cb) {

    modules.forEach(function(moduleInfo) {
        gulp.src(moduleInfo.globs)
            .pipe(gulp.dest(moduleInfo.exportPath))
            .pipe(print(function(filepath) {
                return "built: " + filepath;
            }));
    }, this);

    var err = null;
    cb(err);
});

gulp.task('done', function() {
    gulp.src('')
        .pipe(notify({
            message: "Gulp task done."
        }));
});

gulp.task('dist', ['build-dist', 'done']);

gulp.task('compress', function () {
    // 压缩component内的js（附带min）
    gulp.src(compression.js, {base: '.'})
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename(function(path) {
            path.basename = path.basename + '.min';
        }))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(''));

    // 压缩component内的css（附带min）
    gulp.src(compression.css, {base: '.'})
        .pipe(sourcemaps.init())
        .pipe(cleanCSS())
        .pipe(rename(function(path) {
            path.basename = path.basename + '.min';
        }))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(''));
});

// 自定义 css
var cssApp = {
    name: 'cssApp',
    globs: 'public/static/css/dev/app.sass',
    exportPath: 'public/static/css/dist',
    watchPath: 'public/static/css/dev/**',
}

gulp.task('css', function () {
    gulp.src(cssApp.globs)
        .pipe(sourcemaps.init())
        .pipe(sass())
        .pipe(cleanCSS())
        .pipe(rename(function(path) {
            path.basename = path.basename + '.min';
        }))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(cssApp.exportPath));
});

// 自定义 js
var jsApp = {
    name: 'jsApp',
    globs: [ 'public/static/js/dev/**/*.js', '!public/static/js/dev/framework/*.js' ],
    exportPath: 'public/static/js/dist',
    watchPath: [ 'public/static/js/dev/**/*.js', '!public/static/js/dev/framework/*.js' ]
}

gulp.task('js', function () {
    gulp.src(jsApp.globs)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(jsApp.exportPath));
});

// 公共js

var jsFrameworkModule = {
    name: 'framework',
    globs: [
        'public/static/js/dev/framework/wrap-start.js',
        'public/static/js/dev/framework/pager.js',
        'public/static/js/dev/framework/loader.js',
        'public/static/js/dev/framework/wrap-end.js'
    ],
    exportPath: 'public/static/js/dist/framework.js',
    watchPath: [ 'public/static/js/dev/framework/*.js' ]
};

gulp.task('framework', function () {
    // 合并framework js 输出到dist/framework.js
    gulp.src(jsFrameworkModule.globs)
        .pipe(sourcemaps.init())
        .pipe(concat(jsFrameworkModule.exportPath))
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(''));
});

// 开发时监听程序 执行 gulp watch
gulp.task('watch', function() {
    gulp.watch(cssApp.watchPath, ['css']);
    gulp.watch(jsApp.watchPath, ['js']);
    gulp.watch(jsFrameworkModule.watchPath, ['framework']);
});