<?php
// エラー報告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 設定ファイル読み込み
require_once __DIR__ . '/../config/config.php';

// デバッグモードの設定
if (!DEBUG_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 必要なファイルを読み込み
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/message.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/auth.php';

// Composer オートロードを読み込み（PHPMailer用）
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// セッションを初期化
Security::initSession();

// データベースの初期化
$db = Database::getInstance();
$db->createTablesIfNotExist();

// 管理者ユーザーのチェック
$auth = new Auth();
if (!$auth->checkAdminExists()) {
    // 初期管理者アカウントを作成（実際の運用では削除またはコメントアウトしてください）
    $auth->createUser('admin', 'admin123', MAIL_ADMIN, 'admin');
}

// グローバル変数
$errors = [];
$success = [];
