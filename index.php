<?php
/**
 * トップページ (記事一覧) と個別記事
 * 記事は content/*.md に置く. 設定は config.php
 */
require_once __DIR__ . '/inc/templates/posts.php';

render_posts('blog', [
  // 一覧の1ページ目の見出しの代わりに, このエンジンの紹介を出す
  'intro' => function (): void {
?>
      <h1 class="main__title">Simple CMS | Created by <span> ― QWEL.DESIGN</span></h1>
      <ul class="main__list">
        <li class="main__listItem is-spec">Markdown を HTML に変換して表示</li>
        <li class="main__listItem is-spec">パンくずリストやページネーションを自動生成</li>
        <li class="main__listItem is-spec">データベース不要、ファイルベースで管理</li>
        <li class="main__listItem is-spec">PHP のみで動作、軽量・シンプル</li>
      </ul>
      <h2 class="text-center my-large">SAMPLE BLOG</h2>
<?php
  },
]);
