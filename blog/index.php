<?php
/**
 * ブログ (一覧: /blog/, 2ページ目以降: /blog/page/2/, 個別記事: /blog/xxx/)
 * 原稿は content/blog/*.md に置く. URL は blog/.htaccess で振り分ける
 */

require_once dirname(__DIR__) . '/inc/ContentPage.php';

ContentPage::render('blog');
