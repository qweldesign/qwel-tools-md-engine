<?php
/**
 * ContentEngine.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 記事の一覧・個別記事の取得と, パンくず・ページネーションの生成をまとめる
 */

require_once __DIR__ . '/ContentLoader.php';
require_once __DIR__ . '/ContentNavigation.php';
require_once __DIR__ . '/Parsedown.php';

class ContentEngine {
  protected ?int $count;
  protected int $page;
  protected ?string $slug;
  protected array $posts = [];
  protected ?array $article = null;
  protected array $navigation;
  protected ?string $baseUrl;

  /**
   * オプション:
   * dir: Markdown を置くディレクトリ (規定で content/)
   * count: 一覧の1ページあたりの件数 (規定で 10. null を指定すると全件を1ページに出す)
   * navigation: パンくず・ページ送りの設定 (ContentNavigation のオプション)
   * baseUrl: 本文中の「./」で始まる画像・リンクのパスを, この URL からのパスに置き換える
   *          (例: '/tools/simple-cms/'. 記事の URL の階層が深くても, 原稿は ./content/images/... と書ける)
   * slug, page: 個別記事の slug と一覧のページ番号 (規定で $_GET['slug'], $_GET['page'])
   */
  public function __construct(array $options = []) {
    $count = array_key_exists('count', $options) ? $options['count'] : 10;
    $this->count = $count === null ? null : max(1, (int)$count);
    // slug, page は null を明示すると $_GET を見ない (トップページで別の投稿タイプの一覧を出す場合など)
    $page = array_key_exists('page', $options) ? $options['page'] : ($_GET['page'] ?? 1);
    $this->page = max(1, (int)($page ?? 1));
    $slug = array_key_exists('slug', $options) ? $options['slug'] : ($_GET['slug'] ?? null);
    $this->slug = $slug === null ? null : (string)$slug;
    $this->navigation = $options['navigation'] ?? [];
    $this->baseUrl = $options['baseUrl'] ?? null;

    $dir    = $options['dir'] ?? dirname(__DIR__) . '/content/';
    $loader = new ContentLoader($dir);

    // 全記事取得
    $this->posts = $loader->load();

    // 個別記事取得 (読み込み済みの記事から探す)
    if ($this->is_single()) {
      $index = array_search($this->slug, array_column($this->posts, 'slug'), true);
      $this->article = $index === false ? null : $this->posts[$index];
    }
  }

  // 個別記事ページか否か
  public function is_single(): bool {
    return $this->slug !== null;
  }

  // 個別記事が見つかったか否か
  public function is_found(): bool {
    return $this->article !== null;
  }

  // 404 にすべきか (記事が見つからない, または一覧のページ番号が範囲外)
  public function is_not_found(): bool {
    return $this->is_single()
      ? !$this->is_found()
      : $this->page > 1 && !$this->get_posts();
  }

  // 一覧の現在のページ番号
  public function get_page(): int {
    return $this->page;
  }

  // 一覧の総ページ数
  public function get_total_pages(): int {
    return $this->count === null ? 1 : max(1, (int)ceil(count($this->posts) / $this->count));
  }

  // 全記事からページ数を切り取って取得
  public function get_posts(?int $page = null, ?int $count = null): array {
    $page  = $page  ?? $this->page;
    $count = $count ?? $this->count;
    if ($count === null) return $page === 1 ? $this->posts : [];
    return array_slice($this->posts, $count * ($page - 1), $count);
  }

  // 個別記事のメタ情報 (frontmatter) 取得
  public function get_meta(string $key, string $default = ''): string {
    return $this->article[$key] ?? $default;
  }

  // タイトル取得
  public function get_title(): string {
    return $this->get_meta('title');
  }

  // 日付取得 ($format を省略すると, 年月のみの日付 (例: 2022-08) は Y.m, それ以外は Y.m.d で表示する)
  public function get_date(?string $format = null): string {
    $date = $this->get_meta('date');
    if (!$date) return '';
    return $format === null ? self::format_date($date) : date($format, strtotime($date));
  }

  // time 要素の datetime 属性用の日付 (年月のみなら Y-m, それ以外は Y-m-d)
  public function get_datetime(): string {
    return self::format_date($this->get_meta('date'), true);
  }

  // 日付の整形 (一覧など個別記事以外でも使う)
  // date は 2026-10-09 のほか, 年月のみの 2022-08 も受け付ける
  public static function format_date(string $date, bool $machine = false): string {
    $date = trim($date);
    if ($date === '') return '';
    $monthOnly = (bool)preg_match('/^\d{4}-\d{1,2}$/', $date);
    $format = $machine
      ? ($monthOnly ? 'Y-m' : 'Y-m-d')
      : ($monthOnly ? 'Y.m' : 'Y.m.d');
    return date($format, strtotime($date));
  }

  // 記事内容取得 (Markdown を HTML に変換する)
  public function get_content(): string {
    $parsedown = new Parsedown();
    $html = $parsedown->text($this->get_meta('content'));
    return $this->baseUrl === null ? $html : self::resolve_paths($html, $this->baseUrl);
  }

  // 「./」で始まる src, href を baseUrl からのパスに置き換える
  public static function resolve_paths(string $html, string $baseUrl): string {
    $base = rtrim($baseUrl, '/') . '/';
    return preg_replace('/\b(src|href)="\.\//', '$1="' . $base, $html);
  }

  // 前後の記事 (prev: 新しい記事, next: 古い記事) 取得
  public function get_adjacent(): array {
    $slugs = array_column($this->posts, 'slug');
    $index = array_search($this->get_meta('slug'), $slugs, true);
    if ($index === false) return ['prev' => null, 'next' => null];
    return [
      'prev' => $this->posts[$index - 1] ?? null,
      'next' => $this->posts[$index + 1] ?? null,
    ];
  }

  // パンくず生成
  public function get_breadcrumb(): string {
    $nav = new ContentNavigation($this->navigation);
    return $this->is_found() ? $nav->breadcrumb($this->article) : $nav->breadcrumb();
  }

  // ページネーション生成
  public function pagination(): string {
    $nav = new ContentNavigation($this->navigation);
    return $nav->pagination($this->page, $this->get_total_pages());
  }
}
