<?php
/**
 * ContentNavigation.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * ページ送り, パンくず, 前後の記事, 最新記事のデータを作る
 * HTML はテンプレートが持つ. ここでは配列だけを返す
 */

class ContentNavigation {
  /**
   * 記事を 1 ページ分に切り分ける
   * $perPage: 1 ページあたりの件数 (null で全件を 1 ページに出す)
   * 戻り値: ['posts' => そのページの記事, 'page' => ページ番号, 'totalPages' => 総ページ数, 'outOfRange' => 範囲外のページか]
   */
  public static function paginate(array $posts, int $page, ?int $perPage): array {
    $page = max(1, $page);
    $totalPages = $perPage === null ? 1 : max(1, (int)ceil(count($posts) / $perPage));
    return [
      'posts'      => $perPage === null ? ($page === 1 ? $posts : []) : array_slice($posts, $perPage * ($page - 1), $perPage),
      'page'       => $page,
      'totalPages' => $totalPages,
      'outOfRange' => $page > $totalPages,
    ];
  }

  /**
   * パンくずの階層を作る
   * $items: 先頭からの階層 ([['label' => 表示名, 'path' => パス], ...]). 最後の階層が現在地で, path を null にする
   */
  public static function breadcrumbs(array $items): array {
    $crumbs = array_values($items);
    if ($crumbs) $crumbs[count($crumbs) - 1]['path'] = null;
    return $crumbs;
  }

  /**
   * 前後の記事を探す
   * $posts は新しい順に並ぶので, 前の記事 (prev) は古い記事で後ろにあり, 次の記事 (next) は新しい記事で手前にある
   * (order で並べた投稿タイプでは, 並びの後ろが prev, 手前が next)
   * 戻り値: ['prev' => 記事 または null, 'next' => 記事 または null]
   */
  public static function adjacent(array $posts, string $slug): array {
    $index = array_search($slug, array_column($posts, 'slug'), true);
    if ($index === false) return ['prev' => null, 'next' => null];
    return ['prev' => $posts[$index + 1] ?? null, 'next' => $posts[$index - 1] ?? null];
  }

  // 最新の記事を取り出す ($excludeSlug: 表示中の記事など, 除く記事の slug)
  public static function latest(array $posts, int $count, ?string $excludeSlug = null): array {
    $others = array_filter($posts, fn(array $post): bool => ($post['slug'] ?? null) !== $excludeSlug);
    return array_slice(array_values($others), 0, max(0, $count));
  }
}
