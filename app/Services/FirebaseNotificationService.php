<?php

namespace App\Services;


use Google\Client;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Http;


class FirebaseNotificationService
{

    protected $http;
    protected $messagingUrl;
    protected $accessToken;
    protected $projectId;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->messagingUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $this->http = new HttpClient();

        $this->accessToken = $this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        return $client->fetchAccessTokenWithAssertion()['access_token'];
    }

    public function sendN($deviceToken, $title, $body)
    {
        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'android' => [
                    'priority' => 'high',
                ],
            ],
        ];

        $res = $this->http->post($this->messagingUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return json_decode($res->getBody()->getContents(), true);
    }

    public static function sendNotification($deviceToken, $title, $body)
    {
        // مسار ملف الكريدنشلز
        $credentialsPath = config('services.firebase.credentials');

        // نجيب access token من Google
        $client = new Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $token = $client->fetchAccessTokenWithAssertion();

        $accessToken = $token['access_token'];

        // project id من ملف الكريدنشلز
        $projectId = json_decode(file_get_contents($credentialsPath), true)['project_id'];

        // إرسال الإشعار
        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                ],
            ]);

        return $response->json();
    }
}
