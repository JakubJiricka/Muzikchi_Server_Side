var gulp = require('gulp');
var svgstore = require('gulp-svgstore');
var svgmin = require('gulp-svgmin');
var rename = require('gulp-rename');
var filter = require('gulp-filter');
var clean  = require('gulp-clean');
var fs     = require('fs');
var cheerio = require('cheerio');
var ts = require('typescript');

function extractTranslationsFromTsFiles() {
    var files = getFilesInPath('./src/app', '.ts');
    var props = ['modal', 'toast'];
    var lines = [];

    files.forEach(function(path) {

        //don't need to parse spec or module files
        if (path.indexOf('.spec') > -1 || path.indexOf('.module') > -1) return;

        //create ts source file
        var tsFile = ts.createSourceFile(path, fs.readFileSync(path, 'utf8'), null, false);

        //find all function call expressions in ts file
        var nodes = _findNodes(tsFile, ts.SyntaxKind.CallExpression, tsFile);

        nodes.forEach(function(node) {
            //function (method) name
            const methodAccess = node.getChildAt(0);

            //class name for above function
            const propAccess = node.getChildAt(0).getChildAt(0);

            //check that the class is "modal" or "toast" and function name is "show"
            if ( ! propAccess || ! propAccess.name || props.indexOf(propAccess.name.text) === -1 ) return;
            if ( ! methodAccess || !methodAccess.name || methodAccess.name.text !== 'show') return;

            //toast call will have translatable string as first argument.
            //modal call will have translatable strings in an object as second argument.
            var argIndex = propAccess.name.text === 'toast' ? 0 : 1;
            var keys = _extractTranslationsStringsFromFunctionCallExpression(node, argIndex);

            if (keys && keys.length) {
                lines = lines.concat(keys);
            }
        });
    });

    return lines.filter(function(item, pos, self) {
        return self.indexOf(item) === pos;
    });
};

/**
 * Get strings from function call's first argument
 */
function _extractTranslationsStringsFromFunctionCallExpression(callNode, argIndex) {
    if ( ! callNode.arguments.length) return;

    const argument = callNode.arguments[argIndex];

    if ( ! argument) return;

    switch (argument.kind) {
        case ts.SyntaxKind.StringLiteral:
        case ts.SyntaxKind.FirstTemplateToken:
            return [argument.text];
        case ts.SyntaxKind.ObjectLiteralExpression:
            return argument.properties.filter(function (prop) {
                return prop && prop.initializer &&
                    prop.initializer.kind === ts.SyntaxKind.StringLiteral &&
                    prop.initializer.text;
            }).map(function (prop) {
                return prop.initializer.text;
            });
    }
}

/**
 * Find all child nodes of a kind
 */
function _findNodes(node, kind, source) {
    const childrenNodes = node.getChildren(source);
    const initialValue = node.kind === kind ? [node] : [];

    return childrenNodes.reduce(function(result, childNode) {
        return result.concat(_findNodes(childNode, kind, source));
    }, initialValue);
}

gulp.task('i18n-extract', function() {
    var files = getFilesInPath('./src/app', '.html');
    var fromHtml = {};

    files.forEach(function(path) {
        var $ = cheerio.load(fs.readFileSync(path, 'utf8'));

        $('[trans], [trans-placeholder], [trans-title], [itemsName], [tooltip]').each(function(i, el) {
            var $el = $(el), text;

            //extract input placeholder attribute
            if ($el.attr('placeholder') && $el.attr('placeholder').length) {
                text = $el.attr('placeholder');
            }

            //extract node title attribute
            else if ($el.attr('trans-title') && $el.attr('trans-title').length) {
                text = $el.attr('title');
            }

            //extract custom "itemsName" attribute
            else if ($el.attr('itemsname') && $el.attr('itemsname').length) {
                text = $el.attr('itemsname');
            }

            //extract custom "tooltip" attribute
            else if ($el.attr('tooltip') && $el.attr('tooltip').length) {
                text = $el.attr('tooltip');
            }

            //extract node text content
            else {
                text = $el.text();
            }

            var key = text.trim();
            fromHtml[key] = key;
        });
    });

    var fromTs = extractTranslationsFromTsFiles();

    //concat translations extract from html and ts files
    fromTs.forEach(function(key) {
        if ( ! fromHtml[key]) {
            fromHtml[key] = key;
        }
    });

    fs.writeFile('./../server/resources/client-translations.json', JSON.stringify(fromHtml), 'utf8');
});

