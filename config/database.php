<?php

/**
 * Database Configuration and Connection
 * SI TERNAK - Livestock Management System Buleleng
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'sipandhewa'); // atau 'si_ternak'
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// PDO options
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

/**
 * Get database connection
 * @return PDO Database connection object
 */
function getDBConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    return $pdo;
}

/**
 * Execute a query with parameters
 */
function executeQuery($sql, $params = [])
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        echo "<pre>";
        echo "Query: $sql\n";
        echo "Params: ";
        print_r($params);
        echo "Error: " . $e->getMessage();
        echo "</pre>";
        throw new Exception("Database query failed");
    }
}


/**
 * Get single row from database
 */
function fetchOne($sql, $params = [])
{
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Get multiple rows from database
 */
function fetchAll($sql, $params = [])
{
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Insert data and return last insert ID
 */
function insertData($sql, $params = [])
{
    executeQuery($sql, $params);
    return getDBConnection()->lastInsertId();
}

/**
 * Update data
 */
function updateData($sql, $params = [])
{
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

/**
 * Delete data
 */
function deleteData($sql, $params = [])
{
    return updateData($sql, $params);
}

/**
 * Validate email format
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Sanitize input
 */
function sanitizeInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate numeric input
 */
function validateNumeric($value)
{
    return is_numeric($value) && $value >= 0;
}

/**
 * Format currency
 */
function formatCurrency($number)
{
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Generate unique ID for livestock
 */
function generateLivestockId($prefix = 'TRN')
{
    $date = date('ymd');
    $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    return $prefix . '-' . $date . $random;
}
