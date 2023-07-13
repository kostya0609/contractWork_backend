<?php
namespace App\Modules\ContractWork\Controllers\v1;

use Illuminate\Support\Facades\Route;

Route::middleware('check_auth')->prefix('contract-work')
    ->group(function(){
        Route::prefix('v1')->group(function (){

            Route::prefix('contracts')->group(function (){
                Route::post('create',ContractController::class.'@create');
                Route::post('edit',ContractController::class.'@edit');
                Route::post('list',ContractController::class.'@list');
                Route::post('need-action-list',ContractController::class.'@listNeedAction');
                Route::post('get',ContractController::class.'@get');
                Route::post('detail',ContractController::class.'@detail');
                Route::post('delete',ContractController::class.'@delete');
                Route::post('change-status',ContractController::class.'@changeStatus');
                Route::post('get-additional-info',ContractController::class.'@getAdditionalInfo');
                // ниже роуты для фильтра грида
                Route::post('get-contract-type',ContractController::class.'@getContractType')->withoutMiddleware('check_auth');
                Route::post('get-company-type',ContractController::class.'@getCompanyType')->withoutMiddleware('check_auth');
                Route::post('get-contract-direction',ContractController::class.'@getContractDirection')->withoutMiddleware('check_auth');
            });

            Route::prefix('search')->group(function (){
                Route::post('user',SearchController::class.'@user')->withoutMiddleware('check_auth');
                Route::post('contragent',SearchController::class.'@contragent')->withoutMiddleware('check_auth');
                Route::post('organization',SearchController::class.'@organization')->withoutMiddleware('check_auth');
                Route::post('department',SearchController::class.'@department')->withoutMiddleware('check_auth');
            });

            Route::prefix('transfer')->group(function (){
                Route::any('contragents',TransferController::class.'@getContrAgents');
                Route::any('organizations',TransferController::class.'@getOrganizations');
            });

            Route::prefix('files')->group(function(){
                Route::post('download',FilesController::class.'@download');
            });

            Route::prefix('actions')->group(function(){
                Route::post('update',NeedActionController::class.'@update');
                Route::post('badge',NeedActionController::class.'@badge');
            });

            Route::prefix('lawyer')->group(function(){
                Route::post('add-comment',LawyerController::class.'@addComment');
                Route::post('delete-comment',LawyerController::class.'@deleteComment');
            });

            Route::prefix('protocols')->group(function(){
                Route::post('add',ProtocolController::class.'@add');
                Route::post('delete',ProtocolController::class.'@delete');
            });

            Route::prefix('scans')->group(function(){
                Route::post('add',ScanController::class.'@add');
                Route::post('delete',ScanController::class.'@delete');
            });

            Route::prefix('logs')->group(function(){
                Route::post('get',LogController::class.'@get');
            });

            Route::prefix('roles')->group(function (){
                Route::post('add',RoleController::class.'@add');
                Route::post('list',RoleController::class.'@list');
                Route::post('delete',RoleController::class.'@delete');
                Route::post('get',RoleController::class.'@get');


                Route::prefix('additional')
                    ->group(function(){
                        Route::post('set', AdditionalRightsController::class.'@setAdditionalRights');
                        Route::post('list',AdditionalRightsController::class.'@listAdditionalRights');
                        Route::post('get', AdditionalRightsController::class.'@getAdditionalRights');
                    });
            });

            Route::prefix('users')->group(function (){
                Route::post('get',UserController::class.'@get');
            });


        });
    });

