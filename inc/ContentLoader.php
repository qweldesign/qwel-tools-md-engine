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
   * ディレクトリの原稿をすべて読み, 並べる
   * frontmatter のないファイルは飛ばす
   * $filter: 残す記事に true を返す関数 (例: 書いた人ごとの一覧)
   * $sort: date (日付の新しい順) または order (order の小さい順. order がない記事は後ろ)
   * 日付は文字列で比べるので, 2026-10-09 または 2026-10 の形で書く (年月だけの日付は, 同じ月の中で後ろになる)
   * $includeDrafts: 下書きも読む. 既定で確認用サーバー (php -S) のときだけ読む (XAMPP などの手元のサーバーは本番と同じ扱い)
   *                 読んだ下書きのタイトルには印を付ける. 独自のテンプレートにも, 書き換えなしで効くように, データの側で付ける
   */
  public function load(string $dir, ?callable $filter = null, string $sort = 'date', bool $includeDrafts = PHP_SAPI === 'cli-server'): array {
    $posts = [];
    foreach (glob(rtrim($dir, '/') . '/*.md') ?: [] as $file) {
      $post = $this->read($file);
      if ($post === null || ($filter !== null && !$filter($post))) continue;
      if (self::isDraft($post)) {
        if (!$includeDrafts) continue;
        $post['title'] = '[下書き] ' . ($post['title'] ?? '');
      }
      $posts[] = $post;
    }
    usort($posts, $sort === 'order'
      ? fn(array $a, array $b): int => $this->orderOf($a) <=> $this->orderOf($b)
      : fn(array $a, array $b): int => strcmp($b['date'] ?? '', $a['date'] ?? ''));
    return $posts;
  }

  // 下書きか (原稿に draft: "true" と書く)
  public static function isDraft(array $post): bool {
    return ($post['draft'] ?? '') === 'true';
  }

  // 並び順の数値 (値はすべて文字列で読むので, 比べるときに数値にする)
  private function orderOf(array $post): int {
    return isset($post['order']) ? (int)$post['order'] : PHP_INT_MAX;
  }

  // 読み込んだ記事から, slug が一致する記事を探す (見つからなければ null)
  public function find(array $posts, string $slug): ?array {
    foreach ($posts as $post) {
      if (($post['slug'] ?? null) === $slug) return $post;
    }
    return null;
  }

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

  /**
   * Markdown を HTML にする. 本文は運営者が書くものなので, HTML はそのまま通す
   * $rootUrl: サイトのルートの URL. 「/content/」で始まる src, href の先頭をこれに置き換える
   *           (原稿には /content/images/a.jpg と書く. サブディレクトリに置いたサイトでも画像が表示される)
   */
  public function toHtml(string $markdown, string $rootUrl = '/'): string {
    $html = (new Parsedown())->text($markdown);
    // タグの中だけを置き換える (コードの例など, 本文の文字は変えない)
    return preg_replace_callback('/<[a-z][^>]*>/i', fn(array $tag): string => str_replace(
      ['src="/content/', 'href="/content/'],
      ['src="' . $rootUrl . 'content/', 'href="' . $rootUrl . 'content/'],
      $tag[0]
    ), $html);
  }

  // 値の前後の引用符を外す (`"a"` は a, `"a` はそのまま)
  private function unquote(string $value): string {
    return preg_match('/^"(.*)"$/', $value, $quoted) ? $quoted[1] : $value;
  }
}
