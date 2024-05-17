<?php

namespace App\Exports;

use App\Models\User;
use App\Models\RequestAbsent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RequestAbsentExport implements FromCollection, WithHeadings, WithEvents, WithMapping, ShouldAutoSize, WithTitle
{
    public function __construct($userId, $startDate = null, $endDate = null) {
        $this->userId = $userId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if(isset($this->userId)) {
            $requestAbsents = $this->queryGetRequestAbsentExport(false);
        } else {
            $requestAbsents = $this->queryGetRequestAbsentExport();
        }
        $data = $this->buildRows($requestAbsents);
        return collect($data ?? []);
    }

    function queryGetRequestAbsentExport($isAll = true)
    {
        $query = RequestAbsent::select('request_absent.*', 'email', 'staff_id', 'first_name', 'last_name')
                ->join('users', 'users.id', '=', 'request_absent.user_id')
                ->whereNull('users.deleted_at');
        if(isset($this->startDate) && isset($this->endDate)) {
            $query->where(DB::raw('DATE(from_time)'), '>=', $this->startDate ?? DATE(NOW()))
                    ->where(DB::raw('DATE(from_time)'), '<', $this->endDate ?? DATE(NOW()));
        }
        if(!$isAll) {
            $query->where('user_id', $this->userId);
        }

        return $query->orderBy('from_time', 'DESC')->orderBy('created_at', 'DESC')->get();
    }

    function buildRows($dataExport)
    {
        $index = 0;
        $data = [];
        if(count($dataExport) > 0) {
            foreach ($dataExport as $key => $value) {
                $data[$index]['stt'] = $index + 1;
                $data[$index]['ma_nv'] = $value->staff_id;
                $data[$index]['name'] = get_fullname($value);
                $data[$index]['from_time'] = get_datetime($value->from_time, 'Y/m/d H:i');
                $data[$index]['to_time'] = get_datetime($value->to_time, 'Y/m/d H:i');
                $data[$index]['reason'] = $value->reason;
                $data[$index]['use_leave_hour'] = $value->use_leave_hour == 1 ? 'Có' : 'Không';
                $data[$index]['status'] = $value->status == 1 ? __('layouts.label_waiting') : ( $value->status == 2 ? __('layouts.label_approve') : __('layouts.reject') );
                $index++;
            }
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            ["Bảng nghỉ phép"],
            ["Ngày tạo: " . Date('Y-m-d H:i')],
            [""],
            ["STT", "Mã NV", "Tên", "Từ ngày", "Đến ngày", "Lý do", "Dùng phép", "Trạng thái"]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:H1');
                $event->sheet->getDelegate()->mergeCells('A2:H2');
                $event->sheet->getDelegate()->mergeCells('A3:H3');
                $event->sheet->getDelegate()->getStyle('A1:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1:H1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getDelegate()->getStyle('A4:H4')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(12);
                $event->sheet->getDelegate()->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '00000000'],
                        ],
                    ],
                    'background' => [
                        'color'=> ['argb' => 'FFFFFFFF'],
                    ]
                ];


                $event->sheet->getDelegate()->getStyle('A4:H4')->applyFromArray($styleArray);
            },
        ];
    }

    public function map($row): array{
        $fields = [
            $row['stt'] ?? '',
            $row['ma_nv'] ?? '',
            $row['name'] ?? '',
            $row['from_time'] ?? '',
            $row['to_time'] ?? '',
            $row['reason'] ?? '',
            $row['use_leave_hour'] ?? '',
            $row['status'] ?? ''
        ];

        return $fields;
    }

    public function title(): string
    {
        return "Request Absent";
    }
}
