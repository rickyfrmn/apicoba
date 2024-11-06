<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;  
use Illuminate\Support\Facades\Storage;    

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return new PostResource(true, 'List data post', $posts);
    }

    public function store(Request $request)
    {
        \Log::info('Store method called', [
            'request' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data'    => $validator->errors()
            ], 422);  
        }

        try {
            // Upload gambar
            $image = $request->file('image');
            $imageName = $image->hashName();
            $image->storeAs('public/posts', $imageName);

            // Buat post baru
            $post = Post::create([
                'image'     => $imageName,
                'title'     => $request->title,    
                'content'   => $request->content,  
            ]);

            // Return response
            return new PostResource(true, 'Data Post berhasil ditambahkan', $post);

        } catch (\Exception $e) {
            
            if (isset($imageName)) {
                Storage::delete('public/posts/'.$imageName);
            }

            \Log::error('Error in store method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $post = Post::find($id);

        return new PostResource(true, 'Detail data', $post);
    }

    public function update(Request $request, Post $post)
    {
        \Log::info('update method', [
            'request' => $request->all(),
            'header' => $request->headers->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data'    => $validator->errors()
            ], 422);  
        }
        try {
            if($request->hasFile('image')) {
                Storage::delete('public/posts/'. $post->image);

                $image = $request->file('image');
                $imageName = $image->hashName();
                $image->storeAs('public/posts', $imageName);

                $post->update([
                    'image'     => $imageName,
                    'title'     => $request->title,    
                    'content'   => $request->content,
                ]);
            } else { 
                $post->update([
                    'title'     => $request->title,
                    'content'   => $request->content,
                ]);
            }
            return new PostResource(true, 'Data Post berhasil dirubah', $post);
        } catch (\Exception $e) {
            if (isset($imageName)) {
                Storage::delete('public/posts'.$imageName);
            }

            \Log::error('Error update' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error' . $e->getMessage(),
            ], 500);
        }

        
    }

    public function destroy(Post $post)
    {
        try {
            Storage::delete('public/posts/'. $post->image);

            $post->delete();

            return new PostResource(true, 'Data terhapus', null);
        } catch (\Exception $e) {
            \Log::error('Hapus error' . $e->getMessage());

            return response()->json([
                'success'   =>false,
                'message'   =>'Error' . $e->getMessage(),
            ], 500);
        }
    }
    
}