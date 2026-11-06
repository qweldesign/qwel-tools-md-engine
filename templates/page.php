<?php
/**
 * page.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 固定ページ (layout: page)
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 */

require_once __DIR__ . '/navigation.php';

/**
 * $view:
 *   type, config: 投稿タイプの名前と設定
 *   posts: 投稿タイプの全記事
 *   post: ページの原稿
 *   content: 本文の HTML
 *   crumbs: パンくず
 */
function renderPage(array $view): void {
  $post = $view['post'];
  renderHeader(['title' => $post['title'] ?? '', 'description' => $post['summary'] ?? '']);
?>
    <main class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
      <h1 class="main__title"><?= e($post['title'] ?? '') ?></h1>
      <div class="entry">
<?= $view['content'] ?>

      </div>
    </main>
<?php
  renderFooter();
}
