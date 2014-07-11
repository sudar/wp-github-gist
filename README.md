# WP Github Gist #
**Contributors:** sudar  
**Tags:** github, gist, embed  
**Requires at least:** 2.8  
**Donate Link:** http://sudarmuthu.com/if-you-wanna-thank-me  
**Tested up to:** 3.3.2  
**Stable tag:** 0.3  

Embed files and gist from Github in your blog posts or pages.

## Description ##

WP Github Gist WordPress Plugin, provides the ability to embed gist and files from Github in your blog posts or pages. Even though Github doesn't provide a way to embed files, this Plugin still works by using the gist-it service.

### Features

#### Embed Gist

To embed a gist you have to use the following shortcode

`[gist id = "{GIST_ID}" file = "{GIST_FILE}" width = "{WIDTH}"]`

The following are the different attributes that you can use in the shortcode

- `id` - Id of your gist.
- `file` - File inside gist that you want to display. If there is only one file in the gist, then you can ignore this.
- `width` - Width of the code wrapper. Default is `100%`.

eg: `[gist id = "12345" file = "myfile" width = "100%"]`

#### Embed Github files

To embed a github file you have to use the following shortcode

`[github file = "{GITHUB_FILE}" start_line = "{START_LINE}" end_line = "{END_LINE}"]`

- `{GITHUB_FILE}` - full path to your github file. eg: If you want to embed https://github.com/sudar/MissileLauncher/blob/master/MissileLauncher.cpp then `{GITHUB_FILE}` would be /sudar/MissileLauncher/blob/master/MissileLauncher.cpp
- `{START_LINE}` - If you want to embed only part of the file, then you can specify the starting line number (optional)
- `{END_LINE}` - If you want to embed only part of the file, then you can specify the ending line number (optional)

eg: `[github file = "/sudar/MissileLauncher/blob/master/MissileLauncher.cpp"]`

if you want to embed only part of the file, then you can specify the start and end line as well

`[github file = "/sudar/MissileLauncher/blob/master/MissileLauncher.cpp" start_line = "10" end_line = "20"]`

### Changing Gist-it server

By default, this Plugin uses my own [gist-it server][3] which is hosted on a free Google App Engine account. If you expect significant amount of traffic to your blog, then do consider using your own gist-it server. You can follow the [instructions to deploy your own gist-it server][4] and then go to the settings page to change the url.

### Translation

The pot file is available with the Plugin. If you are willing to do translation for the Plugin, use the pot file to create the .po files for your language and let me know. I will add it to the Plugin after giving credit to you.

### Support

Support for the Plugin is available from the [Plugin's home page][1]. If you have any questions or suggestions, do leave a comment there or contact me in [twitter][2].

 [1]: http://sudarmuthu.com/wordpress/wp-github-gist
 [2]: http://twitter.com/sudarmuthu
 [3]: http://gist-it.sudarmuthu.com
 [4]: http://sudarmuthu.com/wordpress/wp-github-gist/changing-gist-it-server

## Installation ##

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

## Readme Generator ##

This Readme file was generated using <a href = 'http://sudarmuthu.com/wordpress/wp-readme'>wp-readme</a>, which generates readme files for WordPress Plugins.
