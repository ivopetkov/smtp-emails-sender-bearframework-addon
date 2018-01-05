<?php

/*
 * SMTP emails sender addon for Bear Framework
 * https://github.com/ivopetkov/smtp-emails-sender-bearframework-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov\BearFrameworkAddons;

use BearFramework\App;

/**
 * SMTP emails sender.
 */
class SMTPSender implements \BearFramework\Emails\ISender
{

    private $accounts = [];

    public function __construct(array $accounts = [])
    {
        $this->accounts = $accounts;
    }

    public function send(\BearFramework\Emails\Email $email): bool
    {
        $app = App::get();
        foreach ($this->accounts as $account) {
            if (is_array($account) && isset($account['email'], $account['server'], $account['port'], $account['username'], $account['password'])) {
                if (strlen($email->sender->email) > 0 && $account['email'] === $email->sender->email) {
                    $transport = new \Swift_SmtpTransport($account['server'], $account['port']);
                    $transport->setUsername($account['username']);
                    $transport->setPassword($account['password']);
                    $transport->setLocalDomain('[127.0.0.1]');
                    if (isset($account['encryption'])) {
                        $transport->setEncryption(strtolower($account['encryption']));
                    }
                    try {
                        $result = $app->swiftMailer->send($transport, $email);
                    } catch (\Exception $e) {
                        $result = 0;
                        $failureReason = $e->getMessage();
                    }
                    if ($result === 0) {
                        throw new \Exception('The email cannot be send (reason: ' . (isset($failureReason) ? $failureReason : 'unknown') . ')');
                    }
                    return true;
                }
            }
        }
        return false;
    }

}
