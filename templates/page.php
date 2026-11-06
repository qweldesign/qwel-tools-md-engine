<?php
/**
 * page.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 固定ページ (layout: page)
 */

require_once __DIR__ . '/postList.php';

/**
 * $view:
 *   type, config: 投稿タイプの名前と設定
 *   posts: 投稿タイプの全記事
 *   path: このページのパス (canonical に使う)
 *   post: ページの原稿
 *   content: 本文の HTML
 *   crumbs: パンくず
 *   head: 構造化データ (renderHeader() の head に渡す)
 */
function renderPage(array $view): void {
  $post = $view['post'];
  renderHeader(['title' => $post['title'] ?? '', 'description' => $post['summary'] ?? '', 'path' => $view['path'], 'image' => $post['img'] ?? '', 'head' => $view['head']]);
?>
    <main class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
<?php renderSiteHeading(); ?>
      <article>
        <div class="entry">
          <h2><?= e($post['title'] ?? '') ?></h2>
<?= $view['content'] ?>

        </div>
      </article>
    </main>
<?php
  renderFooter();
}
