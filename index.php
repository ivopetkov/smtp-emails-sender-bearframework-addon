<?php

/*
 * SMTP emails sender addon for Bear Framework
 * https://github.com/ivopetkov/smtp-emails-sender-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

use BearFramework\App;

$app = App::get();
$context = $app->context->get(__FILE__);

$context->classes->add('IvoPetkov\BearFrameworkAddons\SMTPSender', 'classes/SMTPSender.php');

$app->emails->registerSender('IvoPetkov\BearFrameworkAddons\SMTPSender');