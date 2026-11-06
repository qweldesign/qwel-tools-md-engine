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

// ヘッダーのロゴ (空ならサイト名を文字で出す)
const SITE_LOGO = 'assets/logo.svg';
// ロゴのリンク先 (空ならトップへ)
const SITE_LOGO_LINK = '';
// ヘッダーのナビ (リンク先 => 表示名. リンク先はサイトのルートからのパス, #section, http から始まる URL)
// 例: ['blog/' => 'Blog', '#about' => 'About']
const SITE_NAV = [];
// フッターのリンク (形は SITE_NAV と同じ)
const SITE_FOOTER_LINKS = [];
// フッターの SNS など (icon は assets/icons.svg の symbol の id. 不要なら空の配列)
const SITE_SNS = [
  ['label' => 'GitHub', 'url' => 'https://github.com/qweldesign', 'icon' => 'si-github'],
  ['label' => 'Contact Me', 'url' => 'contact/', 'icon' => 'si-mail']
];
// 著作権表示 (開始年 - 今年 運営者)
const SITE_OWNER = 'QWEL.DESIGN';
const SITE_START_YEAR = 2019;
