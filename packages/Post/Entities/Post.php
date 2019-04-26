<?php

namespace TTSoft\Post\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $table = 'posts';

    protected $primaryKey = 'id';

    protected $dates = ['deleted_at'];
    
    protected $guarded = [];

    public $timestamps = true;

 
}
