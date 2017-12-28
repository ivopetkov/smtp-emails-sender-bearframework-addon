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

    public function send(\BearFramework\Emails\Email $email): bool
    {
        $app = App::get();
        $options = $app->addons->get('ivopetkov/smtp-emails-sender-bearframework-addon')->options;
        if (isset($options['accounts']) && is_array($options['accounts'])) {
            foreach ($options['accounts'] as $account) {
                if (is_array($account) && isset($account['email'], $account['server'], $account['port'], $account['username'], $account['password'])) {
                    if (strlen($email->sender->email) > 0 && $account['email'] === $email->sender->email) {
                        $transport = new \Swift_SmtpTransport($account['server'], $account['port']);
                        $transport->setUsername($account['username']);
                        $transport->setPassword($account['password']);
                        $transport->setLocalDomain('[127.0.0.1]');
                        if (isset($account['encryption'])) {
                            $transport->setEncryption(strtolower($account['encryption']));
                        }

                        $result = $app->swiftMailer->send($transport, $email);

                        if ($result === 0) {
                            throw new \Exception('The email cannot be send.');
                        }
                        return true;
                    }
                }
            }
        }
        return false;
    }

}
