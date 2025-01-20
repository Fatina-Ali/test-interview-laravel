<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\UserResource;
use App\Mail\ResetPasswordMail;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\UserDeviceToken;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;



class AuthController extends Controller
{

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try{
            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);
            //echo \Illuminate\Support\Facades\Hash::make('12345678');
            if ($this->guard()->attempt($request->only(['email','password']))) {

                /** @var \App\Models\User */
                $user = $this->guard()->user();

                if($user->role == 'admin'){
                    throw new Exception('This service is only for clients');
                }
                $success = (new UserResource($user))->toArray($request);
                $success['token'] =  $user->createToken('auth_token')->plainTextToken;
                return \apiResponse($success);
            }
            else {
                throw new Exception('Wrong credentials, invalid email or password');
            }
        }catch(Exception $e){
            return \apiResponse(null,$e->getMessage(),400);
        }
    }

    public function register(Request $request)
    {
        try{
            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
                'email' => 'unique:users'
            ]);
            $image = $request->file('avatar');
            if($image){
                $image = \storager('profiles',$image);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone,
                'address' => $request->address ?? '',
                'photo' => $image,
                'role'  =>  'client'
                /*'device_token' => $request->device_token ?? '',
                'fcm_token' => $request->firebase_token ?? '',*/

            ]);

            event(new Registered($user));
            // add client
            $client = Client::create([
                'phone' => $request->phone,
                'image' => $image,
                'name'  => $request->name,
                'user_id'  => $user->id
            ]);
            $user->setRelation('client',$client);
            $data =  (new UserResource($user))->toArray($request);
            $data['token'] =  $user->createToken('auth_token')->plainTextToken;
            $data['token_type'] =  "Bearer";


            return \apiResponse($data);
        }catch(Exception $e){
            return \apiResponse(null,$e->getMessage(),400);
        }

    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request)
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            $this->username($request) => $request->username,
            'password' => $request->password,
        ];
    }


    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username(Request $request)
    {
        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        return $fieldType;
    }


    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }



    public function logout(Request $request)
    {
        $user = request()->user(); //or Auth::user()
        // Revoke current user token
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        $data = [
            "code" => 1,
            "data" => [],
            "msg" => trans('logged out')
        ];

        return new JsonResponse($data, 200);
    }




    public function changePassword(Request $request)
    {
        $data = $request->all();
        Validator::make($data, [
            'account_id' => ['required', 'exists:App\Models\User,id'],
            'oldPassword' => ['required',],
            'newPassword' => ['required', Password::min(4)],
        ], [], [])->validate();

        $parent_account = User::find($data['account_id']);
        if (Hash::check($data['oldPassword'], $parent_account->password)) {
            $parent_account->password = Hash::make($data['newPassword']);
            $parent_account->save();
            $data = [
                "code" => 1,
                "data" => [],
                "msg" => trans('passwords.reset')
            ];

            return new JsonResponse($data, 200);

        } else {
            $data = [
                "code" => 1,
                "data" => [],
                "msg" => __('password')
            ];

            return new JsonResponse($data, 400);
        }
    }
    public function submitForgetPasswordForm(Request $request)

    {

        $request->validate([

            'email' => 'required|email|exists:users',

        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            abort(422, __('invalid-email'));
        }
        if($user->status == 0){
            abort(403, __('Account Deactive'));
        }
        $token = Str::random(64);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);


        Mail::send(new ResetPasswordMail($token, $user));

        return apiResponse(null, __('We have e-mailed your password reset link!'));
    }

    public function storeTokenfcm(Request $request){

        $token = PersonalAccessToken::findToken($request->bearerToken());
        if($token){


            $user = $token->tokenable;

            if( $user){
                $user->deviceTokens()->firstOrCreate([
                    'fcm_token' => $request->token
                ], [
                    'fcm_token' =>$request->token
                ]);

            }
        }

        else{

            UserDeviceToken::where('fcm_token',$request->token)->delete();
        }

        return response()->json(['Token successfully stored.']);
    }

    public function infocurrentuser(Request $request)
    {

        $id = Auth::guard()->user()->id;
        $user = User::find($id);
        $arr = $user->toArray();
        $arr["permissions"] = $user->getAllPermissions()->pluck('name')->map(function($name){
            return preg_replace('/\..*/', '', $name);
        });
        $data = [
            "code" => 1,
            "data" =>$arr,

        ];

        return new JsonResponse($data, 200);
    }
}
