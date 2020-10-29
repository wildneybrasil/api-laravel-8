<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User as ModelsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {

        $validator = FacadesValidator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string',
        ]);

        if( $validator->fails() ){
            return responder()->error(422, $validator->errors())->respond(422);
        }

        $user = ModelsUser::create(
            array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            )
        );

        return responder()->success(['user' => $user])->respond(201);

    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {

        $messages = [
            'email.required' => 'O campo Email é obrigatório.',
            'password.required' => 'O campo Senha é obrigatório.',
        ];
        $validator = FacadesValidator::make(request(['email', 'password']), [
            'email' => 'required',
            'password' => 'required',
        ], $messages);
        
        if( $validator->fails() ){
            $aux = array();
            if( $validator->errors()->has('email') ){
                array_push($aux, ['fieldname' => 'email', 'message' => $validator->errors()->get('email')[0]]);
            }

            if( $validator->errors()->has('password') ){
                array_push($aux, ['fieldname' => 'password', 'message' => $validator->errors()->get('password')[0]]);
            }

            return responder()->error(422, 'Ocorreu um erro de validação')->data(['errors' => $aux])->respond(422);
        }

        $credentials = request(['email', 'password']);
        if( ! $token = auth()->attempt( $credentials ) ){
            $aux = array(['fieldname' => 'password', 'message' => 'Usuário ou senha inválidos']);
            return responder()->error(422, 'Ocorreu um erro de validação')->data(['errors' => $aux])->respond(422);
        }

        return responder()->success(['token' => $this->respondWithToken($token)])->respond();

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return responder()->success(['user' => auth()->user()])->respond();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return responder()->success()->meta(['message' => 'Logout efetuado com sucesso!'])->respond();
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return responder()->success(['token' => $this->respondWithToken(auth()->refresh())])->respond();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }

}