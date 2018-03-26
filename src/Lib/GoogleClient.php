<?php

namespace SniTodos\Lib;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @class GoogleClient
 *
 * Encapsulcates logic to create a Google_Client, authorized for appDataFolder.
 */
class GoogleClient {

    // @var GoogleClient
    private static $instance;

    // @var Google_Service_Drive
    private $client;

    const APPLICATION_NAME   = 'SNI_ORGANISER';
    const BASE_PATH          = __DIR__ . '/../../';
    const CREDENTIALS_PATH   = 'credentials.json';
    const CLIENT_SECRET_PATH = 'client_id.json';
    // const SCOPE              = \Google_Service_Drive::DRIVE_APPDATA;
    // Folgendes gibt natürlich zuviel Rechte, aber es war unklar wie das besser geht.
    const SCOPE              = \Google_Service_Drive::DRIVE;

    private function __construct() {}

    /**
     * Singelton
     * @return GoogleClient
     */
    public static function getInstance(): GoogleClient
    {
        if (!self::$instance) {
            self::$instance = new GoogleClient();
        }

        return self::$instance;
    }

    /**
     * Returns the authorized google drive client.
     * @return \Google_Service_Drive
     */
    public function getClient(): \Google_Service_Drive {
        if (!$this->client) {
            $client = new \Google_Client();
            $client->setApplicationName(self::APPLICATION_NAME);
            $client->setScopes([self::SCOPE]);
            $client->setAuthConfig(self::BASE_PATH . self::CLIENT_SECRET_PATH);
            $client->setAccessType('offline');

            // Load previously authorized credentials from a file.
            $credentialsPath = self::BASE_PATH . self::CREDENTIALS_PATH;
            if (file_exists($credentialsPath)) {
                $accessToken = json_decode(file_get_contents($credentialsPath), true);
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

                // Store the credentials to disk.
                if(!file_exists(dirname($credentialsPath))) {
                    mkdir(dirname($credentialsPath), 0700, true);
                }
                file_put_contents($credentialsPath, json_encode($accessToken));
                printf("Credentials saved to %s\n", $credentialsPath);
            }
            $client->setAccessToken($accessToken);

            // Refresh the token if it's expired.
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
            }
        }

        $this->service = new \Google_Service_Drive($client);
        return $this->service;
    }
}
