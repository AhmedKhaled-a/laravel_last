<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;



class PostController extends Controller
{
    public function index()
    {
        $posts = Post::paginate(2);
        return view('posts.index', compact('posts'));
    }
    

    public function create()
    {
        $users = User::all();
        $loggedInUser = Auth::user();
        return view('posts.create', ['users'=>$users, 'authUser'=>$loggedInUser]);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'user_id' => 'required|exists:users,id',   // exists means a user_id must exists in the table users in column id
            'img' => 'image|mimes:png,jpg|max:2048',    // mimes are extensions,  max is size in kilobytes
        ]);

        // image upload handeling
        // move uploaded img to public after using  php artisan storage:link
        if($request->hasFile('img')){
            $fileName = Storage::putFile('public/postsImgs',$validatedData['img']);   // putFileAs() to choose a name for the file
            $validatedData['img'] = $fileName ;
        }
        


        $post = Post::create($validatedData);
        event(new \App\Events\UpdatePostsCount($post));

        return redirect()->route('posts.index');
    }


    public function show(Post $post)
    {
        return view('posts.show', ['post' => $post]);
    }


    public function edit($id)
    {
        $post = Post::findOrFail($id);
        $users = User::all();
        $loggedInUser = Auth::user();
        if ($loggedInUser->id != $post->user_id) {
            return redirect()->route('posts.index')->with('message', 'You are not allowed to edit this post');;
        } else {
            return view('posts.edit', compact('post', 'users'));
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        
        $post = Post::findOrFail($id);
        $post->update($request->all());

        return redirect()->route('posts.index');
    }


    public function destroy($id)
    {
    $post = Post::findOrFail($id);
    Storage::delete($post->img);
    $post->delete();
    return redirect()->route('posts.index');
    }
}
