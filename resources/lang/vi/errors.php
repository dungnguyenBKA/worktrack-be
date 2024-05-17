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
        'message' => 'IP không hợp lệ'
    ],
    'E0002' => [
        'statusCode' => '400',
        'code' => 'E0002',
        'message' => 'URI không xác định'
    ],
    'E0003' => [
        'statusCode' => '400',
        'code' => 'E0003',
        'message' => 'Tham số  yêu cầu không hợp lệ'
    ],
    'E0004' => [
        'statusCode' => '400',
        'code' => 'E0004',
        'message' => 'Email hoặc mật khẩu không hợp lệ'
    ],
    'E0005' => [
        'statusCode' => '400',
        'code' => 'E0005',
        'message' => 'face_id của bạn không hợp lệ'
    ],
    'E0006' => [
        'statusCode' => '400',
        'code' => 'E0006',
        'message' => 'Vị trí của bạn quá xa'
    ],
    /** Status Code : 401 */
    'E0101' => [
        'statusCode' => '401',
        'code' => 'E0101',
        'message' => 'Token truy cập không hợp lệ'
    ],
    'E0102' => [
        'statusCode' => '401',
        'code' => 'E0102',
        'message' => 'Không đủ quyền truy cập'
    ],
    'E0103' => [
        'statusCode' => '401',
        'code' => 'E0103',
        'message' => 'Token đã hết hạn'
    ],
    /** Status Code : 503 */
    'E0201' => [
        'statusCode' => '503',
        'code' => 'E0201',
        'message' => 'Không kết nối được với hệ thống ngoài'
    ],
    'E0202' => [
        'statusCode' => '503',
        'code' => 'E0202',
        'message' => 'Bảo trì'
    ],
    /** Status Code : 500 */
    'E0301' => [
        'statusCode' => '500',
        'code' => 'E0301',
        'message' => 'Lỗi máy chủ'
    ],

    /** Status Code : 400 */
    'G00050001' => [
        'statusCode' => '400',
        'code' => 'G00050001',
        'message' => 'Vui lòng kiểm tra địa chỉ email và mật khẩu của bạn. Nếu bạn vẫn không thể đăng nhập hãy liên hệ với quản trị hệ thống.'
    ],
    'G00050002' => [
        'statusCode' => '400',
        'code' => 'G00050002',
        'message' => 'Địa chỉ email này chưa có sẵn. Xin vui lòng sử dụng một địa chỉ email khác.'
    ],
    'G00050003' => [
        'statusCode' => '400',
        'code' => 'G00050003',
        'message' => 'Địa chỉ email này đang được sử dụng bởi ứng dụng khác. Xin vui lòng sử dụng địa chỉ email khác.'
    ],
    'G00050004' => [
        'statusCode' => '400',
        'code' => 'G00050004',
        'message' => 'Địa chỉ email này được đăng nhập trên một thiết bị khác. Bạn đã đăng xuất trên tất cả các thiết bị chưa?'
    ],
    'G00050005' => [
        'statusCode' => '400',
        'code' => 'G00050005',
        'message' => 'Vui lòng quét lại khuôn mặt của bạn. Nếu bạn vẫn không thể đăng nhập hãy liên hệ với quản trị viên hệ thống.'
    ],
    'G00050006' => [
        'statusCode' => '400',
        'code' => 'G00050006',
        'message' => 'Thời gian đăng ký nghỉ đã tồn tại.'
    ],
    'G00050007' => [
        'statusCode' => '400',
        'code' => 'G00050007',
        'message' => 'Thời gian đăng ký làm thêm đã tồn tại.'
    ],

    /** Status Code : 404 */
    'E0400' => [
        'statusCode' => '404',
        'code' => 'E0400',
        'message' => 'Không tìm thấy thông báo.'
    ],
    'E0401' => [
        'statusCode' => '404',
        'code' => 'E0401',
        'message' => 'Không tìm thấy.'
    ],

    /** Status Code : 403 */
    'E0403' => [
        'statusCode' => '403',
        'code' => 'E0403',
        'message' => 'Access denied.'
    ]
];
