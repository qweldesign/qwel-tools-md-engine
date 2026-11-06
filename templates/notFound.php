<?php
/**
 * notFound.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 見つからないページ
 * このファイルはサイトが持つもの. デザインに合わせて書き換えてよい
 */

require_once __DIR__ . '/navigation.php';

/**
 * $view:
 *   posts: 投稿タイプの全記事 (最新記事などに使う. 404.php から呼ばれたときは空)
 *   crumbs: パンくず
 *   backUrl, backLabel: 戻るリンク
 */
function renderNotFound(array $view): void {
  $title = 'ページが見つかりません';
  renderHeader(['title' => $title, 'noindex' => true]);
?>
    <main class="main">
<?php renderBreadcrumb($view['crumbs']); ?>
      <h1 class="main__title"><?= e($title) ?></h1>
      <p>お探しのページは、削除されたか、URLが変更された可能性があります。</p>
      <p><a href="<?= e($view['backUrl']) ?>"><?= e($view['backLabel']) ?></a></p>
    </main>
<?php
  renderFooter();
}
