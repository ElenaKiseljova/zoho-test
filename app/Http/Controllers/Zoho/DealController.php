<?php

namespace App\Http\Controllers\Zoho;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DealController extends Controller
{
  public $DealURL = 'https://crm.zoho.eu/crm/v2.2/Deals';

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // Response data
    $respData = [
      'error' => null,
      'success' => null,
      'message' => null,
      'data' => null
    ];

    // Init cURL
    $ch = curl_init();

    // Settings cURL
    curl_setopt_array($ch, $this->getCUrlOptions($this->DealURL, $request->all()));

    // Execute
    $response = curl_exec($ch);

    // JSON to array
    $response = json_decode($response, 1);

    dd($response, $request->all());

    if (isset($response['status']) && $response['status'] === 'error') {
      $respData['error'] = [
        'code' => $response['code'],
        'message' => $response['message'],
      ];
    } else if (isset($response['data'])) {
      $respData['success'] = true;
      $respData['message'] = 'Deal were successfully created';
      $respData['data'] = $response['data'][0];
    }

    return $respData;
  }

  /**
   * Get array of options for cUrl
   */
  private function getCUrlOptions(string $url, ?array $postData = null): array
  {
    // Auth
    $AuthenticationController = new AuthenticationController;

    // Base
    $options = [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Zoho-oauthtoken ' . ($AuthenticationController->accessToken() ?? ''),
      ],
    ];

    // Post
    if ($postData) {
      $options[CURLOPT_POST] = 1;
      $options[CURLOPT_POSTFIELDS] = json_encode($postData);
    }

    return $options;
  }
}
