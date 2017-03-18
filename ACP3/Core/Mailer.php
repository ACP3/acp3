<?php

namespace ACP3\Core;

use ACP3\Core\Helpers\StringFormatter;
use ACP3\Core\Mailer\MailerMessage;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\System\Installer\Schema;
use InlineStyle\InlineStyle;

/**
 * Class Email
 * @package ACP3\Core
 */
class Mailer
{
    /**
     * @var \ACP3\Core\Logger
     */
    protected $logger;
    /**
     * @var \ACP3\Core\View
     */
    protected $view;
    /**
     * @var SettingsInterface
     */
    protected $config;
    /**
     * @var \ACP3\Core\Helpers\StringFormatter
     */
    protected $stringFormatter;
    /**
     * @var string
     */
    private $subject = '';
    /**
     * @var string
     */
    private $body = '';
    /**
     * @var string
     */
    private $htmlBody = '';
    /**
     * @var string
     */
    private $urlWeb = '';
    /**
     * @var string
     */
    private $mailSignature = '';
    /**
     * @var string|array
     */
    private $from;
    /**
     * @var string|array
     */
    private $recipients;
    /**
     * @var bool
     */
    private $bcc = false;
    /**
     * @var array
     */
    private $attachments = [];
    /**
     * @var string
     */
    private $template = '';
    /**
     * @var \PHPMailer
     */
    private $phpMailer;

    /**
     * Mailer constructor.
     * @param Logger $logger
     * @param View $view
     * @param SettingsInterface $config
     * @param StringFormatter $stringFormatter
     */
    public function __construct(
        Logger $logger,
        View $view,
        SettingsInterface $config,
        StringFormatter $stringFormatter
    ) {
        $this->logger = $logger;
        $this->view = $view;
        $this->config = $config;
        $this->stringFormatter = $stringFormatter;
    }

    /**
     * @param string|array $from
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param string $mailSignature
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setMailSignature($mailSignature)
    {
        $this->mailSignature = $mailSignature;

        return $this;
    }

    /**
     * @param string $htmlText
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setHtmlBody($htmlText)
    {
        $this->htmlBody = $htmlText;

        return $this;
    }

    /**
     * @param string $urlWeb
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setUrlWeb($urlWeb)
    {
        $this->urlWeb = $urlWeb;

        return $this;
    }

    /**
     * @param bool $bcc
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setBcc($bcc)
    {
        $this->bcc = (bool)$bcc;

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $body
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param array|string $recipients
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @param string $attachment
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setAttachments($attachment)
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * @param string $template
     *
     * @return $this
     * @deprecated since version 4.8.0, to be removed with version 5.0.0
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param MailerMessage $data
     * @return $this
     */
    public function setData(MailerMessage $data)
    {
        $this
            ->setAttachments($data->getAttachments())
            ->setBody($data->getBody())
            ->setFrom($data->getFrom())
            ->setHtmlBody($data->getHtmlBody())
            ->setMailSignature($data->getMailSignature())
            ->setRecipients($data->getRecipients())
            ->setSubject($data->getSubject())
            ->setTemplate($data->getTemplate())
            ->setUrlWeb($data->getUrlWeb());

        return $this;
    }

    /**
     * Sends the email
     *
     * @return bool
     */
    public function send()
    {
        try {
            $this->configure();

            $this->phpMailer->Subject = $this->generateSubject();

            if (is_array($this->from) === true) {
                $this->phpMailer->setFrom($this->from['email'], $this->from['name']);
            } else {
                $this->phpMailer->setFrom($this->from);
            }

            $this->generateBody();

            // Add attachments to the E-mail
            if (count($this->attachments) > 0) {
                foreach ($this->attachments as $attachment) {
                    if (!empty($attachment) && is_file($attachment)) {
                        $this->phpMailer->addAttachment($attachment);
                    }
                }
            }

            if (!empty($this->recipients)) {
                return $this->bcc === true ? $this->sendBcc() : $this->sendTo();
            }
        } catch (\phpmailerException $e) {
            $this->logger->error('mailer', $e);
        } catch (\Exception $e) {
            $this->logger->error('mailer', $e);
        }

        return false;
    }

    /**
     * @return string
     */
    protected function generateSubject()
    {
        return "=?utf-8?b?" . base64_encode($this->decodeHtmlEntities($this->subject)) . "?=";
    }

