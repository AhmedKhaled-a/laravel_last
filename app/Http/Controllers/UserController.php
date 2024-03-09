<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        // dd($users);
        return view("users.index",['users'=>$users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("users.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request -> validate([
            'name' => 'required|string|max:100',
            'email' => 'required',
            'password' => 'required'
        ]);

        User::create([
            'name' => $request -> name,
            'email' => $request -> email,
            'password' => $request -> password
        ]);

        // or we can do this as a shortcut storing the validate into $data(array) then sending it to create function: 
        // $data = $request -> validate([
        //     'name' => 'required|string|max:100',
        //     'email' => 'required',
        //     'password' => 'required'
        // ]);

        // User::create($data);
        // return redirect(route('users.index'));

        return redirect(route('users.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        // dd($user);
        return view("users.show", ["user" => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        // dd($user);
        return view("users.edit", ["user" => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request -> validate([
            'name' => 'required|string|max:100',
            'email' => 'required',
            'password' => 'required'
        ]);

        User::findOrFail($id) -> update([
            'name' => $request -> name,
            'email' => $request -> email,
            'password' => $request -> password
        ]);
        return redirect(route('users.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->posts()->delete();
        $user->delete();
        return redirect(route('users.index'));
    }

    public function callback($provider){
        $providedUser  = Socialite::driver($provider)->user();
        // dd($providedUser );
        $existingUser = User::where('email', $providedUser->email)->first();

    if ($existingUser) {

        auth()->login($existingUser, true);
    } else {

        $newUser = User::create([
            'name' => $providedUser->name,
            'email' => $providedUser->email,
        ]);

        auth()->login($newUser, true);
    }
    return redirect('/dashboard');
    }
};
