<?php

class SimpleMailerProcessor
{
	/**
	 * @var null - SimpleMailer class instance
	 */
	private $sm = null;


	/**
	 * @var array - Sanitized user input
	 */
	private $whitelist = array();


	/**
	 * @var null|SimpleMailerWidget - WP Widget
	 */
	public $widget = null;


	/**
	 * SimpleMailerProcessor constructor.
	 *
	 * @param $sm
	 */
	public function __construct($sm) {
		$this->sm = $sm;
		$this->widget = new SimpleMailerWidget();
	}


	/**
	 * Start session, init language and call our action trigger.
	 *
	 */
	public function init() {
		if(!isset($_SESSION)){ session_start(); }
		$this->initLang();
		$this->checkActions();
		if($this->sm->config->includeHeader) {
			add_action('wp_head', array($this, 'addSmFrontendHeader'));
		}
	}


	/**
	 * Actions trigger.
	 * Watches user input and executes certain actions as necessary
	 *
	 */
	protected function checkActions()
	{
		// check & save config data
		if(isset($_POST['action']) && $_POST['action'] == 'sm_handle_config') {
			if(true === $this->sm->config->validateConfigData($this->sm->allowedAdminRoles)) {
				$this->sm->config->save();
			}
			return;
		}
		// check & submit contact form
		if(isset($_POST['action']) && $_POST['action'] == 'sm_form_sent') {
			if(true === $this->validateFormData()) {
				if(!isset($_POST['isajax'])) { $this->sendMail(); }
				else
				{
					if(true === $this->sendMail(false))
					{
						$msgs = $this->sm->reporter->renderMsgs();
						header('Content-type: application/json; charset=utf-8');
						echo json_encode(array('status' => 1, 'msgs' => $msgs));
						exit();
					} else
					{
						$msgs = $this->sm->reporter->renderMsgs();
						header('Content-type: application/json; charset=utf-8');
						echo json_encode(array('status' => 0, 'msgs' => $msgs));
						exit();
					}
				}
			} elseif(isset($_POST['isajax']))
			{
				$msgs = $this->sm->reporter->renderMsgs();
				header('Content-type: application/json; charset=utf-8');
				echo json_encode(array('status' => 0, 'msgs' => $msgs));
				exit();
			}
		}
	}


	/**
	 * Just one simple method to create a menu item in wp admin area
	 */
	public function createAdminMenu()
	{
		// Create a new options menu
		add_options_page(__('SimpleMailer Settings', 'sm'),
			__('SimpleMailer', 'sm'),
			'administrator',
			'sm',
			array($this, 'renderConfigPage')
		);
		// Icon: plugins_url('/images/icon.svg', __FILE__)
	}

	public function editActionLinks($links, $file)
	{
		$pluginpath = plugin_basename($this->sm->config->pluginFile);
		if($file == $pluginpath)
		{
			unset($links['edit']);
			$link = '<a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=sm">'.__('Settings', 'sm').'</a>';
			array_unshift($links, $link);
		}

		return $links;
	}


	/**
	 * Include css, js librarys in wp admin area
	 */
	public function addSmAdminHeader() {
		echo '<link rel="stylesheet" href="'.
			plugins_url('styles/sm-admin-style.css', __FILE__).'" type="text/css" media="all" />';
	}


	/**
	 * Include some frontend css, js stuff
	 */
	public function addSmFrontendHeader()
	{
		$obj = get_queried_object();
		if($this->sm->config->displayFormOnPageId &&
			$this->sm->config->displayFormOnPageId != $obj->ID) { return; }

		add_shortcode('simplemailer', array($this, 'contentFilter'));

		$output = '';
		if($this->sm->config->includeStyles) {
			$output .= "<link rel=\"stylesheet\" href=\"".
				plugins_url('styles/sm-frontend-style.css', __FILE__).
				"\" type=\"text/css\" media=\"all\" />\r\n";
		}
		if($this->sm->config->includeJquery) {
			$output .= "<script src=\"//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"></script>\r\n";
		}
		if($this->sm->config->includeReCaptchaApi) {
			$output .= "<script src=\"https://www.google.com/recaptcha/api.js\"></script>\r\n";
		}
		echo $output;
	}


	/**
	 * Loads the plugin's translated strings
	 */
	protected function initLang() {
		load_plugin_textdomain( 'sm', false,
			dirname(plugin_basename($this->sm->config->pluginFile)).'/languages/' );
		$this->sm->config->locale = get_locale();
	}


