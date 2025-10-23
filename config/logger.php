<?php
/**
 * Advanced Logger System for StopNow
 */

class Logger {
    private static $logDir = 'logs/';
    private static $levels = [
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7
    ];
    
    public static function init() {
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }
    
    public static function emergency($message, $context = []) {
        self::log('emergency', $message, $context);
    }
    
    public static function alert($message, $context = []) {
        self::log('alert', $message, $context);
    }
    
    public static function critical($message, $context = []) {
        self::log('critical', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('error', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('warning', $message, $context);
    }
    
    public static function notice($message, $context = []) {
        self::log('notice', $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::log('info', $message, $context);
    }
    
    public static function debug($message, $context = []) {
        self::log('debug', $message, $context);
    }
    
    private static function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? ''
        ];
        
        $logLine = json_encode($logEntry) . "\n";
        
        // Write to general log
        file_put_contents(
            self::$logDir . 'app.log',
            $logLine,
            FILE_APPEND | LOCK_EX
        );
        
        // Write to level-specific log
        file_put_contents(
            self::$logDir . $level . '.log',
            $logLine,
            FILE_APPEND | LOCK_EX
        );
        
        // Write to daily log
        $dailyFile = self::$logDir . 'daily-' . date('Y-m-d') . '.log';
        file_put_contents(
            $dailyFile,
            $logLine,
            FILE_APPEND | LOCK_EX
        );
    }
    
    public static function getLogs($level = null, $limit = 100) {
        $logFile = $level ? self::$logDir . $level . '.log' : self::$logDir . 'app.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $logs = [];
        
        foreach (array_reverse(array_slice($lines, -$limit)) as $line) {
            $log = json_decode($line, true);
            if ($log) {
                $logs[] = $log;
            }
        }
        
        return $logs;
    }
    
    public static function clearLogs($level = null) {
        if ($level) {
            $logFile = self::$logDir . $level . '.log';
            if (file_exists($logFile)) {
                unlink($logFile);
            }
        } else {
            $files = glob(self::$logDir . '*.log');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
    
    public static function getStats() {
        $files = glob(self::$logDir . '*.log');
        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'levels' => []
        ];
        
        foreach ($files as $file) {
            $size = filesize($file);
            $stats['total_size'] += $size;
            
            $filename = basename($file);
            if (strpos($filename, '.log') !== false) {
                $level = str_replace('.log', '', $filename);
                $stats['levels'][$level] = $size;
            }
        }
        
        return $stats;
    }
}

// Initialize logger
Logger::init();
?>
