<?php
/**
 * helpers.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * テンプレートで使う関数
 */

// HTMLに出す値のエスケープ
function e(?string $value): string {
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
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
