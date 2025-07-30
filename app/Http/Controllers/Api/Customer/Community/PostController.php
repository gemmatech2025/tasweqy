<?php

namespace App\Http\Controllers\Api\Customer\Community;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\PostMedia;
use App\Models\HashtagPost;
use App\Models\Hashtag;


use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\Customer\Community\PostRequest;
use App\Http\Resources\Customer\Community\PostIndexResource;
use App\Http\Resources\Customer\Community\PostShowResource;

class PostController extends BaseController
{


    protected const RESOURCE = PostIndexResource::class;
    protected const RESOURCE_SHOW = PostShowResource::class;
    protected const REQUEST = PostRequest::class;

    public function model()
    {
        return   Post::class; 
    }




      public function store(Request $request)
    {
        $reqClass      = static::REQUEST;
        $effectiveRequest = $reqClass !== Request::class
            ? app($reqClass)
            : $request;

        $validated = method_exists($effectiveRequest, 'validated')
            ? $effectiveRequest->validated()
            : $effectiveRequest->all();

        DB::beginTransaction();
        try {
            $post = Post::create([
                'content'    => $request->content,
                'user_id'    => Auth::id()
            ]);


            if ($request->filled('hashtags')) {
            $hashtagIds = [];
            foreach ($request->hashtags as $hashtagName) {


                $normalized = strtolower(trim($hashtagName));
                $normalized = preg_replace('/\s+/', '_', $normalized);

                if (strlen($normalized) === 0) {
                    continue; 
                }

                $hashtag = Hashtag::firstOrCreate([
                    'name' => $normalized,
                ]);
                $hashtagIds[] = $hashtag->id;
            }
                $post->hashtags()->sync($hashtagIds);
            }



        if ($request->has('media')) {
            foreach ($request->media as $file) {
                $mime = $file->getMimeType();

                if (str_starts_with($mime, 'image/')) {
                    $type = 'image';
                } elseif (str_starts_with($mime, 'video/')) {
                    $type = 'video';
                } else {
                 jsonResponse(false, 422, __('messages.invalid_media_type'));
                }
                $path = $this->uploadImage($file , 'porsts/media');
                PostMedia::create([
                    'post_id' => $post->id,
                    'media'   => $path,
                    'type'    => $type,
                ]);
            }
        }

            DB::commit();

            return jsonResponse(
                true, 201, __('messages.add_success'),
                new PostIndexResource($post)
            );
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
    }



    public function sharePost(int $id)
    {
        DB::beginTransaction();
        try {
        $post = $this->getModel()->find($id);
        if (! $post) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }
        $post->share_count += 1;
        $post->save();
        DB::commit();
        return jsonResponse(true, 200, __('messages.shared_successfully'),);
    }catch (\Throwable $e) {
            DB::rollBack();
            return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
    }




    public function toggleLikePost(int $id)
    {


        DB::beginTransaction();
        try {

        $user = Auth::user();
        $post = $this->getModel()->find($id);
        if (! $post) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }


        $postLike = PostLike::where('post_id', $post->id)
                    ->where('user_id', $post->user->id)->first();


        if($postLike){
        $postLike->delete();
        $post->likes_count -= 1;
        $post->save();
        DB::commit();

        return jsonResponse(true, 200, __('messages.updated_successfully'),);
        }


        $postLike = PostLike::create(['user_id' => $user->id
        ,'post_id' => $post->id ]);

        $post->likes_count += 1;
        $post->save();
        DB::commit();

        return jsonResponse(true, 200, __('messages.updated_successfully'),);


    }catch (\Throwable $e) {
            DB::rollBack();
            return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
    }

    public function update(int $id, Request $request)
    {
        $reqClass      = static::REQUEST;
        $effectiveRequest = $reqClass !== Request::class
            ? app($reqClass)
            : $request;

        $validated = method_exists($effectiveRequest, 'validated')
            ? $effectiveRequest->validated()
            : $effectiveRequest->all();

        $excludeKeys = $this->uploadImages();
        $baseData    = array_diff_key($validated, array_flip($excludeKeys));
        DB::beginTransaction();
        try {


             $user = Auth::user();



            $post = $this->getModel()->find($id);
            if (! $post) {
                return jsonResponse(false, 404, __('messages.not_found'));
            }
            if($user->id != $post->user_id && $user->role != 'admin'){
                return jsonResponse(false, 401, __('messages.not_the_owner'));
            }




            $post->update($baseData);


            // HashtagPost::where('post_id' , $model->id)->delete();

            if ($request->filled('hashtags')) {
            $hashtagIds = [];
            foreach ($request->hashtags as $hashtagName) {


                $normalized = strtolower(trim($hashtagName));
                $normalized = preg_replace('/\s+/', '_', $normalized);

                if (strlen($normalized) === 0) {
                    continue; 
                }

                $hashtag = Hashtag::firstOrCreate([
                    'name' => $normalized,
                ]);
                $hashtagIds[] = $hashtag->id;
            }
                $post->hashtags()->sync($hashtagIds);
            }




            $oldMedia = PostMedia::where('post_id' , $post->id)->get();

            foreach ($oldMedia as $media) {
                $this->deleteImage($media->media);
                $media->delete();
            }



             if ($request->has('media')) {
            foreach ($request->media as $file) {
                $mime = $file->getMimeType();

                if (str_starts_with($mime, 'image/')) {
                    $type = 'image';
                } elseif (str_starts_with($mime, 'video/')) {
                    $type = 'video';
                } else {
                 jsonResponse(false, 422, __('messages.invalid_media_type'));
                }
                $path = $this->uploadImage($file , 'porsts/media');
                PostMedia::create([
                    'post_id' => $post->id,
                    'media'   => $path,
                    'type'    => $type,
                ]);
            }
        }







            DB::commit();
            return jsonResponse(
                true, 200, __('messages.update_success'),
                // new (static::RESOURCE)($model)
            );
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
    }








    public function delete(int $id)
    {
        DB::beginTransaction();
        try {

            $user = Auth::user();
            $post = $this->getModel()->find($id);
            if (! $post) {
                return jsonResponse(false, 404, __('messages.not_found'));
            }
            if($user->id != $post->user_id && $user->role != 'admin'){
                return jsonResponse(false, 401, __('messages.not_the_owner'));
            }

            $oldMedia = PostMedia::where('post_id' , $post->id)->get();

            foreach ($oldMedia as $media) {
                $this->deleteImage($media->media);
                $media->delete();
            }



            $post->delete();



    

            DB::commit();
            return jsonResponse(true, 200, __('messages.delete_success'));
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
    }



public function show(int $id)
{
    $model = $this->getModel()->find($id);

    if (!$model) {
        return jsonResponse(false, 404, __('messages.not_found'));
    }
    $model->increment('seen_count');

    return jsonResponse(
        true,
        200,
        __('messages.success'),
        new (static::RESOURCE_SHOW)($model)
    );
}







public function index(Request $request)
{

    $validated = $request->validate([
        'from_date'     => 'nullable|date_format:Y-m-d',
        'to_date'       => 'nullable|date_format:Y-m-d',
        'sort_order'    => 'in:asc,desc',
        'content_type'  => 'in:all,image,video',
        'interaction'   => 'in:all,most_liked,most_commented,most_shared',
    ]);

    $searchTerm   = trim($request->input('search', ''));
    $contentType  = $request->input('content_type', 'all'); // 'all', 'image', 'video'
    $from_date    = $request->input('from_date');
    $to_date      = $request->input('to_date');
    $interaction  = $request->input('interaction', 'all'); // 'most_liked', 'most_commented', 'most_shared'
    $sortBy       = $request->input('sort_by', 'id');
    $sortOrder    = $request->input('sort_order', 'desc');
    $perPage      = $request->input('per_page', 20);
    $page         = $request->input('page', 1);

    $query = Post::with(['user', 'hashtags', 'medias']);

    if ($searchTerm) {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('content', 'LIKE', '%' . $searchTerm . '%')
              ->orWhereHas('user', fn($q) => $q->where('name', 'LIKE', '%' . $searchTerm . '%'))
              ->orWhereHas('hashtags', fn($q) => $q->where('name', 'LIKE', '%' . $searchTerm . '%'));
        });
    }

    if ($from_date) {
        $query->whereDate('created_at', '>=', $from_date);
    }

    if ($to_date) {
        $query->whereDate('created_at', '<=', $to_date);
    }

    if (in_array($contentType, ['image', 'video'])) {
        $query->whereHas('medias', function ($q) use ($contentType) {
            $q->where('type', $contentType);
        });
    }

    if ($interaction === 'most_liked') {
        $query->withCount('likes')->orderBy('likes_count', 'desc');
    } elseif ($interaction === 'most_commented') {
        $query->withCount('comments')->orderBy('comments_count', 'desc');
    } elseif ($interaction === 'most_shared') {
        $query->orderBy('share_count', 'desc');
    } else {
        $query->orderBy($sortBy, $sortOrder);
    }

    $data = $query->paginate($perPage, ['*'], 'page', $page);

    $pagination = [
        'total'         => $data->total(),
        'current_page'  => $data->currentPage(),
        'per_page'      => $data->perPage(),
        'last_page'     => $data->lastPage(),
    ];

    return jsonResponse(
        true,
        200,
        __('messages.success'),
        (static::RESOURCE)::collection($data),
        $pagination
    );
}





}