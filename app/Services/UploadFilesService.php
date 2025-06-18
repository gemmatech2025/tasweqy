<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappSession;
class UploadFilesService
{

      public function uploadImage($image , $path){
        $image_path = $image->store($path , 'uploaded_images'); 
        $image_path = 'uploads/images/' . $image_path;
        return $image_path;
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

    
}