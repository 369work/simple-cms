<?php
// PHPMailer のオートロードを読み込み
// composer require phpmailer/phpmailer を実行してインストールする必要があります
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * メール送信を管理するクラス
 */
class Mailer
{
    private $mailer;

    /**
     * コンストラクター - PHPMailerの初期設定
     */
    public function __construct()
    {
        // PHPMailerが正しくインストールされているか確認
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            die('PHPMailerがインストールされていません。composer require phpmailer/phpmailer を実行してください。');
        }

        $this->mailer = new PHPMailer(true);

        // SMTPの設定
        $this->mailer->isSMTP();
        $this->mailer->Host = MAIL_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = MAIL_USERNAME;
        $this->mailer->Password = MAIL_PASSWORD;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = MAIL_PORT;

        // デフォルト送信者設定
        $this->mailer->setFrom(MAIL_FROM, MAIL_FROM_NAME);

        // 文字セット
        $this->mailer->CharSet = 'UTF-8';

        // デバッグモード設定
        $this->mailer->SMTPDebug = DEBUG_MODE ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
    }

    /**
     * 管理者へ通知メールを送信
     */
    public function sendAdminNotification($name, $email, $subject, $message)
    {
        try {
            // メールの設定
            $this->mailer->clearAddresses();
            $this->mailer->addAddress(MAIL_ADMIN);
            $this->mailer->addReplyTo($email, $name);

            $this->mailer->Subject = '[' . SITE_NAME . '] 新しいお問い合わせ: ' . $subject;

            // メール本文
            $body = "以下の内容でお問い合わせを受け付けました。\n\n";
            $body .= "お名前: " . $name . "\n";
            $body .= "メールアドレス: " . $email . "\n";
            $body .= "件名: " . $subject . "\n";
            $body .= "メッセージ:\n" . $message . "\n\n";
            $body .= "-----\n";
            $body .= "このメールは " . SITE_NAME . " から自動送信されています。";

            $this->mailer->Body = $body;

            // メールを送信
            return $this->mailer->send();
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                return 'メール送信エラー: ' . $this->mailer->ErrorInfo;
            }
            return false;
        }
    }

    /**
     * ユーザーへ自動返信メールを送信
     */
    public function sendAutoReply($name, $email, $subject)
    {
        try {
            // メールの設定
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);

            $this->mailer->Subject = '[' . SITE_NAME . '] お問い合わせありがとうございます';

            // メール本文
            $body = $name . " 様\n\n";
            $body .= "お問い合わせありがとうございます。\n";
            $body .= "以下の内容でお問い合わせを受け付けました。\n\n";
            $body .= "件名: " . $subject . "\n\n";
            $body .= "内容を確認の上、担当者より折り返しご連絡いたします。\n";
            $body .= "恐れ入りますが、しばらくお待ちください。\n\n";
            $body .= "-----\n";
            $body .= "このメールは " . SITE_NAME . " から自動送信されています。\n";
            $body .= "このメールに返信されても対応できかねますのでご了承ください。";

            $this->mailer->Body = $body;

            // メールを送信
            return $this->mailer->send();
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                return 'メール送信エラー: ' . $this->mailer->ErrorInfo;
            }
            return false;
        }
    }

    /**
     * カスタムメールを送信
     */
    public function sendCustomMail($to, $toName, $subject, $body, $replyTo = null)
    {
        try {
            // メールの設定
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $toName);

            if ($replyTo) {
                $this->mailer->addReplyTo($replyTo);
            } else {
                $this->mailer->addReplyTo(MAIL_REPLY_TO);
            }

            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            // メールを送信
            return $this->mailer->send();
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                return 'メール送信エラー: ' . $this->mailer->ErrorInfo;
            }
            return false;
        }
    }
}
