<?php

class SimpleMailerConfig
{
	/**
	 * @var null - WP Database instance
	 */
	protected $db = null;

	/**
	 * @var null|string - SM Configs table
	 */
	protected $tablename = null;

	/**
	 * @var null - Error reporter class instance
	 */
	protected $reporter = null;

	/**
	 * @var null - Sanitizer class instance
	 */
	protected $sanitizer = null;

	/**
	 * @var array|null - Data of this plugin
	 */
	public $pluginData = null;

	/**
	 * @var null - The file name of the plugin
	 */
	public $pluginFile = null;

	/**
	 * @var array - Default config values
	 */
	private $defaults = array(
		'version' => '0.9',
		'sitename' => null,
		'smtp' => 1,
		'hostname' => null,
		'port' => null,
		'secure' => null,
		'user' => null,
		'password' => null,
		'emailfrom_name' => null,
		'emailfrom' => null,
		'debug' => 0,
		'send_attachments' => null,
		'multiple_attachments' => null,
		'show_subject' => 1,
		'show_phone' => null,
		'name_required' => 1,
		'email_required' => 1,
		'phone_required' => null,
		'subject_required' => 1,
		'message_required' => 1,
		'recaptcha' => 1,
		'site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
		'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'
	);

	/**
	 * If "true" adds additional includes within the <head></head> section of the template
	 * by the wp_head() function. Although this is theme-dependent, it is one of the most
	 * essential theme hooks, so it is widely supported.
	 *
	 */
	public $includeHeader = true;

		/**
		 * Includes SimpleMailer stylesheets within the head section
		 *
		 */
		public $includeStyles = true;

		/**
		 * Includes jQuery within the head section
		 *
		 */
		public $includeJquery = false;

		/**
		 * Includes Google's reCaptcha API within the head section
		 *
		 */
		public $includeReCaptchaApi = true;

	/**
	 * Client-side form validation
	 *
	 */
	public $noValidateClientSide = false;

	/**
	 * @var string - email language
	 */
	public $emailLanguage = 'en';

	/**
	 * @var string PHPMailer character encoding
	 */
	public $emailCharSet = 'UTF-8';

	/**
	 * @var null|integer - Page id for display form on it
	 */
	public $displayFormOnPageId = null;

	/**
	 * @var int - The maximum number of attachments
	 */
	public $maxFileUploads = 5;

	/**
	 * @var bool - Transfer data via AJAX
	 */
	public $useAjax = false;


	public $smtpPassEncryption = false;

		public $encryptKey = 'bs&tz7G9*h';

		public $encryptIv = '1234567812345678';

		public $cipherMethod = 'aes128';


	public $locale = null;


	public $text = array(
		'en_US' => array(
			'success_message' => 'Your message has been sent successfully. Thank your for your interest!',
			'legend_caption' => 'Please fill in the following fields'
		),
		'de_DE' => array(
			'success_message' => 'Ihre Nachricht wurde erfolgreich versendet. Vielen Dank für Ihr Interesse!',
			'legend_caption' => 'Bitte füllen Sie die folgenden Felder aus'
		)
	);


	/**
	 * SimpleMailerConfig constructor.
	 * Overrides default config values
	 *
	 * @param $sm object - SimpleMailerClass
	 */
	public function __construct($sm)
	{
		if(file_exists(__DIR__.'/config.php')) include(__DIR__.'/config.php');
		$this->db = $sm->db;
		$this->tablename = $this->db->prefix.'simplemailer';
		$this->reporter = $sm->reporter;
		$this->sanitizer = $sm->sanitizer;
		$this->allowedAdminRoles = $sm->allowedAdminRoles;
	}

	/**
	 * initializes the config object using the provided settings
	 */
	public function init()
	{
		if($this->db->get_var("SHOW TABLES LIKE '$this->tablename'"))
		{
			$res = $this->db->get_results("SELECT id, name, value FROM $this->tablename LIMIT 100");
			if(!$res) return;
			foreach($res as $data)
			{
				$this->{$data->name} = $data->value;
			}
		}
	}

