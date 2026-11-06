<?php
/**
 * トップページ
 * 原稿を 1 本読んで表示する
 */

require_once __DIR__ . '/inc/vendor/autoload.php';
require_once __DIR__ . '/inc/ContentLoader.php';

$loader = new ContentLoader();
$post = $loader->read(__DIR__ . '/content/welcome.md');

if ($post === null) {
  http_response_code(500);
  error_log('content/welcome.md を読めませんでした');
  exit;
}

$title = htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="./style.css">
  </head>
  <body>
    <main class="main">
      <h1 class="main__title"><?= $title ?></h1>
      <?= $loader->toHtml($post['content']) ?>
    </main>
  </body>
</html>
