=== Google Cloud Storage plugin ===
Contributors: google
Tags: google, Google Cloud Storage
Requires at least: 3
Stable tag: 0.1.4
Tested up to: 4.8
Requires PHP: 5.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for uploading media files to Google Cloud Storage.

== Description ==

Google Cloud Storage plugin allows you to upload media files to a
Google Cloud Storage bucket.

== Installation ==

1. Download the plugin and place it in your `/wp-content/plugins/` directory.

2. Enable this plugin in the WordPress admin UI,

3. Configure your Google Cloud Storage bucket in the plugin setting
   UI.

4. In Google Cloud Console, set the default acl of the bucket so that
   `allUsers` can read.

Depending on your environment, you may need to configure a Google
Service Account to call the APIs.

To run this plugin on **Google App Engine for PHP 7.2**, it will work
without additional configuration.

To run this plugin on **Google Compute Engine** or **App Engine Flexible**,
you will need to do the following:

* Visit Cloud Console, go to `Compute` -> `instances` and select
  the instance where WordPress is running.

* Stop the instance.

* Once the instance has stopped, click **Edit** and you can now
  modify the scopes under **Cloud API access scopes**. Change
  the Storage scope from **Read Only** to **Full**.

If you want to run this plugin outside of Google Cloud Platform, you
need to configure your service account as follows:

* Visit Cloud Console, go to `IAM & Admin` -> `Service accounts`
  and create a service account with `Storage Object Admin`
  permission and download the json key file.

* Upload the json key file to the hosting server. Don't put it
  in a public serving area.

* Add the following line to wp-config.php (replace the file path
  with the real one).

  ```
  putenv('GOOGLE_APPLICATION_CREDENTIALS=/secure-place/my-service-account.json');
```

== Frequently Asked Questions ==

Q. The plugin crashes with `No project ID was provided, and we
were unable to detect a default project ID`, what's wrong?

A. See the section about configuring the service account in the
`Installation` section.

Q. How to configure the default ACL on my Google Cloud Storage bucket?

A. See: https://wordpress.org/support/topic/google-storage-not-work/page/2/#post-8897852

== Changelog ==

= 0.1.4 =
* Uses the most recent release of the Google Cloud Storage client library
* Updates all dependencies

= 0.1.3 =
* Added a section for configuring service account to the readme
* Added Frequently Asked Questions section to the readme
* Updated dependencies

= 0.1.2 =
* Added "Tested up to" field to the readme

= 0.1.1 =
* Bundle vendor dir in the zip file

= 0.1 =
* Initial release
