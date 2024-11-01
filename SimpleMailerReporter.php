<?php

class SimpleMailerReporter
{
	protected $msgs = array();
	protected $error = false;


	public function setMsg($type, $text) {
		$this->msgs[] = array('type' => $type, 'text' => $text);
		if($type == 'error') $this->error = true;
		$_SESSION['sm']['error'] =  $this->error;
		$_SESSION['sm']['msgs'] = $this->msgs;
	}


	public function setError($value = true) {
		$this->error = $value;
		$_SESSION['sm']['error'] =  $this->error;
	}


	public function isError() {
		$this->error = !empty($_SESSION['sm']['error']) ? true : false;
		return $this->error;
	}


	public function renderMsgs()
	{
		$output = '';
		$this->msgs = !empty($_SESSION['sm']['msgs']) ? $_SESSION['sm']['msgs'] : array();
		if(empty($this->msgs)) return;
		foreach($this->msgs as $msg) {
			switch ($msg['type']) {
				case 'error':
					$output .= '<div id="message" class="notice error notice-error"><p><strong>'.$msg['text'].'</strong></p></div>';
					break;
				case 'success':
					$output .= '<div id="message" class="notice updated notice-success"><p><strong>'.$msg['text'].'</strong></p></div>';
					break;
				case 'warning':
					$output .= '<div id="message" class="notice warning notice-warning"><p><strong>'.$msg['text'].'</strong></p></div>';
					break;
			}
		}
		//$_SESSION['sm']['msgs'] = null;
		//$_SESSION['sm']['error'] = null;
		unset($_SESSION['sm']);

		return $output;
	}

	public static function writeLog($entry)
	{
		if(!defined('WP_DEBUG') || !defined('WP_DEBUG_LOG') ||
			true !== WP_DEBUG || true !== WP_DEBUG_LOG) { return;}

		if(!function_exists('write_log'))
		{
			if(is_array($entry) || is_object($entry)) {
				error_log(print_r($entry, true));
			} else {
				error_log($entry);
			}
			return true;
		}

		write_log($entry);
	}
}