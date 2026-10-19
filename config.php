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
// サイトの説明 (description の既定値)
const SITE_DESCRIPTION = 'Markdown ファイルを記事として読み込み、HTML に変換して表示するシンプルな PHP エンジンのデモです。';
// OGP 画像の既定値 (サイトのルートからのパス)
const SITE_OGP_IMAGE = 'content/images/about/fig_story.jpg';
// 記事一覧のサムネイルの既定値 (記事にアイキャッチがない場合)
const SITE_THUMBNAIL_IMAGE = 'content/images/about/fig_story.jpg';
// Google Fonts などの読み込み (不要なら空文字)
const SITE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500&family=Noto+Sans:wght@100..900&family=Noto+Serif:wght@100..900&display=swap';
// 言語
const SITE_LANG = 'ja';

// ヘッダーのロゴ (画像のパスとリンク先. 画像がなければ SITE_NAME を文字で出す)
const SITE_LOGO = 'assets/logo.svg';
const SITE_LOGO_LINK = 'https://qwel.design/';

// ヘッダーのナビ (サイトのルートからのパス => 表示名. 不要なら空の配列)
const SITE_NAV = [
  'blog/'       => 'Blog',
  'page/about/' => 'About',
];

// パンくずの先頭の階層 (path はサイトのルートからのパス, または http から始まる URL)
const SITE_BREADCRUMB = [
  ['label' => 'QWEL in Action: Creative Tools', 'path' => 'https://qwel.design/tools/'],
  ['label' => 'Simple CMS', 'path' => ''],
];

// フッターのリンク (サイトのルートからのパス => 表示名)
const SITE_FOOTER_LINKS = [
  ''            => 'Top',
  'blog/'       => 'Blog',
  'page/'       => 'Pages',
  'contact/'    => 'Contact',
  'sitemap.xml' => 'Sitemap',
];

// フッターの SNS など (url は http から始まる URL, またはサイトのルートからのパス. icon は icons.svg の symbol の id)
const SITE_SNS = [
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'contact/', 'icon' => 'si-mail'],
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
    'show_date' => true,
    'lead'      => 'Markdown で書いた記事のサンプルです。',
  ],
  // 固定ページ (日付を出さず, 一覧は全件を1ページに出す)
  'page' => [
    'label'     => 'Pages',
    'path'      => 'page/',
    'dir'       => 'content/page',
    'count'     => null,
    'show_date' => false,
    'lead'      => '',
  ],
];

// URL の形式 (false: ?slug=xxx, ?page=2 / true: xxx/, page/2/ ※ .htaccess のリライトが必要)
const SITE_PRETTY_URLS = true;

// 投稿タイプ以外のページ (サイトのルートからのパス. サイトマップに載せる)
const SITE_PAGES = [
  'contact/',
];

/**
 * お問い合わせフォーム (/contact/)
 * SMTP のパスワードなど秘密の値は inc/.env に書く (inc/.env.example を参照)
 *
 * path: フォームの URL (サイトのルートからのパス. 同じ名前のディレクトリに index.php, confirm/, thanks/ を置く. 送信は api/send.php)
 * title: ページのタイトル
 * lead: フォームの上の説明文 (HTML 可)
 * interval: 同じ人 (セッション) が続けて送信できるまでの秒数 (0 で制限しない)
 * reply_footer: 自動返信メールの末尾に付ける文
 * env: .env の場所 (公開ディレクトリの外に置く場合は, このパスを書き換える)
 */
const SITE_CONTACT = [
  'path'         => 'contact/',
  'title'        => 'お問い合わせ',
  'lead'         => 'このページは、<strong>QWEL.DESIGN</strong> へのお問い合わせフォームです。<br>お気軽にお問い合わせください。',
  'interval'     => 60,
  'reply_footer' => "
後日ご返信致しますので今しばらくお待ちください。

────────────────────────────────────────────────
福井の物作りのためのweb制作&プログラミング教室
QWEL.DESIGN (クヴェル・デザイン)
────────────────────────────────────────────────
",
  'env'          => __DIR__ . '/inc/.env',
];

/**
 * お問い合わせフォームの項目 (キーが name 属性. 並び順に表示する)
 * label: 項目名 (確認画面とメール本文にも使う)
 * type: text, email, tel, url, textarea, select, radio, checkbox
 *       (email の最初の項目を, 自動返信の宛先と管理者宛メールの返信先にする)
 * required: 必須か
 * options: select, radio, checkbox の選択肢
 * placeholder, autocomplete: 入力欄の属性 (任意)
 */
const SITE_CONTACT_FIELDS = [
  'name' => [
    'label'        => 'お名前',
    'type'         => 'text',
    'required'     => true,
    'autocomplete' => 'name',
  ],
  'email' => [
    'label'        => 'メールアドレス',
    'type'         => 'email',
    'required'     => true,
    'autocomplete' => 'email',
  ],
  'subject' => [
    'label'   => '件名',
    'type'    => 'select',
    'options' => ['ご質問・お問い合わせ', 'その他'],
  ],
  'message' => [
    'label'    => 'メッセージ本文',
    'type'     => 'textarea',
    'required' => true,
  ],
];
