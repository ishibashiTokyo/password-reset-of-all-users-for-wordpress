<?php
define('PASSWD_LENGTH', 32);// パスワード長
define('SP_CHAR_MIN', 3);// 特殊文字数の最小値
define('SP_CHARS', '!@#$%^&*()');// 特殊文字一覧

// WordPress用パスワード変更処理
if (file_exists(__DIR__ . '/wp-load.php')) {
    include(__DIR__ . '/wp-load.php');
    $users = get_users(array('fields' => array( 'ID' )));

    foreach($users as $user){
        $password_raw = wordpress_GeneratePassword();

        $user_id = wp_update_user( array( 'ID' => $user->ID, 'user_pass' => $password_raw ) );

        if ( is_wp_error( $user_id ) ) {
            printf('[Error] User ID: %s<br>' . PHP_EOL, $user_id);
            continue;
        }

        $user_info = get_userdata( $user_id );
        printf('User Name: %s<br>' . PHP_EOL, $user_info->user_login);
        printf('Role: %s<br>' . PHP_EOL, implode( ', ', $user_info->roles ));
        printf('User ID: %s<br>' . PHP_EOL, $user_id);
        printf('Password: %s<br>' . PHP_EOL, $password_raw);
    }
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