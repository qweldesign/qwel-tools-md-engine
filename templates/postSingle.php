<?php
/**
 * postSingle.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 個別記事のページ (layout: post)
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 */

require_once __DIR__ . '/navigation.php';

/**
 * $view:
 *   type, config: 投稿タイプの名前と設定
 *   post: 記事
 *   content: 本文の HTML
 *   adjacent: 前後の記事
 *   crumbs: パンくず
 */
function renderPostSingle(array $view): void {
  $post = $view['post'];
  $config = $view['config'];
  renderHeader(['title' => $post['title'] ?? '', 'description' => $post['summary'] ?? '']);
?>
    <main class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
      <h1 class="main__title"><?= e($post['title'] ?? '') ?></h1>
<?php if ($config['showDate'] && !empty($post['date'])) { ?>
      <p><?= dateTag($post['date']) ?></p>
<?php } ?>
      <div class="entry">
<?= $view['content'] ?>

      </div>
<?php renderPostNav($view['adjacent'], fn(array $item): string => postUrl($view['type'], $item)); ?>
      <p><a href="<?= e(typeUrl($view['type'])) ?>"><?= e($config['label']) ?>の一覧へ</a></p>
    </main>
<?php
  renderFooter();
}
