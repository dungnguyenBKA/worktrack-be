<?php

namespace App\Http\Remotes;

use Illuminate\Support\Facades\Http;
use Exception;

class CrmRemote
{
    private $domain;

    private $clientId = 'UeIB9mxDGDm3n2TLWr6G';
    private $clientSecret = 'TWvQATu0vF9J58An3pDT';

    public function __construct($subDomain = '')
    {
        $domain = [];
        $this->domain = env('CRM_REMOTE');
        if(isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])){
            $domain = explode('.', $_SERVER['HTTP_HOST']);
        }
        $this->subDomain = $domain[0] ?? $subDomain;

        if(empty($this->subDomain)) {
            throw new Exception(_('Sub domain not found.'));
        }
    }

    private function setHeader() {

        return [
            'client-id' => $this->clientId,
            'client-secret' => $this->clientSecret
        ];
    }

    public function createOrUpdateUserMaster($userId, $email, $password)
    {

        $postInput = [
            'user_id' => $userId,
            'email' => $email,
            'password' => $password,
            'sub_domain' => $this->subDomain
        ];
        $response = Http::withHeaders($this->setHeader())->post($this->domain . 'user-master/create-or-update', $postInput);

        if($response->getStatusCode() != 200) {
            logger()->error('CRM Remote createUserMaster: ' . $response->getBody());
            return ['error' => 1];
        }

        return json_decode($response->getBody(), true);
    }

    public function deleteMaster($userId)
    {
        $postInput = [
            'user_id' => $userId,
            'sub_domain' => $this->subDomain
        ];
        $response = Http::withHeaders($this->setHeader())->delete($this->domain . 'user-master/delete', $postInput);

        if($response->getStatusCode() != 200) {
            logger()->error('CRM Remote createUserMaster: ' . $response->getBody());
            return ['error' => 1];
        }

        return json_decode($response->getBody(), true);
    }

    public function getDetailCompany()
    {
        $response = Http::withHeaders($this->setHeader())->get($this->domain . 'company/get-by-sub-domain/' . $this->subDomain);

        if($response->getStatusCode() != 200) {
            logger()->error('CRM Remote getDetailCompany: ' . $response->getBody());
            return ['error' => 1];
        }

        return json_decode($response->getBody(), true);
    }

    public function getUserMasterByEmail($email) {
        $response = Http::withHeaders($this->setHeader())->get($this->domain . 'user-master/get-by-email/' . $email);

        if($response->getStatusCode() != 200) {
            logger()->error('CRM Remote getUserMasterByEmail: ' . $response->getBody());
            return ['error' => 1];
        }

        return json_decode($response->getBody(), true);
    }

    public function updateDeviceToken($deviceToken, $userMasterId = null)
    {
        $postInput = [
            'device_token' => $deviceToken,
            'user_master_id' => $userMasterId
        ];
        $response = Http::withHeaders($this->setHeader())->post($this->domain . 'update-device-token', $postInput);

        return json_decode($response->getBody(), true);
    }

    public function getListDeviceTokenBySubDomain()
    {
        $response = Http::withHeaders($this->setHeader())->get($this->domain . 'list-device-token/get-by-sub-domain/' . $this->subDomain);

        if($response->getStatusCode() != 200) {
            logger()->error('CRM Remote getDetailCompany: ' . $response->getBody());
            return ['error' => 1];
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * get all sub_domain in company table on crm
     *
     * @return int[]|mixed
     */
    public function getAllSubDomain()
    {
        $response = Http::withHeaders($this->setHeader())->get($this->domain . 'company/get-all-sub-domain');

        if($response->getStatusCode() != 200) {
            logger()->error('CRM Remote get all sub_domain: ' . $response->getBody());
            return ['error' => 1];
        }

        return json_decode($response->getBody(), true);
    }
}
