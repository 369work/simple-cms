<?php
// データベース設定
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'simple_cms');

// メール設定
define('MAIL_HOST', '');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_FROM', 'admin@localhost.com');
define('MAIL_FROM_NAME', 'Simple CMS');
define('MAIL_REPLY_TO', 'reply_to@localhost.com');
define('MAIL_ADMIN', 'admin@example.com'); // 管理者宛先

// セキュリティ設定
define('CSRF_TOKEN_SECRET', 'simple_cms_random_secret_key');
define('CSRF_TOKEN_EXPIRATION', 3600); // 1時間
define('SESSION_NAME', 'SIMPLE_CMS');

// サイト設定
define('SITE_NAME', 'Simple CMS');
define('BASE_URL', 'http://localhost/simple-cms');

// デバッグモード（本番環境では false に設定）
define('DEBUG_MODE', true);
