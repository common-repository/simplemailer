<?php

class SimpleMailerWidget extends WP_Widget
{
	public function __construct()
	{
		// Set base values for the widget
		parent::__construct(
			'SimpleMailerWidget',
			'SimpleMailer Widget',
			array('description' => 'A widget that displays SimpleMailer contact form')
		);
		add_action('widgets_init', array($this, 'register'));
	}

	public function register() {
		register_widget('SimpleMailerWidget');
	}

	public function widget($args, $instance) {
		global $sm;
		$sm->getForm();
	}
}
