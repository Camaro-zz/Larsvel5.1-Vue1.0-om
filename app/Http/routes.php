<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
$authApiMiddleware = 'auth.api';
Route::get('/', 'HomeController@index');

Route::get('captcha.json', 'Auth\AuthController@getCaptcha');

//用户验证
Route::group(['prefix' => 'auth'], function(){
    $Auth = 'Auth\AuthController@';

    Route::post('login.json', $Auth . 'postLogin');//登录
    Route::get('logout.json', $Auth . 'getLogout');//登出
    Route::get('resetpwd', 'Auth\PasswordController@resetPwd');//重设密码
});

Route::group(['middleware'=>$authApiMiddleware],function(){
    Route::put('pass.json', ['as' => 'userPass', 'uses' => 'Users\UsersController@putPassword']);//修改密码

    Route::get('suppliers.json', 'Supplier\SupplierController@getSuppliers');//供应商列表

    Route::get('goods.json', 'Goods\GoodsController@getGoodses');//供应商详情
});

//商品模块
Route::group(['prefix' => 'goods','middleware'=>$authApiMiddleware], function(){
    Route::post('add.json', 'Goods\GoodsController@postGoods');//添加商品
    Route::put('{id}.json', 'Goods\GoodsController@putGoods');//编辑商品
    Route::get('{id}.json', 'Goods\GoodsController@getGoods');//商品详情
    Route::delete('batch.json', 'Goods\GoodsController@deleteGoodses');//商品删除

    Route::post('cat/add.json', 'Goods\GoodsCatController@postCat');//添加商品类目
});

//供应商模块
Route::group(['prefix' => 'supplier','middleware'=>$authApiMiddleware],function(){
    Route::post('add.json', 'Supplier\SupplierController@postSupplier');//添加供应商
    Route::put('sort.json', 'Supplier\SupplierController@putSort');//修改供应商排序
    Route::put('{id}.json', 'Supplier\SupplierController@putSupplier');//修改供应商
    Route::get('{id}.json', 'Supplier\SupplierController@getSupplier');//供应商详情
});