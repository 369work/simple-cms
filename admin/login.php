<?php
// 初期化ファイルを読み込み
require_once __DIR__ . '/../includes/init.php';

// 既にログインしている場合はリダイレクト
$auth = new Auth();
if ($auth->isAuthenticated()) {
    header('Location: index.php');
    exit;
}

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFトークンの検証
    if (!isset($_POST['csrf_token']) || !Security::validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = '不正なリクエストです。ページを再読み込みして再度お試しください。';
    } else {
        // 入力値の取得とサニタイズ
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // パスワードはサニタイズしない

        // バリデーション
        if (!Security::validateRequired($username)) {
            $errors[] = 'ユーザー名を入力してください。';
        }

        if (!Security::validateRequired($password)) {
            $errors[] = 'パスワードを入力してください。';
        }

        // エラーがなければ認証
        if (empty($errors)) {
            $result = $auth->login($username, $password);

            if ($result['success']) {
                // ログイン成功
                header('Location: index.php');
                exit;
            } else {
                $errors[] = $result['message'];
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
    <title>管理者ログイン - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
            height: 100vh;
        }

        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin input[type="text"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
</head>

<body class="text-center">
    <main class="form-signin">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <h1 class="h3 mb-3 fw-normal">管理者ログイン</h1>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 text-start">
                        <?php foreach ($errors as $error): ?>
                            <?php if (!empty($error)): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="ユーザー名" required>
                <label for="username">ユーザー名</label>
            </div>

            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="パスワード" required>
                <label for="password">パスワード</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">ログイン</button>

            <p class="mt-3 mb-3 text-muted">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?></p>
        </form>
    </main>
</body>

</html>