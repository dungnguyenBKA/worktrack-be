<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllExport implements WithMultipleSheets
{
    private $startDate;
    private $endDate;
    private $userId;
    private $dataInDay;

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
        //
    }

    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new TimeSheetExport($this->startDate, $this->endDate, $this->userId, $this->dataInDay);
        $sheets[] = new OvertimeExport($this->userId, $this->startDate, $this->endDate);
        $sheets[] = new RequestAbsentExport($this->userId, $this->startDate, $this->endDate);

        return $sheets;
    }
}
