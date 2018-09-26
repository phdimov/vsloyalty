<?php

class Report
{

    private static function get($remotefile)
    {
        $bind_params = [
            'file_name' => $remotefile,
            'base_url' => BASE_URL,
            'user_name' => BASE_URL_USER,
            'password' => BASE_URL_PASS
        ];

        $fields = ['auth' => [$bind_params['user_name'], $bind_params['password']]];

        $client = new GuzzleHttp\Client();

        $response = $client->request("GET", $bind_params['base_url'] . $bind_params['file_name'], $fields, ['debug' => true]);
        if ($response->getBody()->isReadable()) {
            if ($response->getStatusCode() == 200) {
                // is this the proper way to retrieve mime type?
                //$mime = array_shift(array_values($response->getHeaders()->get('Content-Type')));
                file_put_contents(FTP_LOCAL . $remotefile, $response->getBody());
                error_log("Successfully downloaded $remotefile\n\r.");
                return true;
            } else {
                error_log("Failed to download $remotefile\n\r.");
            }
        }

    }
}