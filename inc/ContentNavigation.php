<?php
/**
 * ContentNavigation.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 */

class ContentNavigation {
  protected string $topLabel;
  protected string $topPath;
  protected string $subdirLabel;
  protected string $subdirPath;

  public function __construct(array $options = []) {
    $this->topLabel    = $options['topLabel']    ?? 'Top';
    $this->topPath     = $options['topPath']     ?? '/';
    $this->subdirLabel = $options['subdirLabel'] ?? 'Blog';
    $this->subdirPath  = $options['subdirPath']  ?? '/blog/';
  }

  // パンくずHTML生成
  public function breadcrumb(array $article = null): string {
    $crumbs = [];

    // Top
    $crumbs[] = '<li class="breadcrumb__item"><a href="'
       . htmlspecialchars($this->topPath) . '">'
       . htmlspecialchars($this->topLabel) . '</a></li>';

    // Subdir
    if ($this->subdirLabel && $this->subdirPath) {
      if ($article && isset($article['title'])) {
        $crumbs[] = '<li class="breadcrumb__item"><a href="'
           . htmlspecialchars($this->subdirPath) . '">'
           . htmlspecialchars($this->subdirLabel) . '</a></li>';
      } else {
        $crumbs[] = '<li class="breadcrumb__item is-current"><span>'
           . htmlspecialchars($this->subdirLabel) . '</span></li>';
      }
    }

    // Article title
    if ($article && isset($article['title'])) {
      $crumbs[] = '<li class="breadcrumb__item is-current"><span>'
         . htmlspecialchars($article['title']) . '</span></li>';
    }

    return '<ul id="breadcrumb" class="breadcrumb">'
       . implode('', $crumbs) . '</ul>';
  }

  // ページネーションHTML生成
  public function pagination(int $currentPage, int $totalPages): string {
    if ($totalPages === 1) return '';
    
    $html = '<ul class="pagination">';
      for ($i = 1; $i <= $totalPages; $i++) {
        if ($i === $currentPage) {
          $html .= '<li class="pagination__item is-current"><span>' . $i . '</span></li>';
        } else {
          $html .= '<li class="pagination__item"><a href="?page=' . $i . '">' . $i . '</a></li>';
        }
      }
      $html .= '</ul>';
      return $html;
  }
}
