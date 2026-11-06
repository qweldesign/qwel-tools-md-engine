<?php
/**
 * helpers.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * テンプレートで使う関数
 * config.php を読み込んだあとに使う
 */

// エンジンの版 (リリースのときに更新する. サイトがどの版から作られたかを, 公開中のページのソースで確かめられるように出力する)
const SITE_ENGINE_VERSION = '0.1.0';

// HTMLに出す値のエスケープ
function e(?string $value): string {
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * サイトのルートからのパスを, リンクに使う URL にする
 * 例: path('blog/') → /blog/, path('#about') → /#about (どのページからでもトップのセクションへ移動する)
 * 先頭の「/」「./」はサイトのルートとみなす. http から始まる URL はそのまま返す
 */
function path(string $path = ''): string {
  if (isExternal($path)) return $path;
  return SITE_ROOT . preg_replace('#^\.?/#', '', $path);
}

// サイトのルートからのパスを, 絶対 URL にする (canonical, OGP 用)
function absoluteUrl(string $path = ''): string {
  return isExternal($path) ? $path : SITE_URL . path($path);
}

/**
 * OGP 画像の情報
 * 指定がないときと, 手元のファイルがないときは SITE_OGP_IMAGE を使う (共有したときのプレビューが空にならないように)
 * 戻り値: ['url' => 絶対 URL, 'width' => 幅 または null, 'height' => 高さ または null, 'isDefault' => 既定の画像か]. 画像がなければ null
 */
function ogImage(string $image = ''): ?array {
  $default = defined('SITE_OGP_IMAGE') ? SITE_OGP_IMAGE : '';
  $isUsable = $image !== '' && (isExternal($image) || siteFileExists($image));
  $path = $isUsable ? $image : $default;
  if ($path === '') return null;

  $size = !isExternal($path) && siteFileExists($path) ? @getimagesize(siteFile($path)) : false;
  return [
    'url'       => absoluteUrl($path),
    'width'     => $size ? $size[0] : null,
    'height'    => $size ? $size[1] : null,
    'isDefault' => !$isUsable,
  ];
}

// http から始まる URL か
function isExternal(string $path): bool {
  return (bool)preg_match('#^https?://#', $path);
}

// サイトのルートからのパスを, サーバー上のパスにする
function siteFile(string $path): string {
  return dirname(__DIR__) . '/' . trim($path, '/');
}

// サイトのルートからのパスにファイルがあるか (設置していない favicon などを出さないため)
function siteFileExists(string $path): bool {
  return is_file(siteFile($path));
}

// 投稿タイプの設定 (未指定の項目は既定値で補う)
function postType(string $type): array {
  if (!isset(SITE_POST_TYPES[$type])) {
    throw new InvalidArgumentException("投稿タイプ {$type} は config.php にありません");
  }
  return SITE_POST_TYPES[$type] + [
    'label'      => $type,
    'path'       => $type . '/',
    'dir'        => 'content/' . $type,
    'count'      => 10,
    'showDate'   => true,
    'lead'       => '',
    'empty'      => 'まだ記事はありません。',
    'layout'     => 'post',
    'items'      => true,
    'sort'       => 'date',
    'breadcrumb' => true,
    'list'       => true,
  ];
}

// 投稿タイプの一覧のパス (サイトのルートからのパス)
function typePath(string $type): string {
  return postType($type)['path'];
}

/**
 * 記事のパス (サイトのルートからのパス)
 * 個別のページを持たない投稿タイプ (items: false) は, 原稿の url
 * ルートに置く投稿タイプ (path: '') は, SITE_PRETTY_URLS が false のときだけ, 入口のディレクトリ (投稿タイプの名前) を通す
 */
function postPath(string $type, array $post): string {
  $config = postType($type);
  if (!$config['items']) return $post['url'] ?? '';
  $slug = rawurlencode($post['slug'] ?? '');
  if (SITE_PRETTY_URLS) return $config['path'] . $slug . '/';
  return ($config['path'] === '' ? $type . '/' : $config['path']) . '?slug=' . $slug;
}

// 一覧のページのパス (サイトのルートからのパス)
function pagePath(string $type, int $page): string {
  $path = typePath($type);
  if ($page === 1) return $path;
  return $path . (SITE_PRETTY_URLS ? 'page/' . $page . '/' : '?page=' . $page);
}

// 上のパスを, リンクに使う URL にしたもの
function typeUrl(string $type): string {
  return path(typePath($type));
}

function postUrl(string $type, array $post): string {
  return path(postPath($type, $post));
}

function pageUrl(string $type, int $page): string {
  return path(pagePath($type, $page));
}

/**
 * 原稿の日付を区切り文字でつなぐ
 * 2026-10-09 → 2026.10.09, 2026-10 → 2026.10
 * 形が違う日付は, そのまま返す
 */
function formatDate(string $date, string $separator = '.'): string {
  if (!preg_match('/^(\d{4})-(\d{1,2})(?:-(\d{1,2}))?$/', trim($date), $parts)) return $date;
  $numbers = array_map(fn(string $part): string => str_pad($part, 2, '0', STR_PAD_LEFT), array_slice($parts, 1));
  return implode($separator, $numbers);
}

// 記事の日付の time 要素 (日付がなければ空文字)
function dateTag(?string $date): string {
  if (!$date) return '';
  return '<time datetime="' . e(formatDate($date, '-')) . '">' . e(formatDate($date)) . '</time>';
}

// 著作権表示の年 (例: 2019 - 2026. 開始年が今年なら 2026)
function copyrightYears(): string {
  $current = (int)date('Y');
  return SITE_START_YEAR < $current ? SITE_START_YEAR . ' - ' . $current : (string)$current;
}
