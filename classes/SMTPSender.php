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
                        $transport = \Swift_SmtpTransport::newInstance($account['server'], $account['port']);
                        $transport->setUsername($account['username']);
                        $transport->setPassword($account['password']);
                        $transport->setLocalDomain('[127.0.0.1]');
                        if (isset($account['encryption'])) {
                            $transport->setEncryption(strtolower($account['encryption']));
                        }
                        $mailer = \Swift_Mailer::newInstance($transport);

                        $message = new \Swift_Message();
                        $message->setId(microtime(true) * 10000 . '.' . $email->sender->email);
                        $message->setBoundary('boundary-' . md5(uniqid()));

                        if ($email->sender->name !== null) {
                            $message->setFrom([$email->sender->email => $email->sender->name]);
                        } else {
                            $message->setFrom($email->sender->email);
                        }

                        if ($email->replyTo->email !== null) {
                            if ($email->replyTo->name !== null) {
                                $message->setReplyTo([$email->replyTo->email => $email->replyTo->name]);
                            } else {
                                $message->setReplyTo($email->replyTo->email);
                            }
                        }

                        $recipients = $email->recipients->getList();
                        foreach ($recipients as $recipient) {
                            $message->addTo($recipient->email, $recipient->name);
                        }

                        if ($email->subject !== null) {
                            $message->setSubject($email->subject);
                        }

                        $contentParts = $email->content->getList();

                        foreach ($contentParts as $contentPart) {
                            $message->attach(new \Swift_MimePart($contentPart->content, $contentPart->mimeType, $contentPart->encoding));
                        }

                        if ($email->returnPath !== null) {
                            $message->setReturnPath($email->returnPath);
                        }

                        if ($email->priority !== null) {
                            $message->setPriority($email->priority);
                        }

                        $attachments = $email->attachments->getList();
                        foreach ($attachments as $attachment) {
                            if ($attachment instanceof \BearFramework\Emails\Email\FileAttachment) {
                                if ($attachment->filename !== null) {
                                    $messageAttachment = \Swift_Attachment::fromPath($attachment->filename);
                                    if ($attachment->mimeType !== null) {
                                        $messageAttachment->setContentType($attachment->mimeType);
                                    }
                                    if ($attachment->name !== null) {
                                        $messageAttachment->setFilename($attachment->name);
                                    }
                                    $message->attach($messageAttachment);
                                }
                            } elseif ($attachment instanceof \BearFramework\Emails\Email\ContentAttachment) {
                                if ($attachment->content !== null) {
                                    $messageAttachment = \Swift_Attachment::newInstance();
                                    $messageAttachment->setBody($attachment->content);
                                    if ($attachment->mimeType !== null) {
                                        $messageAttachment->setContentType($attachment->mimeType);
                                    }
                                    if ($attachment->name !== null) {
                                        $messageAttachment->setFilename($attachment->name);
                                    }
                                    $message->attach($attachment);
                                }
                            }
                        }

                        $embeds = $email->embeds->getList();
                        foreach ($embeds as $embed) {
                            if ($embed instanceof \BearFramework\Emails\Email\FileEmbed) {
                                if ($embed->filename !== null) {
                                    $messageAttachment = \Swift_Attachment::fromPath($embed->filename);
                                    if ($embed->mimeType !== null) {
                                        $messageAttachment->setContentType($embed->mimeType);
                                    }
                                    if ($embed->name !== null) {
                                        $messageAttachment->setFilename($embed->name);
                                    }
                                    if ($embed->cid !== null) {
                                        $messageAttachment->setId($embed->cid);
                                    } else {
                                        $messageAttachment->setId(md5($embed->filename) . '.' . $email->sender->email);
                                    }
                                    $messageAttachment->setDisposition('inline');
                                    $message->attach($messageAttachment);
                                }
                            } elseif ($embed instanceof \BearFramework\Emails\Email\ContentEmbed) {
                                if ($embed->content !== null) {
                                    $messageAttachment = \Swift_Attachment::newInstance();
                                    $messageAttachment->setBody($embed->content);
                                    if ($embed->mimeType !== null) {
                                        $messageAttachment->setContentType($embed->mimeType);
                                    }
                                    if ($embed->name !== null) {
                                        $messageAttachment->setFilename($embed->name);
                                    }
                                    if ($embed->cid !== null) {
                                        $messageAttachment->setId($embed->cid);
                                    } else {
                                        $messageAttachment->setId(md5($embed->content) . '.' . $email->sender->email);
                                    }
                                    $messageAttachment->setDisposition('inline');
                                    $message->attach($embed);
                                }
                            }
                        }

                        $signers = $email->signers->getList();
                        foreach ($signers as $signer) {
                            if ($signer instanceof \BearFramework\Emails\Email\DKIMSigner) {
                                $message->attachSigner(new \Swift_Signers_DKIMSigner($signer->privateKey, $signer->domain, $signer->selector));
                            } elseif ($signer instanceof \BearFramework\Emails\Email\SMIMESigner) {
                                $message->attachSigner(new \Swift_Signers_SMimeSigner($signer->certificate, $signer->privateKey));
                            }
                        }

                        $result = $mailer->send($message) > 0;
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
