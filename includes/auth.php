<?php

/**
 * 認証を管理するクラス
 */
class Auth
{
    private $db;

    /**
     * コンストラクター
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ユーザーを検証しログイン
     */
    public function login($username, $password)
    {
        // ブルートフォース攻撃対策
        if (!Security::checkLoginAttempts($username)) {
            return [
                'success' => false,
                'message' => 'ログイン試行回数が多すぎます。15分後に再試行してください。'
            ];
        }

        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->query($sql, ['username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            Security::recordFailedLogin();
            return [
                'success' => false,
                'message' => 'ユーザー名またはパスワードが正しくありません。'
            ];
        }

        // ログイン成功
        Security::resetLoginAttempts();

        // 最終ログイン日時を更新
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $this->db->query($sql, ['id' => $user['id']]);

        // セッションにユーザー情報を保存
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    }

    /**
     * ログアウト
     */
    public function logout()
    {
        // セッション変数を全て削除
        $_SESSION = [];

        // セッションクッキーも削除
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // セッションを破棄
        session_destroy();

        return true;
    }

    /**
     * ユーザーが認証済みかチェック
     */
    public function isAuthenticated()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }

        // セッションのタイムアウトチェック（30分）
        if (time() - $_SESSION['last_activity'] > 1800) {
            $this->logout();
            return false;
        }

        // 最終アクティビティ時間を更新
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * 現在のユーザーが管理者かチェック
     */
    public function isAdmin()
    {
        return $this->isAuthenticated() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * ユーザーを作成（最初の管理者ユーザー用）
     */
    public function createUser($username, $password, $email, $role = 'user')
    {
        // ユーザー名とメールアドレスの重複チェック
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email";
        $stmt = $this->db->query($sql, ['username' => $username, 'email' => $email]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            return [
                'success' => false,
                'message' => 'ユーザー名またはメールアドレスが既に使用されています。'
            ];
        }

        // パスワードハッシュ化
        $passwordHash = Security::hashPassword($password);

        // ユーザー作成
        $sql = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)";
        $this->db->query($sql, [
            'username' => $username,
            'password' => $passwordHash,
            'email' => $email,
            'role' => $role
        ]);

        return [
            'success' => true,
            'message' => 'ユーザーが正常に作成されました。'
        ];
    }

    /**
     * 最初の管理者ユーザーが存在するかチェック
     */
    public function checkAdminExists()
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'admin'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }
}
