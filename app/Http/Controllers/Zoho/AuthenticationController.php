<?php

namespace App\Http\Controllers\Zoho;

use App\Http\Controllers\Controller;
use App\Models\ZohoAuthToken;
use Error;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
  private $OAuthURL = 'https://accounts.zoho.eu/oauth/v2/token';

  /**
   * Getting refresh token
   */
  public function updateRefreshToken()
  {
    // Data
    $post = [
      'grant_type' => 'authorization_code',
      'client_id' => env('ZOHO_CLIENT_ID'),
      'client_secret' => env('ZOHO_CLIENT_SECRET'),
      'redirect_uri' => env('ZOHO_REDIRECT_URI'),
      'code' => env('ZOHO_GRANT_TOKEN'),
    ];

    // Init cURL
    $ch = curl_init();

    // Settings cURL
    curl_setopt($ch, CURLOPT_URL, $this->OAuthURL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type' => 'application/x-www-form-urlencoded'
    ]);

    // Execute
    $response = curl_exec($ch);

    // JSON to array
    $response = json_decode($response, 1);

    if (isset($response['error'])) {
      switch ($key = $response['error']) {
        case 'invalid_client':
          $message = 'You have passed an invalid Client ID or secret. Specify the correct client ID and secret.';
          break;

        case 'invalid_code':
          $message = 'The grant token has expired. The grant token is valid only for one minute in the redirection-based flow. Generate the access and refresh tokens before the grant token expires.';
          break;

        case 'invalid_redirect_uri':
          $message = 'The redirect URI in the request mismatches the one registered in the developer console. Specify the correct redirect URI in the request.';
          break;

        default:
          $message = 'Failed to obtain refresh token.';
          break;
      }

      // Delete old entity with Tokens
      self::clearTokens();

      return to_route('create.account-with-deal')->withErrors([$key => $message]);
    } else if (isset($response['refresh_token']) && isset($response['access_token'])) {
      ZohoAuthToken::create([
        'refresh_token' => $response['refresh_token'],
        'access_token' => $response['access_token']
      ]);

      return to_route('create.account-with-deal')->with('message', 'Tokens were successfully created');
    } else {
      dump($response);
    }
  }

  /**
   * Getting access token
   */
  public function updateAccessToken()
  {
    $tokens = ZohoAuthToken::first();

    // Data
    $post = [
      'grant_type' => 'refresh_token',
      'client_id' => env('ZOHO_CLIENT_ID'),
      'client_secret' => env('ZOHO_CLIENT_SECRET'),
      'refresh_token' => $tokens->refresh_token,
    ];

    // Init cURL
    $ch = curl_init();

    // Settings cURL
    curl_setopt($ch, CURLOPT_URL, $this->OAuthURL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type' => 'application/x-www-form-urlencoded'
    ]);

    // Execute
    $response = curl_exec($ch);

    // JSON to array
    $response = json_decode($response, 1);

    if (isset($response['error'])) {
      return to_route('create.account-with-deal')->withErrors(['400' => $response['error']]);
    } else if (isset($response['access_token'])) {
      $tokens = ZohoAuthToken::first();

      if ($tokens) {
        $tokens->update([
          'access_token' => $response['access_token']
        ]);

        return to_route('create.account-with-deal')->with('message', 'Access Token was successfully updated');
      } else {
        return to_route('create.account-with-deal')->withErrors(['400' => 'The access token was not updated in the DB but was successfully updated in the CRM because ZohoAuthToken::first() does not exist']);
      }
    } else {
      dump($response);
    }
  }

  /**
   * Delete old entity with Tokens
   */
  public function clearTokens()
  {
    dd('hello');
    // $tokens = ZohoAuthToken::first();

    // if ($tokens) {
    //   $tokens->delete();
    // }
  }
}


// 1000.cc3afeeef55463d023974935afb4eaa0.c246d38a5f4ac64ab51c8535184bcb20
// 1000.e19526c4ba5804b2963ccb133b17c334.fb274ab54a90a37d99e44cb4e607b502
// 2025-05-25 19:25:03
// 2025-05-25 20:57:41
