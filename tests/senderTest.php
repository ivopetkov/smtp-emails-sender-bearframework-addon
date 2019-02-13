<?php

/*
 * SMTP emails sender addon for Bear Framework
 * https://github.com/ivopetkov/smtp-emails-sender-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class SenderTest extends BearFramework\AddonTests\PHPUnitTestCase
{

    /**
     * 
     * @return \BearFramework\App
     */
    protected function getApp(): \BearFramework\App
    {
        $app = parent::getApp();
        $app->emails
                ->registerSender(function() {
                    return new IvoPetkov\BearFrameworkAddons\SMTPSender([
                        [
                            'email' => 'john@example.com',
                            'server' => '',
                            'port' => '',
                            'encryption' => '',
                            'username' => '',
                            'password' => ''
                        ]
                    ]);
                });
        return $app;
    }

    /**
     * 
     */
    public function testSend()
    {
        $app = $this->getApp();

        $email = $app->emails->make();
        $email->subject = 'Hi';
        $email->sender->email = 'john@example.com';
        $email->sender->name = 'John';
        $email->recipients->add('mark@example.com', 'Mark');
        $email->content->add('<strong>Hi</strong>', 'text/html');
        $email->content->add('Hi there', 'text/plain');

        // Cannot connect error is expected.
        try {
            $app->emails->send($email);
        } catch (\Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), 'The email cannot be send (reason: Connection could not be established') !== false);
            return;
        }
        $this->assertFalse(true); // Should not come here.
    }

}