	/**
	 * Validates config data before save()
	 *
	 * @param $allowedRoles
	 *
	 * @return bool
	 */
	public function validateConfigData($allowedRoles)
	{
		if(!function_exists('wp_get_current_user')) return;
		$user = wp_get_current_user();

		$this->init();

		// Only allowed for admin users
		if(!array_intersect($this->allowedAdminRoles, $user->roles)) {
			return;
		}

		// First of, check required data
		if(!isset($_POST['emailfrom']) || empty($_POST['emailfrom'])) {
			$this->reporter->setMsg('error', __('Please enter the sender email address.', 'sm'));
		} else {
			$this->emailfrom = $this->sanitizer->email($_POST['emailfrom']);
			if(empty($this->emailfrom)) {
				$this->reporter->setMsg('error', __('Please enter a valid email address of the sender.', 'sm'));
			}
		}

		// Ok, the Admin selected SMTP option, let's check required SMTP params
		if(isset($_POST['smtp']) && $_POST['smtp'] == 1)
		{
			if(!isset($_POST['hostname']) || empty($_POST['hostname'])) {
				$this->reporter->setMsg('error', __('Please enter SMTP host name.', 'sm'));
			}
			if(!isset($_POST['port']) || empty($_POST['port'])) {
				$this->reporter->setMsg('error', __('Please enter the SMTP port number.', 'sm'));
			}
			if(!isset($_POST['user']) || empty($_POST['user'])) {
				$this->reporter->setMsg('error', __('Please enter your SMTP username.', 'sm'));
			}
			if(!isset($_POST['password']) || empty($_POST['password'])) {
				$this->reporter->setMsg('error', __('Please enter your SMTP password.', 'sm'));
			}
		}

		// Is the Google reCAPTCHA activated, check required params
		if(isset($_POST['recaptcha']) && $_POST['recaptcha'] == 1)
		{
			if(!isset($_POST['site_key']) || empty($_POST['site_key'])) {
				$this->reporter->setMsg('error', __('Please enter the reCAPTCHA site key.', 'sm'));
			}
			if(!isset($_POST['secret_key']) || empty($_POST['secret_key'])) {
				$this->reporter->setMsg('error', __('Please enter reCAPTCHA secret key.', 'sm'));
			}
		}

		// Validate site name
		if(!isset($_POST['sitename']) || empty($_POST['sitename'])) {
			$this->sitename = get_bloginfo('name');
		} else {
			$this->sitename = $this->sanitizer->text($_POST['sitename']);
		}
		// Validate SMTP
		$this->smtp = null;
		if(isset($_POST['smtp']) && $_POST['smtp'] == 1) { $this->smtp = 1; }
		// Validate host name
		if(isset($_POST['hostname'])) { $this->hostname = $this->sanitizer->text($_POST['hostname']);}
		// Validate port number
		if(isset($_POST['port'])) { $this->port = (int)$_POST['port']; }
		// Validate protocol
		$this->secure = null;
		if(isset($_POST['secure']) && ($_POST['secure'] == 'SSL' || $_POST['secure'] == 'START_TLS')) {
			$this->secure = $_POST['secure'];
		}
		// Validate SMTP user
		if(isset($_POST['user'])) { $this->user = $this->sanitizer->text($_POST['user']); }
		// Validate pass
		if(isset($_POST['password'])) {
			// Let's shorten out pass to the max len
			$shorten = mb_substr($_POST['password'], 0, 100, 'utf-8');
			// Encryption enabled?
			if(true === $this->smtpPassEncryption) {
				$this->password = base64_encode($this->encrypt($shorten));
			} else {
				$this->password = $shorten;
			}
		}
		// Validate email from name
		if(isset($_POST['emailfrom_name'])) {$this->emailfrom_name = $this->sanitizer->text($_POST['emailfrom_name']);}
		// Validate debug
		if(isset($_POST['debug']) && ((int)$_POST['debug'] > -1 && (int)$_POST['debug'] <= 3)) {
			$this->debug = (int)$_POST['debug'];
		}
		// Validate enabled/disabled fields
		$this->send_attachments = null;
		if(isset($_POST['send_attachments']) && $_POST['send_attachments'] == 1) {$this->send_attachments = 1;}
		$this->multiple_attachments = null;
		if(isset($_POST['multiple_attachments']) && $_POST['multiple_attachments'] == 1) {$this->multiple_attachments = 1;}
		$this->show_subject = null;
		if(isset($_POST['show_subject']) && $_POST['show_subject'] == 1) {$this->show_subject = 1;}
		$this->show_phone = null;
		if(isset($_POST['show_phone']) && $_POST['show_phone'] == 1) {$this->show_phone = 1;}
		// Validate required fields
		$this->name_required = null;
		if(isset($_POST['name_required']) && $_POST['name_required'] == 1) {$this->name_required = 1;}
		$this->email_required = null;
		if(isset($_POST['email_required']) && $_POST['email_required'] == 1) {$this->email_required = 1;}
		$this->phone_required = null;
		if(isset($_POST['phone_required']) && $_POST['phone_required'] == 1) {$this->phone_required = 1;}
		$this->subject_required = null;
		if(isset($_POST['subject_required']) && $_POST['subject_required'] == 1) {$this->subject_required = 1;}
		$this->message_required = null;
		if(isset($_POST['message_required']) && $_POST['message_required'] == 1) {$this->message_required = 1;}
		// Validate google recaptcha
		$this->recaptcha = null;
		if(isset($_POST['recaptcha']) && $_POST['recaptcha'] == 1) {$this->recaptcha = 1;}
		if(isset($_POST['site_key'])) { $this->site_key = $this->sanitizer->text($_POST['site_key']);}
		if(isset($_POST['secret_key'])) { $this->secret_key = $this->sanitizer->text($_POST['secret_key']);}

		if($this->reporter->isError()) return false;
		return true;
	}

