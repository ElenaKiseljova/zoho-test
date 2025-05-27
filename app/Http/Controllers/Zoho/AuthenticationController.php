<?php

namespace App\Http\Controllers\Zoho;

use App\Http\Controllers\Controller;
use App\Models\ZohoAuthToken;

class AuthenticationController extends Controller
{
  private $OAuthURL = 'https://accounts.zoho.eu/oauth/v2/token';

  /**
   * Getting and saving Refresh token
   */
  public function updateRefreshToken()
  {
    // Response data
    $respData = [
      'error' => null,
      'success' => null,
      'message' => null,
      'data' => null
    ];

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
    curl_setopt_array($ch, $this->getCUrlOptions($post));

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
      $this->clearTokens();

      $respData['error'] = [
        'code' => $key,
        'message' => $message,
      ];
    } else if (isset($response['refresh_token']) && isset($response['access_token'])) {
      ZohoAuthToken::create([
        'refresh_token' => $response['refresh_token'],
        'access_token' => $response['access_token'],
        'expires_in' => $response['expires_in'],
      ]);

      $respData['success'] = true;
      $respData['message'] = 'Tokens were successfully created';
      $respData['data'] = $response;
    }

    return $respData;
  }

  /**
   * Getting and saving Refresh token Handler
   */
  public function updateRefreshTokenHandler()
  {
    // Response data
    ['error' => $error, 'message' => $message] = $this->updateRefreshToken();

    if (isset($error)) {
      return back()->withErrors([$error['code'] => $error['message']]);
    }

    return back()->with('message', $message);
  }

  /**
   * Getting and saving Access token
   */
  public function updateAccessToken()
  {
    // Response data
    $respData = [
      'error' => null,
      'success' => null,
      'message' => null,
      'data' => null
    ];

    // Saved tokens
    $tokens = ZohoAuthToken::first();

    if ($tokens && $tokens->refresh_token) {
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
      curl_setopt_array($ch, $this->getCUrlOptions($post));

      // Execute
      $response = curl_exec($ch);

      // JSON to array
      $response = json_decode($response, 1);

      if (isset($response['error'])) {
        $respData['error'] = ['code' => 400, 'message' => $response['error']];
      } else if (isset($response['access_token'])) {
        $tokens->update([
          'access_token' => $response['access_token'],
          'expires_in' => $response['expires_in']
        ]);

        $respData['success'] = true;
        $respData['message'] = 'Access Token was successfully updated';
        $respData['data'] = $response;
      }
    } else {
      $respData['error'] = ['code' => 400, 'message' => 'ZohoAuthToken::first() does not exist'];
    }

    return $respData;
  }

  /**
   * Getting and saving Access token Handler
   */
  public function updateAccessTokenHandler()
  {
    // Response data
    ['error' => $error, 'message' => $message] = $this->updateAccessToken();

    if (isset($error)) {
      return back()->withErrors([$error['code'] => $error['message']]);
    }

    return back()->with('message', $message);
  }

  /**
   * Access token
   */
  public function accessToken(): ?string
  {
    $tokens = ZohoAuthToken::first();

    $accessToken = $tokens ? $tokens->access_token : null;

    if (!$tokens || $tokens->updated_at->diffInSeconds() > $tokens->expires_in) {
      ['data' => $data] = $this->updateAccessToken();

      if ($data) {
        $accessToken =  $data['access_token'];
      }
    }

    return $accessToken;
  }

  /**
   * Delete old DB entity with Tokens
   */
  public function clearTokens()
  {
    $tokens = ZohoAuthToken::first();

    if ($tokens) {
      $tokens->delete();
    }
  }

  /**
   * Get array of options for cUrl
   */
  private function getCUrlOptions(?array $postData = null): array
  {
    $options = [
      CURLOPT_URL => $this->OAuthURL,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded'
      ],
    ];

    if ($postData) {
      $options[CURLOPT_POST] = 1;
      $options[CURLOPT_POSTFIELDS] = http_build_query($postData);
    }

    return $options;
  }
}
