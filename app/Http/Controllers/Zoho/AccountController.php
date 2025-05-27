<?php

namespace App\Http\Controllers\Zoho;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Zoho\AuthenticationController;
use App\Http\Requests\Zoho\CreateAccountWithDealRequest;
use App\Models\ZohoAuthToken;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
  private $AccountsURL = 'https://www.zohoapis.eu/crm/v2/Accounts';
  private $StagesDealURL = 'https://crm.zoho.eu/crm/v2/settings/stages?module=Deals';

  /**
   * Show the form for creating a new resource.
   */
  public function createAccountWithDeal()
  {
    // Check tokens
    $hasTokens = ZohoAuthToken::first();

    // Get Stages for Deal
    $stages = $this->getStagesDeal();

    return Inertia::render('CreateAccountWithDeal', ['hasTokens' => !!$hasTokens, 'stages' => $stages]);
  }

  /**
   * Getting Stages for Deal
   */
  public function getStagesDeal()
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
    curl_setopt_array($ch, $this->getCUrlOptions($this->StagesDealURL));

    // Execute
    $response = curl_exec($ch);

    // JSON to array
    $response = json_decode($response, 1);

    if (isset($response['status']) && $response['status'] === 'error') {
      $respData['error'] = [
        'code' => $response['code'],
        'message' => $response['message'],
      ];
    } else if (isset($response['stages'])) {
      $respData['success'] = true;
      $respData['message'] = 'List of Stages was successfully received';
      $respData['data'] = $response['stages'];
    }

    return  $respData;
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
    curl_setopt_array($ch, $this->getCUrlOptions($this->AccountsURL, ['data' => [$request->all()]]));

    // Execute
    $response = curl_exec($ch);

    // JSON to array
    $response = json_decode($response, 1);

    if (isset($response['status']) && $response['status'] === 'error') {
      $respData['error'] = [
        'code' => $response['code'],
        'message' => $response['message'],
      ];
    } else if (isset($response['data'])) {
      $respData['success'] = true;
      $respData['message'] = 'Account were successfully created';
      $respData['data'] = $response['data'][0];
    }

    return $respData;
  }

  /**
   * Store a newly created Account in storage and after to store Deal.
   */
  public function storeAccountWithDealHandler(CreateAccountWithDealRequest $request)
  {
    // Form Fields
    [
      'Account_Name' => $accountName,
      'Website' => $accountWebsite,
      'Phone' => $accountPhone,
      'Deal_Name' => $dealName,
      'Closing_Date' => $dealClosingDate,
      'Stage' => $dealStage,
    ] = $request->validated();

    // Account Data
    $requestAccount = new Request;
    $requestAccount->replace([
      'Account_Name' => $accountName,
      'Website' => $accountWebsite,
      'Phone' => $accountPhone,
    ]);

    // Response data
    ['error' => $error, 'data' => $data] = $this->store($requestAccount);

    if (isset($error)) {
      return back()->withErrors([$error['code'] => $error['message']]);
    }

    // Create Deal
    $requestDeal = new Request;

    $requestDeal->replace([
      'Deal_Name' => $dealName,
      'Closing_Date' => $dealClosingDate,
      'Stage' => $dealStage,
      'Account_Name' => [
        'id' => $data['details']['id'],
        'name' => $accountName,
      ]
    ]);

    $DealController = new DealController;

    ['error' => $errorDeal] = $DealController->store($requestDeal);

    if (isset($errorDeal)) {
      return back()->withErrors([$errorDeal['code'] => $errorDeal['message']]);
    }

    return back()->with('message', 'Account and Deal were created successfully');
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
