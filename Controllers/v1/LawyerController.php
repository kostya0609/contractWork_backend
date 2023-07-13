<?php
namespace App\Modules\ContractWork\Controllers\v1;

use App\Modules\ContractWork\Action\v1\FileAction;
use App\Modules\ContractWork\Model\File;
use App\Modules\ContractWork\Model\LawyerComment;

use App\Modules\ContractWork\Model\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class LawyerController extends Controller{
    public function addComment(Request $request){

        $contract_id = $request->contract_id;
        $user_id     = $request->user_id;
        $comment     = $request->comment;
        $files = $request->file('files');

        if(!is_numeric($user_id) || !is_numeric($contract_id) || !$comment)
            return response()->json([
                'success'    => false,
                'message'   => 'Возникла ошибка, нет contract_id или user_id или текста комментария',
            ]);

        DB::beginTransaction();
        try {
            $lawyerCommentModel = new LawyerComment();
            $lawyerCommentModel->contract_id = $contract_id;
            $lawyerCommentModel->user_id     = $user_id;
            $lawyerCommentModel->date        = Carbon::now();
            $lawyerCommentModel->comment     = $comment;
            $lawyerCommentModel->save();

            if($files){
                foreach ($files as $item){
                    $newFile = new File();
                    FileAction::saveFile($newFile, $lawyerCommentModel->id, LawyerComment::class, 'Lawyer', $item, 'lawyer');
                }
            }

            $files_save = $lawyerCommentModel->files->map(function($file){
                return
                    '<span class="link_download_files"><a onclick="contractWorkLoadFile('.$file->id.',\''.$file->translated_name.'.' . $file->type_file . '\''.')" href="#'.$file->id.'">'.$file->original_name.'</a></span>';
            });

            $log = new Log();
            $logMessage = 'Добавлен комментарий юриста';
            $log->setLog(
                $contract_id,
                $user_id,
                $logMessage,
            );

            DB::commit();
            return response()->json([
                'success'     => true,
                'data'        => [
                    'comment_id' => $lawyerCommentModel->id,
                    'files'      => $files_save,
                ],
                'notify'      => [
                    'title'    => 'Добавление комментария',
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

    public function deleteComment(Request $request){

        $contract_id = $request->contract_id;
        $comment_id  = $request->comment_id;
        $user_id     = $request->user_id;

        if(!is_numeric($user_id) || !is_numeric($comment_id) || !is_numeric($contract_id))
            return response()->json([
                'success'    => false,
                'message'   => 'Возникла ошибка, нет $comment_id или user_id или нет contract_id' ,
            ]);

        DB::beginTransaction();
        try {

            FileAction::deleteFiles($comment_id,LawyerComment::class, 'lawyer');

            LawyerComment::find($comment_id)->delete();

            $log = new Log();
            $logMessage = 'Удален комментарий юриста';
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
                    'title'    => 'Удаление комментария',
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
