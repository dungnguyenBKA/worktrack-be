<?php

return [
    'work_time' => [
        'start' => '08:30:00',
        'start_morning_late' => '08:31',
        'end_morning' => '12:00:00',
        'start_afternoon' => '13:30:00',
        'start_afternoon_late' => '13:31',
        'end' => '18:00:00',
    ],
    'work_month' => [
        'start' => env('START_OF_MONTH', now()->startOfMonth()),
        'end' => env('END_OF_MONTH', now()->endOfMonth()),
    ],
    'report_config' => [
        'type' => [
            'scan_late' => 1,
            'report_mail' => 2,
        ],
        'period' => [
            'day' => 1,
            'week' => 2,
            'month' => 3,
            'string' => [
                1 => 'Ngày',
                2 => 'Tuần',
            ],
        ],
        'week' => [
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7',
            7 => 'Chủ nhật',
        ]
    ],
    'message' => [
        'create' => [
            'success' => 'Tạo mới thành công',
            'error' => 'Tạo mới thất bại'
        ],
        'edit' => [
            'success' => 'Chỉnh sửa thành công',
            'error' => 'Chỉnh sửa thất bạt'
        ],
        'delete' => [
            'success' => 'Xóa thành công',
            'error' => 'Xóa thất bạt'
        ],
        'msg_confirm' => 'Bạn có chắc muốn xóa chứ?',
        'no_data' => 'Không có dữ liệu để hiển thị',
    ],
    'user' => [
        'role' => [
            'user' => 1,
            'admin' => 2,
        ],
        'status' => [
            'working' => 2,
            'resign' => 1,
        ],
        'position' => [
            'Developer' => 1,
            'Tester' => 2,
            'BO' => 3,
            'PM' => 4,
        ]
    ],
    'overtime' => [
        'waiting' => 1,
        'approve' => 2,
        'reject' => 3,
    ],
    'request_absent' => [
        'waiting' => 1,
        'approve' => 2,
        'reject' => 3,
    ],
    'status_map' => [
        1 => [
            'icon' => 'text-primary fas fa-spinner',
            'text' => 'Chờ duyệt'
        ],
        2 => [
            'icon' => 'text-success fas fa-check-circle',
            'text' => 'Phê Duyệt'
        ],
        3 => [
            'icon' => 'text-danger fas fa-times-circle',
            'text' => 'Từ chối'
        ],
    ]
];
