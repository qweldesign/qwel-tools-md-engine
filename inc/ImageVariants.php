<?php
/**
 * ImageVariants.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * 画像の幅ごとの WebP (srcset と sizes)
 * 元の画像 (JPEG, PNG) から, SITE_IMAGE_WIDTHS の幅の WebP を cache/images/ に作る
 * FTP で画像を上げるだけで使えるように, ページを表示するときに, まだない幅をその場で作る
 * cache/images/ はいつ丸ごと消してもよい (次の表示で作り直す)
 * sizes は style.css の :root のブレイクポイントとコンテナ幅から作る (scss/settings/_gridSystem.scss の値を二重に持たないため)
 */

class ImageVariants {
  // 作った画像の置き場 (サイトのルートからのパス. 元の画像のパスをそのまま写す)
  private const DIR = 'cache/images';
  // WebP の品質
  private const QUALITY = 75;
  // 1 回の表示で, 作るのに使ってよい秒数 (画像を上げた直後の表示を遅くしすぎないため. 超えた分は次の表示で作る)
  private const TIME_BUDGET = 2.0;

  private static float $spent = 0.0;

  /**
   * 画像の幅と高さ (EXIF の向きを直したあとの値. 読めなければ null)
   * スマートフォンの縦の写真は, 横のまま保存して EXIF で回すことがある. ブラウザは回して表示するので, 回したあとの値を返す
   */
  public static function size(string $file): ?array {
    $size = @getimagesize($file);
    if (!$size || $size[0] <= 0 || $size[1] <= 0) return null;
    return in_array(self::orientation($file, $size[2]), [5, 6, 7, 8], true) ? [$size[1], $size[0]] : [$size[0], $size[1]];
  }

  /**
   * srcset の値 (作れなければ空文字)
   * $path: サイトのルートからのパス, $width: 画像の幅 (size() の値)
   * 次のときは, 元の画像も候補に入れる (小さい WebP だけが候補になり, 粗く表示されないように)
   * - 元の画像が SITE_IMAGE_WIDTHS の最大より大きい (高解像度の画面で, 最大の幅より大きく表示するとき)
   * - 間に合わなかった幅がある
   */
  public static function srcset(string $path, int $width): string {
    if (!self::isEnabled() || !preg_match('/\.(?:jpe?g|png)$/i', $path)) return '';

    $source = siteFile($path);
    $mtime = (int)filemtime($source);
    $items = [];
    $widths = self::widths($width);
    $needsOriginal = max($widths) < $width;
    foreach ($widths as $variantWidth) {
      $variant = self::DIR . '/' . trim($path, '/') . '-' . $variantWidth . '.webp';
      $file = siteFile($variant);
      if (self::isFresh($file, $mtime) || self::create($source, $file, $variantWidth, $mtime)) {
        $items[] = self::url($variant) . '?v=' . $mtime . ' ' . $variantWidth . 'w';
      } else {
        $needsOriginal = true;
      }
    }
    if (!$items) return '';
    if ($needsOriginal) $items[] = self::url($path) . ' ' . $width . 'w';
    return implode(', ', $items);
  }

