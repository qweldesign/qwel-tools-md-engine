<?php
/**
 * ContentLoader.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * ディレクトリ内の Markdown (frontmatter 付き) を読み込む
 */

class ContentLoader {
  private string $dir;

  public function __construct(string $dir) {
    $this->dir = rtrim($dir, '/');
  }

  // 全記事を日付の新しい順に取得 (draft: "true" の記事は除く)
  public function load(): array {
    $articles = [];
    foreach (glob("$this->dir/*.md") as $file) {
      $parsed = $this->parse($file);
      if ($parsed && !$this->is_draft($parsed)) $articles[] = $parsed;
    }
    usort($articles, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
    return $articles;
  }

  // slug が一致する記事を取得 (見つからなければ null)
  public function find(string $slug): ?array {
    foreach ($this->load() as $article) {
      if (($article['slug'] ?? null) === $slug) return $article;
    }
    return null;
  }

  protected function is_draft(array $article): bool {
    return ($article['draft'] ?? 'false') === 'true';
  }

  protected function parse(string $file): ?array {
    $content = file_get_contents($file);
    if (!$content) return null;

    // 改行コードを揃える (Windows で書いた原稿にも対応する)
    $content = str_replace(["\r\n", "\r"], "\n", $content);

    if (preg_match('/^---\s*(.*?)\s*---\s*(.*)$/s', $content, $matches)) {
      $front = $matches[1];
      $body  = $matches[2];

      $meta = [];
      foreach (explode("\n", trim($front)) as $line) {
        if (preg_match('/^(\w+):\s*"?(.+?)"?$/', trim($line), $m)) {
          $meta[$m[1]] = $m[2];
        }
      }

      $meta['content'] = $body;
      return $meta;
    }

    return null;
  }
}
