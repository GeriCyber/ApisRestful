<?php

namespace App\Http\Controllers\User;

use App\User;
use App\Product;
use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return response()->json(['data' => $users], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::USUARIO_NO_VERIFICADO;
        $data['verification_token'] = User::generateVerificationToken();
        $data['admin'] = User::USUARIO_REGULAR;

        $user = User::create($data);
        return response()->json(['data' => $user], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json(['data' => $user], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::USUARIO_ADMINISTRADOR . ',' .User::USUARIO_REGULAR,
        ];

        $this->validate($request, $rules);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email) {
            $user->verified = User::USUARIO_NO_VERIFICADO;
            $user->verification_token = User::generateVerificationToken();
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')) {
            if (!$user->esVerificado()) {
                return response()->json(['error' => 'Unicamente los usuarios verificados pueden cambiar su valor de administrador', 'code' => 409], 409);
                $user->admin = $request->admin;
            }
        }

        /*Si no hubieron cambios*/
        if(!$user->isDirty()) {
            return response()->json(['error' => 'Se debe especificar al menos un valor diferente para actualizar', 'code' => 422], 422);
        }

        $user->save();
        return response()->json(['data' => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /*$transactions = Transaction::has('buyer')->findOrFail($id)->get();
        if ($transactions->isEmpty()) {
            $user = User::findOrFail($id);
            $user->delete();
        } else {
            return response()->json(['error' => 'Error de clave foranea', 'code' => 422], 422);
        }*/
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['data' => $user], 200);
    }
}
