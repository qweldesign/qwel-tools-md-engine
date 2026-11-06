<?php
/**
 * お問い合わせフォーム (入力画面: /contact/)
 * 項目は templates/config.php の SITE_CONTACT_FIELDS で設定する
 */

require_once dirname(__DIR__) . '/templates/contact.php';

renderContact('form');
