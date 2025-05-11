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

// ページネーション
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// フィルター処理
$status = isset($_GET['status']) ? Security::sanitizeInput($_GET['status']) : null;
if ($status && !in_array($status, ['new', 'read', 'replied', 'spam'])) {
    $status = null;
}

// メッセージを取得
$messages = $messageManager->getAllMessages($perPage, $offset, $status);
$totalMessages = $messageManager->getMessageCount($status);
$totalPages = ceil($totalMessages / $perPage);

// CSRFトークンを生成
$csrfToken = Security::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メッセージ一覧 - <?php echo htmlspecialchars(SITE_NAME); ?></title>
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
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-house-door"></i>
                                ダッシュボード
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="messages.php">
                                <i class="bi bi-envelope"></i>
                                メッセージ一覧
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="new_messages.php">
                                <i class="bi bi-envelope-open"></i>
                                新着メッセージ
                                <?php
                                $newMessageCount = $messageManager->getMessageCount('new');
                                if ($newMessageCount > 0):
                                ?>
                                    <span class="badge bg-success rounded-pill"><?php echo $newMessageCount; ?></span>
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
                    <h1 class="h2">メッセージ一覧</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="export.php?type=messages<?php echo $status ? '&status=' . $status : ''; ?>" class="btn btn-sm btn-outline-secondary">エクスポート</a>
                        </div>
                    </div>
                </div>

                <!-- フィルター -->
                <div class="mb-4">
                    <div class="btn-group" role="group" aria-label="メッセージフィルター">
                        <a href="messages.php" class="btn btn-outline-secondary <?php echo !$status ? 'active' : ''; ?>">全て</a>
                        <a href="messages.php?status=new" class="btn btn-outline-success <?php echo $status === 'new' ? 'active' : ''; ?>">新規</a>
                        <a href="messages.php?status=read" class="btn btn-outline-info <?php echo $status === 'read' ? 'active' : ''; ?>">既読</a>
                        <a href="messages.php?status=replied" class="btn btn-outline-secondary <?php echo $status === 'replied' ? 'active' : ''; ?>">返信済</a>
                        <a href="messages.php?status=spam" class="btn btn-outline-danger <?php echo $status === 'spam' ? 'active' : ''; ?>">スパム</a>
                    </div>
                </div>

                <!-- メッセージ一覧 -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                            <?php if (count($messages) > 0): ?>
                                <?php foreach ($messages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['id']); ?></td>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($message['status']); ?></td>
                                        <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                                        <td>
                                            <a href="view_message.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-primary">表示</a>
                                            <a href="delete_message.php?id=<?php echo $message['id']; ?>&csrf_token=<?php echo $csrfToken; ?>" class="btn btn-sm btn-danger">削除</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">メッセージはありません。</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>">前へ</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>">次へ</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons"></script>
    <script>
        feather.replace();
    </script>
</body>
</html>

