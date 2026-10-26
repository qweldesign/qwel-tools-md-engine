<?php
/**
 * サイトの設定
 * サイト名や URL, 投稿タイプなど, サイトごとに変わる値はここにまとめる
 * (inc/ 以下のエンジン・テンプレートには, サイト固有の値を書かない)
 */

// 日付の判定・表示のタイムゾーン
date_default_timezone_set('Asia/Tokyo');

// サイト名 (<title> の「記事タイトル | サイト名」と og:site_name に使う)
const SITE_NAME = 'SIMPLE CMS | QWEL in Action';
// サイトの URL (末尾のスラッシュなし. OGP, canonical の絶対URLに使う)
const SITE_URL = 'https://qwel.design';
// サイトのルートのパス (ドメイン直下なら '/'. 例: /tools/simple-cms/ に置く場合)
const SITE_ROOT = '/tools/simple-cms/';
// サイトの見出し (トップページと個別記事の h1. SITE_HEADING_SUB は改行して小さく出す副題. 不要なら空文字)
const SITE_HEADING = '鋤と鉋とプログラミング';
const SITE_HEADING_SUB = '―子ども向けプログラミング教室講師ブログ';
// サイトの説明 (description の既定値)
const SITE_DESCRIPTION = 'Markdown ファイルを記事として読み込み、HTML に変換して表示するシンプルな PHP エンジンのデモです。';
// OGP 画像の既定値 (サイトのルートからのパス)
const SITE_OGP_IMAGE = 'content/images/about/fig_story.jpg';
// 記事一覧のサムネイルの既定値 (記事にアイキャッチがない場合)
const SITE_THUMBNAIL_IMAGE = 'content/images/about/fig_story.jpg';
// Google Fonts などの読み込み (不要なら空文字)
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&family=Zen+Old+Mincho:wght@600&display=swap';
// 言語
const SITE_LANG = 'ja';

// ヘッダーのロゴ (画像のパスとリンク先. 画像がなければ SITE_NAME を文字で出す)
const SITE_LOGO = 'assets/logo.svg';
const SITE_LOGO_LINK = 'https://qwel.design/';

// ヘッダーのナビ (サイトのルートからのパス => 表示名. 不要なら空の配列)
const SITE_NAV = [];

// パンくずの先頭の階層 (path はサイトのルートからのパス, または http から始まる URL)
const SITE_BREADCRUMB = [
  ['label' => 'QWEL in Action: Creative Tools', 'path' => 'https://qwel.design/tools/'],
  ['label' => 'Simple CMS', 'path' => ''],
];

// フッターのリンク (サイトのルートからのパス => 表示名)
const SITE_FOOTER_LINKS = [];

// トップページ・個別記事の右の aside に並べる記事 (投稿タイプと件数)
// このサイトでは投稿タイプの一覧ページを持たないので, ここが記事の一覧の代わりになる
const SITE_LATEST_TYPE = 'blog';
const SITE_LATEST_COUNT = 10;

// フッターの SNS など (icon は icons.svg の symbol の id)
const SITE_SNS = [
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'https://qwel.design/tools/contact-form/', 'icon' => 'si-mail'],
];

// 著作権表示 (開始年 - 今年 運営者)
const SITE_OWNER = 'QWEL.DESIGN';
const SITE_START_YEAR = 2019;

/**
 * 投稿タイプ
 * キー: 投稿タイプの名前
 * label: 表示名 (一覧の見出し, パンくず)
 * path: 一覧の URL (サイトのルートからのパス. 同じ名前のディレクトリに index.php と .htaccess を置く)
 * dir: Markdown を置くディレクトリ (サイトのルートからのパス)
 * list: 一覧ページをサイトマップに載せるか (規定で true)
 * count: 一覧の1ページあたりの件数 (null で全件)
 * show_date: 日付を表示するか
 * lead: 一覧の見出しの下の文
 */
const SITE_POST_TYPES = [
  'blog' => [
    'label'     => 'Blog',
    'path'      => 'blog/',
    'dir'       => 'content/blog',
    'count'     => 10,
    'list'      => false, // 一覧ページは持たない (サイトマップに載せない)
    'show_date' => true,
    'lead'      => 'Markdown で書いた記事のサンプルです。',
  ],
  // 固定ページ (日付を出さず, 一覧は全件を1ページに出す)
  'page' => [
    'label'     => 'Pages',
    'path'      => 'page/',
    'dir'       => 'content/page',
    'count'     => null,
    'list'      => false,
    'show_date' => false,
    'lead'      => '',
  ],
];

// URL の形式 (false: ?slug=xxx, ?page=2 / true: xxx/, page/2/ ※ .htaccess のリライトが必要)
const SITE_PRETTY_URLS = true;
