<?php

namespace App\Services;


use Google\Client;
use GuzzleHttp\Client as HttpClient;


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

    public function sendNotification($deviceToken, $title, $body)
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
}
