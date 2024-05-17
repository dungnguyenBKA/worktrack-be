<?php

namespace App\Console\Commands;

use App\Http\Remotes\CrmRemote;
use App\Models\Notifications;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $crmRemote = new CrmRemote('call api to crm');
        $subDomains = $crmRemote->getAllSubDomain();
        $subDomains = $subDomains['data'] ?? [];
        foreach ($subDomains as $subDomain) {
            $crmRemote = new CrmRemote($subDomain);
            $response = $crmRemote->getListDeviceTokenBySubDomain();
            $deviceTokens = $response['data'] ?? [];
            [$notification, $mysqli] = $this->getNotificationOfSubDomain($subDomain);
            if ($notification) {
                $postFields = [
                    "priority" => "high",
                    "content_available" => true,
//                "to" => "/topics/flutter_notification"
                ];

                $notificationId = (int) $notification['id'];
                $data = [
                    "id" => $notificationId,
                    "title" => $notification['title'],
                    "body" => $notification['content']
                ];

                foreach (array_chunk($deviceTokens, 1000) as $item) {
                    $postFields["registration_ids"] = $item;
                    $postFields["data"] = $data;
                    $postFields["notification"] = $data;
                    $this->send(json_encode($postFields));                    
                }

                $sql = "UPDATE notifications SET status = 1, updated_at = NOW() WHERE id = ". $notificationId;

                if (mysqli_query($mysqli, $sql)) {
                    info("Notification id : $notificationId updated successfully of subdomain : ". $subDomain);
                } else {
                    info("Error updating status for notification: " . $mysqli->error);
                }
                $mysqli->close();
            }
        }

        return 0;
    }

    public function send($postFields) {        
        $url = 'https://fcm.googleapis.com/fcm/send';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                'Authorization: key='.Config::get('constants.authorization_key', ''),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * get first notification of subdomain
     *
     * @param $subDomain
     * @return array|boolean
     */
    private function getNotificationOfSubDomain($subDomain)
    {
        $now = date("Y-m-d H:i");
        $sql = "SELECT * FROM notifications WHERE start_time <= '" . $now . "' AND status = 0";
        $mysqli = $this->connectToDatabaseSubdomain($subDomain);
        if (!$mysqli) {
            return false;
        }
        $notification = '';
        if ($result = $mysqli->query($sql)) {
            $notification = $result->fetch_assoc();
        }
        if (!empty($notification)) {
            return [$notification, $mysqli];
        } else {
            //info("Sub domain has empty notification : ". $subDomain);
            $mysqli->close();
            return false;
        }
    }

    /**
     * connect to database of sub domain
     *
     * @param $subDomain
     * @return false|\mysqli
     */
    private function connectToDatabaseSubdomain($subDomain)
    {
        $database = env('DB_PREFIX') . $subDomain;
        $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), $database);

        mysqli_set_charset($mysqli, 'UTF8');
        if ($mysqli->connect_errno) {
            Logger("Failed to connect to MySQL: " . $mysqli->connect_error);

            return false;
        }

        return $mysqli;
    }
}
