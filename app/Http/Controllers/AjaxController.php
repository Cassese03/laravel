<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function iscriviti($email,$password){
        $password2 = $password;
        $password = DB::SELECT('Select SubString(Convert(varchar(max), HASHBYTES(\'SHA2_256\', \''.$password.'\'), 1), 3, 64) as Password')[0]->Password;
        $pos = strpos($email,'@');
        if($pos !='')
        $operatore = substr($email,'0',$pos);
        else
            $operatore = $email;
        DB::table('Operatore')->insertGetId(['Cd_Operatore' => $operatore,'email' => $email, 'Password' => $password]);
        return Redirect::to('/ajax/accedi/'.$email.'/'.$password2);
    }
    public function accedi($email,$password){
        $password = DB::SELECT('Select SubString(Convert(varchar(max), HASHBYTES(\'SHA2_256\', \''.$password.'\'), 1), 3, 64) as Password')[0]->Password;
        $utente = DB::SELECT('SELECT * FROM OPERATORE WHERE Email = \''.$email.'\' and Password = \''.$password.'\'');
        if(sizeof($utente)>0)
        {
            session(['utente' => $utente[0]->Cd_Operatore]);
            session()->save();
            return Redirect::to('');
        }else
            echo 'error';
    }
    public function logout(){
        session()->flush();
    }

    public function redirectToInstagramProvider()
    {
        $appId = config('services.instagram.client_id');
        $redirectUri = urlencode(config('services.instagram.redirect'));
        return redirect()->to("https://api.instagram.com/oauth/authorize?app_id={$appId}&redirect_uri={$redirectUri}&scope=user_profile,user_media&response_type=code");
    }
    public function instagramProviderCallback(Request $request)
    {
        $code = $request->code;
        if (empty($code)) return redirect()->route('home')->with('error', 'Failed to login with Instagram.');

        $appId = config('services.instagram.client_id');
        $secret = config('services.instagram.client_secret');
        $redirectUri = config('services.instagram.redirect');

        $client = new Client();

        // Get access token
        $response = $client->request('POST', 'https://api.instagram.com/oauth/access_token', [
            'form_params' => [
                'app_id' => $appId,
                'app_secret' => $secret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return redirect()->route('home')->with('error', 'Unauthorized login to Instagram.');
        }

        $content = $response->getBody()->getContents();
        $content = json_decode($content);

        $accessToken = $content->access_token;
        $userId = $content->user_id;

        // Get user info
        $response = $client->request('GET', "https://graph.instagram.com/me?fields=id,username,account_type&access_token={$accessToken}");

        $content = $response->getBody()->getContents();
        $oAuth = json_decode($content);

        // Get instagram user name
        $username = $oAuth->username;

        // do your code here
        $db   = DB::SELECT('SELECT * FROM OPERATORE WHERE email = \''.$oAuth->username.'\'');
        if(sizeof($db)>0)
            return Redirect::to('/ajax/accedi/'.$oAuth->username.'/'.$userId);
        else
            return Redirect::to('/ajax/iscriviti/'.$oAuth->username.'/'.$userId);

    }
}
