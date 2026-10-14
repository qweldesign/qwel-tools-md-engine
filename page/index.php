<?php
/**
 * 固定ページ (個別のページ: /page/xxx/. 一覧 /page/ はトップページへ転送する)
 * ページは content/page/*.md に置く. URL は page/.htaccess のリライトで振り分ける
 */
require_once dirname(__DIR__) . '/inc/templates/posts.php';

render_posts('page', [
  'latest'   => 0,
  'adjacent' => false,
]);
