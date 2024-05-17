<?php

namespace App\Exports;

use App\Models\Timesheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\PaidLeave;
use Carbon\Carbon;


class TimeSheetExport implements FromCollection, WithHeadings, WithEvents, WithMapping, ShouldAutoSize, WithTitle
{
    //private Timesheet $timesheets;
    public function __construct($startDate, $endDate, $userId, $dataInDay) {
        $this->userId = $userId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->dataInDay = $dataInDay;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->timesheets['timesheets'] ?? []);
    }

    public function headings(): array
    {
        $this->timesheets = (new Timesheet)->getTimesheetCalendar($this->startDate, $this->endDate, $this->userId, $this->dataInDay);

        $paidLeaveModel = new PaidLeave();
        $paidLeave = $paidLeaveModel->getOnePaidLeaveWithCondition([
            'month_year' => Carbon::make($this->startDate)->format('Y-m'),
            'user_id' => $this->userId
        ]);
        $total = $this->timesheets['total'];
        $dayLeft = $this->userId && $paidLeave ? (float)$paidLeave->day_left : 0;
        $dayUseInMonth = $this->userId && $paidLeave ? $paidLeave->day_use_in_month + $paidLeave->leave_hour_in_work_hour - $total['timeInFuture'] : 0;
        $dayAddInMonth = $this->userId && $paidLeave ? (float)$paidLeave->day_add_in_month : 0;
        $salaryDeductionHour = $this->userId && $paidLeave && $paidLeave->salaryDeductionDay($total['timeInFuture']) ? (float)$paidLeave->salaryDeductionDay($total['timeInFuture']) : 0;
        $dayEdit = $this->userId && $paidLeave && $paidLeave->day_edit ? (float)$paidLeave->day_edit : 0;
        $leaveDayLeft = $this->userId && $paidLeave ? $paidLeave->leaveDaysLeft($total['timeInFuture']) : 0;

        $header = $this->dataInDay == '1' ? ["STT", "Ngày", "Mã NV", "Tên", "Họ", "Giờ Chấm Công", "Địa Điểm"] :
            ["STT", "Ngày", "Mã NV", "Họ", "Tên", "Giờ Vào", "Giờ Ra", "Giờ Làm", "Giờ Thiếu", "OT1", "OT2", "OT3", "OT4", "Chú thích", "Địa Điểm Vào", "Địa Điểm Ra"];
        $description = $this->dataInDay == '1' ? [""] : ["", "", "", "", "", "", "", "", "", "(*) Thời gian làm thêm (OT) được tính toán dựa trên chấm công thực tế."];
        return [
                ["Bảng Chấm Công"],
                ["Ngày tạo: " . Date('Y-m-d H:i')],
                [""],
                ["Giờ nghỉ phép"],
                ["", "Giờ nghỉ phép còn lại (đến tháng trước):", "", "", "", "","$dayLeft"],
                ["", "Giờ phép cộng thêm tháng này:", "", "", "", "","$dayAddInMonth"],
                ["", "Số giờ nghỉ tháng này:", "", "", "", "","$dayUseInMonth"],
                ["", "Số giờ nghỉ trừ lương tháng này:", "", "", "", "","$salaryDeductionHour"],
                ["", "Điều chỉnh giờ nghỉ phép:", "", "", "", "","$dayEdit"],
                ["", "Giờ nghỉ phép còn lại:", "", "", "", "","$leaveDayLeft"],
                [""],
                $description,
                $header,
                [""],
            ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
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

                $isDataInDay = $this->dataInDay == '1';

                $event->sheet->getDelegate()->mergeCells($isDataInDay ? 'A1:G1' : 'A1:P1');
                $event->sheet->getDelegate()->mergeCells($isDataInDay ? 'A2:G2' : 'A2:P2');
                $event->sheet->getDelegate()->mergeCells($isDataInDay ? 'A3:G3' : 'A3:P3');
                $event->sheet->getDelegate()->getStyle($isDataInDay ? 'A1:G2' : 'A1:P2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getStyle($isDataInDay ? 'A1:G1' : 'A1:P1')->getFont()->setBold(true)->setSize(16);


                $event->sheet->getDelegate()->mergeCells('A4:G4');
                $event->sheet->getDelegate()->getStyle('A4:G4')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('A4:G4')->applyFromArray($styleArray);

                $event->sheet->getDelegate()->mergeCells('B5:F5');
                $event->sheet->getDelegate()->mergeCells('B6:F6');
                $event->sheet->getDelegate()->mergeCells('B7:F7');
                $event->sheet->getDelegate()->mergeCells('B8:F8');
                $event->sheet->getDelegate()->mergeCells('B9:F9');
                $event->sheet->getDelegate()->mergeCells('B10:F10');
                $event->sheet->getDelegate()->getStyle('A5:G10')->applyFromArray($styleArray);

                if (!$isDataInDay){
                    $event->sheet->getDelegate()->mergeCells('J12:P12');
                }

                $event->sheet->getDelegate()->getStyle($isDataInDay ? 'A13:G13' : 'A13:P13')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(12);

                $event->sheet->getDelegate()->getStyle($isDataInDay ? 'A13:G13' : 'A13:P13')->applyFromArray($styleArray);

                $event->sheet->getDelegate()->getStyle('N')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

             },
        ];
    }

    public function map($row): array{
        if (!isset($row['stt']) && $this->dataInDay != '1') {
            $fields = $row;
        }else {
            $fields = [
                $row['stt'] ?? '',
                $row['date'] ?? '',
                $row['staff_id'] ?? '',
                $row['first_name'] ?? '',
                $row['last_name'] ?? ''
            ];
            $values= [
                $row['checkin'] ?? '',
                $row['checkout'] ?? '',
                $row['wh'] ?? '',
                $row['mh'] ?? '',
                $row['ot1'] ?? '',
                $row['ot2'] ?? '',
                $row['ot3'] ?? '',
                $row['ot4'] ?? '',
                $row['comment'] ?? ''
            ];
            if($this->dataInDay != '1') {
                array_push($fields, ...$values);
                if(isset($row['location']) && !empty($row['location']) ) {
                    if(count($row['location']) == 1) {
                        array_push($fields, $row['location'][0]['location_name'] ?? '');
                    } else if(count($row['location']) >= 2) {
                        $lastCheckinLoc = count($row['location'])-1;
                        array_push($fields, $row['location'][0]['location_name'] ?? '', $row['location'][$lastCheckinLoc]['location_name'] ?? '');
                    }
                }
            } else {
                array_push($fields, ...[$row['checktime'] ?? '', $row['location'] ?? '']);
            }
        }

        return $fields;
    }

    public function title(): string
    {
        return 'Timesheet';
    }

    public function prepareRows($rows) {
        $rows[] = [
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            $this->timesheets['total']['wh'],
            $this->timesheets['total']['mh'],
            $this->timesheets['total']['ot1'],
            $this->timesheets['total']['ot2'],
            $this->timesheets['total']['ot3'],
            $this->timesheets['total']['ot4'],
        ];

        return $rows;
    }
}
