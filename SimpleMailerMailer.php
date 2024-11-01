<?php
include(ABSPATH.'wp-includes/class-phpmailer.php');

class SimpleMailerMailer extends \PHPMailer
{
	protected $config;

	public function __construct($config) {
		parent::__construct();
		$this->config = $config;
		$this->setDefaults();
	}

	protected function setDefaults()
	{
		try
		{
			$this->CharSet = $this->config->emailCharSet;
			if($this->config->smtp == 1) $this->isSMTP();
			$this->setLanguage($this->config->emailLanguage);
			$this->SMTPDebug = $this->config->debug;
			$this->Host = $this->config->hostname;
			$this->SMTPAuth = true;
			$this->Username = $this->config->user;
			if(true === $this->config->smtpPassEncryption) {
				$this->Password = $this->config->decrypt(base64_decode($this->config->password));
			} else {
				$this->Password = $this->config->password;
			}
			$this->SMTPSecure = $this->config->secure;
			$this->Port = $this->config->port;
			$this->From = $this->config->emailfrom;
			$this->FromName = $this->config->emailfrom_name;
			$this->isHTML(false);

		} catch (phpmailerException $e)
		{
			$error = $e->errorMessage();
			SimpleMailerReporter::writeLog($error);
			return false;
		}
	}

	public function send()
	{
		$o = '';
		ob_start();
		if(!parent::send()) {
			SimpleMailerReporter::writeLog('Mailer Error: ' . $this->ErrorInfo);
			$o = ob_get_clean();
			SimpleMailerReporter::writeLog($o);
			@ob_end_clean();
			return false;
		}
		@ob_end_clean();
		return true;
	}
}