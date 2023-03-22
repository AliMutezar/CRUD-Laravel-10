<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        
        // get posts
        $posts = Post::latest()->paginate(5);
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {

        // Validate Form
        $this->validate($request, [
            'image'     =>  'required|image|mimes:jpeg, jpg, png|max:2048',
            'title'     =>  'required|min:5',
            'content'   =>  'required|min:10'
        ]);

        // Upload Image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // Create Post
        Post::create([
            'image'     =>  $image->hashName(),
            'title'     =>  $request->title,
            'content'   =>  $request->content,
        ]);

        // Redirect to Index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Disimpan']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        //get post by ID
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        //get post by ID
        $post = Post::findOrFail($id);
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'image'     =>  'images|mimes:jpeg, jpg, png|max:2048',
            'title'     =>  'required|min:5',
            'content'   =>  'required|min:10'
        ]);

        // get post by ID
        $post = Post::findOrFail($id);

        // Check if image is uploaded
        if($request->hasFile('image')) {

            // upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());


            // delete old image
            Storage::delete('public/posts/' . $post->image);

            // update post with new image
            $post->update([
                'image'   =>  $image->hasName(),
                'title'   =>  $request->title,
                'content' =>  $request->content
            ]);

        } else {
            
            // update without image
            $post->update([
                'title'     =>  $request->title,
                'content'   =>  $request->content
            ]); 
        }

        // redirect to index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Diupdate']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        // get post by ID
        $post = Post::findOrFail($id);
        Storage::delete('public/posts/' . $post->image);

        $post->delete();
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Dihapus']);
    }
}
