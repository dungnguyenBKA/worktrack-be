<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\ReportConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetupController extends BaseController
{
    private $host = '';
    private $password = '';
    private $username = '';
    private $port = '';
    private $dbPrefix = 'aikao_';

    function __construct() {
        $this->host = env('DB_HOST');
        $this->username = env('DB_USERNAME');
        $this->password = env('DB_PASSWORD');
        $this->port = env('DB_PORT');

        parent::__construct();
    }

    public function createEnv($subdomain) {
        try {
            $database = $this->dbPrefix . $subdomain;

            $file = '.env.' . $subdomain;
            $content = 'DB_CONNECTION=mysql
                        DB_HOST=' . $this->host . '
                        DB_PORT=' . $this->port . '
                        DB_DATABASE=' . $database . '
                        DB_USERNAME=' . $this->username . '
                        DB_PASSWORD=' . $this->password ;

            if(Storage::disk('root')->has($file)) {
                return send_error('E0005', 'File .env exist');
            }

            Storage::disk('root')->put($file, $content);

            return send_success();
        } catch (\Exception $e) {
            return send_error('E0005', 'Create .env api: ' . $e->getMessage());
        }
    }

    public function maintenance(Request $request)
    {
        $config = ReportConfig::first();
        if ($config) {
            try {
                $config->update(['maintenance' => $request->has('maintenance') ? $request->get('maintenance') : 0]);
                return send_success();
            } catch (\Exception $e) {
                return send_error('E0301', $e->getMessage());
            }
        }else{
            return send_error('E0401');
        }
    }

    public function getNow()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        return send_success(['now' => $now]);
    }
}
