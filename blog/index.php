<?php
/**
 * ブログ (一覧: /blog/, 2ページ目以降: /blog/page/2/, 個別記事: /blog/xxx/)
 * 記事は content/blog/*.md に置く. URL は blog/.htaccess のリライトで振り分ける
 */
require_once dirname(__DIR__) . '/inc/templates/posts.php';

render_posts('blog');
