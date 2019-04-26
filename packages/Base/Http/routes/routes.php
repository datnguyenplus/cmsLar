<?php

Route::middleware(['admin'])->prefix(config('base.cms_path'))->group(function () {
	Route::get("language/{lang}",'AdminController@lang')->name('base.lang.get.setconfig');
});


