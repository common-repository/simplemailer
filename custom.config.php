<?php defined('ABSPATH') or die('Yes, you\'re a real hacker!');
/**
 * NOTE: In order to have seperate configs for your SimpleMailer plugin,
 * rename this file from custom.config.php to config.php.
 * The configuration system integrates these override values automatically.
 */

/**
 * Enter a valid post or page ID here, to allow display form for that specific page
 *
 * @var null|integer
 */
$this->displayFormOnPageId = null;

/**
 * Include Headers
 * If "true" adds additional includes within the <head></head> section of the template
 * by the wp_head() function. Although this is theme-dependent, it is one of the most
 * essential theme hooks, so it is widely supported.
 *
 * @var bool
 */
$this->includeHeader = true;

	/**
	 * Includes SimpleMailer stylesheets within the head section
	 *
	 * @var bool
	 */
	$this->includeStyles = true;

	/**
	 * Includes jQuery within the head section
	 *
	 * @var bool
	 */
	$this->includeJquery = false;

	/**
	 * Includes Google's reCaptcha API within the head section
	 *
	 * @var bool
	 */
	$this->includeReCaptchaApi = true;

/**
 * Set to "true" in order to turn off client-side form validation
 */
$this->noValidateClientSide = false;

/**
 * Set the language for error messages in the log file (Mailer only)
 * optional default: en – ISO 639-1 2-character language code (e.g. French is "fr", "de" for German)
 *
 * @var string
 */
$this->emailLanguage = 'en';

/**
 * @var string - PHPMailer character encoding
 */
$this->emailCharSet = 'UTF-8';

/**
 * @var int - The maximum number of file attachments
 */
$this->maxFileUploads = 5;

/**
 * Requires header includes to be "true" and jQuery loaded, see: $this->includeHeader
 * and $this->includeJquery variables above in the file
 *
 * @var bool - Transfer data via AJAX
 */
$this->useAjax = false;

/**
 * Set this value to "true" if you want to store the SMTP password encrypted in the database.
 * The openssl_encrypt() method will be used to encrypt the passwords.
 *
 * NOTE: Do not change this variable after you have finished configuring the plugin or repeat plugin configuration.
 *
 * @var bool
 */
$this->smtpPassEncryption = false;

	/**
	 * The cipher method.
	 * For a list of available cipher methods, use openssl_get_cipher_methods()
	 *
	 * @var string
	 */
	$this->cipherMethod = 'aes128';

	/**
	 * A non-NULL Initialization Vector
	 *
	 * @var string
	 */
	$this->encryptIv = '1234567812345678';

	/**
	 * An openssl_encrypt/decrypt key
	 *
	 * @var string
	 */
	$this->encryptKey = 'bs&tz7G9*h';

/**
 * Customize confirmation messages and some labels.
 * If you intend plugin to be used in a language other than English,
 * enter the text in your language here with the corresponding locale.
 *
 * @var array
 */
$this->text = array(
	'en_US' => array(
		'success_message' => 'Your message has been sent successfully. Thank your for your interest!',
		'legend_caption' => 'Please fill in the following fields'
	),
	'de_DE' => array(
		'success_message' => 'Ihre Nachricht wurde erfolgreich versendet. Vielen Dank für Ihr Interesse!',
		'legend_caption' => 'Bitte füllen Sie die folgenden Felder aus'
	)
);