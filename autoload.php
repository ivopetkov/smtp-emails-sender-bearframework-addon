<?php

/*
 * SMTP emails sender addon for Bear Framework
 * https://github.com/ivopetkov/smtp-emails-sender-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

BearFramework\Addons::register('ivopetkov/smtp-emails-sender-bearframework-addon', __DIR__, [
    'require' => [
        'bearframework/emails-addon',
        'ivopetkov/swiftmailer-bearframework-addon'
    ]
]);
