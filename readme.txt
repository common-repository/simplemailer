=== SimpleMailer Contact Form ===
Contributors: bigin
Donate link: https://ehret-studio.com/lab/simplemailer-kontaktformular-plugin-fur-wordpress/#donate
Tags: contact form, form, email, SMTP
Requires at least: 4.0
Tested up to: 4.8.2
Stable tag: 0.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SimpleMailer - is an end-user friendly contact form plugin, it is quite easy to configure and customize.

== Description ==

A common feature of many websites is a contact form. SimpleMailer - is an end-user friendly contact form plugin, it is quite easy to configure and customize. The basic plugin has been kept extremely simple to allow an easy and quick implementation in your WordPress site.

SimpleMailer supports SMTP by default, but if you like, you can also use the PHP **mail()** function. 

Standard SimpleMailer form submits data via "normal" browser request(POST), but you can make them nicer by using AJAX to submit the form data in the background. 

This plugin was developed completely object-oriented and requires at least **PHP 5.6 >**. SimpleMailer places a great importance on the security of sensitive user data such as the password and treats it with special care.


== Installation ==

1. Upload the SimpleMailer plugin to your plugins directory **/wp-content/plugins/**, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Once done, go to the plugin settings page at **Settings » SimpleMailer** and enter the relevant data, then save (Note that you also need "administrator" capability to change the SimpleMailer settings).

= Displaying the contact form on the front-end =

There are three options on how to display the contact form on your WordPress site:

1. Displaying the SimpleMailer Widget
2. Including Short Code **[simplemailer]** on a specific page
3. By calling the function **<?php $sm->getForm(); ?>** in your theme file

Let's just say, you want to add the SimpleMailer form widget to your website’s sidebar. You can find the SimpleMailer form widget on **Appearance » Widgets** page in your WordPress dashboard. Go ahead and add SimpleMailer widget into your right sidebar area. If you visit your website now, you will be able to see the contact form widget in the sidebar on your page. Note that your WordPress theme should support the widget areas.

The easiest way to display SimpleMailer form on a specific page is to use Shortcode. For instance, the following Shortcode in the body of a post or page would add contact form to that post or page: **[simplemailer]**

And the third option can be used to display the form anywhere you want in your theme files. To do this, simply add the function **<?php $sm->getForm(); ?>**  to one of your theme PHP files.

= Configuring SimpleMailer =

As mentioned before, SimpleMailer supports SMTP by default, the checkbox **Enable SMTP** must be activated.

Now, fill in the child input fields below this checkbox to complete the SMTP settings.

A valid configuration for a Gmail account could look something like this:

* Enable SMTP: Checked
* SMTP Host name: smtp.gmail.com
* SMTP Port: 25
* Encryption: START_TLS
* SMTP username: fake.user@gmail.com
* SMTP password: your_password
* SMTP Debug: 0

If you would like to use the PHP **mail()** function instead, just deactivate the **Enable SMTP** checkbox. The entries in the child input fields below are then irrelevant.

At the bottom of the settings page you will find the Google [reCaptcha](https://developers.google.com/recaptcha/) configuration. To start using reCAPTCHA, you need to sign up for an API key pair for your site. You then insert these into the corresponding input fields. Please also note that the PHP cURL extension has to be installed on your server for reCAPTCHA to work.

All other settings are self-explanatory.

= Advanced settings for advanced users =

Advanced configuration settings are managed within the **config.php** file. By default, however, there is no config.php file in your plugin installation directory, you have to create this file first, as follow. The directory **/wp-content/plugins/simplemailer/** contains a **custom.config.php** file. You will need to make a copy of the custom.config.php file and name it config.php, that one will take priority over default SimpleMailer settings.

== Changelog ==

Nothing changed yet

== Upgrade Notice ==

Nothing to upgrade

== Screenshots ==

1. SimpleMailer automatically adapts to your website's style.