<?php
define('PASSWD_LENGTH', 32);// パスワード長
define('SP_CHAR_MIN', 3);// 特殊文字数の最小値
define('SP_CHARS', '!@#$%^&*()');// 特殊文字一覧

if (! file_exists(__DIR__ . '/wp-load.php')) {
    die('wp-load.phpが見つかりません。');
}
include( __DIR__ . '/wp-load.php');

// WordPress用パスワード変更処理
if (isset($_POST['mode']) && 'pw_update' === $_POST['mode']) {
    $users = get_users( array( 'fields' => array( 'ID' ) ) );
    $exclusion_ids = array_map(intval, $_POST['exclusion']);

    foreach($users as $user){
        if (in_array(intval($user->ID), $exclusion_ids, true)) {
            continue;
        }
        // パスワード生成
        $password_raw = wordpress_GeneratePassword();
        // パスワードのアップデート実行
        $user_id = wp_update_user( array( 'ID' => $user->ID, 'user_pass' => $password_raw ) );
        // パスワードのアップデート結果の評価
        if ( is_wp_error( $user_id ) ) {
            // エラー時
            $output .= sprintf('[Error] User ID: %s<br>' . PHP_EOL, $user_id);
        } else {
            // 成功時
            $user_info = get_userdata( $user_id );
            $output .= sprintf('ID          : %s' . PHP_EOL, $user_id);
            $output .= sprintf('User Name   : %s' . PHP_EOL, $user_info->user_login);
            $output .= sprintf('Password    : %s' . PHP_EOL, $password_raw);
            $output .= sprintf('Role        : %s' . PHP_EOL, implode( ', ', $user_info->roles ));
        }
    }
    // パスワードの一覧を出力して処理を終える
    echo '<pre>' . $output . '</pre>';
    exit();
}

/**
 * WordPress用パスワード生成
 * 特殊文字が指定回数以上含まれるまで再起処理
 * @return void
 */
function wordpress_GeneratePassword() {
    $sp_char_count = 0;
    $_password = wp_generate_password( $length = 32, $special_chars = true, $extra_special_chars = false );

    foreach (str_split(SP_CHARS) as $char) {
        $sp_char_count += substr_count($_password, $char);
    }

    if ($sp_char_count < SP_CHAR_MIN) {
        wordpress_GeneratePassword();
    }

    return $_password;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress パスワード一括返還</title>
</head>
<body>
パスワードの変更を除外するユーザを選択してください。
<form action="" method="POST">
<table>
<thead>
    <tr>
        <th>除外</th>
        <th>ID</th>
        <th>display_name</th>
        <th>user_login</th>
        <th>user_nicename</th>
        <th>user_email</th>
        <th>roles</th>
        <th>user_registered</th>
    </tr>
</thead>
<tbody>
<?php
$users = get_users();
foreach ($users as $user) {
    printf(
        '<tr><td><input type="checkbox" name="exclusion[]" value="%s"></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
        $user->data->ID,
        $user->data->ID,
        $user->data->display_name,
        $user->data->user_login,
        $user->data->user_nicename,
        $user->data->user_email,
        implode(', ', $user->roles),
        $user->data->user_registered
    );
}
?>
</tbody>
</table>
<input type="hidden" name="mode" value="pw_update">
<input type="submit" value="パスワード一括変更">
</form>
</body>
</html>
