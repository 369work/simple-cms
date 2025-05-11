<?php
// 初期化ファイルを読み込み
require_once __DIR__ . '/../includes/init.php';

// 認証チェック
$auth = new Auth();
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// メッセージ管理クラスのインスタンスを作成
$messageManager = new Message();

// メッセージ統計情報の取得
$totalMessages = $messageManager->getMessageCount();
$newMessages = $messageManager->getMessageCount('new');
$readMessages = $messageManager->getMessageCount('read');
$repliedMessages = $messageManager->getMessageCount('replied');
$spamMessages = $messageManager->getMessageCount('spam');

// 最近のメッセージを取得（10件）
$recentMessages = $messageManager->getAllMessages(10);

// CSRFトークンを生成
$csrfToken = Security::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        .feather {
            width: 16px;
            height: 16px;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        .sidebar-sticky {
            height: calc(100vh - 48px);
            overflow-x: hidden;
            overflow-y: auto;
        }

        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }

        .navbar .navbar-toggler {
            top: .25rem;
            right: 1rem;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
        }

        .sidebar .nav-link.active {
            color: #2470dc;
        }

        .sidebar .nav-link:hover {
            color: #2470dc;
        }

        .sidebar .nav-link .feather {
            margin-right: 4px;
            color: #727272;
        }

        .sidebar .nav-link.active .feather {
            color: inherit;
        }

        .sidebar-heading {
            font-size: .75rem;
        }

        .navbar-brand {
            background-color: #343a40;
        }

        .bg-dark {
            background-color: #343a40 !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        .card-counter {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 5px;
            padding: 20px 10px;
            background-color: #fff;
            height: 100px;
            border-radius: 5px;
            transition: .3s linear all;
            position: relative;
        }

        .card-counter:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-counter i {
            font-size: 5em;
            opacity: 0.2;
            position: absolute;
            right: 10px;
            top: 10px;
        }

        .card-counter .count-numbers {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 32px;
            display: block;
        }

        .card-counter .count-name {
            position: absolute;
            left: 20px;
            top: 65px;
            font-style: italic;
            opacity: 0.7;
            display: block;
            font-size: 18px;
        }

        .message-badge-new {
            background-color: #28a745;
        }

        .message-badge-read {
            background-color: #17a2b8;
        }

        .message-badge-replied {
            background-color: #6c757d;
        }

        .message-badge-spam {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#"><?php echo htmlspecialchars(SITE_NAME); ?></a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="logout.php">ログアウト</a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php">
                                <i class="bi bi-house-door"></i>
                                ダッシュボード
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="messages.php">
                                <i class="bi bi-envelope"></i>
                                メッセージ一覧
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="new_messages.php">
                                <i class="bi bi-envelope-open"></i>
                                新着メッセージ
                                <?php if ($newMessages > 0): ?>
                                    <span class="badge bg-success rounded-pill"><?php echo $newMessages; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-gear"></i>
                                設定
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>管理者メニュー</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people"></i>
                                ユーザー管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="export.php">
                                <i class="bi bi-download"></i>
                                データエクスポート
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">ダッシュボード</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="new_messages.php" class="btn btn-sm btn-outline-secondary">新着メッセージ</a>
                            <a href="export.php?type=messages" class="btn btn-sm btn-outline-secondary">データエクスポート</a>
                        </div>
                    </div>
                </div>

                <!-- メッセージ統計情報 -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card-counter bg-light">
                            <i class="bi bi-envelope text-success"></i>
                            <span class="count-numbers"><?php echo $newMessages; ?></span>
                            <span class="count-name">新着メッセージ</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-counter bg-light">
                            <i class="bi bi-eye text-info"></i>
                            <span class="count-numbers"><?php echo $readMessages; ?></span>
                            <span class="count-name">既読メッセージ</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-counter bg-light">
                            <i class="bi bi-reply text-warning"></i>
                            <span class="count-numbers"><?php echo $repliedMessages; ?></span>
                            <span class="count-name">返信済みメッセージ</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-counter bg-light">
                            <i class="bi bi-exclamation-triangle text-danger"></i>
                            <span class="count-numbers"><?php echo $spamMessages; ?></span>
                            <span class="count-name">スパムメッセージ</span>
                        </div>
                    </div>
                </div>

                <!-- 最近のメッセージ -->
                <h2>最近のメッセージ</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>名前</th>
                                <th>メールアドレス</th>
                                <th>件名</th>
                                <th>ステータス</th>
                                <th>日時</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentMessages)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">メッセージはありません</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentMessages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['id']); ?></td>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = '';
                                            $statusText = '';

                                            switch ($message['status']) {
                                                case 'new':
                                                    $badgeClass = 'bg-success';
                                                    $statusText = '新規';
                                                    break;
                                                case 'read':
                                                    $badgeClass = 'bg-info';
                                                    $statusText = '既読';
                                                    break;
                                                case 'replied':
                                                    $badgeClass = 'bg-secondary';
                                                    $statusText = '返信済';
                                                    break;
                                                case 'spam':
                                                    $badgeClass = 'bg-danger';
                                                    $statusText = 'スパム';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-light text-dark';
                                                    $statusText = '不明';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                        </td>
                                        <td><?php echo date('Y/m/d H:i', strtotime($message['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="view_message.php?id=<?php echo $message['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="reply_message.php?id=<?php echo $message['id']; ?>" class="btn btn-outline-success">
                                                    <i class="bi bi-reply"></i>
                                                </a>
                                                <a href="delete_message.php?id=<?php echo $message['id']; ?>&csrf_token=<?php echo $csrfToken; ?>"
                                                    class="btn btn-outline-danger"
                                                    onclick="return confirm('このメッセージを削除してもよろしいですか？');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 mb-5">
                    <a href="messages.php" class="btn btn-primary">全てのメッセージを表示</a>
                </div>

                <!-- システム情報 -->
                <h2>システム情報</h2>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">アプリケーション情報</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">サイト名: <?php echo htmlspecialchars(SITE_NAME); ?></li>
                                    <li class="list-group-item">PHP バージョン: <?php echo phpversion(); ?></li>
                                    <li class="list-group-item">サーバー: <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? '不明'); ?></li>
                                    <li class="list-group-item">データベース: MySQL</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">ユーザー情報</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">ユーザー名: <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                                    <li class="list-group-item">役割: <?php echo $_SESSION['user_role'] === 'admin' ? '管理者' : 'ユーザー'; ?></li>
                                    <li class="list-group-item">最終アクティビティ: <?php echo date('Y-m-d H:i:s', $_SESSION['last_activity']); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>