<?php
namespace App\Modules\ContractWork\Action\v1;

use App\Modules\ContractWork\Model\File;
use Illuminate\Support\Str;

class FileAction{
    public static function saveFile($model, $fileable_id, $fileable_type,  $dir, $file, $type){
        $dir = '/Modules/ContractWork/Files/' . $dir;

        $original = str_replace('.' . $file->getClientOriginalExtension(),
            '', $file->getClientOriginalName());
        $translated = Str::slug($original, '_');
        $hash = md5($translated . date('YmdHis' . $type));

        $file->move(app_path() . $dir, $hash);

        $model->fileable_id      = $fileable_id;
        $model->fileable_type    = $fileable_type;
        $model->original_name    = $original;
        $model->translated_name  = $translated;
        $model->hash_name        = $hash;
        $model->dir              = $dir;
        $model->type             = $type;
        $model->type_file        = $file->getClientOriginalExtension();
        $model->save();

        return $model->id;
    }

    public static function deleteFiles($fileable_id, $fileable_type, $type){
        $files = File::where([ ['fileable_id', '=', $fileable_id], ['fileable_type', '=', $fileable_type] , ['type', '=', $type]])->get();

        foreach ($files as $file){
            if(file_exists(app_path() . $file->dir . '/' . $file->hash_name)){
                unlink(app_path() . $file->dir . '/' . $file->hash_name);
                $file->delete();
            }
        }

    }

    public static function updateFile($fileable_id, $fileable_type, $dir, $files, $type){
        //ниже актуализировтаь существующие файлы
        $file_save = [];
        if (isset($files['file_save'])) $file_save = $files['file_save'];

        $file_exists = File::where([ ['fileable_id', '=', $fileable_id], ['fileable_type', '=', $fileable_type], ['type', '=', $type] ])->get();

        $deleteFile = array_diff($file_exists->pluck('id')->toArray(),$file_save);

        foreach ($file_exists as $file){
            if(in_array($file->id,$deleteFile)){
                unlink(app_path() . $file->dir . '/' . $file->hash_name);
                $file->delete();
            }
        }

        // ниже добавить новые файлы если есть
        if (isset($files['file'])){
            foreach ($files['file'] as $item){
                $newFile = new File();
                FileAction::saveFile($newFile, $fileable_id, $fileable_type, $dir, $item, $type);
            };
        }

    }
}
