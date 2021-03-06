<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests;
use JWTAuth;
use Response;
use App\Repository\Transformers\UserTransformer;
use \Illuminate\Http\Response as Res;
use Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Http\Controllers\ApiController;



class UserController extends Controller
{
    
    /**
     * @var \App\Repository\Transformers\UserTransformer
     * */

     /**
     * @var App\Http\Controllers\ApiController
     * */

    protected $userTransformer;
    protected $ApiController;


    public function __construct(userTransformer $userTransformer, ApiController $ApiController)
    {
        $this->userTransformer = $userTransformer;
        $this->ApiController = $ApiController;

    }



    /**
     * @description: Api user authenticate method
     * @author: Joy Joel
     * @param: email, password
     * @return: Json String response
     */

    public function authenticate(Request $request)
    {

        $rules = array (

            'email' => 'required|email',
            'password' => 'required',

        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator-> fails()){

            return $this->ApiController->respondValidationError('Fields Validation Failed.', $validator->errors());

        }

        else{

            $user = User::where('email', $request['email'])->first();

            if($user){
                $api_token = $user->api_token;

                if ($api_token == NULL){
                    return $this->_login($request['email'], $request['password']);
                }

                try{

                    // $user = JWTAuth::toUser($api_token);
                    $user = auth()->user();
                    return $this->ApiController->respond([

                        'status' => 'success',
                        'status_code' => $this->ApiController->getStatusCode(),
                        'message' => 'Already logged in',
                        'user' => $this->userTransformer->transform($user)

                    ]);

                }catch(JWTException $e){

                    $user->api_token = NULL;
                    $user->save();

                    return $this->ApiController->respondInternalError("Login Unsuccessful. An error occurred while performing an action!");

                }
            }
            else{
                return $this->ApiController->respondWithError("Invalid Email or Password");
            }

        }

    }

    private function _login($email, $password)
    {

        $credentials = ['email' => $email, 'password' => $password];


        if ( !$token = JWTAuth::attempt($credentials)) {

            return $this->ApiController->respondWithError("User does not exist!");

        }
        else {

            // $user = JWTAuth::toUser($token);
            $user = auth()->user();

            $user->remember_token = $token;
            $user->save();

            return $this->ApiController->respond([

                'status' => 'success',
                'status_code' => $this->ApiController->getStatusCode(),
                'message' => 'Registration successful!',
                'data' => $this->userTransformer->transform($user)

            ]);
        }
    }

     /**
     * @description: Api user register method
     * @author: Joy Joel
     * @param: lastname, firstname, username, email, password
     * @return: Json String response
     */
    public function register(Request $request)
    {

        $rules = array (

            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'

        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator-> fails()){

            return $this->ApiController->respondValidationError('Fields Validation Failed.', $validator->errors());

        }

        else{

            $user = User::create([

                'name' => $request['name'],
                'email' => $request['email'],
                'password' => \Hash::make($request['password']),

            ]);

            return $this->_login($request['email'], $request['password']);

        }

    }


    /**
     * @description: Api user logout method
     * @author: Joy Joel
     * @param: null
     * @return: Json String response
     */
    public function logout($api_token)
    {

        try{

            // $user = JWTAuth::toUser($api_token);
                // dd($user);
            $user = auth()->user();

            // $user->remember_token = $api_token;

            JWTAuth::setToken($api_token)->invalidate();

            $this->ApiController->setStatusCode(Res::HTTP_OK);

            return $this->ApiController->respond([

                'status' => 'success',
                'status_code' => $this->ApiController->getStatusCode(),
                'message' => 'Logout successful!',

            ]);


        }catch(JWTException $e){

            return $this->ApiController->respondInternalError("An error occurred while performing an action!");

        }

    }






}
