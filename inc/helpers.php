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

// http から始まる URL か
function isExternal(string $path): bool {
  return (bool)preg_match('#^https?://#', $path);
}

// サイトのルートからのパスにファイルがあるか (設置していない favicon などを出さないため)
function siteFileExists(string $path): bool {
  return is_file(dirname(__DIR__) . '/' . $path);
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