    /**
     * Generates the E-mail body
     *
     * @return $this
     */
    private function generateBody()
    {
        if (!empty($this->template)) {
            $mail = [
                'charset' => 'UTF-8',
                'title' => $this->subject,
                'body' => !empty($this->htmlBody) ? $this->htmlBody : $this->stringFormatter->nl2p($this->body),
                'signature' => $this->getHtmlSignature(),
                'url_web_view' => $this->urlWeb
            ];
            $this->view->assign('mail', $mail);

            $htmlDocument = new InlineStyle($this->view->fetchTemplate($this->template));
            $htmlDocument->applyStylesheet($htmlDocument->extractStylesheets());

            $this->phpMailer->msgHTML($htmlDocument->getHTML());

            // Fallback for E-mail clients which don't support HTML E-mails
            if (!empty($this->body)) {
                $this->phpMailer->AltBody = $this->decodeHtmlEntities($this->body . $this->getTextSignature());
            } else {
                $this->phpMailer->AltBody = $this->phpMailer->html2text(
                    $this->htmlBody . $this->getHtmlSignature(),
                    true
                );
            }
        } else {
            $this->phpMailer->Body = $this->decodeHtmlEntities($this->body . $this->getTextSignature());
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getHtmlSignature()
    {
        if (!empty($this->mailSignature)) {
            if ($this->mailSignature === strip_tags($this->mailSignature)) {
                return $this->stringFormatter->nl2p($this->mailSignature);
            }
            return $this->mailSignature;
        }
        return '';
    }

    /**
     *
     * @param string $data
     *
     * @return string
     */
    private function decodeHtmlEntities($data)
    {
        return html_entity_decode($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @return string
     */
    private function getTextSignature()
    {
        if (!empty($this->mailSignature)) {
            return "\n-- \n" . $this->phpMailer->html2text($this->mailSignature, true);
        }
        return '';
    }

    /**
     * Special sending logic for bcc only E-mails
     *
     * @return bool
     */
    private function sendBcc()
    {
        if (is_array($this->recipients) === false || isset($this->recipients['email']) === true) {
            $this->recipients = [$this->recipients];
        }

        foreach ($this->recipients as $recipient) {
            set_time_limit(10);

            $this->addRecipients($recipient, true);
        }

        return $this->phpMailer->send();
    }

    /**
     * Adds multiple recipients to the to be send email
     *
     * @param string|array $recipients
     * @param bool $bcc
     *
     * @return $this
     */
    private function addRecipients($recipients, $bcc = false)
    {
        if (is_array($recipients) === true) {
            if (empty($recipients['email']) === false && empty($recipients['name']) === false) {
                $this->addRecipient($recipients['email'], $recipients['name'], $bcc);
            } else {
                foreach ($recipients as $recipient) {
                    if (is_array($recipient) === true) {
                        $this->addRecipient($recipient['email'], $recipient['name'], $bcc);
                    } else {
                        $this->addRecipient($recipient, '', $bcc);
                    }
                }
            }
        } else {
            $this->addRecipient($recipients, '', $bcc);
        }

        return $this;
    }

    /**
     * Adds a single recipient to the to be send email
     *
     * @param string $email
     * @param string $name
     * @param bool $bcc
     *
     * @return $this
     */
    private function addRecipient($email, $name = '', $bcc = false)
    {
        if ($bcc === true) {
            $this->phpMailer->addBCC($email, $name);
        } else {
            $this->phpMailer->addAddress($email, $name);
        }

        return $this;
    }

    /**
     * Special sending logic for E-mails without bcc addresses
     *
     * @return bool
     */
    private function sendTo()
    {
        if (is_array($this->recipients) === false || isset($this->recipients['email']) === true) {
            $this->recipients = [$this->recipients];
        }

        foreach ($this->recipients as $recipient) {
            set_time_limit(20);
            $this->addRecipients($recipient);
            $this->phpMailer->send();
            $this->phpMailer->clearAllRecipients();
        }

        return true;
    }

    /**
     * Resets the currently set mailer values back to there default values
     *
     * @return $this
     */
    public function reset()
    {
        $this->subject = '';
        $this->body = '';
        $this->htmlBody = '';
        $this->urlWeb = '';
        $this->mailSignature = '';
        $this->from = '';
        $this->recipients = null;
        $this->bcc = false;
        $this->attachments = [];
        $this->template = '';

        if ($this->phpMailer) {
            $this->phpMailer->clearAllRecipients();
            $this->phpMailer->clearAttachments();
        }

        return $this;
    }

    /**
     * Initializes PHPMailer and sets the basic configuration parameters
     *
     * @return $this
     */
    private function configure()
    {
        if ($this->phpMailer === null) {
            $this->phpMailer = new \PHPMailer(true);

            $settings = $this->config->getSettings(Schema::MODULE_NAME);

            if (strtolower($settings['mailer_type']) === 'smtp') {
                $this->phpMailer->set('Mailer', 'smtp');
                $this->phpMailer->Host = $settings['mailer_smtp_host'];
                $this->phpMailer->Port = $settings['mailer_smtp_port'];
                $this->phpMailer->SMTPSecure = in_array($settings['mailer_smtp_security'], ['ssl', 'tls'])
                    ? $settings['mailer_smtp_security']
                    : '';
                if ((bool)$settings['mailer_smtp_auth'] === true) {
                    $this->phpMailer->SMTPAuth = true;
                    $this->phpMailer->Username = $settings['mailer_smtp_user'];
                    $this->phpMailer->Password = $settings['mailer_smtp_password'];
                }
            } else {
                $this->phpMailer->set('Mailer', 'mail');
            }
            $this->phpMailer->CharSet = 'UTF-8';
            $this->phpMailer->Encoding = 'quoted-printable';
            $this->phpMailer->WordWrap = 76;
        }

        return $this;
    }
}
