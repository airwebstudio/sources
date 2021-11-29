<?php

namespace App\Http\Controllers;

use App\Email\ApiEmail;
use App\Journal\Journal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'login_with_google', 'create', 'forgot_password','update_password']]);
    }

    /**
     * @OA\Post(
     *      tags={"user"},
     *      path="/api/user/login",
     *      summary="Login by email, password",
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *              @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zZWxsbXl0aW1lLnRlc3RcL2FwaVwvYXV0aFwvbG9naW4iLCJpYXQiOjE2MTM5NDk3ODAsImV4cCI6MTYxMzk1MzM4MCwibmJmIjoxNjEzOTQ5NzgwLCJqdGkiOiI1MTM5dm9FU3gxdjJGdURxIiwic3ViIjo0LCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.w3J1loLeiQRBWm9a5-mXaEuAf6-4KILJzSK1z0tnYlg"),
     *              @OA\Property(property="token_type", type="string", example="bearer"),
     *              @OA\Property(property="expires_in", type="int", example="3600"),
     *              @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *          )
     *      )
     * )
     *
     * Get a JWT via given credentials.
     *
     * @return JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::with(['avatar'])->find(auth()->user()->id);

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $user->id,
            'type'=> 'user_login',
            'description'=> $token,
            'details'=> $user->toJson(),
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }


    public function login_with_google()
    {
        try {
            info(request(['gd']));
            $google_id = request(['gd'])['gd']['googleId'];

            $user = User::where('google_id', $google_id)->first();

            if($user){
                $userToken=JWTAuth::fromUser($user);
                if($userToken){
                    Auth::login($user);
                    return response()->json([
                        'access_token' => $userToken,
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL() * 60,
                        'user' => $user
                    ]);
                }
            }else{
                $newUser = User::create([
                    'name' => request(['gd'])['gd']['At']['Ve'],
                    'email' => request(['gd'])['gd']['At']['ku'],
                    'google_id'=> $google_id,
                ]);
                $userToken=JWTAuth::fromUser($newUser);

                if($userToken){
                    Auth::login($newUser);
                    return response()->json([
                        'access_token' => $userToken,
                        'token_type' => 'bearer',
                        'expires_in' => auth()->factory()->getTTL() * 60,
                        'user' => $newUser
                    ]);
                }
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function login_with_facebook()
    {
        try {

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function login_with_apple()
    {
        try {

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
    /**
     * @OA\Put (
     *     tags={"user"},
     *     path="/api/user/create",
     *     summary="Registration",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="name, email, password",
     *          @OA\JsonContent(
     *              required={"name", "email","password"},
     *              @OA\Property(property="name", type="string", format="name", example="user1"),
     *              @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *              @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Successfully registration!")
     *          )
     *      )
     * )
     *
     * User registration
     */
    public function create()
    {
        $name = request('name');
        $email = request('email');
        $password = request('password');

        if(User::where('email', $email)->exists()){
            return response()->json(['error' => 'User with this email exists!']);
        }

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->save();

        $email = new ApiEmail;
        $email->send_email([
            'email'=>$user->email,
            'template_name'=> ApiEmail::EMAIL_TEMPLATE_WELCOME,
            'USER_NAME'=>$user->name
        ]);

        $Journal = new Journal;
        $Journal->add_event([
            'primary_id'=> $user->id,
            'secondary_id'=> $user->id,
            'type'=> 'user_create',
            'description'=> 'Successfully registration!',
            'details'=> $user->toJson(),
        ]);


        return response()->json(['message' => 'Successfully registration!']);
    }

    /**
     * @OA\Post(
     *     tags={"user"},
     *     path="/api/user",
     *     summary="Edit user",
     *
     *     @OA\RequestBody(
     *          required=true,
     *          description="name, email",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", format="name", example="user1"),
     *              @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Successfully updatet!")
     *          )
     *      )
     * )
     *
     * Edit user
     *
     * @return JsonResponse
     */
    public function edit()
    {

        $name = request('name');
        $email = request('email');
        $description = request('description');

        $user = auth()->user();
        if($user) {
            $user->name = $name ?: $user->name;
            $user->email = $email ?: $user->email;
            $user->description = $description ?: $user->description;
            $user->save();
            return response()->json(['message' => 'Successfully updatet!']);
        }
        return response()->json(['message' => 'User not found']);
    }

    /**
     * @OA\Get(
     *     path="/api/user/me",
     *     operationId="Get my info",
     *     tags={"user"},
     *     summary="Get my info",
     *     description="Get my info",
     *
     *     @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *      )
     * )
     *
     * Get the authenticated User.
     * @return JsonResponse
     */
    public function me()
    {
        $user = \App\Models\User::find(auth()->user()->id);

        return response()->json($user);
    }

    /**
     * @OA\Get(
     *     path="/api/user/logout",
     *     operationId="Logout",
     *     tags={"user"},
     *     summary="Logout",
     *     description="Logout",
     *     @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Successfully logged out")
     *          )
     *      )
     * )
     *
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Post(
     *     path="/api/user/refresh",
     *     operationId="Refresh",
     *     tags={"user"},
     *     summary="Refresh",
     *     description="Refresh",
     *     @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(

     *          )
     *      )
     * )
     *
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * @OA\Post(
     *     tags={"user"},
     *     path="/api/user/forgot_password",
     *     summary="Forgot password",
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="email", type="string", format="email", example="email"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(

     *          )
     *      )
     * )
     *
     * @return JsonResponse
     */
    public function forgot_password(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if(!$user){
            return response()->json(['message' => 'User not found!']);
        }

        $token = $this->createToken($request->email);

        $email = new ApiEmail;
        $email->send_email([
            'email'=>$request->email,
            'template_name'=> ApiEmail::EMAIL_TEMPLATE_FORGOT_PASSWORD,
            'reset_url'=>URL::to('/user/update-password/'.$token)
        ]);

        return response()->json(['message' => 'Please check your email']);

    }

    public function createToken($email){
        $isToken = DB::table('password_resets')->where('email', $email)->first();

        if($isToken) {
            return $isToken->token;
        }

        $token = Str::random(80);
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
        return $token;
    }

    /**
     * @OA\Post(
     *     tags={"user"},
     *     path="/api/user/update_password/",
     *     summary="Update password",
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="email", type="string", format="email", example="email"),
     *              @OA\Property(property="password", type="string", format="password", example="password"),
     *              @OA\Property(property="token", type="string", format="token", example="token"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="",
     *          @OA\JsonContent(

     *          )
     *      )
     * )
     *
     * @return JsonResponse
     */
    public function update_password(Request $request)
    {
        $validateToken = DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->token
        ]);
        if(!$validateToken->first()){
            return response()->json(['error' => 'not valid']);
        }

        $user = User::whereEmail($request->email)->first();
        $user->update([
            'password'=>bcrypt($request->password)
        ]);
        $validateToken->delete();
        return response()->json(['message' => 'Password changed successfully.']);

    }



}
