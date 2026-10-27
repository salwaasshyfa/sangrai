<?php
/**
 * Koneksi database (PDO).
 * - Di Azure/produksi: isi App Settings DB_HOST, DB_NAME, DB_USER, DB_PASS (MySQL).
 * - Tanpa DB_HOST: otomatis memakai SQLite (file), cocok untuk lokal.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    global $seedProducts;
    $opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

    if (getenv('DB_HOST')) {
        $dsn = 'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306)
             . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4';
        $opt[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt'; // Azure MySQL wajib SSL
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), $opt);
        $pk = 'INT AUTO_INCREMENT PRIMARY KEY';
    } else {
        // Di Azure, /home bersifat permanen (folder aplikasi ditimpa tiap deploy)
        $dir = getenv('WEBSITE_SITE_NAME') ? '/home/data' : __DIR__ . '/storage';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $pdo = new PDO('sqlite:' . $dir . '/sangrai.sqlite', null, null, $opt);
        $pk = 'INTEGER PRIMARY KEY AUTOINCREMENT';
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id $pk, name VARCHAR(100) NOT NULL, origin VARCHAR(100) NOT NULL,
        roast INT NOT NULL, notes VARCHAR(255) NOT NULL, price INT NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id $pk, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, message TEXT NOT NULL)");

    if ((int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO products (name, origin, roast, notes, price) VALUES (?,?,?,?,?)');
        foreach ($seedProducts as $p) {
            $st->execute([$p['name'], $p['origin'], $p['roast'], $p['notes'], $p['price']]);
        }
    }
    return $pdo;
}
