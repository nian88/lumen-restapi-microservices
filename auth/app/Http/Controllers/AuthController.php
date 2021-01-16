<?php

namespace App\Http\Controllers;

use App\User;
use App\Jobs\SendEmailJob;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register', 'forgotpassword']]);
    }

     /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json([
                'response_code' => 90,
                'error' => true,
                'messsage' => 'Login Failed',
            ], 200);
        }

        return $this->createNewToken($token);
    }


    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|between:2,100',
            'last_name' => 'required|between:2,100',
            'username' => 'required|between:2,100',
            'email' => 'required|email|unique:users|max:50',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => app('hash')->make($request->password)]
                ));
        $token = auth()->attempt($validator->validated());
        return $this->createNewToken($token, "register");
    }


     /**
     * Get user details.
     *
     * @param  Request  $request
     * @return Response
     */
    public function profile()
    {
        return response()->json([
            'response_code' => 99,
            'error' => false,
            'messsage' => 'success',
            'result' => auth()->user()
        ], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'response_code' => 99,
            'error' => false,
            'message' => 'Successfully logged out'
        ]);
    }

    public function forgotpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response_code' => 94,
                'error' => true,
                'message' => 'The selected email is invalid'
            ], 200);
        }


        $user = User::where('email', $request->email)->first();

        $new_password = Str::random(6);

        $user->update(['password' => Hash::make($new_password)]);

        $data = array('email'=>$user->email, 'name'=>$user->username, 'password' => $new_password);

        dispatch(new SendEmailJob($data));

        return response()->json([
            'response_code' => 99,
            'error' => false,
            'message' => 'Successfully Forgot Password, Please Check your Email'
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token, $state = "login")
    {
        $message = 'Successfully logged in';
        if($state != "login"){
            $message = 'Register Successfully';
        }
        return response()->json([
            'response_code' => 99,
            'error' => false,
            'message' => $message,
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60,
            'result' => auth()->user()
        ]);
    }
}
