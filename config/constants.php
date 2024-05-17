<?php

return [
    /** User */
    'gender' => [
        1 => 'Male',
        0 => 'Female',
    ],
    'platform' => [
        'android' => 1,
        'ios' => 2
    ],
    'role' => [
        'user' => 1,
        'admin' => 2
    ],

    /** Code setting */
    'password_lenght' => 16,
    'forgot_password_token_expired' => 60, // minutes
    'format_datetime_show' => 'Y-m-d H:i',
    'format_date_show' => 'Y-m-d',

    /** Upload file path */
    'upload_file_path' => [
        'user_info_icon' => '/user/icons/',
    ],
    'status' => [
        1 => 'Có hiệu lực',
        2 => 'Vô hiệu lực',
    ],
    'postion_type' => [
        1 => 'Văn phòng',
        2 => 'Từ xa',
    ],
    'authorization_key' => 'AAAAEvtr2l8:APA91bFYqtYlL-LYBOrTReq4u3ln9O7KzU9qUuK3Qj9lddpIK87r9mcRd1bnRSEMaryMhzJyFmzVgp89Exo4XR7VYuk2c2wMaQ33_OvgJOLgXy20JAJDIp5SRsRlkO6aebePihRKeJWc'
];
