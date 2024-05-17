<?php

return [
    'SUCCESS' => '200',
    'BAD_REQUEST' => '400',
    'UNAUTHORIZED' => '401',
    'PAYMENT_REQUIRED' => '402',
    'ACCESS_DENIED' => '403',
    'NOT_FOUND' => '404',
    'METHOD_NOT_ALLOWED' => '405',
    'CONFLICT' => '409',
    'PAYLOAD_TOO_LARGE' => '413',
    'INTERNAL_SERVER_ERROR' => '500',
    'SERVICE_UNAVAILABLE' => '503',

    /** Status Code : 400 */
    'E0001' => [
        'statusCode' => '400',
        'code' => 'E0001',
        'message' => '接続IPが不正'
    ],
    'E0002' => [
        'statusCode' => '400',
        'code' => 'E0002',
        'message' => '不明なURI'
    ],
    'E0003' => [
        'statusCode' => '400',
        'code' => 'E0003',
        'message' => 'リクエストパラメータが不正'
    ],
    'E0004' => [
        'statusCode' => '400',
        'code' => 'E0004',
        'message' => 'メールかパスワードが不正'
    ],
    'E0005' => [
        'statusCode' => '400',
        'code' => 'E0005',
        'message' => 'パラメータface_idが不正'
    ],
    /** Status Code : 401 */
    'E0101' => [
        'statusCode' => '401',
        'code' => 'E0101',
        'message' => 'アクセスキーが不正'
    ],
    'E0102' => [
        'statusCode' => '401',
        'code' => 'E0102',
        'message' => '権限が不正'
    ],
    'E0103' => [
        'statusCode' => '401',
        'code' => 'E0103',
        'message' => 'トークンの有効期限が切れています'
    ],
    /** Status Code : 503 */
    'E0201' => [
        'statusCode' => '503',
        'code' => 'E0201',
        'message' => '外部システムとの連携に失敗'
    ],
    'E0202' => [
        'statusCode' => '503',
        'code' => 'E0202',
        'message' => 'メンテナンス中'
    ],
    /** Status Code : 500 */
    'E0301' => [
        'statusCode' => '500',
        'code' => 'E0301',
        'message' => '内部サーバーエラー'
    ],

    /** Status Code : 400 */
    'G00050001' => [
        'statusCode' => '400',
        'code' => 'G00050001',
        'message' => 'メールアドレスとパスワードを確認してください。それでもログインできない場合は、システム管理者にお問い合わせください。'
    ],
    'G00050002' => [
        'statusCode' => '400',
        'code' => 'G00050002',
        'message' => 'このメールアドレスは、まだ使用できない状態です。別のメールアドレスを使用してください。'
    ],
    'G00050003' => [
        'statusCode' => '400',
        'code' => 'G00050003',
        'message' => 'このメールアドレスは、別のアプリで使用中です。別のメールアドレスを使用してください。'
    ],
    'G00050004' => [
        'statusCode' => '400',
        'code' => 'G00050004',
        'message' => 'このメールアドレスは、別のデバイスでログイン中です。全てのデバイスがログアウトしてよろしいでしょうか。'
    ],
    'G00050005' => [
        'statusCode' => '400',
        'code' => 'G00050005',
        'message' => '顔認証を確認してください。それでもログインできない場合は、システム管理者にお問い合わせください。'
    ],
    'G00050006' => [
        'statusCode' => '400',
        'code' => 'G00050006',
        'message' => '休暇申請の期間はすでに存在しています。'
    ],
    'G00050007' => [
        'statusCode' => '400',
        'code' => 'G00050007',
        'message' => '残業申請の期間はすでに存在しています。'
    ],
];
