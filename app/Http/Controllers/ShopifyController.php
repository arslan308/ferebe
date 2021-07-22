<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Store;
use App\Models\User;
use Auth;

class ShopifyController extends Controller
{

    public function install(Request $request){
        $response = install($_GET['shop']);
        // dd($response);
        return redirect($response);
    }

    public function authenticate(Request $request){
        if (Store::where('domain', '=', $_GET['shop'])->exists()) {
            $shop =  Store::where('domain', '=', $_GET['shop'])->first();
            $user = User::where('id', '=', $shop['user_id'])->first();
            Auth::login($user);
            $redirect = '/home';
            return redirect($redirect);
            }
            else{
            $accessToken = generate_token($_GET);
            // Set variables for our request
            $shop = $_GET['shop'];
            $query = array(
                "Content-type" => "application/json"
            );

            // Run API call to get store data
            $shopData = shopify_call($accessToken, $shop, "/admin/api/2021-07/shop.json", array(), 'GET');
            $data =json_decode($shopData['response'],true);
            $newuser = User::create([
                'name' => $data['shop']['name'],
                'email' => $data['shop']['email'],
                'password' => Hash::make('password'),
            ]);
            $mainshop = Store::create([
                'user_id' => $newuser['id'],
                'domain' => $data['shop']['myshopify_domain'],
                'access_token' => $accessToken,
            ]);

            $user = User::where('email', '=', $data['shop']['email'])->first();
            Auth::login($user);
            $redirect = '/home';
            return redirect($redirect);
            }
    }
}
