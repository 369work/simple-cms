<?php
// 初期化ファイルを読み込み
require_once __DIR__ . '/../includes/init.php';

// 認証クラスのインスタンスを作成
$auth = new Auth();

// ログアウト処理
$auth->logout();

// ログインページにリダイレクト
header('Location: login.php');
exit;
