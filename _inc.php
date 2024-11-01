<?php
include(__DIR__.'/SimpleMailerReporter.php');
include(__DIR__.'/SimpleMailerSanitizer.php');
include(__DIR__.'/SimpleMailerConfig.php');
include(__DIR__.'/SimpleMailerMailer.php');
include(__DIR__.'/SimpleMailerForm.php');
include(__DIR__.'/SimpleMailerWidget.php');
include(__DIR__.'/SimpleMailerProcessor.php');
include(__DIR__.'/SimpleMailerClass.php');

global $sm;
$sm = new SimpleMailer();