<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Overtime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\Timesheet;

class OvertimeExport implements FromCollection, WithHeadings, WithEvents, WithMapping, ShouldAutoSize, WithTitle
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
            $overtimes = $this->queryGetRequestOvertimeExport(false);
        } else {
            $overtimes = $this->queryGetRequestOvertimeExport();
        }

        $ot = [
            'ot1' => 0,
            'ot2' => 0,
            'ot3' => 0,
            'ot4' => 0,
        ];

        foreach ($overtimes as $key => $value) {
            $ot = (new Timesheet)->overtimeRequestBase($value->from_time, $value->to_time);
            $value->ot1 = $ot['ot1'];
            $value->ot2 = $ot['ot2'];
            $value->ot3 = $ot['ot3'];
            $value->ot4 = $ot['ot4'];

        }

        $data = $this->buildRows($overtimes);
        return collect($data ?? []);
    }

    function queryGetRequestOvertimeExport($isAll = true)
    {
        $query = Overtime::select('overtime.*', 'users.staff_id', 'users.first_name', 'users.last_name', 'users.email')
            ->join('users', 'overtime.created_by', '=', 'users.id')
            ->join('overtime_user', 'overtime.id', '=', 'overtime_user.overtime_id')
            ->whereNull('users.deleted_at')
            ->with('overtimeUsers', function ($q) {
                $q->join('users', 'users.id', '=', 'overtime_user.user_id')
                    ->whereNull('users.deleted_at')
                    ->select('overtime_user.*', 'users.staff_id', 'users.first_name', 'users.last_name', 'users.email');
            });
        if(isset($this->startDate) && isset($this->endDate)) {
            $query->where(DB::raw('DATE(from_time)'), '>=', $this->startDate ?? DATE(NOW()))
                    ->where(DB::raw('DATE(from_time)'), '<', $this->endDate ?? DATE(NOW()));
        }
        if(!$isAll) {
            $query->where(function ($query) {
                $query->orWhere('overtime.created_by', $this->userId);
                $query->orWhere('overtime_user.user_id', $this->userId);
            });
        }

        $query->groupBy('overtime.id');
        $query->orderBy('from_time', 'DESC')->orderBy('created_at', 'DESC');

        return  $query->get();
    }

    function buildRows($dataExport)
    {
        $index = 0;
        $data = [];
        $dataNew = [];
        if(count($dataExport) > 0) {
            foreach ($dataExport as $key => $value) {
                $data[$index]['stt'] = $index + 1;
                $data[$index]['ma_nv_dk'] = $value->staff_id;
                $data[$index]['create_by'] = get_fullname($value);
                $data[$index]['name'] = get_fullname($value);
                $data[$index]['project'] = $value->project;
                $data[$index]['from_time'] = get_datetime($value->from_time, 'Y/m/d H:i');
                $data[$index]['to_time'] = get_datetime($value->to_time, 'Y/m/d H:i');
                $data[$index]['reason'] = $value->reason;
                $data[$index]['status'] = $value->status == 1 ? __('layouts.label_waiting') : ( $value->status == 2 ? __('layouts.label_approve') : __('layouts.reject') );
                $data[$index]['list'] = $value->overtimeUsers;
                $data[$index]['ot1'] = $value->ot1;
                $data[$index]['ot2'] = $value->ot2;
                $data[$index]['ot3'] = $value->ot3;
                $data[$index]['ot4'] = $value->ot4;
                $index++;
            }

            foreach ($data as $key => $valExport) {
                if(count($valExport['list']) > 0) {
                    $i = 0;
                    foreach ($valExport['list'] as $keyUser => $user) {
                        // if($i > 0) {
                        //     $valExport = array_map(function($v){
                        //         return "";
                        //     }, $valExport);
                        // }
                        $valExport['ma_nv'] = $user->staff_id;
                        $valExport['name'] = get_fullname($user);

                        unset($valExport['list']);
                        array_push($dataNew, $valExport);
                        $i++;
                    }
                } else {
                    unset($valExport['list']);
                    array_push($dataNew, $valExport);
                }
            }
        }
        return $dataNew;
    }

    public function headings(): array
    {
        return [
            ["Bảng làm thêm"],
            ["Ngày tạo: " . Date('Y-m-d H:i')],
            [""],
            ["", "", "", "", "", "", "", "", "", "", "(*) Thời gian làm thêm (OT) được tính toán dựa trên thời gian đăng ký."],
            ["STT", "Mã NVĐK", "Người đăng ký", "Mã NV", "Tên NV làm thêm", "Dự án", "Từ ngày", "Đến ngày", "Lý do", "Trạng thái", "OT1", "OT2", "OT3", "OT4"]
    ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:N1');
                $event->sheet->getDelegate()->mergeCells('A2:N2');
                $event->sheet->getDelegate()->mergeCells('A3:N3');
                $event->sheet->getDelegate()->mergeCells('K4:S4');
                $event->sheet->getDelegate()->getStyle('A1:N2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1:N1')->getFont()->setBold(true)->setSize(16);
                $event->sheet->getDelegate()->getStyle('A5:N5')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(12);
                $event->sheet->getDelegate()->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('D:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('I:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);


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


                $event->sheet->getDelegate()->getStyle('A5:N5')->applyFromArray($styleArray);
            },
        ];
    }

    public function map($row): array{
        $fields = [
            $row['stt'] ?? '',
            $row['ma_nv_dk'] ?? '',
            $row['create_by'] ?? '',
            $row['ma_nv'] ?? '',
            $row['name'] ?? '',
            $row['project'] ?? '',
            $row['from_time'] ?? '',
            $row['to_time'] ?? '',
            $row['reason'] ?? '',
            $row['status'] ?? '',
            $row['ot1'] ?? '',
            $row['ot2'] ?? '',
            $row['ot3'] ?? '',
            $row['ot4'] ?? '',
        ];

        return $fields;
    }

    public function title(): string
    {
        return "OverTime";
    }
}
