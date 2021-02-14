=== WP STAGING PRO - DB & File Duplicator & Migration  ===

Author URL: https://wordpress.org/plugins/wp-staging
Plugin URL: https://wordpress.org/plugins/wp-staging
Contributors: ReneHermi, WP-Staging
Donate link: https://wordpress.org/plugins/wp-staging
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: backup, database backup, staging, duplication, clone
Requires at least: 3.6+
Tested up to: 5.6
Stable tag: 3.1.9
Requires PHP: 5.5

A duplicator plugin - clone/move, duplicate & migrate websites to staging, backup and development sites that only authorized users can access.

== Description ==

<h3>WP STAGING for WordPress Migration & Cloning </h3>
This duplicator, staging and backup plugin can create an exact copy of your entire website in seconds.* Great for staging, backup or development purposes.
(Cloning and Backup time depends on the size of your website)<br /><br />
This clone plugin creates a clone of your website into a subfolder or subdomain (Pro) of your main WordPress installation. The clone includes an entire copy of your database.
 <br /> <br />
<strong>Note: </strong> For pushing & migrating plugins and theme files to production site, check out the pro edition [https://wp-staging.com/](https://wp-staging.com/ "WP STAGING PRO")
<br /><br />
All the time-consumptive database and file cloning operations are done in the background. The plugin even automatically does an entire search & replace of all serialized links and paths.
 <br /><br />
This staging and backup plugin can clone your website even if it runs on a weak shared hosting server.
 <br /><br />
 <br /><br />
WP STAGING can help you to prevent your website from being broken or unavailable because of installing untested plugin updates!

[youtube https://www.youtube.com/watch?v=Ye3fC6cdB3A]

= Main Features =

* WPSTAGING clones the whole production site into a subfolder like example.com/staging-site.
* Easy to use! Create a clone of your site by clicking one button "CREATE NEW STAGING SITE".
* No Software as a Service - No account needed! All data belong to you only and stay on your server.
* No server timeouts on huge websites or/and small hosting servers
* Very fast - Migration and clone process takes only a few seconds or minutes, depending on the website's size and server I/O power.
* Use the clone as part of your backup strategy
* Only administrators can access the clone website. (Login with the same credentials you use on your production site)
* SEO friendly: The clone website is unavailable to search engines due to a custom login prompt and gets the meta entry no-index.
* The admin bar on the staging website is orange colored and shows clearly when you work on the staging site.
* Extensive logging features
* Supports all popular web servers: Apache, Nginx, and Microsoft IIS
* <strong>[Premium]: </strong>Choose a separate database and select a custom directory for cloning
* <strong>[Premium]: </strong>Make the clone website available from a subdomain like dev.example.com
* <strong>[Premium]: </strong>Push & migrate entire clone site inc. all plugins, themes, and media files to production website.
* <strong>[Premium]: </strong>Define user roles that get access to the clone site only. For instance, clients or external developers.
* <strong>[Premium]: </strong>Migration and cloning of WordPress multisites

> Note: Some features are Premium. Which means you need WP STAGING Pro to use those features. You can [get WP STAGING Premium here](https://wp-staging.com)!

* New: Compatible with WordFence & All In One WP Security & Firewall

= Additional Features WP STAGING PRO Edition  =

* Cloning and migration of WordPress multisites
* Define a separate database and a custom directory for cloning
* Clone your website into a subdomain
* Specify certain user roles for accessing the staging site
* Copy all modifications from staging site to the production website

<strong>Change your workflow of updating themes and plugins data:</strong>

1. Use WP STAGING to clone a production website for staging, testing or backup purposes
2. Create a backup of your website
3. Customize theme, configuration, update or install new plugins
4. Test everything on your staging site and keep a backup of the original site
5. If everything works on the staging site start the migration and copy all modifications to your production site!

<h3> Why should I Use a Staging Website? </h3>

Plugin updates and theme customizations should be tested on a staging platform first before they are done on your production website.
It's recommended having the staging platform on the same server where the production website is located to use the same hardware and software environment for your test website and to catch all possible errors during testing.

Before you update a plugin or going to install a new one, it is highly recommended to check out the modifications on a clone of your production website.
This makes sure that any modifications work on your production website without throwing unexpected errors or preventing your site from loading. Better known as the "WordPress blank page error".

Testing a plugin update before installing it in a production environment isn´t done very often by most users because existing staging solutions are too complex and need a lot of time to create a
an up-to-date copy of your website.

Some of you might be afraid of installing plugins updates because Your follow the rule "never touch a running system" with having in mind that untested updates are increasing the risk of breaking Your site.
This is one of the main reasons why WordPress installations are often outdated, not updated at all and insecure because of this non-update behavior.

<strong> It's time to change this, so use "WP STAGING" for cloning, backup and migration of WordPress websites</strong>

<h3> Can´t I just use my local wordpress development system like xampp / lampp for testing and backup purposes? </h3>

You can test your website locally but if your local hardware and software environment is not a 100% exact clone of your production server there is NO guarantee that every aspect of your local copy is working on your production website exactly as you expect it.
There are some obvious things like differences in the config of PHP and the server you are running but even such non-obvious settings like the amount of RAM or the CPU performance can lead to unexpected results on your production website.
There are dozens of other possible cause of failure which can not be handled well when you are testing your changes on a local platform only without creating a backup staging site.

This is were WP STAGING jumps in... Site cloning, backup and staging site creation simplified!

<h3>I just want to migrate the database from one installation to another</h3>
If you want to migrate your local database to an already existing production site you can use a tool like WP Migrate DB.
WP STAGING is intended for creating a staging site with latest data from your production site or creating a backup of it. So it goes the opposite way of WP Migrate DB.
Both tools are excellent cooperating each other.

<h3>What are the benefits compared to a plugin like Duplicator?</h3>
I really the Duplicator plugin. It is a great tool for migrating from a development site to production one or from production site to development one and a good tool to create a backup of your WordPress website.
The downside is that before you can even create an export or backup file with Duplicator a lot of adjustments, manually interventions and requirements are needed before you can start the backup process.
Duplicator also needs some skills to be able to create a backup and development/staging site, where WP STAGING does not need more than a click from you.
Duplicator is best placed to be a tool for first-time creation of your production site. This is something where it is very handy and powerful.

If you have created a local or web-hosted development site and you need to migrate this site the first time to your production domain than you are doing nothing wrong with using
the Duplicator plugin! If you need all your latest production data like posts, updated plugins, theme data and styles in a testing environment or want to create a quick backup before testing out omething than I recommend to use WP STAGING instead!

= Can I give You some Feedback? =
This plugin has been created in thousands of hours and works even with the smallest shared web hosting package.
We also use enterprise level approved testing coding structures to make sure that the plugin runs rock solid on your system.
If you are a developer you will probably like to hear that we use Codeception and PHPUnit for our software.

As there are infinite numbers of possible server constellations it still might happen that something does not work for you 100%. In that case,
please open a [support request](https://wp-staging.com/support/ "support request") and describe your issue.


= Important =

Permalinks are disabled on the staging / backup site after first time cloning / backup creation
[Read here](https://wp-staging.com/docs/activate-permalinks-staging-site/ "activate permalinks on staging site") how to activate permalinks on the staging site.



= How to install and setup? =
Install it via the admin dashboard and to 'Plugins', click 'Add New' and search the plugins for 'WP STAGING'. Install the plugin with 'Install Now'.
After installation, go to the settings page 'Staging' and do your adjustments there.


== Frequently Asked Questions ==

* What is the Difference between WP STAGING and a Regular Backup Plugin?
You may have heard about other popular backup plugins like BackWPUp, BackupWordPress, Simple Backup, WordPress Backup to Dropbox or similar WordPress backup plugins and now wonder about the difference between WP STAGING and those backup tools.
Other backup plugins usually create a backup of your WordPress filesystem and a database backup which you can use to restore your website in case it became corrupted or you want to go back in time to a previous state.
The backup files are compressed and can not be executed directly. WP STAGING on the other hand creates a full backup of the whole file system and the database in a working state that you can open like your original production website.

Even though WP STAGING comes with some backup capabilities it's main purpose is to create a clone of your website which you can work on. It harmonies very well with all the mentioned backup plugins above and we recommend that you use it in conjunction with these backup plugins.

Note, that some free backup plugins are not able to support custom tables. (For instance the free version of Updraft plus backup plugin). In that case, your backup plugin is not able to create a backup of your staging site when it is executed on the production site.
The reason is that the tables created by WP STAGING are kind of custom tables beginning with another table prefix.
To bypass this limitation and to be able to create a backup of your staging site, you can setup your backup plugin on the staging site and create the backup from that location. This works well with every available WordPress backup plugin.

* I can not log in to the staging / backup site
If you are using a security plugin like All In One WP Security & Firewall you need to install the latest version of WP STAGING to access your cloned backup site.
Go to WP STAGING > Settings and add the slug to the custom login page which you set up in All In One WP Security & Firewall plugin.



== Official Site ==
https://wp-staging.com

== Installation ==
1. Download the file "wp-staging.zip":
2. Upload and install it via the WordPress plugin backend wp-admin > plugins > add new > uploads
3. Activate the plugin through the 'Plugins' menu in WordPress.

== Official Site ==
https://wp-staging.com

== Installation ==
1. Download the file "wp-staging-pro.zip":
2. Upload and install it via the WordPress plugin backend wp-admin > plugins > add new > uploads
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Start Plugins->Staging

== Screenshots ==

1. Step 1. Create new WordPress staging site
2. Step 2. Scanning your website for files and database tables
3. Step 3. Wordpress Staging site creation in progress
4. Finish!

== Changelog ==

= 3.1.9 =
* New: Option to clean folders uploads, plugins and themes before updating staging site
* New: Option to clean folders uploads, plugins and/or themes during push
* New: Option to symlink uploads folder incl. images from staging to production site. No need to copy images between staging and production site anymore
* New: Show creator user name of staging site
* New: Show notice if sending mails are disabled
* New: Option to disable emails from being sent from staging site
* Fix: Show message and stop execution if php version is lower than 5.5
* Fix: Abort cloning process if table already exists in external database
* Fix: Can not update database credentials in staging sites wp-config.php under rare circumstances
* Fix: During the update process if options table was not selected it didn't get skipped
* Fix: Error if WP is lower than 4.6
* Fix: Can not delete entire staging site on error

= 3.1.8 =
* New: Compatible up to WordPress 5.6
* Fix: Can not clone properly network site if it is in a subfolder
* Fix: Uninstall function throws fatal error
* Fix: Do not write sensitive information into debug.log
* Fix: Update notification shown even if there is no more recent version
* Dev: Prefix composer vendor libraries
* Dev: Add more tests to improve QA

= 3.1.7 =
* Fix: Update notification shown even though there is no more recent release

= 3.1.6 =
* Fix: Database restore fails
* Fix: Do not show cache notice after push
* Fix: memory exhaust on tests
* New: Show confirmation alert on closing website while site cloning or pushing is executed
* Enh: Change authentication to a combination of nonces and access tokens
* Enh: Improve tests performance
* Enh: Add tests for database export and restore

= 3.1.5 =
* HotFix: Activation hook is not fired after first time installation and wpstg optimizer and cron tasks are not set up
* New: Add special admin notice if plugin is not tested with latest WordPress version

= 3.1.4 =
* Fix: Missed updating supported WordPress version

= 3.1.3 =
* New: Compatible up to WordPress 5.5.3
* New: Allow deleting of orphaned staging site entries if staging site was deleted manually before
* Fix: Staging site does not work if database password contains dollar sign in password
* Fix: Prevent fatal error when the plugin is activated, but there is no permission to create folder wp-content/uploads/wp-staging or wp-content/uploads/wp-staging/logs.
* Fix: Notices if plugin is used on PHP 5.6
* Dev: Add new DI container implementation
* Dev: Add composer 2

= 3.1.2 =
* Fix: Fatal error on activation (Syntax error)

= 3.1.2rc =
* Feature: Disable sending emails on staging site
* Feature: Edit button to reconnect broken staging site
* Feature: Copy themes to tmp folder first before pushing to production site
* New: Compatible up to WordPress 5.5.1
* New: Rename Snapshots to Backup
* New: Add WP_ENVIRONMENT_TYPE constant for staging site
* New: Better and wider test coverage
* New: Implementing of automated CI tests
* New: Huge code base refactor for cleaner code
* New: Updated authentication mechanism for ajax requests
* New: Show welcome video message
* New: Show message asking for admin credentials on login form
* New: Move WP STAGING menu down below the menu Plugins
* New: Selected tables are highlighted with a blue background color
* Fix: Show access denied message if a non but existing user tries to access the staging site
* Fix: Remove wp_logout() in staging site login form to prevent multiple login log entries with plugin WP Activity Log
* Fix: Wrong german translations
* Fix: Cloning fails if there is no underscore in table prefix

= 3.1.1 =
* Major refactoring and automated tests integration to make the plugin more robust

= 3.1.0 =
* New: Tested up to WP 5.5.1
* Fix: Cloning fails if there is no table prefix underscore

= 3.0.9 =
* Fix: Login does not work with custom user role
* Fix: Can not login with custom user name

= 3.0.8 =
* Fix: Plugins are sometimes deleted on staging site after log in to admin dashboard
* Fix: Cloning multisite fails without any error message

= 3.0.7 =
* Fix: System info does not work after latest update

= 3.0.6 =
* New: Support for WP 5.5
* New: Major code refactoring
* New: Highlight table selection with blue background color to better differentiate if table is selected or not

= 3.0.5 =
* New: Automatically recreate permalinks after pushing
* New: Don't create wpstgbak_ tables any longer and use the snapshot function
* Fix: Exclude views from cloning and pushing
* Fix: Step switching logic does not work properly
* Fix: Don't select network site tables when main site is cloned
* Fix: Remove snapshot tables from list of copyable tables
* Fix: Fix progress bar when certains steps are skipped
* Fix: Change german translation for REPORT ISSUE
* Fix: Create adapter for function sanitize_textarea_field to prevent fatal error on old WP version

= 3.0.4 =
* New: Support for WordPress 5.4.2
* New: Add nice looking modal after successful pushing
* New: Scroll to bottom if staging site is going to be deleted
* New: Ask for hosting provider in contact form
* New: Ask for login credentials in contact form
* New: Send debug.log after sending error report and user allows it
* New: Show tooltip for unfinished status
* New: Show license key in system info
* New: Improve license expiration notice
* New: Show warning if destination hostname does not contain a scheme
* New: Allow filtering of staging site title
* Fix: Performance improvement. Disable creating back tables since as the new snapshot function is included
* Fix: Stop cloning and show error message if user tries to clone into local database and is going to overwrite production tables
* Fix: Under certain circumstances cloning is interrupted by a missing file exists check
* Fix: Make sure user can not add decimal points into search & replace settings
* Fix: Cluttered user interface. 1,2,3 steps elements are not shown correctly
* Fix: Allow special characters in database password
* Fix: When a staging site is cloned remove orphaned listed staging sites
* Fix: Can not copy tables if prefix is capitalized & has no underscore

= 3.0.3 =
* New: Support for WordPress 5.4.1
* Fix: Fatal error by using get_user_locale() in WordPress 4.7 and older
* Fix: Restoring a snapshot creates another snapshot with prefix wpstgmp_

= 3.0.2 =
* Fix: Preparing Data Step6 fails due to latest change in WordPress 5.4. Previous fix did not solve this for external database cloning

= 3.0.1 =
* Fix: Preparing Data Step6 fails due to latest change in WordPress 5.4
* Fix: Plugin can not be uninstalled on PHP 7.2 and later

= 3.0.0 =
* New: Support for WordPress 5.4
* New: Snapshot function for backing up, exporting and restoring the database
* New: Refactoring code to get more unit-testable code
* New: User interface improvements
* New: Raise minimum PHP version to 5.5
* Fix: Fatal error if the user uses a custom date time format
* Fix: Fatal error if function curl_version() is not available

Full changelog:
[https://wp-staging.com/wp-staging-pro-changelog/](https://wp-staging.com/wp-staging-pro-changelog/)


== Upgrade Notice ==

= 3.1.9 =
* New: Option to clean folders uploads, plugins and themes before updating staging site
* New: Option to clean folders uploads, plugins and/or themes during push
* New: Option to symlink uploads folder incl. images from staging to production site. No need to copy images between staging and production site anymore
* New: Show creator user name of staging site
* New: Show notice if sending mails are disabled
* New: Option to disable emails from being sent from staging site
* Fix: Show message and stop execution if php version is lower than 5.5
* Fix: Abort cloning process if table already exists in external database
* Fix: Can not update database credentials in staging sites wp-config.php under rare circumstances
* Fix: During the update process if options table was not selected it didn't get skipped
* Fix: Error if WP is lower than 4.6
* Fix: Can not delete entire staging site on error
