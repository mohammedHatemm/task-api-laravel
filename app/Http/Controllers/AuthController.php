<?php

namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;


use Illuminate\Http\Request;

class AuthController extends Controller
{
    // //
    public function register(Request $request)
    {
        //
       $field=  $request -> validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:customers,email',
            'password' => 'required|min:6|confirmed'
        ]);
        $field['password'] = bcrypt($field['password']);
        $customer =Customer::create($field);
        $token = $customer->createToken($request->name)->plainTextToken;
        $response = [
            'customer' => $customer,
            'token' => $token
        ];
        return response($response, 201);
    }

    public function login(Request $request)
    {
        //
         $request -> validate([
            'email' =>'required|exists:customers',
            'password' =>'required'
        ]);
        $customer =Customer::where('email', $request->email)->first();
        if(!$customer || !Hash::check($request->password, $customer->password)){
            return response([
                'message' => 'the provided credentials are  incorrect '
            ]);


        }
        $token = $customer->createToken($customer->name)->plainTextToken;
        $response = [
            'customer' => $customer,
            'token' => $token
        ];
        return response($response, 201);


    }

    public function logout(Request $request)
    {
        //
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'successfully logged out'
        ]);
    }
}
