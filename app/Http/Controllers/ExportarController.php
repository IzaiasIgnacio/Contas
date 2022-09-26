<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Movimentacao;
use App\Models\Consolidado;
use File;
use Illuminate\Support\Facades\Storage;

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
    private $sobra;

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
        //awardspace
        $dump = "C:\\xampp\\htdocs\\contas\dump_local.sql";
        shell_exec("C:\\xampp\\mysql\\bin\\mysqldump -u root contas > ".$dump);
        Storage::disk('drive')->put('db_contas.sql', File::get($dump));
        Storage::disk('ftp')->put('/izaiasignacio.atwebpages.com/contas/dump.sql', File::get($dump));        
        unlink($dump);

        // Get the API client and construct the service object.
        // $client = $this->getClient();
        // $this->service = new \Google_Service_Sheets($client);

        // // TODO: Assign values to desired properties of `requestBody`:
        // $requestBody = new \Google_Service_Sheets_ClearValuesRequest();

        // // The A1 notation of the values to clear.
        // $range = '!A1:Z1000';  // TODO: Update placeholder value.
        // $response = $this->service->spreadsheets_values->clear($this->id_planilha, $range, $requestBody);

        // $d = explode(".", '12.2019');
        // $this->data = \Carbon\Carbon::createFromFormat('d/m/Y', '01/'.$d[0]."/".$d[1]);
        // $valores = [];
        // $maximo = 0;
        // for ($i=0;$i<=6;$i++) {
        //     $mes = $this->getValoresMes($i);
        //     $valores[$i] = $mes['valores'];
        //     $totais[$i] = $mes['totais'];
        //     $save[$i] = $mes['save'];
        //     if (count($valores[$i]) > $maximo) {
        //         $maximo = count($valores[$i]);
        //     }
        //     $this->data->addMonth();
        // }
        
        // $maximo++;
        // foreach ($valores as $p => $valor) {
        //     $range = '!'.$this->posicao_mes[$p].$maximo;
        //     $this->inserirDadosPlanilha($range, $valor);
        // }
        
        // foreach ($totais as $p => $valor) {
        //     $range = '!'.str_replace('2', $maximo+1, $this->posicao_mes[$p]).($maximo+3);
        //     $this->inserirDadosPlanilha($range, $valor);
        // }

        // foreach ($save as $p => $valor) {
        //     $range = '!'.str_replace('2', $maximo+5, $this->posicao_mes[$p]).($maximo+7);
        //     $this->inserirDadosPlanilha($range, $valor);
        // }
    }

    // private function getValoresMes($i) {
    //     $movimentacoes = Movimentacao::select('nome', 'valor', 'tipo')
    //                                     ->whereMonth('data', $this->data->format('m'))
    //                                     ->whereYear('data', $this->data->format('Y'))
    //                                     ->whereNotIN('tipo', ['save', 'terceiros'])
    //                                         ->orderBy('tipo')
    //                                         ->orderBy('posicao')
    //                                             ->get();

    //     $save = Movimentacao::whereMonth('data', $this->data->format('m'))
    //                           ->whereYear('data', $this->data->format('Y'))
    //                           ->where('tipo', 'save')
    //                             ->first();
    //     $values = [];
    //     switch ($i) {
    //         case 0:
    //             $total_atual = Consolidado::where('nome', 'itau')->first()->valor + Consolidado::where('nome', 'casa')->first()->valor;
    //         break;
    //         case 1:
    //             $total_atual = Consolidado::where('nome', 'salario')->first()->valor + $this->sobra;
    //         break;
    //         default:
    //             $total_atual = Consolidado::where('nome', 'salario')->first()->valor;
    //         break;
    //     }
        
    //     $values[] = [ucfirst($this->data->locale('pt-br')->monthName), $total_atual];
    //     $total = 0;
    //     $renda = 0;
    //     foreach ($movimentacoes as $movimentacao) {
    //         $values[] = [$movimentacao->nome, $movimentacao->valor];
    //         if ($movimentacao->tipo != 'renda') {
    //             $total += $movimentacao->valor;
    //         }
    //         else {
    //             $renda += $movimentacao->valor;
    //         }
    //     }

    //     $totais = [];
    //     $totais[] = ['Total', $total];
    //     if ($i > 0) {
    //         $totais[] = ['Definido', 0];
    //     }
    //     $totais[] = ['Save', $save['valor']];
    //     if ($i == 0) {
    //         $totais[] = ['Sobra', $total_atual-$total+$renda-$save['valor']];
    //     }

    //     $save = 0;
    //     if ($i == 0) {
    //         $save = Consolidado::where('nome', 'savings')->first()->valor+@$totais[$i]['Save'];
    //     }
        
    //     $save = [
    //         ['save', $save],
    //         ['total', 'col 2'],
    //         ['sobra', 'col 2']
    //     ];

    //     return [
    //         'valores' => $values,
    //         'totais' => $totais,
    //         'save' => $save
    //     ];
    // }
    
    // private function inserirDadosPlanilha($range, $valores) {
    //     return $this->service->spreadsheets_values->update(
    //         $this->id_planilha,
    //         $range,
    //         new \Google_Service_Sheets_ValueRange(['values' => $valores]),
    //         ['valueInputOption' => 'RAW']
    //     );
    // }
    
}