  /**
   * sizes の値 (例: (min-width: 1200px) 384px, (min-width: 900px) 33.33vw, 100vw)
   * $container: コンテナのキー (sm, md, lg) または内寸の px. キーなら, style.css のコンテナ幅から余白の最大値を引いた値を内寸にする
   * $ratios: ブレイクポイントのキー => コンテナの何割に表示するか (そのブレイクポイント以上で効く. xs はすべての幅)
   * 列の間のガターは引かない (少し大きめになるが, 候補の幅は表示する幅より少し上にそろえているので困らない)
   * style.css から読めないときは 100vw
   */
  public static function sizes(string|int $container = 'lg', array $ratios = []): string {
    $layout = self::layout();
    if (is_int($container)) {
      $inner = $outer = $container;
    } else {
      $outer = $layout['container-width'][$container] ?? null;
      $inner = $outer === null ? null : $outer - 2 * ($layout['container-pad'][$container] ?? 0);
    }
    if ($inner === null || $inner <= 0) return '100vw';

    // ブレイクポイント (px) => 割合. コンテナが内寸で止まる幅も区切りにする
    // 区切りは整数の px に切り上げる (コンテナ幅が 710.4px のような端数でも, 区切りの幅で比べられるように)
    $outerMin = (int)ceil($outer);
    $steps = [0 => 1.0];
    foreach ($ratios as $key => $ratio) {
      $min = $key === 'xs' ? 0 : ($layout['breakpoint'][$key] ?? null);
      if ($min !== null) $steps[(int)ceil($min)] = (float)$ratio;
    }
    ksort($steps);
    $thresholds = array_unique([...array_keys($steps), $outerMin]);
    rsort($thresholds);

    $items = [];
    foreach ($thresholds as $min) {
      $ratio = 1.0;
      foreach ($steps as $stepMin => $stepRatio) {
        if ($stepMin <= $min) $ratio = $stepRatio;
      }
      $items[$min] = $min >= $outerMin ? round($inner * $ratio) . 'px' : self::number(100 * $ratio) . 'vw';
    }
    // 次に狭い区切りと同じ値なら, まとめる
    $result = [];
    $values = array_values($items);
    foreach (array_keys($items) as $index => $min) {
      $isSame = isset($values[$index + 1]) && $values[$index + 1] === $values[$index];
      if ($isSame) continue;
      $result[] = $min > 0 ? "(min-width: {$min}px) {$values[$index]}" : $values[$index];
    }
    return implode(', ', $result);
  }

  // style.css の :root のブレイクポイント, コンテナ幅, コンテナの余白 (px). 1 回のリクエストで 1 度だけ読む
  private static function layout(): array {
    static $layout = null;
    if ($layout !== null) return $layout;

    $layout = ['breakpoint' => [], 'container-width' => [], 'container-pad' => []];
    $css = siteFileExists('style.css') ? (string)file_get_contents(siteFile('style.css')) : '';
    // ルートの文字サイズの上限 ($font-size-base. ない古い style.css では既定の 16px)
    $rem = preg_match('/--root-font-size\s*:\s*([\d.]+)px/', $css, $root) ? (float)$root[1] : 16.0;
    preg_match_all('/--(breakpoint|container-width|container-pad)--([\w-]+)\s*:\s*([^;}]+)/', $css, $matches, PREG_SET_ORDER);
    foreach ($matches as [, $name, $key, $value]) {
      $px = self::px($value, $rem);
      if ($px !== null) $layout[$name][$key] = $px;
    }
    return $layout;
  }

  /**
   * CSS の長さを px にする (読めなければ null)
   * rem は $rem (ルートの文字サイズの上限) で換算する. sizes の px の値が効くのは, コンテナが内寸で止まる広い画面で, そこではルートの文字サイズが上限になるため
   * clamp() は最大値 (コンテナが内寸で止まる幅では, 余白は最大値になる)
   */
  private static function px(string $value, float $rem): ?float {
    $value = trim($value);
    if (preg_match('/^clamp\((.+)\)$/', $value, $clamp)) {
      $parts = explode(',', $clamp[1]);
      $value = trim((string)end($parts));
    }
    if (!preg_match('/^(-?[\d.]+)(px|rem)$/', $value, $length)) return null;
    return (float)$length[1] * ($length[2] === 'rem' ? $rem : 1);
  }

  // 小数 2 桁まで, 末尾の 0 を省いた数
  private static function number(float $value): string {
    return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
  }

  // WebP を作れるか (SITE_IMAGE_WIDTHS がない古い config.php と, GD がないサーバーでは作らない)
  private static function isEnabled(): bool {
    return defined('SITE_IMAGE_WIDTHS') && SITE_IMAGE_WIDTHS && function_exists('imagewebp');
  }

  /**
   * 作る幅 (元の画像より大きい幅は作らない)
   * 元の画像の幅が一覧の最大より小さいときは, 元の幅でも作る (いちばん大きい候補を WebP にするため)
   */
  private static function widths(int $width): array {
    $widths = array_map('intval', SITE_IMAGE_WIDTHS);
    $largest = min($width, max($widths));
    $widths = array_filter($widths, fn(int $value): bool => $value > 0 && $value < $largest);
    $widths[] = $largest;
    $widths = array_unique($widths);
    sort($widths);
    return $widths;
  }

