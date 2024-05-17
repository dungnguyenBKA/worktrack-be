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
        'message' => 'Illegal connection IP'
    ],
    'E0002' => [
        'statusCode' => '400',
        'code' => 'E0002',
        'message' => 'Unknown URI'
    ],
    'E0003' => [
        'statusCode' => '400',
        'code' => 'E0003',
        'message' => 'Invalid request parameter'
    ],
    'E0004' => [
        'statusCode' => '400',
        'code' => 'E0004',
        'message' => 'Email or password is incorrect'
    ],
    'E0005' => [
        'statusCode' => '400',
        'code' => 'E0005',
        'message' => 'Input face_id is wrong'
    ],
    'E0006' => [
        'statusCode' => '400',
        'code' => 'E0006',
        'message' => 'Your location is too far'
    ],
    /** Status Code : 401 */
    'E0101' => [
        'statusCode' => '401',
        'code' => 'E0101',
        'message' => 'Illegal access token'
    ],
    'E0102' => [
        'statusCode' => '401',
        'code' => 'E0102',
        'message' => 'Illegal authority'
    ],
    'E0103' => [
        'statusCode' => '401',
        'code' => 'E0103',
        'message' => 'Token is expired'
    ],
    /** Status Code : 503 */
    'E0201' => [
        'statusCode' => '503',
        'code' => 'E0201',
        'message' => 'Failure to link with external system'
    ],
    'E0202' => [
        'statusCode' => '503',
        'code' => 'E0202',
        'message' => 'Maintenance'
    ],
    /** Status Code : 500 */
    'E0301' => [
        'statusCode' => '500',
        'code' => 'E0301',
        'message' => 'Internal Server Error'
    ],

    /** Status Code : 400 */
    'G00050001' => [
        'statusCode' => '400',
        'code' => 'G00050001',
        'message' => 'Please check your email address and password. If you still cannot log in, contact your system administrator.'
    ],
    'G00050002' => [
        'statusCode' => '400',
        'code' => 'G00050002',
        'message' => 'This email address is not yet available. Please use a different email address.'
    ],
    'G00050003' => [
        'statusCode' => '400',
        'code' => 'G00050003',
        'message' => 'This email address is being used by another app. Please use a different email address.'
    ],
    'G00050004' => [
        'statusCode' => '400',
        'code' => 'G00050004',
        'message' => 'This email address is logged in on another device. Are you sure all your devices are logged out?'
    ],
    'G00050005' => [
        'statusCode' => '400',
        'code' => 'G00050005',
        'message' => 'Please scan your face again. If you still cannot log in, contact your system administrator.'
    ],
    'G00050006' => [
        'statusCode' => '400',
        'code' => 'G00050006',
        'message' => 'Request absent time already exists.'
    ],
    'G00050007' => [
        'statusCode' => '400',
        'code' => 'G00050007',
        'message' => 'Request overtime already exists.'
    ],

    /** Status Code : 404 */
    'E0400' => [
        'statusCode' => '404',
        'code' => 'E0400',
        'message' => 'Notification is not found.'
    ],
    'E0401' => [
        'statusCode' => '404',
        'code' => 'E0401',
        'message' => 'Not found.'
    ],

    /** Status Code : 403 */
    'E0403' => [
    'statusCode' => '403',
    'code' => 'E0403',
    'message' => 'Access denied.'
    ]
];
