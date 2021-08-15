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
    
    public function createDiscount(Request $request){

        try{
        $data =  json_decode($request->data);
        $shopDiscount =  json_decode($request->discount);
        $productIDs = [];
        foreach ($data as $obj) {
            if (!in_array($obj->product_id, $productIDs)){
             array_push($productIDs, $obj->product_id);
           }
        }
        $shopdomain = Store::where('domain','ferebe-com.myshopify.com')->first();
        $digits = 5;
        $disTitle = rand(pow(10, $digits-1), pow(10, $digits)-1);
        $priceData = array(
            "price_rule" => array(
                "title" => "DISCOUNT".$disTitle,
                "target_type" => "line_item",
                "target_selection" => "all",
                "allocation_method" => "across",
                "value_type" => "fixed_amount",
                "value" => "-".$shopDiscount,
                "customer_selection" => "all",
                "usage_limit" => 1,
                // "prerequisite_subtotal_range" =>  array(
                //     "greater_than_or_equal_to" => $request->subtotal
                // ),
                // "entitled_product_ids" => $productIDs,
                "starts_at" => $request->start,
                "ends_at" => $request->end,
            )
        );
        $shop = 'ferebe-com.myshopify.com';
        $accessToken = $shopdomain->access_token;
        $priceRule =  shopify_call($accessToken, $shop, "/admin/api/2021-04/price_rules.json", $priceData, 'POST');
        $discountData = json_decode($priceRule['response'], true);
        $disDigits = 7;
        $disNum = rand(pow(10, $disDigits-1), pow(10, $disDigits)-1);
        $discount = array(
            "discount_code" => array(
                "code" => 'DISCOUNT'.$disNum,
                "usage_count" => 1,
            )
        );
        $discountCode = shopify_call($accessToken, $shop, '/admin/api/2021-04/price_rules/'.$discountData['price_rule']['id'].'/discount_codes.json', $discount, 'POST');
        $res = json_decode($discountCode['response'], true);
        return $res;
    } catch (\Exception $e) {
            \Log::alert("Shopify discount: " . $e->getMessage());
    }
    }
}
