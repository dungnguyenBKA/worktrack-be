<?php

namespace App\Services;

use Aws\Sns\SnsClient; 
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;
use Exception;

class SNSPushService
{
    private $SnSclient;
    private $platformApp;
    private $userTopic;

    public function __construct()
    {
        $this->SnSclient = new SnsClient([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID', ''),
                'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
            ],
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'version' => env('AWS_SNS_VERSION', 'latest'),
        ]);
        $this->platformApp = env('AWS_SNS_PLATFORM_APPLICATION_ARN', '');
        $this->userTopic = env('AWS_SNS_USER_TOPIC_ARN', '');
    }

    public function sendSMStoPhone($phoneNumber, $message)
    {
        try {
            $response = $this->SnSclient->publish([
                'Message' => $message,
                'PhoneNumber' => $phoneNumber,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('ERROR PUSH SMS to phone number: ' . $phoneNumber . '; Error message: ' . $e->getMessage());
            return false;
        }
    }

    public function pushAll($message)
    {
        $messagePush = $this->messagePush($message);

        $errors['user_topic'] = $this->pushNotificationToTopic($messagePush, $this->userTopic);

        return $errors;
    }

    public function pushToListEndPoint($userData, $message)
    {
        $success = [];
        $errors = [];

        $messagePush = $this->messagePushAndroid($message);

        foreach ($userData as $user) {
            if (!$user->endpoint_arn) {
                continue;
            }

            $resuilt = $this->pushNotificationToDevice($messagePush, $user->endpoint_arn);

            if ($resuilt) {
                $success[] = $user->id;
            } else {
                $errors[] = $user->id;
            }
        }

        return [
            'success' => $success,
            'error' => $errors
        ];
    }

    public function pushNotificationToTopic($messagePush, $topic) {
        try {
            $response = $this->SnSclient->publish([
                'Message' => $messagePush,
                'MessageStructure' => 'json',
                'TopicArn' => $topic,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('ERROR PUSH Notification to Topic; Error message: ' . $e->getMessage());
            return false;
        }
    }

    public function pushNotificationToDevice($messagePush, $endpoint) {
        try {
            $response = $this->SnSclient->publish([
                'Message' => $messagePush,
                'MessageStructure' => 'json',
                'TargetArn' => $endpoint,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('ERROR PUSH Notification to Device; Error message: ' . $e->getMessage());
            return false;
        }
    }

    public function createEndpoint($deviceToken)
    {
        try {
            $response = $this->SnSclient->createPlatformEndpoint([
                'PlatformApplicationArn' => $this->platformApp,
                'Token' => $deviceToken,
            ]);

            return $response['EndpointArn'];
        } catch (AwsException $e) {
            Log::error('ERROR createEndpoint; Error message: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function deleteEndpoint($endpoint)
    {
        try {
            $response = $this->SnSclient->deleteEndpoint([
                'EndpointArn' => $endpoint,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('ERROR delete Endpoint; Error message: ' . $e->getMessage());
            return false;
        }
    }

    public function subscribeDeviceTokenToTopic($endpoint)
    {
        try {
            $response = $this->SnSclient->subscribe([
                'Endpoint' => $endpoint,
                'Protocol' => 'application',
                'TopicArn' => $this->userTopic,
            ]);

            return $response['SubscriptionArn'];
        } catch (AwsException $e) {
            Log::error('ERROR subscribe Device Token End point To Topic; Error message: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function unsubscribeDeviceTokenToTopic($subscription)
    {
        try {
            $response = $this->SnSclient->unsubscribe([
                'SubscriptionArn' => $subscription,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('ERROR unsubscribe subscription; Error message: ' . $e->getMessage());
            return false;
        }
    }

    public function messagePush($data)
    {
        return json_encode([
            'default' => 'Fail',
            'GCM' => json_encode([
                'notification' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'sound' => 'default',
                    'badge' => 0
                ],
            ]),
            'APNS' => json_encode([
                "aps" => [
                     "alert" => [
                         "title" => $data["title"],
                         "body" => $data["body"],
                     ],
                     "badge" => 0,
                 ],
             ])
        ]);
    }
}
