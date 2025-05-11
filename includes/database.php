<?php

/**
 * データベース接続を管理するクラス
 */
class Database
{
    private $conn;
    private static $instance = null;

    /**
     * コンストラクター - データベースに接続
     */
    private function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("データベース接続エラー: " . $e->getMessage());
            } else {
                die("システムエラーが発生しました。管理者にお問い合わせください。");
            }
        }
    }

    /**
     * シングルトンインスタンスを取得
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * PDO接続オブジェクトを取得
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * クエリを実行し、結果を取得
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("クエリエラー: " . $e->getMessage() . " SQL: " . $sql);
            } else {
                die("システムエラーが発生しました。管理者にお問い合わせください。");
            }
        }
    }

    /**
     * データベーステーブルの作成
     */
    public function createTablesIfNotExist()
    {
        // メッセージテーブル
        $this->query("
            CREATE TABLE IF NOT EXISTS `messages` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `subject` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `ip_address` VARCHAR(45) NULL,
                `user_agent` TEXT NULL,
                `status` ENUM('new', 'read', 'replied', 'spam') DEFAULT 'new',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // ユーザーテーブル
        $this->query("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(255) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `role` ENUM('admin', 'user') DEFAULT 'user',
                `last_login` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
}
