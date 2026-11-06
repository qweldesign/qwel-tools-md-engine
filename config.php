<?php
/**
 * サイトの設定
 * サイトごとに変わる値は, ここにまとめる
 */

// サイト名 (下層ページの title, ロゴの代わりの文字)
const SITE_NAME = 'Simple CMS';
// トップページの title (空なら SITE_NAME)
const SITE_TITLE = 'Simple CMS | Markdown で作る軽量なサイト';
// サイトのルートのパス (ドメイン直下なら /, サブディレクトリなら /tools/simple-cms/ のように)
const SITE_ROOT = '/';
const SITE_LANG = 'ja';
// description の既定値
const SITE_DESCRIPTION = 'Markdown の原稿から、データベースなしでサイトを作るエンジンです。';
// Google Fonts などの CSS の URL (不要なら空文字)
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500&family=Noto+Sans:wght@100..900&family=Noto+Serif:wght@100..900&display=swap';
// Google Analytics の測定 ID (不要なら空文字)
const SITE_GA_ID = '';
// 一覧の画像の既定値 (記事に img がない場合. 不要なら空文字)
const SITE_THUMBNAIL_IMAGE = '';
// URL の形式 (true: /blog/xxx/, false: /blog/?slug=xxx). .htaccess が効かない php -S では false になる
const SITE_PRETTY_URLS = PHP_SAPI !== 'cli-server';

// ヘッダーのロゴ (空ならサイト名を文字で出す)
const SITE_LOGO = 'assets/logo.svg';
// ロゴのリンク先 (空ならトップへ)
const SITE_LOGO_LINK = '';
// ヘッダーのナビ (リンク先 => 表示名. リンク先はサイトのルートからのパス, #section, http から始まる URL)
// 例: ['blog/' => 'Blog', '#about' => 'About']
const SITE_NAV = [];
// フッターのリンク (形は SITE_NAV と同じ)
const SITE_FOOTER_LINKS = [];
// パンくずの先頭の階層 (path はサイトのルートからのパス, または http から始まる URL)
const SITE_BREADCRUMB = [
  ['label' => 'Top', 'path' => ''],
];
// フッターの SNS など (icon は assets/icons.svg の symbol の id. 不要なら空の配列)
const SITE_SNS = [
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'contact/', 'icon' => 'si-mail']
];
// 著作権表示 (開始年 - 今年 運営者)
const SITE_OWNER = 'QWEL.DESIGN';
const SITE_START_YEAR = 2019;

/**
 * 投稿タイプ
 * キー: 投稿タイプの名前. 入口のディレクトリ (投稿タイプの名前/index.php) に使う
 * label: 表示名 (一覧の見出し, パンくず)
 * path: 一覧の URL (サイトのルートからのパス. 省略するとキー + /. '' でルートに置く)
 * dir: 原稿のディレクトリ (サイトのルートからのパス. 省略すると content/キー)
 * count: 1 ページの件数 (既定で 10. null で全件)
 * showDate: 日付を出すか (既定で true)
 * lead: 一覧の見出しの下の文. 一覧の description にも使う
 * empty: 記事が 0 件のときの文 (既定で「まだ記事はありません。」)
 * layout: post (記事. 前後の記事を出す) または page (固定ページ. 既定で post)
 * items: false で個別のページを作らず, 一覧から原稿の url へ直接リンクする (既定で true)
 * sort: date (日付の新しい順) または order (原稿の order の小さい順. 既定で date)
 */
const SITE_POST_TYPES = [
  'blog' => [
    'label' => 'Blog',
  ],
  'page' => [
    'label'  => 'Pages',
    'path'   => '',
    'layout' => 'page',
    'count'  => null,
  ],
];
