<?php

namespace App\Http\Controllers\BasController;


use App\Contracts\CrudInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
 use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;



abstract class BaseController implements BaseControllerInterface
{


    protected const RESOURCE = JsonResource::class;
    protected const RESOURCE_SHOW = JsonResource::class;
    protected const REQUEST = Request::class;
    // protected const REQUEST_UPDATE = Request::class;


    protected $model;

    protected function getModel(): Model
    {
         return app($this->model);
    }

    public function __construct()
    {
        // dd($this->model());

        $this->model = $this->model();

    }
    abstract public function model();


    public function getFilters()
    {
        return [];
    }

    public function getRelations()
    {
        return [];
    }


    public function getSort()
    {
        return [];
    }


    public function indexPaginat()
    {
        return false;
    }

    public function getSearchableFields()
    {
        return [];
    }

public function index(Request $request)
{

    $searchTerm = trim($request->input('search', ''));
    $filters = $request->input('filter', []);
    $sortBy = $request->input('sort_by', 'id');
    $sortOrder = $request->input('sort_order', 'asc');
    $query = $this->getModel()->with($this->getRelations());
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

    if ($this->indexPaginat()) {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
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

    return jsonResponse(
        true,
        200,
        __('messages.success'),
        (static::RESOURCE)::collection($query->get())
    );
}





    public function uploadImages()
    {
        return [];
    }

    public function MultipleChildren()
    {
        return [];
    }

    public function storeDefaultValues()
    {

        return [];
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
            $model = $this->getModel()->find($id);
            if (! $model) {
                return jsonResponse(false, 404, __('messages.not_found'));
            }
            $this->updateChildren($model, $effectiveRequest);

            $images = $this->updateImageDynamically($effectiveRequest, $excludeKeys, $model);

            $model->update(array_merge($baseData, ...$images));

            DB::commit();
            return jsonResponse(
                true, 200, __('messages.update_success'),
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
    $model = $this->getModel()->find($id);

    if (!$model) {
        return jsonResponse(false, 404, __('messages.not_found'));
    }

    $toBeDeleted = $this->uploadImages();

    // Delete associated images and related children
    $this->deleteImageDynamically($model, $toBeDeleted);
    $this->deleteChildren($model->id);

    // Delete the model itself
    $model->delete();

    return jsonResponse(true, 200, __('messages.delete_success'));
}



    public function showRelations()
    {

        return [];
    }

public function show(int $id)
{
    $relations = $this->showRelations();
    $model = $this->getModel()->with($relations)->find($id);

    if (!$model) {
        return jsonResponse(false, 404, __('messages.not_found'));
    }

    return jsonResponse(
        true,
        200,
        __('messages.success'),
        new (static::RESOURCE_SHOW)($model)
    );
}


    public function updateImageDynamically(Request $request, $images, $model)
    {

        $images_to_be_stored = [];
        foreach ($images as $image) {
            $image_path = '';
            if ($request->hasFile($image)) {
                $imagePath = $model->$image;
                if ($imagePath) {
                    $this->deleteImage($imagePath);
                }
                $myImage = $request->file($image);
                $image_path = $this->uploadImage($myImage, $this->getModel()->getTable());
                $images_to_be_stored[] = [
                    $image => $image_path,
                ];
            }
        }
        return $images_to_be_stored;
    }


    public function uploadImageDynamically(Request $request, $images)
    {

        $images_to_be_stored = [];
        foreach ($images as $image) {
            $image_path = '';
            if ($request->hasFile($image)) {
                $myImage = $request->file($image);
                $image_path = $this->uploadImage($myImage, $this->getModel()->getTable());
                $images_to_be_stored[] = [
                    $image => $image_path,
                ];
            }
        }
        return $images_to_be_stored;
    }
    public function deleteImageDynamically($model, $images)
    {

        foreach ($images as $image) {
            $imagePath = $model->$image;
            if ($imagePath) {
                $this->deleteImage($imagePath);
            }
        }
    }

    public function deleteImage($imagePath)
    {
        $relativePath = str_replace('uploads/images/', '', $imagePath);
        if (Storage::disk('uploaded_images')->exists($relativePath)) {
            Storage::disk('uploaded_images')->delete($relativePath);
            return true;
        }
        return false;
    }


/**
 * @param  UploadedFile  $file
 * @param  string        $subfolder  // e.g. 'users' or 'products'
 * @return string        // relative path to saved file
 */
public function uploadImage(UploadedFile $file, string $subfolder): string
{
    // 1) Build a safe filename
    $name     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $slug     = Str::slug($name);
    $ext      = $file->getClientOriginalExtension();
    $filename = "{$slug}-" . time() . ".{$ext}";

    // 2) Determine destination directory under public/uploads/images
    $destination = public_path("uploads/images/{$subfolder}");

    // 3) Ensure directory exists
    if (! File::exists($destination)) {
        File::makeDirectory($destination, 0755, true);
    }

    // 4) Move the uploaded file
    $file->move($destination, $filename);

    // 5) Return the public-relative path
    return "uploads/images/{$subfolder}/{$filename}";
}



    public function modelName()
    {

    }

    public function storeChildren($model, Request $request)
    {
        $children = $this->MultipleChildren();
        foreach ($children as $child) {
            $data = $request->all();
            if ($request->has($child['name'])) {
                foreach ($data[$child['name']] as $index => $obj) {
                    $myModel = new $child['model']();
                    if (isset($child['attr']) && is_array($child['attr'])) {
                        foreach ($child['attr'] as $attribute) {
                            if (isset($obj[$attribute])) {
                                $myModel->$attribute = $obj[$attribute];
                            }
                        }
                    }
                    if (isset($child['images']) && is_array($child['images'])) {
                        foreach ($child['images'] as $image) {
                            $image_path = '';
                            if ($request->hasFile("{$child['name']}.{$index}.{$image}")) {
                                $myImage = $request->file("{$child['name']}.{$index}.{$image}");
                                $image_path = $this->uploadImage($myImage, $this->getModel()->getTable() . '/' . $myModel->getTable());
                            }

                            $myModel->$image = $image_path;
                        }
                    }
                    $myModel->{$child['parent']} = $model->id;
                    $myModel->save();
                }
            }
        }
    }


    public function updateChildren($model, Request $request)
    {
        $children = $this->MultipleChildren();
        foreach ($children as $child) {


            if ($child['update_scenario'] == 'delete_old') {
                $old_children = $child['model']::where($child['parent'], $model->id)->get();

                foreach ($old_children as $old_child) {
                    foreach ($child['images'] as $image) {
                        $this->deleteImage($old_child->$image);
                    }

                    $old_child->delete();
                }

                if ($request->has($child['name'])) {
                    $data = $request->all();

                    foreach ($data[$child['name']] as $index => $obj) {
                        $myModel = new $child['model']();
                        if (isset($child['attr']) && is_array($child['attr'])) {
                            foreach ($child['attr'] as $attribute) {
                                if (isset($obj[$attribute])) {
                                    $myModel->$attribute = $obj[$attribute];
                                }
                            }
                        }

                        if (isset($child['images']) && is_array($child['images'])) {
                            foreach ($child['images'] as $image) {
                                $image_path = '';
                                if ($request->hasFile("{$child['name']}.{$index}.{$image}")) {
                                    $myImage = $request->file("{$child['name']}.{$index}.{$image}");
                                    $image_path = $this->uploadImage($myImage, $this->getModel()->getTable() . '/' . $myModel->getTable());
                                }
                                $myModel->$image = $image_path;
                            }
                        }
                        $myModel->{$child['parent']} = $model->id;
                        $myModel->save();
                    }
                }

            } else if ($child['update_scenario'] == 'update_old') {

                if ($request->has($child['name'])) {
                    $data = $request->all();

                    foreach ($data[$child['name']] as $index => $obj) {

                        $is_new = isset($obj['id']) ? false : true;
                        $myModel = !$is_new ? $child['model']::find($obj['id']) : new $child['model']();

                        if ($myModel) {
                            if (isset($child['attr']) && is_array($child['attr'])) {
                                foreach ($child['attr'] as $attribute) {
                                    if (isset($obj[$attribute])) {
                                        $myModel->$attribute = $obj[$attribute];
                                    }
                                }
                            }

                            if (isset($child['images']) && is_array($child['images'])) {
                                foreach ($child['images'] as $image) {
                                    $image_path = '';
                                    if ($is_new) {
                                        if ($request->hasFile("{$child['name']}.{$index}.{$image}")) {
                                            $myImage = $request->file("{$child['name']}.{$index}.{$image}");
                                            $image_path = $this->uploadImage($myImage, $this->getModel()->getTable() . '/' . $myModel->getTable());
                                        }
                                    } else {
                                        $image_path = $myModel->$image;
                                        if ($request->hasFile("{$child['name']}.{$index}.{$image}")) {
                                            $this->deleteImage($image_path);
                                            $myImage = $request->file("{$child['name']}.{$index}.{$image}");
                                            $image_path = $this->uploadImage($myImage, $this->getModel()->getTable() . '/' . $myModel->getTable());
                                        }
                                    }
                                    $myModel->$image = $image_path;
                                }
                            }
                            if ($is_new) {
                                $myModel->{$child['parent']} = $model->id;
                            }
                            $myModel->save();
                        }
                    }
                }
            }
        }
    }



    public function deleteChildren($parent_id)
    {
        $children = $this->MultipleChildren();
        foreach ($children as $child) {
            $old_children = $child['model']::where($child['parent'], $parent_id)->get();
            foreach ($old_children as $old_child) {
                foreach ($child['images'] as $image) {
                    $this->deleteImage($old_child->$image);
                }
                $old_child->delete();
            }

        }
    }
}
