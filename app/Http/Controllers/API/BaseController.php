<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ManageFile;

class BaseController extends Controller
{
    use ManageFile;

    protected $user;
    protected $userId;

    public function __construct()
    {
        $this->user = auth('api')->getToken() ? auth('api')->user() : null;
        $this->userId = $this->user ? $this->user->id : null;
    }

    /**
     * Return Success response.
     *
     * @param  mixed $data
     * @param  string $message
     * @return \Illuminate\Http\Response
     */
    public function sendSuccess($data = null, $message = '')
    {
        $statusCode = __('errors.SUCCESS');
        $response = null;

        if (!empty($data)) {
            $response['data'] = $data;
        }
        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return Error response.
     *
     * @param  string $code
     * @param  mixed $data
     * @param  mixed $validations
     * @return \Illuminate\Http\Response
     */
    public function sendError($code, $data = null)
    {
        $statusCode = __('errors.' . $code . '.statusCode');
        $response = [
            'code' => __('errors.' . $code . '.code'),
            'message' => __('errors.' . $code . '.message')
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (!empty($validations)) {
            $response['validations'] = $validations;
        }

        return response()->json($response, $statusCode);
    }

    protected function uploadFile($request, $fieldName, $filePath, $oldFile = null)
    {
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);
            $urlFile = $this->uploadFileToServer($filePath, $file);
            if($urlFile && $oldFile) {
                $this->deleteFileInServer($oldFile);
            }
            return $urlFile;
        }
        return false;
    }

    protected function deleteFile($fileUrl)
    {
        if (!$fileUrl) return false;

        $this->deleteFileInServer($fileUrl);
        return true;
    }
}
