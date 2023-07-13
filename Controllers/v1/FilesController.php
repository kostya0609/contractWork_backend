<?php
namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Model\File;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FilesController extends Controller{
    public function download(Request $request){

        $file = File::find($request->file_id);

        $path = app_path().$file->dir.'/'.$file->hash_name;

        return response()->file($path);
    }

}
