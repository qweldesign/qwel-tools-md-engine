<?php
/**
 * 404 (ページが見つかりません)
 * 存在しない URL へのアクセスは, ルートの .htaccess の ErrorDocument からここに振り分ける
 * 投稿タイプの存在しない記事と範囲外のページ番号は, ContentPage が自分で 404 を返す
 */

require_once __DIR__ . '/inc/ContentNavigation.php';
require_once __DIR__ . '/templates/notFound.php';

// 404.php を直接開いた場合も 404 を返す
http_response_code(404);

renderNotFound([
  'posts'     => [],
  'crumbs'    => ContentNavigation::breadcrumbs([...SITE_BREADCRUMB, ['label' => 'ページが見つかりません']]),
  'backUrl'   => path(),
  'backLabel' => 'トップページへ',
]);
