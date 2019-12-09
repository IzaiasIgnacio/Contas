<?php

namespace App\Http\Controllers;

error_reporting(0);

use App\Http\Controllers\Controller;
use App\Models\Movimentacao;

class ExportarController extends Controller {

    private $id_planilha = '1I6SEQnarqrTfe2uiyiaBgpxSdof8KE5DQaK4g7f15e4';

    private function getClient() {
        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig('credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            }
            else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }

    public function exportar() {
        // Get the API client and construct the service object.
        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);

        // The A1 notation of the values to clear.
        $range = '!A1:Z1000';  // TODO: Update placeholder value.

        // TODO: Assign values to desired properties of `requestBody`:
        $requestBody = new \Google_Service_Sheets_ClearValuesRequest();

        $response = $service->spreadsheets_values->clear($this->id_planilha, $range, $requestBody);

        $range = '!B1:C40';

        $d = explode(".", '12.2019');
        $data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$d[0]."/".$d[1]);
        $movimentacoes = Movimentacao::select('nome', 'valor')
                                        ->whereMonth('data', $data->format('m'))
                                        ->whereYear('data', $data->format('Y'))
                                        ->where('tipo', 'terceiros')
                                            ->get();

        $values = [];
        foreach ($movimentacoes as $movimentacao) {
            $values[] = [$movimentacao->nome, $movimentacao->valor];
        }
        // $values = [
        //     ['col 1', 'col 2'],
        //     ['col 1', 'col 2'],
        //     ['col 1', 'col 2']
        // ];

        $requestBody = new \Google_Service_Sheets_ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];

        $response = $service->spreadsheets_values->update($this->id_planilha, $range, $requestBody, $params);
    }
    
}