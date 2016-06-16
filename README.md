# scanzyCMS
Macro substitutions based CMS

##### Note: The project is currently in development

ScanzyCMS is a web content management system, suitable for dynamic websites. 
It is based on macros replacements, defined with html elements within the page(eg. &lt;macro&gt;macroname&lt;/macro&gt;).

### Requirements

* Apache server (.htaccess support)
* PHP 5.4 or higher
* MySQL database

If you want to use MsSql you only have to change the setup query (change foreign keys stuff)

### Getting started

1. Download all files and place them on your server (obviusly you can delete license and readme)
2. Make sure your server has .htaccess enabled and RewriteEngine overridable 
3. Visit admin/ with your browser, enter default credentials and login (username: admin, password: root)
4. Click on setup (well, I'll create a setup button), this will connect to database and create needed table