<?php
/**
 * Simple Cache System for StopNow
 */

class Cache {
    private static $cacheDir = 'cache/';
    private static $defaultTTL = 3600; // 1 hour
    
    public static function init() {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    public static function get($key) {
        $file = self::getFilePath($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            self::delete($key);
            return null;
        }
        
        return $data['value'];
    }
    
    public static function set($key, $value, $ttl = null) {
        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        $file = self::getFilePath($key);
        return file_put_contents($file, serialize($data)) !== false;
    }
    
    public static function delete($key) {
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }
    
    public static function clear() {
        $files = glob(self::$cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
    
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value === null) {
            $value = $callback();
            self::set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    private static function getFilePath($key) {
        return self::$cacheDir . md5($key) . '.cache';
    }
    
    public static function getStats() {
        $files = glob(self::$cacheDir . '*.cache');
        $totalSize = 0;
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $totalSize += filesize($file);
                $count++;
            }
        }
        
        return [
            'count' => $count,
            'size' => $totalSize,
            'size_formatted' => self::formatBytes($totalSize)
        ];
    }
    
    private static function formatBytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}

// Initialize cache directory
Cache::init();
?>