	/**
	 * Just a simple method wrapper, used to replace the site/post
	 * content placeholder with the form in the frontend
	 *
	 * @param $params - wp generated content variables
	 *
	 * @return string - Rendered form for showing on the website
	 */
	public function contentFilter($params) { return $this->renderForm(); }


	/**
	 * This method renders the form dyanmically
	 *
	 * @return string - Rendered form for showing on the website
	 */
	public function renderForm()
	{
		$this->sm->config->init();

		$formWrapper = new SimpleMailerWrapper();
		$formWrapper->class = 'sm-form-wrapper';

		$form = new SimpleMailerForm();
		$form->novalidate = $this->sm->config->noValidateClientSide;
		// Do not allow dynamic form names, if someone wants it, they have to pay for it
		$form->name = 'contact';
		$form->id = 'contact';
		if((int)$this->sm->config->send_attachments == 1)
		{
			$form->enctype = 'multipart/form-data';
		}

		$fieldset = new SimpleMailerFieldset();
		$fieldset->legend = $this->sm->config->getText('legend_caption');

		// Field "Name"
		$fieldWrapper = new SimpleMailerWrapper();
		$fieldLabel = new SimpleMailerLabel();
		$fieldLabel->for = 'sendername';
		$input = new SimpleMailerInput();
		$input->class = 'form-control';
		$input->id = 'sendername';
		$input->name = 'sendername';
		$input->size = '30';
		if((int)$this->sm->config->name_required == 1)
		{
			$input->required = true;
			$fieldLabel->add('<span class="required">' . __('Name', 'sm') . "</span>\r\n");
		} else
		{
			$fieldLabel->add('<span>' . __('Name', 'sm') . "</span>\r\n");
		}
		$input->value = (isset($this->whitelist['sendername']) ? $this->whitelist['sendername'] : '');
		$fieldLabel->add($input);
		$fieldWrapper->add($fieldLabel);
		$fieldset->add($fieldWrapper);

		// Field "E-Mail"
		$fieldWrapper = new SimpleMailerWrapper();
		$fieldLabel = new SimpleMailerLabel();
		$fieldLabel->for = 'email';
		$input = new SimpleMailerInput();
		$input->class = 'form-control';
		$input->id = 'email';
		$input->type = 'email';
		$input->placeholder = 'your@email.com';
		$input->name = 'email';
		$input->size = '30';
		if((int)$this->sm->config->email_required == 1)
		{
			$input->required = true;
			$fieldLabel->add('<span class="required">' . __('Email address', 'sm') . "</span>\r\n");
		} else
		{
			$fieldLabel->add('<span>' . __('Email address', 'sm') . "</span>\r\n");
		}
		$input->value = (isset($this->whitelist['email']) ? $this->whitelist['email'] : '');
		$fieldLabel->add($input);
		$fieldWrapper->add($fieldLabel);
		$fieldset->add($fieldWrapper);

		// Field "Phone"
		if((int)$this->sm->config->show_phone == 1)
		{
			$fieldWrapper = new SimpleMailerWrapper();
			$fieldLabel = new SimpleMailerLabel();
			$fieldLabel->for = 'phone';
			$input = new SimpleMailerInput();
			$input->class = 'form-control';
			$input->id = 'phone';
			$input->type = 'tel';
			$input->name = 'phone';
			$input->size = '30';
			if((int)$this->sm->config->phone_required == 1)
			{
				$input->required = true;
				$fieldLabel->add('<span class="required">' . __('Phone number', 'sm') . "</span>\r\n");
			} else
			{
				$fieldLabel->add('<span>' . __('Phone number', 'sm') . "</span>\r\n");
			}
			$input->value = (isset($this->whitelist['phone']) ? $this->whitelist['phone'] : '');
			$fieldLabel->add($input);
			$fieldWrapper->add($fieldLabel);
			$fieldset->add($fieldWrapper);
		}

		// Field "Subject"
		if((int)$this->sm->config->show_subject == 1)
		{
			$fieldWrapper = new SimpleMailerWrapper();
			$fieldLabel = new SimpleMailerLabel();
			$fieldLabel->for = 'subject';
			$input = new SimpleMailerInput();
			$input->class = 'form-control';
			$input->id = 'subject';
			$input->name = 'subject';
			$input->size = '30';
			if((int)$this->sm->config->subject_required == 1)
			{
				$input->required = true;
				$fieldLabel->add('<span class="required">' . __('Subject', 'sm') . "</span>\r\n");
			} else
			{
				$fieldLabel->add('<span>' . __('Subject', 'sm') . "</span>\r\n");
			}
			$input->value = (isset($this->whitelist['subject']) ? $this->whitelist['subject'] : '');
			$fieldLabel->add($input);
			$fieldWrapper->add($fieldLabel);
			$fieldset->add($fieldWrapper);
		}

		// Textarea "Message"
		$fieldWrapper = new SimpleMailerWrapper();
		$fieldLabel = new SimpleMailerLabel();
		$fieldLabel->for = 'message';
		$textarea = new SimpleMailerTextarea();
		$textarea->id = 'message';
		$textarea->class = 'form-control';
		$textarea->name = 'message';
		$textarea->rows = 8;
		$textarea->cols = 40;
		if((int)$this->sm->config->message_required == 1)
		{
			$textarea->required = true;
			$fieldLabel->add('<span class="required">' . __('Message', 'sm') . "</span>\r\n");
		} else
		{
			$fieldLabel->add('<span>' . __('Message', 'sm') . "</span>\r\n");
		}
		$textarea->content = (isset($this->whitelist['message']) ? $this->whitelist['message'] : '');
		$fieldLabel->add($textarea);
		$fieldWrapper->add($fieldLabel);
		$fieldset->add($fieldWrapper);

		// Field "Files"
		if((int)$this->sm->config->send_attachments == 1)
		{
			$fieldWrapper = new SimpleMailerWrapper();
			$fieldLabel = new SimpleMailerLabel();
			$fieldLabel->for = 'attachments';
			$input = new SimpleMailerInput();
			$input->class = 'form-control';
			$input->id = 'attachments';
			$input->name = 'attachments';
			if((int)$this->sm->config->multiple_attachments == 1)
			{
				$input->multiple = true;
				$input->name = 'attachments[]';
				$fieldLabel->add('<span>' . __('Attach documents', 'sm') . "</span><br>\r\n");
			} else
			{
				$fieldLabel->add('<span>' . __('Attach document', 'sm') . "</span><br>\r\n");
			}
			$input->size = '30';
			$input->type = 'file';
			$input->value = (isset($_POST['attachments']) ? $_POST['attachments'] : '');
			$fieldLabel->add($input);
			$fieldWrapper->add($fieldLabel);
			$fieldset->add($fieldWrapper);
		}

		// Honey pot
		$fieldWrapper = new SimpleMailerWrapper();
		$fieldWrapper->class = 'field-wrapper hey-honey';
		$fieldWrapper->style = 'display:none;';
		$fieldLabel = new SimpleMailerLabel();
		$fieldLabel->for = 'honey';
		$fieldLabel->add('Please leave this field empty - we\'re using it to stop robots submitting the form<br>');
		$input = new SimpleMailerInput();
		$input->type = 'text';
		$input->class = 'form-control';
		$input->name = 'honey';
		$input->value = '';
		$fieldLabel->add($input);
		$fieldWrapper->add($fieldLabel);
		$fieldset->add($fieldWrapper);

		// Google reCaptcha
		if((int)$this->sm->config->recaptcha == 1)
		{
			$reCaptcha = new SimpleMailerReCaptcha();
			$reCaptcha->site_key = $this->sm->config->site_key;
			$fieldset->add($reCaptcha);
		}

		// Submit button
		$fieldWrapper = new SimpleMailerWrapper();
		$button = new SimpleMailerButton();
		$button->class = 'button primary';
		$button->id = 'submit';
		$button->add(__('Send message', 'sm'));
		// Action field
		$input = new SimpleMailerInput();
		$input->type = 'hidden';
		$input->name = 'action';
		$input->value = 'sm_form_sent';
		$fieldWrapper->add($input);
		// Add AJAX indicator
		if($this->sm->config->useAjax == true) {
			$input = new SimpleMailerInput();
			$input->type = 'hidden';
			$input->name = 'isajax';
			$input->value = 1;
			$fieldWrapper->add($input);
		}
		$fieldWrapper->add($button);
		$fieldset->add($fieldWrapper);

		$form->add($fieldset);

		// Render delay block
		if($this->sm->config->useAjax == true) {
			$js = new SimpleMailerJsBlocks();
			$js->delaytext = __('Please wait...', 'sm');
			$formWrapper->add($js->renderDelay());
		}

		// Output messages
		$msgs = $this->sm->reporter->renderMsgs();
		if(!empty($msgs)) {
			$formWrapper->add($msgs);
		}

		$formWrapper->add($form);

		// Ajax stuff
		if($this->sm->config->useAjax == true) {
			$formWrapper->add($js->renderJs());
		}

		return $formWrapper->render();
	}


