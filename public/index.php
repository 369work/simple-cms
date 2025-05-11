<?php
// 初期化ファイルを読み込み
require_once __DIR__ . '/../includes/init.php';

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (!isset($_POST['csrf_token']) || !Security::validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = '不正なリクエストです。ページを再読み込みして再度お試しください。';
    } else {
        // 入力値の取得とサニタイズ
        $name = Security::sanitizeInput($_POST['name'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $subject = Security::sanitizeInput($_POST['subject'] ?? '');
        $message = Security::sanitizeInput($_POST['message'] ?? '');

        // バリデーション
        if (!Security::validateRequired($name)) {
            $errors[] = 'お名前を入力してください。';
        }

        if (!Security::validateRequired($email) || !Security::validateEmail($email)) {
            $errors[] = '有効なメールアドレスを入力してください。';
        }

        if (!Security::validateRequired($subject)) {
            $errors[] = '件名を入力してください。';
        }

        if (!Security::validateRequired($message)) {
            $errors[] = 'メッセージを入力してください。';
        }

        // エラーがなければ処理を続行
        if (empty($errors)) {
            // メッセージを保存
            $messageObj = new Message();
            $messageId = $messageObj->saveMessage($name, $email, $subject, $message);

            if ($messageId) {
                // メール送信
                $mailer = new Mailer();

                // 管理者に通知
                $adminMailResult = $mailer->sendAdminNotification($name, $email, $subject, $message);

                // 自動返信
                $autoReplyResult = $mailer->sendAutoReply($name, $email, $subject);

                if ($adminMailResult === true && $autoReplyResult === true) {
                    $success[] = 'お問い合わせを受け付けました。確認メールをお送りしましたのでご確認ください。';

                    // フォームをクリア
                    $name = $email = $subject = $message = '';
                } else {
                    $success[] = 'お問い合わせは受け付けましたが、メール送信に問題が発生しました。管理者に連絡してください。';
                    $errors[] = DEBUG_MODE ? $adminMailResult . ' / ' . $autoReplyResult : '';
                }
            } else {
                $errors[] = 'お問い合わせの保存中に問題が発生しました。後でもう一度お試しください。';
            }
        }
    }
}

// CSRFトークンを生成
$csrfToken = Security::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            padding-top: 2rem;
            padding-bottom: 2rem;
            background-color: #f8f9fa;
        }

        .form-container {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .required::after {
            content: " *";
            color: red;
        }
    </style>
</head>

<body>
    <div class="container mx-auto">
        <div class="mx-auto w-full">
            <div class="max-w-2xl mx-auto p-4">
                <div class="text-center mb-4">
                    <h1><?php echo htmlspecialchars(SITE_NAME); ?></h1>
                    <p class="lead">お問い合わせフォーム</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 text-red-700 border border-red-400 rounded p-4 mb-4">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <?php if (!empty($error)): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="bg-green-100 text-green-700 border border-green-400 rounded p-4 mb-4">
                        <ul class="list-disc list-inside">
                            <?php foreach ($success as $message): ?>
                                <li><?php echo htmlspecialchars($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="shadow-md rounded-lg form-container">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 required">お名前</label>
                            <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 required">メールアドレス</label>
                            <input type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" id=" email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="subject" class="block text-sm font-medium text-gray-700 required">件名</label>
                            <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="block text-sm font-medium text-gray-700 required">メッセージ</label>
                            <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" id="message" name="message" rows="6" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        </div>

                        <div class="mb-4">
                            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">送信する</button>
                        </div>
                    </form>
                </div>

                <div class="text-center text-gray-500 mt-6">
                    <small>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?>. All rights reserved.</small>
                </div>
            </div>
        </div>
    </div>
</body>

</html>