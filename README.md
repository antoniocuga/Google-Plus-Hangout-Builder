Google Hangout Builder (PHP)
==========================

The Google Hangout Builder is a PHP build tool that combines the use of Google's Closure Compiler, Yahoo's CSS Compressor and Apache's mod_include to output one Google Hangout XML file.

It's intended audience is for anyone interested in building a Google Hangout app that already has Apache configured and running locally and is also perhaps familiar with how Apache's mod_include combines files.

For more information on how to get mod_include working in Apache, please refer to `http://httpd.apache.org/docs/2.0/mod/mod_include.html`.

How it works
==========================
It's simple.  We tell Apache that certain naming conventions on files need to use the Includes module to output combined content.  You can define which types of files to match and their output type by modifying the .htaccess file.

You setup a locally running site (the example provided here asserts `http://my-hangout.local`) and execute a PHP script from the command line to compress and combine all of your JavaScript, CSS and HTML into one big Google Hangout XML file.

You then have the option to deploy the content to a remote server by utilizing rsync.

Setting it up
==========================
1. Create an Apache virtual host for your project
2. Add the alias to your hosts file
3. Edit the build/build.php file with your project-specific options
- Change the $_HOST private variable to your project's request URL.
- Change the $_PROJECT private variable to whatever you want.
- For remote syncing, you can pass in the rsync options to the constructor found in this file or change the constructor code itself.  There are plans to abstract this out to a separate file later.
4. If you are going to rsync, add any additional files/folders you don't want to have synced to the .ignore file.

Build and Deploy
==========================
With your local machine configured with all the correct options, you can edit the index.combined.html file to include any local files.  A CURL request will be made on this file, which can then include any local files relative to the project folder.  Look at the index.combined.html file for a good example of how this works.

Once the build file has been given the correct options, it's as simple as running `php build/build.php` from the project root folder to have one XML file output and optionally deployed to a remote server.

By default, your combined files will be output as follows:

- JavaScript: js/all.min.js
- CSS: css/all.min.css
- XML: PROJECT_NAME.xml

That's it.  It should combine all of your content and deploy to a remote server, ready for you to test your new Hangout App!

####Example Usage
<pre>
bash-3.2$ cd /path/to/project
bash-3.2$ php build/build.php

Compressing CSS
Successfully wrote /path/to/project/css/all.min.css...

Compressing JS
Successfully wrote /path/to/project/js/all.min.js...

Updating the project XML
Successfully updated /path/to/project/my-hangout.xml

Publishing XML to server
total size is 751005  speedup is 365.81
Published updated contents to server
</pre>

Requirements
==========================
A locally running [ L | M | W ]AMP stack, rsync (if you plan to remote sync to a server), Java, Apache and mod_include.
