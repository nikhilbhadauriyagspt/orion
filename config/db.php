<?php

/**
 * Orion Advent Database Configuration
 * Kisi bhi new system ya server par sirf ye 4 values change karni hongi.
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'orion');

/**
 * Returns PDO Database Connection
 */
function getDBConnection()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Return null or handle if DB doesn't exist yet (for setup installer)
            return null;
        }
    }
    return $pdo;
}

/**
 * Direct Server Connection (Database create karne ke liye jab DB exist na karti ho)
 */
function getServerConnection()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Resolves an image path correctly for admin views (prepending ../ if relative to root uploads)
 */
function getAdminImageUrl($path, $fallback = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80')
{
    if (empty($path)) return $fallback;
    $path = trim($path);
    if (preg_match('/^https?:\/\//i', $path) || str_starts_with($path, 'data:')) {
        return $path;
    }
    $clean = ltrim($path, '/');
    if (str_starts_with($clean, '../')) {
        return $clean;
    }
    return '../' . $clean;
}
