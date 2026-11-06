<?php
/**
 * ContentLoader.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * Markdown の原稿を読み, frontmatter と本文に分ける
 *
 * 原稿の形:
 *   ---
 *   title: "タイトル"
 *   ---
 *
 *   本文の Markdown
 *
 * frontmatter は 1 行に 1 項目の `key: value` だけを読む. 値はすべて文字列
 */

class ContentLoader {
  /**
   * 原稿を 1 本読む
   * 戻り値: frontmatter の項目と, 本文の Markdown (content) の配列
   * 読めないファイルと, frontmatter のないファイルは null
   */
  public function read(string $file): ?array {
    if (!is_file($file)) return null;

    // Windows で書いた原稿も同じように読めるように, BOM を除き, 改行を揃える
    $text = preg_replace('/^\xEF\xBB\xBF/', '', (string)file_get_contents($file));
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    if (!preg_match('/\A---\n(.*?)\n---(?:\n(.*))?\z/s', $text, $parts)) return null;

    $meta = [];
    foreach (explode("\n", $parts[1]) as $line) {
      if (preg_match('/^(\w+):\s*(.*)$/', trim($line), $item)) {
        $meta[$item[1]] = $this->unquote($item[2]);
      }
    }
    $meta['content'] = trim($parts[2] ?? '');
    return $meta;
  }

  // Markdown を HTML にする. 本文は運営者が書くものなので, HTML はそのまま通す
  public function toHtml(string $markdown): string {
    return (new Parsedown())->text($markdown);
  }

  // 値の前後の引用符を外す (`"a"` は a, `"a` はそのまま)
  private function unquote(string $value): string {
    return preg_match('/^"(.*)"$/', $value, $quoted) ? $quoted[1] : $value;
  }
}
