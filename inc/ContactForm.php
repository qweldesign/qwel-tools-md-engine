<?php
/**
 * ContactForm.php
 * © 2026 QWEL.DESIGN (https://qwel.design)
 * Released under the MIT License.
 * See LICENSE file for details.
 *
 * お問い合わせフォームの項目の検証と, メールの送信
 * 項目は templates/config.php の SITE_CONTACT_FIELDS, 宛先と SMTP の秘密の値は inc/.env から読む
 */

use PHPMailer\PHPMailer\PHPMailer;

class ContactForm {
  // ボット対策の見えない入力欄の name 属性 (人は入力しないので, 値があれば破棄する)
  public const HONEYPOT = 'website_url';

  // 1 項目あたりの最大文字数 (textarea とそれ以外)
  private const MAX_LENGTH_TEXTAREA = 5000;
  private const MAX_LENGTH = 200;

  public function __construct(private array $fields) {}

  // 返信先 (最初の email 項目) の name 属性
  public function emailField(): ?string {
    foreach ($this->fields as $name => $field) {
      if (($field['type'] ?? 'text') === 'email') return $name;
    }
    return null;
  }

  // ボットの送信か (見えない入力欄に値がある)
  public function isBot(array $data): bool {
    $value = $data[self::HONEYPOT] ?? '';
    return !is_string($value) || trim($value) !== '';
  }

  /**
   * 送信された値を検証し, 定義済みの項目だけを取り出す
   * 戻り値: ['values' => [name => 値], 'errors' => [name => メッセージ]]
   */
  public function validate(array $data): array {
    $values = [];
    $errors = [];

    foreach ($this->fields as $name => $field) {
      $type = $field['type'] ?? 'text';
      $label = $field['label'] ?? $name;
      $raw = $data[$name] ?? ($type === 'checkbox' ? [] : '');

      if ($type === 'checkbox') {
        [$values[$name], $error] = $this->checkChoices($raw, $field, $label);
      } elseif (is_string($raw)) {
        [$values[$name], $error] = $this->checkText($raw, $type, $field, $label);
      } else {
        $error = "{$label}の値が正しくありません";
      }
      if ($error !== null) $errors[$name] = $error;
    }

    return ['values' => $values, 'errors' => $errors];
  }

  // チェックボックス (複数の値)
  private function checkChoices(mixed $raw, array $field, string $label): array {
    if (!is_array($raw) && !is_string($raw)) return [[], "{$label}の値が正しくありません"];

    $items = is_array($raw) ? $raw : [$raw];
    if (array_filter($items, fn(mixed $item): bool => !is_string($item))) return [[], "{$label}の値が正しくありません"];

    $values = array_values(array_filter($items, fn(string $item): bool => $item !== ''));
    if (!empty($field['required']) && !$values) return [$values, "{$label}を選択してください"];
    if (array_diff($values, $field['options'] ?? [])) return [$values, "{$label}の選択肢が正しくありません"];
    return [$values, null];
  }

  // チェックボックス以外 (1 つの文字列)
  private function checkText(string $raw, string $type, array $field, string $label): array {
    $value = trim(str_replace(["\r\n", "\r"], "\n", $raw));
    // 1 行の項目は改行を許さない (メールのヘッダーに値を入れても壊れないように)
    if ($type !== 'textarea') $value = preg_replace('/[\n\t]+/', ' ', $value);

    return [$value, $this->textError($value, $type, $field, $label)];
  }

  private function textError(string $value, string $type, array $field, string $label): ?string {
    if ($value === '') return !empty($field['required']) ? "{$label}を入力してください" : null;
    if (mb_strlen($value) > ($type === 'textarea' ? self::MAX_LENGTH_TEXTAREA : self::MAX_LENGTH)) return "{$label}が長すぎます";
    if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) return "{$label}の形式が正しくありません";
    if ($type === 'url' && !preg_match('#^https?://#', $value)) return "{$label}の形式が正しくありません";
    if ($type === 'date' && !$this->isDate($value)) return "{$label}の日付が正しくありません";
    if (in_array($type, ['select', 'radio'], true) && !in_array($value, $field['options'] ?? [], true)) return "{$label}の選択肢が正しくありません";
    return null;
  }

  // YYYY-MM-DD の実在する日付か (input type="date" の値)
  private function isDate(string $value): bool {
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
  }

  // メール本文 (項目名と値)
  public function mailBody(array $values): string {
    $lines = [];
    foreach ($this->fields as $name => $field) {
      $value = $values[$name] ?? '';
      if (is_array($value)) $value = implode(', ', $value);
      $label = $field['label'] ?? $name;
      // 複数行の値は, 項目名の次の行から書く
      $lines[] = str_contains($value, "\n") ? "【{$label}】\n{$value}" : "【{$label}】{$value}";
    }
    return implode("\n\n", $lines) . "\n";
  }

  /**
   * 管理者宛と自動返信の 2 通を送る
   * 管理者宛に失敗したら例外を投げる. 自動返信の失敗はログに残すだけにする
   * $env: inc/.env の値 (ADMIN_EMAIL, SMTP_HOST, SMTP_USER, SMTP_PASS, SMTP_PORT, SMTP_SECURE)
   */
  public function send(array $values, array $env, string $siteName, string $replyFooter = ''): void {
    $adminEmail = envRequired($env, 'ADMIN_EMAIL');
    $smtp = [
      'host'   => envRequired($env, 'SMTP_HOST'),
      'user'   => envRequired($env, 'SMTP_USER'),
      'pass'   => envRequired($env, 'SMTP_PASS'),
      'port'   => (int)($env['SMTP_PORT'] ?? 587),
      'secure' => strtolower($env['SMTP_SECURE'] ?? 'tls'),
    ];
    $emailField = $this->emailField();
    $userEmail = $emailField ? ($values[$emailField] ?? '') : '';
    $body = "以下の内容で受け付けました。\n\n" . $this->mailBody($values);

    $admin = $this->createMailer($smtp);
    $admin->setFrom($adminEmail, $siteName);
    $admin->addAddress($adminEmail);
    if ($userEmail !== '') $admin->addReplyTo($userEmail);
    $admin->Subject = "{$siteName} からのお問い合わせ";
    $admin->Body = $body;
    $admin->send();

    // お問い合わせ自体は受け付けているので, 自動返信に失敗してもエラーにしない
    if ($userEmail === '') return;
    try {
      $reply = $this->createMailer($smtp);
      $reply->setFrom($adminEmail, $siteName);
      $reply->addAddress($userEmail);
      $reply->Subject = "{$siteName} へのお問い合わせありがとうございます";
      $reply->Body = $body . $replyFooter;
      $reply->send();
    } catch (Throwable $error) {
      error_log('お問い合わせフォームの自動返信に失敗しました: ' . $error->getMessage() . ' 宛先: ' . $userEmail);
    }
  }

  // SMTP の設定を済ませた PHPMailer を作る
  private function createMailer(array $smtp): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtp['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['user'];
    $mail->Password = $smtp['pass'];
    if ($smtp['secure'] === 'none') {
      // 暗号化なし (手元のテスト用メールサーバー向け)
      $mail->SMTPSecure = '';
      $mail->SMTPAutoTLS = false;
    } else {
      $mail->SMTPSecure = $smtp['secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    }
    $mail->Port = $smtp['port'];
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    return $mail;
  }
}
