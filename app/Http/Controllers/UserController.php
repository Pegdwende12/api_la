<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;

class UserController extends Controller
{
   

   // La partie connection 
   public function login(Request $request){
    if (Auth()->attempt($request->only(['email','password']))) {
        $user = auth()->user();
        $token = $user->createToken("auth_user")->plainTextToken;
        return response()->json([
            "statut" => 200,
            "message" => "connecté",
            "user" => $user,
            "token" => $token
        ]);
    }else{
        return response()->json([
            "statut" => 403,
            "message" => "information",
            
        ]);
    }

   }

 
 
 
 
   //La partie inscription


   public function register(Request $request){
    try{
        $input = $request->all();
        $validator = Validator::make($input, [

            "name" =>"required",
            "prenom" =>"required",
            "email" =>"required|email|unique:users,email",
            "password" =>"required|confirmed",
            "password_confirmation" =>"required|",

        ]);
        if($validator->fails()){
            return response()->json([
                "statut"=>false,
                "message"=>"erreur de validation",
                "errors"=>$validator->errors(),
            ] ,422,);
        }
        $input["password"]= Hash::make($request->password);
        $user = User::create($input);
        return response()->json([
            "statut"=>true,
            "message"=>"utilisteur créé avec succès",
        ] );



    }catch(\Throwable $th){
        return response()->json([
            "statut"=>false,
            "message"=>$th->getMessage(),
        ] ,500,);

    }
   }

   public function profile(Request $request){
    return response()->json([
        "statut"=>true,
        "message"=> "Profil utilisateur",
        "data"=>$request->user(),
    ]);
   }


   public function edit(Request $request){
    try{
        $input = $request->all();
        $validator = Validator::make($input, [
            "email" =>"email|unique:users,email",
        ]);
        if($validator->fails()){
            return response()->json([
                "statut"=>false,
                "message"=>"erreur de validation",
                "errors"=>$validator->errors(),
            ] ,422,);
        }
        $request->user()->update($input);
        return response()->json([
            "statut" => true,
            "message" => "Utilisateur modifié avec succès",
            "data" => $request->user(),
        ]);



    }catch(\Throwable $th){
        return response()->json([
            "statut"=>false,
            "message"=>$th->getMessage(),
        ] ,500,);

    }
   }


   public function updatePassword(Request $request){
    try{
        $input = $request->all();
        $validator = Validator::make($input, [
            "old_password" =>"required",
            "new_password" =>"required|confirmed",
        ]);
        if($validator->fails()){
            return response()->json([
                "statut"=>false,
                "message"=>"erreur de validation",
                "errors"=>$validator->errors(),
            ] ,422,);
        }
        if(!Hash::check($input['old_password'],$request->user()->password)){
            return response()->json([
                "statut" => false,
                "message" => "L'ancien mot de passe est incorrect",
            ],401,);  
        }
        $input['password']= Hash::make($input['new_password']);
        $request->user()->update($input);
        return response()->json([
            "statut" => true,
            "message" => "Mot modifié avec succès",
            "data" => $request->user(),
        ]);



    }catch(\Throwable $th){
        return response()->json([
            "statut"=>false,
            "message"=>$th->getMessage(),
        ] ,500,);

    }
   }


   public function logout(Request $request){
    $accessToken =$request->bearerToken();
    $token = PersonalAccessToken::findToken($accessToken);
    $token->delete();
    return response()->json([
        "statut" => true,
        "message" => "Utilisateur deconnecté avec succès",
        "data" => $request->user(),
    ]);
   }

}