	/**
	 * Inserts the configuration values into the database
	 */
	public function save()
	{
		foreach($this->defaults as $name => $value)
		{
			if(false === $this->db->update($this->tablename, array(
					'value' => $this->{$name}
				),
				array('name' => $name),
				array('%s', '%s'),
				array('%s')
			)) {
				SimpleMailerReporter::writeLog('MySQL-Error when updating ' . $name . ' value');
				$this->reporter->setMsg('warning', __('The settings may not have been saved completely. Turn error reporting on to get more detailed error information in the debug.log file.', 'sm'));
			}
		}
		$this->reporter->setMsg('success', __('SimpleMailer settings have been saved successfully.', 'sm'));
	}


	/**
	 * openssl_encrypt — Encrypts data
	 *
	 * @param $string - The plaintext string to be encrypted
	 *
	 * @return string
	 */
	public function encrypt($string) {
		return openssl_encrypt($string, $this->cipherMethod, $this->encryptIv, true, $this->encryptIv);
	}


	/**
	 * openssl_decrypt — Decrypts data
	 *
	 * @param $encrypted - The encrypted string to be decrypted
	 *
	 * @return string
	 */
	public function decrypt($encrypted) {
		return openssl_decrypt($encrypted, $this->cipherMethod, $this->encryptIv, true, $this->encryptIv);
	}

	/**
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getText($key) {
		return (isset($this->text[$this->locale][$key]) ? $this->text[$this->locale][$key] : @$this->text['en_US'][$key]);
	}

	/**
	 * Method triggered during installation of the plugin
	 * and creates the relevant table in the database
	 */
	public function installConfig()
	{
		$this->pluginData = get_plugin_data($this->pluginFile);
		if(empty($this->pluginData['Version'])) {
			SimpleMailerReporter::writeLog('SimpleMailer Error when installing the plugin: "Cannot read version number"');
			return false;
		}
		$this->init();
		// The plugin does not yet exist
		if(empty($this->version))
		{
			$charset_collate = $this->db->get_charset_collate();
			$sql = "CREATE TABLE $this->tablename (
				id int(10) NOT NULL AUTO_INCREMENT,
				name varchar(55) NOT NULL,
				value tinytext,
				PRIMARY KEY  (id),
				KEY name (name)
			) $charset_collate;";

			require_once(ABSPATH.'wp-admin/includes/upgrade.php' );
			dbDelta($sql);
			foreach($this->defaults as $name => $value) {
				$this->db->insert($this->tablename, array(
						'name' => $name,
						'value' => $value
					)
				);
			}
			return true;
		}

		// Installed version is not equivalent to the new one you are trying to install
		if(version_compare($this->version, $this->pluginData['Version'], '!=')) {
			// Let's upgrade/downgrade our plugin version in the database to the current one
			$this->version = $this->sanitizer->text($this->pluginData['Version']);
			$this->save();
			return true;
		}
	}

	/**
	 * Uninstall plugin data
	 */
	public static function uninstallConfig()
	{
		global $wpdb, $sm;
		if($wpdb->get_var("SHOW TABLES LIKE '".$sm->config->tablename."'")) {
			$wpdb->query("DROP TABLE IF EXISTS ".$sm->config->tablename);
		}
	}
}