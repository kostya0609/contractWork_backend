<?php
namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Action\v1\FileAction;
use App\Modules\ContractWork\Model\File;
use App\Modules\ContractWork\Model\LawyerComment;

use App\Modules\ContractWork\Model\Log;
use App\Modules\ContractWork\Model\Scan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ScanController extends Controller{
    public function add(Request $request){

        $contract_id = $request->contract_id;
        $user_id     = $request->user_id;
        $comment     = $request->comment;
        $files       = $request->file('files');

        if(!is_numeric($user_id) || !is_numeric($contract_id) || !$files || count($files) === 0)
            return response()->json([
                'success'    => false,
                'message'   => 'Возникла ошибка, нет contract_id или user_id или нет скана оригинала документа',
            ]);

        DB::beginTransaction();
        try {
            $scanModel = new Scan();
            $scanModel->contract_id = $contract_id;
            $scanModel->user_id     = $user_id;
            $scanModel->date        = Carbon::now();
            $scanModel->comment     = $comment;
            $scanModel->save();

            if($files){
                foreach ($files as $item){
                    $newFile = new File();
                    FileAction::saveFile($newFile, $scanModel->id, Scan::class, 'Scan', $item, 'scan');
                }
            }

            $files_save = $scanModel->files->map(function($file){
                return
                    '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$file->id.',\''.$file->translated_name.'.' . $file->type_file . '\''.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>';
            });

            $log = new Log();
            $logMessage = 'Добавлен скана оригинала документа';
            $log->setLog(
                $contract_id,
                $user_id,
                $logMessage,
            );

            DB::commit();
            return response()->json([
                'success'     => true,
                'data'        => [
                    'comment_id' => $scanModel->id,
                    'files'      => $files_save,
                ],
                'notify'      => [
                    'title'    => 'Добавление скана оригинала документа',
                    'message'  => "Успешно",
                    'type'     => 'success',
                    'duration' => 1000,
                ]
            ]);
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function delete(Request $request){

        $contract_id = $request->contract_id;
        $scan_id     = $request->scan_id;
        $user_id     = $request->user_id;

        if(!is_numeric($user_id) || !is_numeric($scan_id) || !is_numeric($contract_id))
            return response()->json([
                'success'    => false,
                'message'   => 'Возникла ошибка, нет $scan_id или user_id или нет contract_id' ,
            ]);

        DB::beginTransaction();
        try {

            FileAction::deleteFiles($scan_id,Scan::class, 'scan');

            Scan::find($scan_id)->delete();

            $log = new Log();
            $logMessage = 'Удален скан оригинала документа';
            $log->setLog(
                $contract_id,
                $user_id,
                $logMessage,
            );

            DB::commit();
            return response()->json([
                'success'     => true,
                'data'        => [],
                'notify'      => [
                    'title'    => 'Удаление скана оригинала документа',
                    'message'  => "Успешно",
                    'type'     => 'success',
                    'duration' => 1000,
                ]
            ]);
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
