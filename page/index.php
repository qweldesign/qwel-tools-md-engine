<?php
/**
 * 固定ページ (/xxx/)
 * 原稿は content/page/*.md に置く. URL はルートの .htaccess で振り分ける
 * このディレクトリに .htaccess を置かない (ルートのルールが効かなくなるため)
 */

require_once dirname(__DIR__) . '/inc/ContentPage.php';

ContentPage::render('page');
