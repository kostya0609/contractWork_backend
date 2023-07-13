<?php
namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Action\v1\FileAction;
use App\Modules\ContractWork\Model\File;
use App\Modules\ContractWork\Model\Log;
use App\Modules\ContractWork\Model\Protocol;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ProtocolController extends Controller{
    public function add(Request $request){

        $contract_id = $request->contract_id;
        $user_id     = $request->user_id;
        $comment     = $request->comment;
        $files       = $request->file('files');

        if(!is_numeric($user_id) || !is_numeric($contract_id) || !$files || count($files) === 0)
            return response()->json([
                'success'    => false,
                'message'   => 'Возникла ошибка, нет contract_id или user_id или нет скана протокола',
            ]);

        $lastProtocol = Protocol::where([['contract_id','=',$contract_id]])
            ->orderBy('version')
            ->get()
            ->last();

        $version = $lastProtocol ? $lastProtocol->version + 1 : 1;

        DB::beginTransaction();
        try {
            $ProtocolModel = new Protocol();
            $ProtocolModel->contract_id = $contract_id;
            $ProtocolModel->user_id     = $user_id;
            $ProtocolModel->version     = $version;
            $ProtocolModel->date        = Carbon::now();
            $ProtocolModel->comment     = $comment ?: '';
            $ProtocolModel->save();

            if($files){
                foreach ($files as $item){
                    $newFile = new File();
                    FileAction::saveFile($newFile, $ProtocolModel->id, Protocol::class, 'Protocol', $item, 'protocol');
                }
            }

            $files_save = $ProtocolModel->files->map(function($file){
                return
                    '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$file->id.',\''.$file->translated_name.'.' . $file->type_file . '\''.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>';
            });

            $log = new Log();
            $logMessage = 'Добавлен протокол разногласий';
            $log->setLog(
                $contract_id,
                $user_id,
                $logMessage,
            );

            DB::commit();
            return response()->json([
                'success'     => true,
                'data'        => [
                    'comment_id' => $ProtocolModel->id,
                    'version'    => $version,
                    'files'      => $files_save,
                ],
                'notify'      => [
                    'title'    => 'Добавление протокола',
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

        $contract_id  = $request->contract_id;
        $protocol_id  = $request->protocol_id;
        $user_id      = $request->user_id;

        if(!is_numeric($user_id) || !is_numeric($protocol_id) || !is_numeric($contract_id) )
            return response()->json([
                'success'    => false,
                'message'   => 'Возникла ошибка, нет $protocol_id или user_id или нет contract_id' ,
            ]);

        DB::beginTransaction();
        try {

            FileAction::deleteFiles($protocol_id,Protocol::class, 'protocol');

            Protocol::find($protocol_id)->delete();

            $log = new Log();
            $logMessage = 'Удален протокол разногласий';
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
                    'title'    => 'Удаление протокола',
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
