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

use App\Http\Requests\Customer\Community\CommentRequest;
use App\Http\Resources\Customer\Community\CommentResource;
// use App\Http\Resources\Customer\Community\PostShowResource;

class PostCommentController extends BaseController
{


    protected const RESOURCE = CommentResource::class;
    protected const RESOURCE_SHOW = CommentResource::class;
    protected const REQUEST = CommentRequest::class;

    public function model()
    {
        return   PostComment::class; 
    }

    public function storeDefaultValues()
    {
        return ['user_id' => Auth::id()];
    }

    public function indexPaginat()
    {
        return true;
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
            $excludeKeys   = $this->uploadImages();
            $baseData      = array_diff_key($validated, array_flip($excludeKeys));
            $images        = $this->uploadImageDynamically($effectiveRequest, $excludeKeys);

            $baseData = array_merge($baseData, $this->storeDefaultValues());

            $model = $this->getModel()->create(array_merge($baseData, ...$images));

            $post = $model->post;
            $post->increment('comments_count');

            $this->storeChildren($model, $effectiveRequest);

            DB::commit();

            return jsonResponse(
                true, 201, __('messages.add_success'),
                new (static::RESOURCE)($model)
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
            $comment = $this->getModel()->find($id);
            if (! $comment) {
                return jsonResponse(false, 404, __('messages.not_found'));
            }
            if($user->id != $comment->user_id && $user->role != 'admin'){
                return jsonResponse(false, 401, __('messages.not_the_owner'));
            }

            $post = $comment->post;
            $post->comments_count -= 1;
            $post->save();

            $comment->delete();
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


    



public function getCommentsByPost(Request $request , $id)
{

    $searchTerm = trim($request->input('search', ''));
    $filters = $request->input('filter', []);
    $sortBy = $request->input('sort_by', 'id');
    $sortOrder = $request->input('sort_order', 'asc');

      $post = Post::find($id);

    if (!$post) {
        return jsonResponse(false, 404, __('messages.not_found'));
    }



    $query = $this->getModel()->where('post_id' , $post->id);
    $columns = \Schema::getColumnListing($this->getModel()->getTable());
    

    $filters = array_map(function ($value) {
        if (is_string($value)) {
            $lower = strtolower($value);
            return match ($lower) {
                'true' => 1,
                'false' => 0,
                default => is_numeric($value) ? $value + 0 : $value,
            };
        }
        return $value;
    }, $filters);


  


    $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

    foreach ($filters as $key => $value) {
        if (in_array($key, $columns)) {
            $query->where($key, $value);
        }
    }



    if (!empty($searchTerm)) {
        $searchableFields = $this->getSearchableFields();
        $query->where(function ($q) use ($searchableFields, $searchTerm) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
            }
        });
    }

    if ($sortBy && $sortOrder) {
        $query->orderBy($sortBy, $sortOrder);
    } else {
        foreach ($this->getSort() as $sort) {
            $query->orderBy($sort['sort'], $sort['order']);
        }
    }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);


        $query->orderBy('created_at', 'desc');

        $data = $query->paginate($perPage, ['*'], 'page', $page);

        $pagination = [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
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