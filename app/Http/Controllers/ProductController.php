<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProducts(){
        $client = new \GuzzleHttp\Client();
        $headers =  array(
            'userid: 5318',
            'authorization: 1436b9378f48f2ec8cd06bf3c0da55d145930e40'
        );

        // $client->setDefaultOption('headers', array('userid' => '5318','authorization' => '1436b9378f48f2ec8cd06bf3c0da55d145930e40'));
        $request = $client->get('https://msyds.madtec.be/api/categories', [
            'headers' => ['userid' => 5318, 'authorization' => '1436b9378f48f2ec8cd06bf3c0da55d145930e40',]
          ]);
        $response = $request->getBody();
       
        return $response;

        dd('getProducts');
    }
    public function listedProducts(){
        dd('listedProducts');
    }
    public function importedProducts(){
        dd('importedProducts');
    }
}
