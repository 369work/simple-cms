<?php

/**
 * セキュリティ関連の機能を提供するクラス
 */
class Security
{
    /**
     * CSRFトークンを生成
     */
    public static function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * CSRFトークンを検証
     */
    public static function validateCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    /**
     * 入力値をサニタイズ
     */
    public static function sanitizeInput($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::sanitizeInput($value);
            }
        } else {
            $input = trim($input);
            $input = stripslashes($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    /**
     * メールアドレスの形式をバリデーション
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 必須項目のバリデーション
     */
    public static function validateRequired($value)
    {
        return !empty(trim($value));
    }

    /**
     * パスワードハッシュを生成
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * パスワードを検証
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * セッションを安全に初期化
     */
    public static function initSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // セッションの設定
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);

            // セッションクッキーのパラメータ設定
            session_set_cookie_params([
                'lifetime' => 3600,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_name(SESSION_NAME);
            session_start();

            // セッションIDの再生成（セッション固定攻撃対策）
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) {
                // 30分ごとにセッションIDを再生成
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }

    /**
     * ログイン試行回数を制限（ブルートフォース攻撃対策）
     */
    public static function checkLoginAttempts($username)
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt'] = 0;
        }

        // 15分のクールダウン期間
        if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt']) < 900) {
            return false;
        }

        if ((time() - $_SESSION['last_attempt']) >= 900) {
            $_SESSION['login_attempts'] = 0;
        }

        return true;
    }

    /**
     * ログイン失敗を記録
     */
    public static function recordFailedLogin()
    {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
    }

    /**
     * ログイン成功時に試行回数をリセット
     */
    public static function resetLoginAttempts()
    {
        $_SESSION['login_attempts'] = 0;
    }

    /**
     * クライアントIPアドレスを取得
     */
    public static function getClientIp()
    {
        $ipAddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        return $ipAddress;
    }

    /**
     * ユーザーエージェントを取得
     */
    public static function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
}
