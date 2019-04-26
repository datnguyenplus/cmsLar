<?php
Route::middleware(['admin'])->prefix(config('base.cms_path'))->group(function () 
{
    Route::prefix('post')->group(function () {
    	Route::get('/','PostController@index')->name('admin.post.get.index');
    });
});
