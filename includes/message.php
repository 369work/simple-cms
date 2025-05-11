<?php

/**
 * メッセージを管理するクラス
 */
class Message
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
     * メッセージをデータベースに保存
     */
    public function saveMessage($name, $email, $subject, $message)
    {
        $ipAddress = Security::getClientIp();
        $userAgent = Security::getUserAgent();

        $params = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ];

        $sql = "INSERT INTO messages (name, email, subject, message, ip_address, user_agent)
                VALUES (:name, :email, :subject, :message, :ip_address, :user_agent)";

        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0 ? $this->db->getConnection()->lastInsertId() : false;
    }

    /**
     * すべてのメッセージを取得
     */
    public function getAllMessages($limit = 100, $offset = 0, $status = null)
    {
        $params = [];
        $sql = "SELECT * FROM messages";

        if ($status !== null) {
            $sql .= " WHERE status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * メッセージ数を取得
     */
    public function getMessageCount($status = null)
    {
        $params = [];
        $sql = "SELECT COUNT(*) as count FROM messages";

        if ($status !== null) {
            $sql .= " WHERE status = :status";
            $params['status'] = $status;
        }

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * IDでメッセージを取得
     */
    public function getMessageById($id)
    {
        $sql = "SELECT * FROM messages WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * メッセージのステータスを更新
     */
    public function updateMessageStatus($id, $status)
    {
        $sql = "UPDATE messages SET status = :status WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id, 'status' => $status]);
        return $stmt->rowCount() > 0;
    }

    /**
     * メッセージを削除
     */
    public function deleteMessage($id)
    {
        $sql = "DELETE FROM messages WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
