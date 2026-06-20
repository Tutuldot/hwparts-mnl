<?php

namespace App\Libraries;

class SmsService
{
    /**
     * Send dummy SMS notification and log to sms_simulation.log
     *
     * @param string $mobile
     * @param string $message
     * @return bool
     */
    public static function send(string $mobile, string $message): bool
    {
        $logDir = WRITEPATH . 'logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logPath = $logDir . '/sms_simulation.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMsg = "[{$timestamp}] SMS SIMULATION -> To: {$mobile} | Message: \"{$message}\"\n";
        
        file_put_contents($logPath, $logMsg, FILE_APPEND);
        
        // Return true to simulate a successful API response
        return true;
    }
}
