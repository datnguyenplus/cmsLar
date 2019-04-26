<?php 

namespace TTSoft\Post\Repositories\Eloquent;

use TTSoft\Post\Repositories\Post\RepositoryInterface;

use TTSoft\Base\Repositories\Eloquent\EloquentRepository;
/**
* @return class name use repository
*/
class PostEloquentRepository extends EloquentRepository implements PostRepositoryInterface
{
    
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \TTSoft\Post\Entities\Post::class;
    }

    
}