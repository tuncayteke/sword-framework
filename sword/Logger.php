<?php

/**
 * Sword Framework
 * 
 * by Tuncay TEKE (https://www.tuncayteke.com.tr)
 *
 * Logger sınıfı - Kayıt tutma işlemlerini yönetir
 */

class Logger
{
    /**
     * Log seviyeleri
     */
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    /**
     * Log dosyası yolu
     */
    private static $logPath = null;

    /**
     * Log dosyası formatı
     */
    private static $logFormat = 'Y-m-d';

    /**
     * Log mesajı formatı
     */
    private static $messageFormat = '[%datetime%] [%level%] %message%';

    /**
     * Aktif log seviyeleri
     */
    private static $enabledLevels = [
        self::EMERGENCY,
        self::ALERT,
        self::CRITICAL,
        self::ERROR,
        self::WARNING,
        self::NOTICE,
        self::INFO,
        self::DEBUG
    ];

    /**
     * Log dosyası yolunu ayarlar
     *
     * @param string $path Log dosyası yolu
     * @return void
     */
    public static function setLogPath($path)
    {
        self::$logPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;

        // Dizin yoksa oluştur
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
    }

    /**
     * Log dosyası formatını ayarlar
     *
     * @param string $format Tarih formatı
     * @return void
     */
    public static function setLogFormat($format)
    {
        self::$logFormat = $format;
    }

    /**
     * Log mesajı formatını ayarlar
     *
     * @param string $format Mesaj formatı
     * @return void
     */
    public static function setMessageFormat($format)
    {
        self::$messageFormat = $format;
    }

    /**
     * Aktif log seviyelerini ayarlar
     *
     * @param array $levels Log seviyeleri
     * @return void
     */
    public static function setEnabledLevels(array $levels)
    {
        self::$enabledLevels = $levels;
    }

    /**
     * Log dosyasının tam yolunu döndürür
     *
     * @return string Log dosyası yolu
     */
    private static function getLogFile()
    {
        // Log yolu ayarlanmamışsa Sword sınıfından al
        if (self::$logPath === null) {
            if (class_exists('Sword')) {
                // Sword::getPath kullanarak log yolunu al
                if (method_exists('Sword', 'getPath')) {
                    self::$logPath = Sword::getPath('logs') . DIRECTORY_SEPARATOR;
                }
                // Geriye dönük uyumluluk için getData da kontrol et
                else if (method_exists('Sword', 'getData')) {
                    $logsPath = Sword::getData('logs_path');
                    if ($logsPath) {
                        self::$logPath = $logsPath . DIRECTORY_SEPARATOR;
                    }
                }
            }

            // Hala ayarlanmamışsa varsayılan yolu kullan
            if (self::$logPath === null) {
                if (defined('BASE_PATH')) {
                    self::$logPath = BASE_PATH . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
                } else {
                    self::$logPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
                }

                // Dizin yoksa oluştur
                if (!is_dir(self::$logPath)) {
                    mkdir(self::$logPath, 0755, true);
                }
            }
        }

        return self::$logPath . 'log-' . date(self::$logFormat) . '.log';
    }

    /**
     * Log mesajını formatlar
     *
     * @param string $level Log seviyesi
     * @param string $message Log mesajı
     * @return string Formatlanmış mesaj
     */
    private static function formatMessage($level, $message)
    {
        $datetime = date('Y-m-d H:i:s');

        $replacements = [
            '%datetime%' => $datetime,
            '%level%' => strtoupper($level),
            '%message%' => $message
        ];

        return str_replace(array_keys($replacements), array_values($replacements), self::$messageFormat) . PHP_EOL;
    }

    /**
     * Log kaydı ekler
     *
     * @param string $level Log seviyesi
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function log($level, $message, array $context = [])
    {
        // Log seviyesi aktif değilse kaydetme
        if (!in_array($level, self::$enabledLevels)) {
            return false;
        }

        // Bağlam verilerini mesaja ekle
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                $message = str_replace('{' . $key . '}', $value, $message);
            }
        }

        // Mesajı formatla
        $formattedMessage = self::formatMessage($level, $message);

        // Log dosyasına yaz
        $logFile = self::getLogFile();

        return file_put_contents($logFile, $formattedMessage, FILE_APPEND | LOCK_EX) !== false;
    }

    /**
     * Acil durum log kaydı ekler
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function emergency($message, array $context = [])
    {
        return self::log(self::EMERGENCY, $message, $context);
    }

    /**
     * Uyarı log kaydı ekler
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function alert($message, array $context = [])
    {
        return self::log(self::ALERT, $message, $context);
    }

    /**
     * Kritik log kaydı ekler
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function critical($message, array $context = [])
    {
        return self::log(self::CRITICAL, $message, $context);
    }

    /**
     * Hata log kaydı ekler
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function error($message, array $context = [])
    {
        return self::log(self::ERROR, $message, $context);
    }

    /**
     * Uyarı log kaydı ekler
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function warning($message, array $context = [])
    {
        return self::log(self::WARNING, $message, $context);
    }

    /**
     * Bildirim log kaydı ekler
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function notice($message, array $context = [])
    {
        return self::log(self::NOTICE, $message, $context);
    }

    /**
     * Bilgi log kaydı ekler
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function info($message, array $context = [])
    {
        return self::log(self::INFO, $message, $context);
    }

    /**
     * Hata ayıklama log kaydı ekler
     *
     * @param string $message Log mesajı
     * @param array $context Bağlam verileri
     * @return bool Başarılı mı?
     */
    public static function debug($message, array $context = [])
    {
        return self::log(self::DEBUG, $message, $context);
    }
}
