<?php
/**
 * サイトの設定
 * サイトごとに変わる値は, ここにまとめる
 */

// 日付の判定と表示のタイムゾーン
date_default_timezone_set('Asia/Tokyo');

// サイト名 (下層ページの title, ロゴの代わりの文字)
const SITE_NAME = 'SIMPLE CMS | QWEL in Action';
// トップページの title (空なら SITE_NAME)
const SITE_TITLE = '';
// サイトの見出し (トップページと個別記事の h1. SITE_HEADING_SUB は改行して小さく出す副題)
const SITE_HEADING = '鋤と鉋とプログラミング';
const SITE_HEADING_SUB = '―子ども向けプログラミング教室講師ブログ';
// サイトの URL (末尾のスラッシュなし. canonical, OGP の絶対 URL と, お問い合わせフォームの送信元の確認に使う)
const SITE_URL = 'https://qwel.design';
// サイトのルートのパス (ドメイン直下なら /, サブディレクトリなら /tools/simple-cms/ のように)
const SITE_ROOT = '/tools/simple-cms/';
const SITE_LANG = 'ja';
// description の既定値
const SITE_DESCRIPTION = 'Markdown ファイルを記事として読み込み、HTMLに変換して表示するシンプルなPHPエンジンのデモです。';
// Google Fonts などの CSS の URL (不要なら空文字. display=swap を付ける. 書体と太さを絞ると転送量が減る)
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&family=Zen+Old+Mincho:wght@600&display=swap';
// Google Analytics の測定 ID (不要なら空文字)
const SITE_GA_ID = '';
// OGP 画像の既定値 (サイトのルートからのパス. 不要なら空文字) と代替テキスト
const SITE_OGP_IMAGE = 'content/images/about/fig_story.jpg';
const SITE_OGP_IMAGE_ALT = '';
// og:locale (不要なら空文字)
const SITE_OG_LOCALE = 'ja_JP';
// 一覧の画像の既定値 (記事に img がない場合. 不要なら空文字)
const SITE_THUMBNAIL_IMAGE = 'content/images/about/fig_story.jpg';
// URL の形式 (true: /blog/xxx/, false: /blog/?slug=xxx). .htaccess が効かない php -S では false になる
const SITE_PRETTY_URLS = PHP_SAPI !== 'cli-server';

// ヘッダーのロゴ (空ならサイト名を文字で出す)
const SITE_LOGO = 'assets/logo.svg';
// ロゴのリンク先 (空ならトップへ)
const SITE_LOGO_LINK = 'https://qwel.design/';
// ヘッダーのナビ (リンク先 => 表示名. リンク先はサイトのルートからのパス, #section, http から始まる URL)
// 例: ['blog/' => 'Blog', '#about' => 'About']
const SITE_NAV = [];
// フッターのリンク (形は SITE_NAV と同じ)
const SITE_FOOTER_LINKS = [];
// パンくずの先頭の階層 (path はサイトのルートからのパス, または http から始まる URL)
const SITE_BREADCRUMB = [
  ['label' => 'QWEL in Action: Creative Tools', 'path' => 'https://qwel.design/tools/'],
  ['label' => 'Simple CMS', 'path' => ''],
];
// フッターの SNS など (icon は assets/icons.svg の symbol の id. 不要なら空の配列)
const SITE_SNS = [
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'https://qwel.design/contact/', 'icon' => 'si-mail']
];
// 投稿タイプ以外のページ (サイトのルートからのパス. サイトマップに載せる)
const SITE_PAGES = [];
// robots.txt でクロールを拒否するパス (サイトのルートからのパス. 例: 'private/'. 不要なら空の配列)
const SITE_ROBOTS_DISALLOW = [];
// 記事の著者 (構造化データの author. 不要なら空の配列. 例: ['@type' => 'Organization', 'name' => 'QWEL.DESIGN'])
const SITE_AUTHOR = [];
// トップページと個別記事の aside に並べる記事 (このサイトは一覧ページを持たないので, 一覧の代わりになる)
const SITE_LATEST_TYPE = 'blog';
const SITE_LATEST_COUNT = 10;
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
 * schema: 個別記事の構造化データの種類 (schema.org の @type. 既定で BlogPosting. null で出さない)
 * list: false で一覧をサイトマップに載せない (既定で true)
 * breadcrumb: false でパンくずに投稿タイプの階層を出さない (一覧ページを持たないサイトなど. 既定で true)
 */
const SITE_POST_TYPES = [
  'blog' => [
    'label'      => 'Blog',
    'breadcrumb' => false,
    'list'     => false,
  ],
  'page' => [
    'label'    => 'Pages',
    'path'     => '',
    'layout'   => 'page',
    'count'    => null,
    'showDate' => false,
    'schema'   => 'WebPage',
  ],
];
