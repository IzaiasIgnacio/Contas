<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Movimentacao;

class ExportarController extends Controller {

    private $id_planilha = '1I6SEQnarqrTfe2uiyiaBgpxSdof8KE5DQaK4g7f15e4';
    private $service;
    private $data;
    private $posicao_mes = [
        '0' => 'B2:C',
        '1' => 'E2:F',
        '2' => 'H2:I',
        '3' => 'K2:L',
        '4' => 'N2:O',
        '5' => 'Q2:R',
        '6' => 'T2:S',
    ];

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
        $this->service = new \Google_Service_Sheets($client);

        // TODO: Assign values to desired properties of `requestBody`:
        $requestBody = new \Google_Service_Sheets_ClearValuesRequest();

        // The A1 notation of the values to clear.
        $range = '!A1:Z1000';  // TODO: Update placeholder value.
        $response = $this->service->spreadsheets_values->clear($this->id_planilha, $range, $requestBody);

        $d = explode(".", '12.2019');
        $this->data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$d[0]."/".$d[1]);
        $valores = [];
        $maximo = 0;
        for ($i=0;$i<=6;$i++) {
            $valores[$i] = $this->getValoresMes();
            if (count($valores[$i]) > $maximo) {
                $maximo = count($valores[$i]);
            }
            $this->data->addMonth();
        }

        $maximo++;
        foreach ($valores as $mes => $valor) {
            $range = '!'.$this->posicao_mes[$mes].$maximo;
            $this->inserirDadosPlanilha($range, $valor);
        }

        for ($i=0;$i<=6;$i++) {
            $save = [
                ['save', 'col 2'],
                ['total', 'col 2'],
                ['sobra', 'col 2']
            ];
            $range = '!'.str_replace('2', $maximo+2, $this->posicao_mes[$i]).($maximo+4);
            $this->inserirDadosPlanilha($range, $save);
        }
    }

    private function getValoresMes() {
        // $movimentacoes = Movimentacao::select('nome', 'valor')
        //                                 ->whereMonth('data', $this->data->format('m'))
        //                                 ->whereYear('data', $this->data->format('Y'))
        //                                 ->where('tipo', 'terceiros')
        //                                     ->get();

        $values = [];
        $values[] = ['Mês', '00000'];
        $total = 0;
        // foreach ($movimentacoes as $movimentacao) {
        for ($i=0;$i<=5;$i++) {
            $values[] = ['col 1', '5'];
            $total += 5;
            // $values[] = [$movimentacao->nome, $movimentacao->valor];
        }
        $values[] = ['Save', '00000'];
        $values[] = ['Total', $total];

        return $values;
    }
    
    private function inserirDadosPlanilha($range, $valores) {
        return $this->service->spreadsheets_values->update(
            $this->id_planilha,
            $range,
            new \Google_Service_Sheets_ValueRange(['values' => $valores]),
            ['valueInputOption' => 'RAW']
        );
    }
    
}