	/**
	 * Renders and outputs the config page in wp admin area
	 *
	 */
	public function renderConfigPage()
	{
		ob_start();
		$this->sm->config->init();
		?>
		<div class="wrap">
			<h1>SimpleMailer</h1>
			<?php echo $this->sm->reporter->renderMsgs(); ?>
			<div class="edit-forms-panel">
				<form method="post" action="">
					<div class="field-group-compresser first">
						<fieldset>
							<legend><?php echo __('Common Settings', 'sm'); ?></legend>
							<div class="form-group">
								<label for="title"><?php echo __('Website name', 'sm'); ?></label><br>
								<p class="field-info"><?php echo __('Website name to be used for email transmission.', 'sm'); ?></p>
								<input id="title" name="sitename" class="form-control" type="text" value="<?php
								echo ((!$this->sm->config->sitename) ? get_bloginfo('name') : $this->sm->config->sitename); ?>">
							</div>
						</fieldset>
					</div>
					<div class="field-group-compresser">
						<fieldset>
							<legend><?php echo __('Email settings', 'sm'); ?></legend>
							<div class="form-group">
								<label for="emailfrom"><?php echo __('Email Address', 'sm'); ?> <span class="required">*</span></label><br>
								<p class="field-info"><?php echo
									__('Your email address. This is the email address to which the SimpleMailer will send all the messages.', 'sm'); ?></p>
								<input id="emailfrom" name="emailfrom" class="form-control" type="email" value="<?php
								echo (($this->sm->config->emailfrom) ? $this->sm->config->emailfrom : ''); ?>" required="required">
							</div>
							<div class="form-group">
								<label for="smtp"><?php echo __('Sending emails via SMTP', 'sm'); ?></label><br>
								<p class="field-info"><?php
									echo __('Simple Mail Transfer Protocol (SMTP) is a protocol that allows the sending of messages over a TCP/IP-based network from one server to another.', 'sm'); ?></p>
								<div class="radio">
									<label class="checkbox-inline"><input id="smtp" name="smtp" type="checkbox" value="1"
											<?php echo ((int)$this->sm->config->smtp == 1) ? ' checked="checked"' : '' ?>> <?php
										echo __('Enable SMTP', 'sm'); ?></label>
								</div>

								<div class="form-group">
									<label for="hostname"><?php echo __('SMTP Host name', 'sm'); ?></label><br>
									<p class="field-info"><?php
										echo __('The host name of the outgoing SMTP (Simple Mail Transfer Protocol) server, such as smtp.example.com.', 'sm'); ?></p>
									<input id="hostname" name="hostname" class="form-control" type="text" value="<?php
									echo (($this->sm->config->hostname) ? $this->sm->config->hostname : ''); ?>">
								</div>
								<div class="form-group">
									<label for="port"><?php echo __('SMTP Port', 'sm'); ?></label><br>
									<p class="field-info"><?php
										echo __('The port number used by the outgoing mail server. Common port numbers for outgoing mail are 25, 465, and 587.', 'sm'); ?></p>
									<input id="port" name="port" class="form-control" type="number" value="<?php
									echo (((int)$this->sm->config->port) ? $this->sm->config->port : ''); ?>">
								</div>
								<div class="form-group">
									<label for="secure"><?php echo __('Encryption', 'sm'); ?></label><br>
									<p class="field-info"><?php echo __('Does the outgoing mail server support SSL or TLS encryption?', 'sm'); ?></p>
									<div class="radio">
										<label class="checkbox-inline"><input id="secure" name="secure" type="checkbox" value="SSL"
												<?php echo ($this->sm->config->secure == 'SSL') ? ' checked="checked"' : '' ?>> SSL</label>
									</div>
									<div class="radio">
										<label class="checkbox-inline"><input id="secure" name="secure" type="checkbox" value="START_TLS"
												<?php echo ($this->sm->config->secure == 'START_TLS') ? ' checked="checked"' : '' ?>> START_TLS</label>
									</div>
								</div>
								<div class="form-group">
									<label for="user"><?php echo __('SMTP username', 'sm'); ?></label><br>
									<p class="field-info"><?php echo
										__('Your user name for this account, such as appleseed. Some email providers want your full email address as your user name.', 'sm'); ?></p>
									<input id="user" name="user" class="form-control" type="text" value="<?php
									echo (($this->sm->config->user) ? $this->sm->config->user : ''); ?>">
								</div>
								<div class="form-group">
									<label for="password"><?php echo __('SMTP password', 'sm'); ?></label><br>
									<p class="field-info"><?php echo __('The email password you use to sign in to your account.', 'sm'); ?></p>
									<input id="password" name="password" class="form-control" type="password" value="<?php
									echo (($this->sm->config->password) ? (($this->sm->config->smtpPassEncryption) ?
										$this->sm->config->decrypt(base64_decode($this->sm->config->password)) : $this->sm->config->password) : ''); ?>">
								</div>
								<div class="form-group">
									<label for="emailfrom_name"><?php echo __('Full Name', 'sm'); ?></label><br>
									<p class="field-info"><?php echo
										__('Choose your sender name as you would like it to appear in messages that you send. Example: John Appleseed.', 'sm'); ?></p>
									<input id="emailfrom_name" name="emailfrom_name" class="form-control" type="text" value="<?php
									echo (($this->sm->config->emailfrom_name) ? $this->sm->config->emailfrom_name : ''); ?>">
								</div>
								<div class="form-group">
									<label for="debug"><?php echo __('SMTP Debug', 'sm'); ?></label><br>
									<p class="field-info"><?php echo __('Debug Mode: 0, 1, 2 or 3. Default: 0 (Off).', 'sm'); ?></p>
									<input id="debug" name="debug" class="form-control" type="number" value="<?php
									echo (isset($this->sm->config->debug) ? $this->sm->config->debug : 0); ?>">
								</div>
							</div>
						</fieldset>
					</div>
					<div class="field-group-compresser">
						<fieldset>
							<legend><?php echo __('Form fields', 'sm'); ?></legend>
							<div class="form-group">
								<label for="send_attachments"><?php echo __('File Upload field', 'sm'); ?></label><br>
								<p class="field-info"><?php echo
									__('Allow sending of files as email attachments, e. g. a collection of images or a PDF document.', 'sm'); ?></p><br>
								<div class="checkbox">
									<label><input id="send_attachments" name="send_attachments" type="checkbox" value="1"
										<?php echo (((int)$this->sm->config->send_attachments == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Show file upload', 'sm'); ?></label><br>
								</div>

								<div class="form-group">
									<label for="multiple_attachments"><?php echo __('Multiple file uploads', 'sm'); ?></label><br>
									<p class="field-info"><?php echo __('Allow sending multiple files in the attachment.', 'sm'); ?></p><br>
									<div class="checkbox">
											<label><input id="multiple_attachments" name="multiple_attachments" type="checkbox" value="1"
												<?php echo (((int)$this->sm->config->multiple_attachments == 1) ? ' checked="checked"' : ''); ?>>
												<?php echo __('Allow multiple uploads', 'sm'); ?></label><br>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label for="show_subject"><?php echo __('Subject field', 'sm'); ?></label><br>
								<p class="field-info"><?php echo
									__('Add a subject field to the form.', 'sm'); ?></p><br>
								<div class="checkbox">
									<label><input id="show_subject" name="show_subject" type="checkbox" value="1"
										<?php echo (((int)$this->sm->config->show_subject == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Show subject', 'sm'); ?></label><br>
								</div>
							</div>

							<div class="form-group">
									<label for="show_phone"><?php echo __('Phone number field', 'sm'); ?></label><br>
									<p class="field-info"><?php echo __('Add a phone number field to the form.', 'sm'); ?></p><br>
								<div class="checkbox">
									<label><input id="show_phone" name="show_phone" type="checkbox" value="1"
										<?php echo (((int)$this->sm->config->show_phone == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Show phone number', 'sm'); ?></label><br>
								</div>
							</div>
						</fieldset>
					</div>
					<div class="field-group-compresser">
						<fieldset>
							<legend><?php echo __('Required fields', 'sm'); ?></legend>
							<div class="form-group">
								<label for="name_required"><?php echo __('Field Name', 'sm'); ?></label><br>
								<p class="field-info"><?php echo __('This option allows you to define Name as a required field.', 'sm'); ?></p>
								<div class="checkbox">
									<label><input id="name_required" name="name_required" type="checkbox" value="1"
										<?php echo (((int)$this->sm->config->name_required == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Define name as required field', 'sm'); ?></label>
								</div>
							</div>

							<div class="form-group">
								<label for="email_required"><?php echo __('Email Address', 'sm'); ?></label><br>
								<p class="field-info"><?php echo __('This option allows you to define Email as a required field.', 'sm'); ?></p>
								<div class="checkbox">
									<label><input id="email_required" name="email_required" type="checkbox" value="1"
										<?php echo (((int)$this->sm->config->email_required == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Define Email as required field', 'sm'); ?></label>
								</div>
							</div>

							<div class="form-group">
								<label for="phone_required"><?php echo __('Phone number field', 'sm'); ?></label><br>
								<p class="field-info"><?php echo __('This option allows you to define Phone Number as a required field.', 'sm'); ?></p>
								<div class="checkbox">
									<label><input id="phone_required" name="phone_required" type="checkbox" value="1"
										<?php echo (((int)$this->sm->config->phone_required == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Define Phone as required field', 'sm'); ?></label>
								</div>
							</div>

							<div class="form-group">
								<label for="subject_required"><?php echo __('Email subject', 'sm'); ?></label><br>
								<p class="field-info"><?php echo __('This option allows you to define Subject as a required field.', 'sm'); ?></p>
								<div class="checkbox">
									<label><input id="subject_required" name="subject_required" type="checkbox" value="1"
										<?php echo (((int)$this->sm->config->subject_required == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Define Subject as required field', 'sm'); ?>
									</label>
								</div>
							</div>

							<div class="form-group">
								<label for="message_required"><?php echo __('Message field', 'sm'); ?></label><br>
								<p class="field-info"><?php echo __('This option allows you to define Message as a required field.', 'sm'); ?></p>
								<div class="checkbox">
									<label><input id="message_required" name="message_required" type="checkbox" value="1"
										<?php echo (((int)$this->sm->config->message_required == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Define Message as required field', 'sm'); ?></label>
								</div>
							</div>

						</fieldset>
					</div>

					<div class="field-group-compresser">
						<fieldset>
							<legend><?php echo __('Security settings', 'sm'); ?></legend>
							<div class="form-group">
								<label for="recaptcha"><?php echo __('Google reCAPTCHA V2', 'sm'); ?></label><br>
								<p class="field-info"><?php echo __('Activate this checkbox if you want to use reCAPTCHA.', 'sm'); ?></p>
								<div class="radio">
									<label class="checkbox-inline"><input id="recaptcha" type="checkbox" name="recaptcha" value="1"
										<?php echo (((int)$this->sm->config->recaptcha == 1) ? ' checked="checked"' : ''); ?>>
										<?php echo __('Use reCAPTCHA', 'sm'); ?></label>
								</div>
								<div class="form-group">
									<label for="site-key"><?php echo __('Site key', 'sm'); ?></label><br>
									<p class="field-info"><?php echo __('Get Google\'s ReCaptcha Site key and enter it here', 'sm'); ?>
										<a href="https://www.google.com/recaptcha/admin">https://www.google.com/recaptcha/admin</a></p>
									<input id="site-key" class="form-control" name="site_key" value="<?php
									echo (($this->sm->config->site_key) ? $this->sm->config->site_key : '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'); ?>">
								</div>
								<div class="form-group">
									<label for="secret-key"><?php echo __('Secret key', 'sm'); ?></label><br>
									<p class="field-info"><?php echo __('Get Google\'s ReCaptcha Secret key and enter it here', 'sm'); ?>
										<a href="https://www.google.com/recaptcha/admin">https://www.google.com/recaptcha/admin</a></p>
									<input id="secret-key" class="form-control" name="secret_key" value="<?php
									echo (($this->sm->config->secret_key) ? $this->sm->config->secret_key : '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'); ?>">
								</div>
							</div>
						</fieldset>
					</div>

					<input type="hidden" name="action" value="sm_handle_config" />
					<div class="interact-compresser">
						<input type="submit" name="send" class="button-primary" value="<?php echo __('Save Settings', 'sm'); ?>">
					</div>
				</form>
			</div>
		</div>
		<?php echo ob_get_clean();
	}


	/**
	 * This method ensures that all user input have been
	 * made correctly before the form can be sent.
	 *
	 * @return bool
	 */
	public function validateFormData()
	{
		$this->sm->config->init();

		$email = null;
		$sendername = null;
		$phone = null;
		$message = null;
		$subject = null;
		$attachments = array();

		// Validate email
		$email = !empty($_POST['email']) ? $this->sm->sanitizer->email($_POST['email']) : null;
		// Validate sendername
		$sendername = !empty($_POST['sendername']) ? stripslashes($this->sm->sanitizer->text($_POST['sendername'],
			array('maxLength' => 100))) : null;
		// Validate phone
		$phone = !empty($_POST['phone']) ? $this->sm->sanitizer->text($_POST['phone'],
			array('maxLength' => 50)) : null;
		// Validate subject
		$subject = !empty($_POST['subject']) ? stripslashes($this->sm->sanitizer->text($_POST['subject'],
			array('maxLength' => 200))) : null;
		// Validate message
		$message = !empty($_POST['message']) ? stripslashes($this->sm->sanitizer->textarea($_POST['message'])) : null;

		// Prepare attachments
		if(isset($_FILES['attachments']) &&
			(!empty($_FILES['attachments']['name'][0]) || !empty($_FILES['attachments']['name']))
			&& !$this->sm->reporter->isError())
		{
			if(count($_FILES['attachments']['name']) >= 1 && (int)$this->sm->config->multiple_attachments == 1) {
				$i = 0;
				foreach($_FILES['attachments']['name'] as $key => $attachment) {

					if($this->sm->config->maxFileUploads && $this->sm->config->maxFileUploads <= $i) {
						$this->sm->reporter->setMsg('warning',
							sprintf(__('File %s could not sent, the maximum number (%d) of attachments has been reached!', 'sm'),
							$this->sm->sanitizer->text($_FILES['attachments']['name'][$key]),
							(int)$this->sm->config->maxFileUploads));
						continue;
					}
					if($_FILES['attachments']['error'][$key] == UPLOAD_ERR_OK) {
						$attachments[$i] = array('tmp_name' =>
							$_FILES['attachments']['tmp_name'][$key],
							'name' => $_FILES['attachments']['name'][$key]
						);
						$i++;
					} else {
						$this->sm->reporter->setMsg('warning', sprintf(__('File %s could not sent!', 'sm'),
							$this->sm->sanitizer->text($_FILES['attachments']['name'][$key])));
					}
				}
			} else {
				if($_FILES['attachments']['error'] == UPLOAD_ERR_OK) {
					$attachments[0] = array('tmp_name' =>
						$_FILES['attachments']['tmp_name'],
						'name' => $_FILES['attachments']['name']
					);
				} else {
					$this->sm->reporter->setMsg('warning', __('No file was sent!', 'sm'));
				}
			}
		}

		$this->whitelist = array(
			'email' => $email,
			'sendername' => $sendername,
			'phone' => $phone,
			'message' => $message,
			'subject' => $subject,
			'attachments' => $attachments
		);

		// Check empty required values
		if((int)$this->sm->config->name_required == 1 && empty($_POST['sendername'])) {
			$this->sm->reporter->setMsg('error', __('Please enter your name!', 'sm'));
		}
		if((int)$this->sm->config->email_required == 1 && empty($_POST['email'])) {
			$this->sm->reporter->setMsg('error', __('Please enter your email address!', 'sm'));
		}
		if((int)$this->sm->config->show_phone == 1 && (int)$this->sm->config->phone_required == 1 &&
			empty($_POST['phone'])) {
			$this->sm->reporter->setMsg('error', __('Please enter your phone number!', 'sm'));
		}
		if((int)$this->sm->config->message_required == 1 && empty($_POST['message'])) {
			$this->sm->reporter->setMsg('error', __('Please enter your message!', 'sm'));
		}
		if((int)$this->sm->config->show_subject == 1 && (int)$this->sm->config->subject_required == 1 &&
			empty($_POST['subject'])) {
			$this->sm->reporter->setMsg('error', __('Please enter the subject of the message you want to send!', 'sm'));
		}
		if(!empty($_POST['honey'])) {
			$this->sm->reporter->setMsg('error',
				__('You have been identified as a bot because you filled in a special field, which should remain empty.',
					'sm'));
		}

		if($this->sm->reporter->isError()) return false;

		if((int)$this->sm->config->recaptcha == 1)
		{
			if(empty($_POST['g-recaptcha-response'])) {
				$this->sm->reporter->setMsg('error', __('Please complete the reCAPTCHA below!', 'sm'));
				return false;
			}
			$response = json_decode($this->verifySite('https://www.google.com/recaptcha/api/siteverify?secret='
				.$this->sm->config->secret_key.'&response='.$_POST['g-recaptcha-response'].
				'&remoteip='.@$_SERVER['REMOTE_ADDR']), true );

			if($response['success'] == false) {
				$this->sm->reporter->setMsg('error', __('The response parameter is invalid or malformed!', 'sm'));
			}
		}

		if($this->sm->reporter->isError()) return false;

		if((int)$this->sm->config->email_required == 1 && empty($email)) {
			$this->sm->reporter->setMsg('error', __('The entered email address is not valid!', 'sm'));
		}
		if((int)$this->sm->config->name_required == 1 && empty($sendername)) {
			$this->sm->reporter->setMsg('error', __('The entered "name" is not valid!', 'sm'));
		}
		if((int)$this->sm->config->show_phone == 1 && (int)$this->sm->config->phone_required == 1 && empty($phone)) {
			$this->sm->reporter->setMsg('error', __('The entered "phone number" is not valid!', 'sm'));
		}
		if((int)$this->sm->config->show_subject == 1 && (int)$this->sm->config->subject_required == 1 && empty($subject)) {
			$this->sm->reporter->setMsg('error', __('The entered "subject" is not valid!', 'sm'));
		}
		if((int)$this->sm->config->message_required == 1 && empty($message)) {
			$this->sm->reporter->setMsg('error', __('The entered "message" is not valid!', 'sm'));
		}

		// Let's validate required admin params (Only when the Admin has forgotten to finish his configuration)
		$confcompleted = true;
		if(empty($this->sm->config->emailfrom)) { $confcompleted = false; }
		// Ok, the Admin selected SMTP option, let's check required SMTP params
		if((int)$this->sm->config->smtp == 1 && (
				empty($this->sm->config->hostname) ||
				empty($this->sm->config->port) ||
				empty($this->sm->config->user) ||
				empty($this->sm->config->password))
			)
		{ $confcompleted = false; }
		// Is the Google reCAPTCHA activated, check required params
		if((int)$this->sm->config->recaptcha == 1 && (
				empty($this->sm->config->site_key) ||
				empty($this->sm->config->secret_key))
			)
		{ $confcompleted = false; }
		if(!$confcompleted) {
			$this->sm->reporter->setMsg('error',
				__('The configuration of the SimpleMailer plugin was not completed. Please complete the configuration in the administrator area!', 'sm'));
		}

		if($this->sm->reporter->isError()) return false;
		return true;
	}


	/**
	 * Send our contact mail
	 */
	protected function sendMail($redirect = true)
	{
		$mailer = new SimpleMailerMailer($this->sm->config);

		// SMTP isn't enabled
		if((int)$this->sm->config->smtp != 1) {
			$mailer->IsMail();
		}
		$mailer->addReplyTo($this->whitelist['email'], $this->whitelist['sendername']);
		$mailer->addAddress($mailer->From, $mailer->FromName);
		$mailer->Subject = $this->whitelist['subject'];

		$mailer->Body = '';

		if(!empty($this->whitelist['sendername'])) {
			$mailer->Body .= __('Name: ', 'sm').$this->whitelist['sendername']."\r\n";
		}
		if(!empty($this->whitelist['email'])) {
			$mailer->Body .= __('Email: ', 'sm').$this->whitelist['email']."\r\n";
		}
		if(!empty($this->whitelist['phone'])) {
			$mailer->Body .= __('Phone number: ', 'sm').$this->whitelist['phone']."\r\n";
		}
		if(!empty($this->whitelist['message'])) {
			$mailer->Body .= "\r\n".$this->whitelist['message'];
		}
		if(!empty($this->whitelist['attachments'])) {
			foreach($this->whitelist['attachments'] as $attachment) {
				$mailer->AddAttachment($attachment['tmp_name'], $attachment['name']);
			}
		}

		if($mailer->send())
		{
			$this->sm->reporter->setMsg('success', $this->sm->config->getText('success_message'));
			$this->whitelist = array();

			if($redirect) $this->redirect($this->getCurrentPageUrl());
			return true;
		}
		return false;
	}


	/**
	 * Let Google verify the captcha
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
	protected function verifySite($url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_USERAGENT,
			"Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
		$curlData = curl_exec($curl);
		curl_close($curl);
		return $curlData;
	}


	/**
	 * Returns the current page URL
	 *
	 * @return string - Current page URL
	 */
	public function getCurrentPageUrl() {
		return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}

	/**
	 * Just a simple redirect method
	 *
	 * @param $url
	 * @param bool $flag
	 * @param int $statusCode
	 */
	public function redirect($url, $flag = true, $statusCode = 303)
	{
		header('Location: '.$this->sm->sanitizer->url($url), $flag, $statusCode);
		die();
	}

	/**
	 * Install plugin
	 *
	 */
	public function install() { $this->sm->config->installConfig(); }


	/**
	 * Uninstall plugin
	 *
	 */
	public static function uninstall() { SimpleMailerConfig::uninstallConfig(); }
}