  // 作った画像が元の画像と同じ版か (作ったときに更新時刻を元の画像に揃えている. FTP が古い時刻のまま上げても作り直せるように, 前後ではなく一致で比べる)
  private static function isFresh(string $file, int $mtime): bool {
    return is_file($file) && filemtime($file) === $mtime;
  }

  // WebP を作る (作れなかったら false)
  private static function create(string $source, string $file, int $width, int $mtime): bool {
    if (self::$spent >= self::TIME_BUDGET) return false;

    $info = @getimagesize($source);
    // 元の画像と, 回したときの複製と, 縮めた画像 (縦長でも足りるように多めに見る)
    if (!$info || !self::hasMemory($info[0] * $info[1] * 2 + $width * $width * 2)) {
      error_log("画像の WebP を作れません (メモリが足りないか, 読めない画像): {$source}");
      return false;
    }

    $start = microtime(true);
    $image = match ($info[2]) {
      IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
      IMAGETYPE_PNG  => @imagecreatefrompng($source),
      default        => false,
    };
    $isCreated = false;
    if ($image) {
      $image = self::orient($image, self::orientation($source, $info[2]));
      $height = (int)round(imagesy($image) * $width / imagesx($image));
      $resized = imagecreatetruecolor($width, $height);
      // PNG の透明を残す
      imagealphablending($resized, false);
      imagesavealpha($resized, true);
      imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
      // 大きな元の画像のメモリを, 書き出す前に放す (imagedestroy() は PHP 8 から不要で, 8.5 で非推奨)
      unset($image);

      // 別の名前で書いてから置き換える (同時に開いた人に, 書きかけのファイルを返さないため)
      $dir = dirname($file);
      $temp = $file . '.' . getmypid() . '.tmp';
      $isCreated = (is_dir($dir) || @mkdir($dir, 0755, true)) && imagewebp($resized, $temp, self::QUALITY) && @rename($temp, $file);
      if ($isCreated) {
        touch($file, $mtime);
        clearstatcache(true, $file);
      } else {
        @unlink($temp);
        error_log("画像の WebP を書き込めません: {$file}");
      }
    }
    self::$spent += microtime(true) - $start;
    return $isCreated;
  }

  // EXIF の向き (1 から 8. JPEG 以外と, 読めないときは 1)
  private static function orientation(string $file, int $type): int {
    if ($type !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) return 1;
    $exif = @exif_read_data($file);
    return is_array($exif) ? (int)($exif['Orientation'] ?? 1) : 1;
  }

  // EXIF の向きに合わせて回す (imagerotate() の角度は反時計回り)
  private static function orient(GdImage $image, int $orientation): GdImage {
    if (in_array($orientation, [4, 5], true)) imageflip($image, IMG_FLIP_VERTICAL);
    if (in_array($orientation, [2, 7], true)) imageflip($image, IMG_FLIP_HORIZONTAL);
    $angle = match ($orientation) {
      3       => 180,
      5, 6, 7 => -90,
      8       => 90,
      default => 0,
    };
    if ($angle === 0) return $image;
    return imagerotate($image, $angle, 0) ?: $image;
  }

  // 画素数から見て, 画像を開くメモリが残っているか (GD は 1 画素に約 5 バイトを使う. 足りないと PHP ごと止まるので, 先に確かめる)
  private static function hasMemory(int $pixels): bool {
    $limit = self::bytes((string)ini_get('memory_limit'));
    return $limit < 0 || $limit - memory_get_usage() > $pixels * 5 * 1.2;
  }

  // php.ini の値 (128M など) をバイトにする (-1 は上限なし)
  private static function bytes(string $value): int {
    $value = trim($value);
    if ($value === '' || $value === '-1') return -1;
    $number = (int)$value;
    return match (strtolower(substr($value, -1))) {
      'g'     => $number * 1024 ** 3,
      'm'     => $number * 1024 ** 2,
      'k'     => $number * 1024,
      default => $number,
    };
  }

  // srcset に書く URL (空白とカンマで区切られるので, パスの各部分をエンコードする)
  private static function url(string $path): string {
    return path(implode('/', array_map('rawurlencode', explode('/', trim($path, '/')))));
  }
}
