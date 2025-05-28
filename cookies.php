<?php
/**
 * Cookie Handler for YOUR_APP_NAME
 * 
 * Replace all YOUR_* placeholders with your actual values
 */

class Cookie {
    // Cookie configuration - EDIT THESE
    const DEFAULT_EXPIRY_DAYS = 30;    // YOUR_DEFAULT_EXPIRY (in days)
    const DEFAULT_PATH = '/';          // YOUR_DEFAULT_PATH
    const DOMAIN = 'YOUR_DOMAIN.com';  // YOUR_DOMAIN (without http/https)
    
    // Cookie names - DEFINE YOUR COOKIE NAMES HERE
    const AUTH_TOKEN = 'YOUR_AUTH_COOKIE';
    const USER_PREFS = 'YOUR_PREFS_COOKIE';
    const CART_ITEMS = 'YOUR_CART_COOKIE';

    /**
     * Set a cookie
     */
    public static function set(
        string $name,
        string $value,
        int $expiryDays = self::DEFAULT_EXPIRY_DAYS,
        bool $httpOnly = true
    ): bool {
        $options = [
            'expires'  => time() + ($expiryDays * 86400),
            'path'     => self::DEFAULT_PATH,
            'domain'   => self::DOMAIN,
            'secure'   => self::isSecure(),
            'httponly' => $httpOnly,
            'samesite' => 'Lax'
        ];
        
        return setcookie($name, $value, $options);
    }

    /**
     * Get a cookie value
     */
    public static function get(string $name, $default = null) {
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Check if cookie exists
     */
    public static function has(string $name): bool {
        return isset($_COOKIE[$name]);
    }

    /**
     * Delete a cookie
     */
    public static function delete(string $name): bool {
        if (self::has($name)) {
            $options = [
                'expires'  => time() - 3600,
                'path'     => self::DEFAULT_PATH,
                'domain'   => self::DOMAIN,
                'secure'   => self::isSecure(),
                'httponly' => true
            ];
            
            setcookie($name, '', $options);
            unset($_COOKIE[$name]);
            return true;
        }
        return false;
    }

    private static function isSecure(): bool {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }
}
?>