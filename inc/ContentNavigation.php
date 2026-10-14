<?php
/**
 * ContentNavigation.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * パンくずリストとページネーションの HTML を生成する
 */

class ContentNavigation {
  protected array $parents;
  protected ?string $subdirLabel;
  protected ?string $subdirPath;
  protected string $pageUrl;
  protected string $firstPageUrl;

  /**
   * オプション:
   * topLabel, topPath: パンくずの先頭 (規定で Top, /)
   * parents: パンくずの先頭に並べる階層 ([['label' => ..., 'path' => ...], ...])
   *          指定すると topLabel, topPath の代わりに使う (例: 親サイト > このサイトのトップ)
   * subdirLabel, subdirPath: 記事一覧の階層 (規定で Blog, /blog/)
   *          null を指定すると, 一覧の階層を出さない (固定ページなど)
   * pageUrl: ページ送りの URL (%d にページ番号が入る. 規定で ?page=%d)
   * firstPageUrl: 1ページ目の URL (規定で pageUrl の 1ページ目)
   */
  public function __construct(array $options = []) {
    $this->parents = $options['parents'] ?? [[
      'label' => $options['topLabel'] ?? 'Top',
      'path'  => $options['topPath'] ?? '/',
    ]];
    // null を明示した場合は一覧の階層を出さないため, ?? ではなく array_key_exists で判定する
    $this->subdirLabel = array_key_exists('subdirLabel', $options) ? $options['subdirLabel'] : 'Blog';
    $this->subdirPath  = array_key_exists('subdirPath', $options)  ? $options['subdirPath']  : '/blog/';
    $this->pageUrl      = $options['pageUrl'] ?? '?page=%d';
    $this->firstPageUrl = $options['firstPageUrl'] ?? sprintf($this->pageUrl, 1);
  }

  // パンくずHTML生成
  public function breadcrumb(?array $article = null): string {
    $item = fn(string $label, ?string $path = null): string => $path === null
      ? '<li class="breadcrumb__item is-current"><span aria-current="page">' . htmlspecialchars($label) . '</span></li>'
      : '<li class="breadcrumb__item"><a href="' . htmlspecialchars($path) . '">' . htmlspecialchars($label) . '</a></li>';

    $crumbs = [];
    $hasTitle = $article && isset($article['title']);

    // 先頭の階層
    foreach ($this->parents as $parent) {
      $crumbs[] = $item($parent['label'], $parent['path']);
    }

    // 記事一覧
    if ($this->subdirLabel) {
      $crumbs[] = $item($this->subdirLabel, $hasTitle ? $this->subdirPath : null);
    }

    // 記事タイトル
    if ($hasTitle) {
      $crumbs[] = $item($article['title']);
    }

    return '<nav aria-label="パンくずリスト"><ol id="breadcrumb" class="breadcrumb">'
      . implode('', $crumbs) . '</ol></nav>';
  }

  // ページネーションHTML生成
  public function pagination(int $currentPage, int $totalPages): string {
    if ($totalPages <= 1) return '';

    $html = '<nav aria-label="ページ送り"><ul class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
      if ($i === $currentPage) {
        $html .= '<li class="pagination__item is-current"><span aria-current="page">' . $i . '</span></li>';
      } else {
        $url = $i === 1 ? $this->firstPageUrl : sprintf($this->pageUrl, $i);
        $html .= '<li class="pagination__item"><a href="' . htmlspecialchars($url) . '">' . $i . '</a></li>';
      }
    }
    $html .= '</ul></nav>';
    return $html;
  }
}
