<?php

/**
 * Newsletter
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

namespace ACP3\Modules\Newsletter;

use ACP3\Core;

abstract class Helpers
{

    /**
     * @var Core\Date
     */
    protected static $date;
    /**
     * @var Core\Lang
     */
    protected static $lang;

    /**
     * @var Core\URI
     */
    protected static $uri;
    /**
     * @var Model
     */
    protected static $model;
    /**
     * @var Core\View
     */
    protected static $view;

    protected static function _init()
    {
        if (!self::$model) {
            self::$model = new Model(
                Core\Registry::get('Db'),
                Core\Registry::get('Lang'),
                Core\Registry::get('Auth')
            );

            self::$lang = Core\Registry::get('Lang');
            self::$uri = Core\Registry::get('URI');
            self::$view = Core\Registry::get('View');
            self::$date = Core\Registry::get('Date');
        }
    }

    /**
     * Versendet einen Newsletter
     *
     * @param $newsletterId
     * @param null $recipients
     * @param bool $bcc
     * @return bool
     */
    public static function sendNewsletter($newsletterId, $recipients, $bcc = false)
    {
        self::_init();

        $settings = Core\Config::getSettings('newsletter');
        $newsletter = self::$model->getOneById($newsletterId);
        $from = array(
            'email' => $settings['mail'],
            'name' => CONFIG_SEO_TITLE
        );

        $mailer = new Core\Mailer(self::$view, $bcc);
        $mailer
            ->setFrom($from)
            ->setSubject($newsletter['title'])
            ->setUrlWeb(HOST_NAME . self::$uri->route('newsletter/index/details/id_' . $newsletterId))
            ->setMailSignature($settings['mailsig']);

        if ($newsletter['html'] == 1) {
            $mailer->setTemplate('newsletter/email.tpl');
            $mailer->setHtmlBody($newsletter['text']);
        } else {
            $mailer->setBody($newsletter['text']);
        }

        $mailer->setRecipients($recipients);

        return $mailer->send();
    }

    /**
     * Meldet eine E-Mail-Adresse beim Newsletter an
     *
     * @param string $emailAddress
     *    Die anzumeldende E-Mail-Adresse
     * @return boolean
     */
    public static function subscribeToNewsletter($emailAddress)
    {
        self::_init();

        $hash = md5(mt_rand(0, microtime(true)));
        $host = htmlentities($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8');
        $url = 'http://' . $host . self::$uri->route('newsletter/index/activate/hash_' . $hash . '/mail_' . $emailAddress);
        $settings = Core\Config::getSettings('newsletter');

        $subject = sprintf(self::$lang->t('newsletter', 'subscribe_mail_subject'), CONFIG_SEO_TITLE);
        $body = str_replace('{host}', $host, self::$lang->t('newsletter', 'subscribe_mail_body')) . "\n\n";

        $from = array(
            'email' => $settings['mail'],
            'name' => CONFIG_SEO_TITLE
        );

        $mailer = new Core\Mailer(Core\Registry::get('View'));
        $mailer
            ->setFrom($from)
            ->setSubject($subject)
            ->setMailSignature($settings['mailsig']);

        if ($settings['html'] == 1) {
            $mailer->setTemplate('newsletter/email.tpl');

            $body .= '<a href="' . $url . '">' . $url . '<a>';
            $mailer->setHtmlBody(Core\Functions::nl2p($body));
        } else {
            $body .= $url;
            $mailer->setBody($body);
        }

        $mailer->setRecipients($emailAddress);

        $mailSent = $mailer->send();
        $bool = false;

        // Newsletter-Konto nur erstellen, wenn die E-Mail erfolgreich versendet werden konnte
        if ($mailSent === true) {
            $insertValues = array(
                'id' => '',
                'mail' => $emailAddress,
                'hash' => $hash
            );
            $bool = self::$model->insert($insertValues, Model::TABLE_NAME_ACCOUNTS);
        }

        return $mailSent === true && $bool !== false;
    }

}