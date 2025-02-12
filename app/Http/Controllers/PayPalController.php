<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;


class PaypalController extends Controller
{
    
public function createPayment()
{
    $provider = new PayPalClient;
    $provider->setApiCredentials(config('paypal'));
    $token = $provider->getAccessToken();
    $provider->setAccessToken($token);

    $response = $provider->createOrder([
        "intent" => "CAPTURE",
        "application_context" => [
            "return_url" => route('payment.capture'),
            "cancel_url" => route('canscel')
        ],
        "purchase_units" => [
            [
                "amount" => [
                    "currency_code" => "USD",
                    "value" => "100.00" 
                ]
            ]
        ]
    ]);

//  dd($response);
 if(isset($response["id"]) && $response['id'] != null){
    foreach ($response['links'] as $link){
        if($link['rel'] == 'approve') {
            // session() ->put('product_name', $request->product_name);
            // session() ->put('quantity', $request->quantity);
            return redirect()->away($link['href']);
        }
    }
    }else {
        return redirect()->route(cansel);
    }      
}

public function capturePayment(Request $request)
{
    $provider = new PayPalClient;
    $provider->setApiCredentials(config('paypal'));
    $token = $provider->getAccessToken(); // アクセストークンの取得
    $provider->setAccessToken($token);

    // リダイレクトURLからtoken（注文ID）を取得
    $orderId = $request->query('token'); 
    
    // PayPalからのトークンを受け取る
    $data = [
        'payer_id' => $request->query('PayerID'), // PayerIDを取得して渡す
        'payment_source' => [
            'paypal' => [
                'email' => $request->query('PayerEmail'), // PayPalのメールアドレスを追加
            ]
        ]
    ];

    // 注文ID（$orderId）を使用して支払い確認

    $response = $provider->confirmOrder($orderId, $data); // 注文IDを渡す

    dd($response); 


    if (isset($response['status']) && $response['status'] == 'COMPLETED') {
        return view('home'); // 支払い完了画面
    } elseif (isset($response['status']) && $response['status'] == 'PAYER_ACTION_REQUIRED') {
        // 支払いに追加アクションが必要な場合、PayPalのページにリダイレクト
        return redirect($response['links'][1]['href']);
    } else {
        return back()->with('error', '支払いに失敗しました。');
    }
}


}
