<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Mailer sınıfı - E-posta gönderme işlemlerini yönetir
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * PHPMailer örneği
     */
    private $mailer;

    /**
     * Yapılandırıcı
     */
    public function __construct()
    {
        // PHPMailer dosyalarını yükle
        $this->loadPHPMailer();

        // PHPMailer örneğini oluştur
        $this->mailer = new PHPMailer(true);

        // Ayarları yükle
        $this->configure();
    }

    /**
     * PHPMailer dosyalarını yükler
     */
    private function loadPHPMailer()
    {
        $vendorPath = dirname(__FILE__) . '/Vendors/PHPMailer/src/';

        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            require_once $vendorPath . 'PHPMailer.php';
            require_once $vendorPath . 'SMTP.php';
            require_once $vendorPath . 'Exception.php';
        }
    }

    /**
     * Mailer'ı yapılandırır
     */
    private function configure()
    {
        // SMTP ayarları
        if (Sword::getData('mail_driver', 'smtp') === 'smtp') {
            $this->mailer->isSMTP();
            $this->mailer->Host = Sword::getData('mail_host', 'localhost');
            $this->mailer->SMTPAuth = Sword::getData('mail_auth', true);
            $this->mailer->Username = Sword::getData('mail_username', '');
            $this->mailer->Password = Sword::getData('mail_password', '');
            $this->mailer->SMTPSecure = Sword::getData('mail_encryption', PHPMailer::ENCRYPTION_STARTTLS);
            $this->mailer->Port = Sword::getData('mail_port', 587);
        }

        // Varsayılan gönderen
        $fromEmail = Sword::getData('mail_from_email', 'noreply@localhost');
        $fromName = Sword::getData('mail_from_name', 'Sword Framework');
        $this->mailer->setFrom($fromEmail, $fromName);

        // Karakter seti
        $this->mailer->CharSet = Sword::getData('mail_charset', 'UTF-8');
    }

    /**
     * E-posta gönderir
     *
     * @param string|array $to Alıcı e-posta adresi
     * @param string $subject Konu
     * @param string $body İçerik
     * @param bool $isHTML HTML mi?
     * @return bool Başarılı mı?
     */
    public function send($to, $subject, $body, $isHTML = true)
    {
        try {
            // Alıcıları ekle
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // İçerik ayarları
            $this->mailer->isHTML($isHTML);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            // Gönder
            return $this->mailer->send();
        } catch (Exception $e) {
            // Hata loglama
            if (class_exists('Logger')) {
                Logger::error('Mail gönderme hatası: ' . $this->mailer->ErrorInfo);
            }
            return false;
        }
    }

    /**
     * Ek dosya ekler
     *
     * @param string $path Dosya yolu
     * @param string $name Dosya adı
     * @return Mailer
     */
    public function attach($path, $name = '')
    {
        $this->mailer->addAttachment($path, $name);
        return $this;
    }

    /**
     * CC ekler
     *
     * @param string $email E-posta adresi
     * @param string $name İsim
     * @return Mailer
     */
    public function cc($email, $name = '')
    {
        $this->mailer->addCC($email, $name);
        return $this;
    }

    /**
     * BCC ekler
     *
     * @param string $email E-posta adresi
     * @param string $name İsim
     * @return Mailer
     */
    public function bcc($email, $name = '')
    {
        $this->mailer->addBCC($email, $name);
        return $this;
    }

    /**
     * PHPMailer örneğini döndürür
     *
     * @return PHPMailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }
}
