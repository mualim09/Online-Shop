<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

trait FileUploadTrait
{

    /**
     * File upload trait used in controllers to upload files
     */

     public function saveFiles(Request $request)
    {

        $uploadPath = public_path(env('UPLOAD_PATH').'/uploads/product');
        $thumbPath = public_path(env('UPLOAD_PATH').'/uploads/thumb');
        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0775);
            mkdir($thumbPath, 0775);
        }

        $finalRequest = $request;

        foreach ($request->all() as $key => $value) {
            if ($request->hasFile($key)) {
                if ($request->has($key . '_max_width') && $request->has($key . '_max_height')) {
                    // Check file width
                    $filename = str_slug($request->name) . '-' . date("YmdHi") .'.jpg';
                    $file     = $request->file($key);
                    $image    = Image::make($file);
                    if (! file_exists($thumbPath)) {
                        mkdir($thumbPath, 0775, true);
                    }

                    $width  = $image->width();
                    $height = $image->height();
                    if ($width > $height) {
                        Image::make($file)->resize(100, null, function($constrait) {
                            $constrait->aspectRatio();
                        })->save($thumbPath . '/' . $filename); 
                    } elseif ($width < $height) {
                        Image::make($file)->resize(null, 100, function($constrait) {
                            $constrait->aspectRatio();
                        })->save($thumbPath . '/' . $filename);
                    } elseif ($width = $height) {
                        Image::make($file)->resize(100, 100)->save($thumbPath . '/' . $filename);
                    };
                    
                    if ($width > $request->{$key . '_max_width'} && $height > $request->{$key . '_max_height'}) {
                        $image->resize($request->{$key . '_max_width'}, $request->{$key . '_max_height'});
                    } elseif ($width > $request->{$key . '_max_width'}) {
                        $image->resize($request->{$key . '_max_width'}, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } elseif ($height > $request->{$key . '_max_width'}) {
                        $image->resize(null, $request->{$key . '_max_height'}, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                    $image->save($uploadPath . '/' . $filename);
                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                } else {
                    $filename = str_slug($request->name) . '-' . date("YmdHi") .'.jpg';
                    $request->file($key)->move($uploadPath, $filename);
                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                }
            }
        }

        return $finalRequest;
    }
}