gulp.task('dist', function() {
    var files = [
        './../server/public/*',
        '!./../server/public/.htaccess',
        '!./../server/public/index.php',
        '!./../server/public/storage',
        '!./../server/public/install_files'
    ];

    //remove old dist files from laravel public folder
    gulp.src(files, {read: false}).pipe(clean({force: true}));

    //copy dist folder into laravel public folder
    gulp.src(['./../dist/**/*', '!./../dist/index.html', '!./../dist/stats.json']).pipe(gulp.dest('./../server/public'));

    var $ = cheerio.load(fs.readFileSync('./../dist/index.html', 'utf8'));

    //get script tags that need to be injected into main laravel view
    var scripts = $('script').map(function(i, el) {
        return $('<div>').append($(el)).html();
    }).toArray();

    //get css tags that need to be injected into main laravel view
    var styles = $('link').filter(function(i, el) {
        return $(el).attr('href').indexOf('bundle.css') > -1;
    }).map(function(i, el) {
        return $('<div>').append($(el)).html();
    }).toArray();

    //js scripts replace regex
    var jsSearch = /{{--angular scripts begin--}}[\s\S]*{{--angular scripts end--}}/;
    var jsReplaceStr = '{{--angular scripts begin--}}' + "\n\t\t" + scripts.join("\n\t\t") + "\n\t\t{{--angular scripts end--}}";

    //css styles replace regex
    var cssSearch = /{{--angular styles begin--}}[\s\S]*{{--angular styles end--}}/;
    var cssReplaceStr = '{{--angular styles begin--}}' + "\n\t\t" + styles.join("\n\t\t") + "\n\t\t{{--angular styles end--}}";

    //replace app stylesheet links and js script tags with new ones
    var content = fs.readFileSync('./../server/resources/views/main.blade.php', 'utf8');
    content = content.replace(jsSearch, jsReplaceStr).replace(cssSearch, cssReplaceStr);

    fs.writeFileSync('./../server/resources/views/main.blade.php', content, 'utf8');
});

//Compile svg icons into a single file
gulp.task('svgstore', function () {
    var iconNames = getIconNames();

    gulp.src(['node_modules/material-design-icons/*/svg/production/*_24px.svg', 'src/assets/icons/*.svg'])

    //filter out svg icons that are not used in project
    .pipe(filter(function(file) {
        return iconNames.indexOf(normalizeIconName(file.path)) > -1;
    }))

    //normalize icon names
    .pipe(rename(function (file) {
        file.basename = normalizeIconName(file.basename);
    }))

    //compile, minify and store svg on disk.
    .pipe(svgmin({plugins: [{
        removeAttrs: {attrs: ['color']}
    }]}))
    .pipe(svgstore())
    .pipe(rename('icons.svg'))
    .pipe(gulp.dest('src/assets'));
});

/**
 * Normalize custom icons names by replacing underscore and
 * white space with dash and appending '-custom' to file name
 */
gulp.task('normalize-icon-names', function() {
    return gulp.src('src/assets/icons/*.svg')
        .pipe(clean())
        .pipe(rename(function(file) {
            if (file.basename.indexOf('-custom') === -1) {
                file.basename = file.basename.toLowerCase().replace(/[_\s]/, '-') + '-custom';
            }
        }))
        .pipe(gulp.dest('src/assets/icons'))
});

/**
 * Normalize icon file name.
 * "ic_icon_name_24px.svg" to "icon-name"
 */
function normalizeIconName(path) {
    var filename = path.replace(/^.*[\\\/]/, '');
    return filename.replace('ic_', '').replace('_24px', '').replace(/[_=]/g, '-').replace('.svg', '');
}

/**
 * Extract names of icons that should be included into
 * compiled svg file from project .html files.
 */
function getIconNames() {
    var htmlFiles = getFilesInPath('./src/app', '.html'),
        names     = extractIconNamesFromHtmlFiles(htmlFiles);

    //add icons that are not in html files, but should be included
    names = names.concat(['trending-up', 'whatshot', 'album', 'local-offer', 'audiotrack', 'mic', 'person', 'library-music']);

    //filter out duplicates from icon names
    return names.reduce(function(accum, current) {
        if (accum.indexOf(current) < 0) accum.push(current);
        return accum;
    }, []);
}

/**
 * Extract all icon names from contents of files
 * in specified files path list.
 */
function extractIconNamesFromHtmlFiles(files) {
    var names = [];

    files.forEach(function(path) {
        var contents = fs.readFileSync(path, 'utf8');
        var regex = /<svg-icon.*?name="(.+?)".*?>/g;

        var matches, output = [];
        while (matches = regex.exec(contents)) {
            output.push(matches[1]);
        }

        names = names.concat(output);
    });

    return names;
}

/**
 * get a list of all project's .html file paths.
 */
function getFilesInPath(dir, extension, filelist) {
    if (dir[dir.length-1] !== '/') dir = dir.concat('/');

    var files = fs.readdirSync(dir);
    filelist = filelist || [];

    files.forEach(function(file) {
        if (fs.statSync(dir + file).isDirectory()) {
            filelist = getFilesInPath(dir + file + '/', extension, filelist);
        }
        else {
            if (file.indexOf(extension) > -1) {
                filelist.push(dir+file);
            }
        }
    });

    return filelist;
}