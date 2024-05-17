<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Http\Remotes\CrmRemote;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class UserImport implements ToCollection
{
    private $errLimit = false;
    private $errRow = false;
    private $errDuplicate = false;
    private $errMessageRows = '';
    private $errMessageDuplicate = '';

    public function __construct($type) {
        $this->type = $type;
    }

    public function collection(Collection $rows)
    {
        try {
            if(hasSubdomain()) {
                $userCount = User::count();
                $crmRemote = new CrmRemote();
                $numUser = $userCount > 0 ? $userCount + ( count($rows) - 1 ) : ( count($rows) - 1 );
                $company = $crmRemote->getDetailCompany();
                $isAllowImport = $numUser <= $company['data']['limit_users'];
                $isHaveCompany = isset($company['data']['limit_users']) && !empty($company['data']['limit_users']);
                $overwirte = $this->type === '1';
                if($isAllowImport && $isHaveCompany) {
                    $arrLineErr = [];
                    $duplicateStaff = $this->getDuplicates($rows, 0);
                    $duplicateEmail = $this->getDuplicates($rows, 3);
                    if(!$duplicateStaff && !$duplicateEmail) {
                        foreach ($rows as $key => $row) {
                            if($key > 0) {
                                try {
                                    $birth = Carbon::parse($row[5])->format('Y-m-d');
                                    $dateStart = Carbon::parse($row[7])->format('Y-m-d');
                                    $data = [
                                        'staff_id' => $row[0],
                                        'first_name' => $row[1],
                                        'last_name' => $row[2],
                                        'email' => $row[3],
                                        'phone_number' => $row[4],
                                        'birth' => $birth,
                                        'address' => $row[6],
                                        'date_start_work' => $dateStart,
                                        'position_id' => 1,
                                        'role' => 1,
                                        'status' => $row[8],
                                        'password' => Hash::make($row[9]),
                                        'created_by' => $row[10]
                                    ];
                                    $isValidEmail = isset($row[3]) && $this->emailValidation($row[3]);
                                    $isValidInput = isset($row[0]) && isset($row[3]) && (isset($row[8]) && in_array($row[8], [1, 2])) && isset($row[9]) && isset($row[10]);
                                    $userMaster = $crmRemote->getUserMasterByEmail($row[3]);
                                    $userByStaff = User::where('staff_id', $row[0])->first();
                                    $isValidData = $isValidInput && $isValidEmail;
                                    $isCurrentCompany = count($userMaster) > 0 && isset($userMaster['data']) && $company['data']['id'] === $userMaster['data']['company_id'];
                                    $isUpdateWithEmail = $isCurrentCompany && $overwirte && $isValidData;
                                    $isUpdateWithStaff = $overwirte && isset($userByStaff) && count($userMaster) <= 0 && $isValidData;
                                    // check upadte with email
                                    if($isUpdateWithEmail) {
                                        $userByEmail = User::where('email', $userMaster['data']['email'])->first();
                                        $userByEmail->IS_UPDATE_MASTER = false;
                                        $userByEmail->update($data);
                                    }
                                    // check update with staff id
                                    if($isUpdateWithStaff) {
                                        $userByStaff->update($data);
                                    }
                                    if(!($isCurrentCompany || count($userMaster) <= 0) || !$isValidData || ((isset($userByStaff) || count($userMaster) > 0) && !$overwirte) ) {
                                        $arrLineErr[] = $key + 1;
                                    }
                                    if(!isset($userByStaff) && count($userMaster) <= 0 && $isValidData) {
                                        User::create($data);
                                    }
                                } catch (\Throwable $th) {
                                    $arrLineErr[] = $key + 1;
                                }
                            }
                        }
                        if(count($arrLineErr) > 0) {
                            $this->errMessageRows = __('messages.upload_error'). join(",", $arrLineErr);
                            $this->errRow = count($arrLineErr) > 0;
                        }
                    } else {
                        $this->errDuplicate = true;
                        $this->errMessageDuplicate = __('messages.upload_error_duplicate');
                    }
                } else {
                    $this->errLimit = true;
                }
            }
        } catch(\Exception $exception) {
            Log::error('Impport User Error: '.$exception->getMessage());
        }
    }

    function getDuplicates ($rows, $key) {
        try {
            $groupByKey = $rows->groupBy([$key])->toArray();
            $haveDuplicate = false;
            foreach ($groupByKey as $key => $value) {
                if(count($value) > 1 && isset($key) && $key > 0) {
                    $haveDuplicate = true;
                }
            }
            return $haveDuplicate;
        } catch(\Exception $exception) {
            Log::error('Get row duplicate error : '.$exception->getMessage());
        }

    }

    public function getErrLimituser()
    {
        return $this->errLimit;
    }

    public function getErrRows()
    {
        return $this->errRow;
    }

    public function getErrDuplicate()
    {
        return $this->errDuplicate;
    }

    public function getErrMessageRows()
    {
        return $this->errMessageRows;
    }

    public function getErrMessageDuplicate()
    {
        return $this->errMessageDuplicate;
    }

    public function emailValidation($email) 
    {
        $regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,10})$/";
        $email = strtolower($email);

        return preg_match ($regex, $email);
    }
}
