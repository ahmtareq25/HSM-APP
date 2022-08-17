<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends BaseModel
{
    use HasFactory;

    protected $table = 'pages';
    protected $guarded = [];

    public function submodules()
    {
        return $this->hasOne(\App\Models\Submodule::class, 'id', 'submodule_id');
    }

    public function modules()
    {
        return $this->hasOne(\App\Models\Module::class, 'id', 'module_id');
    }

    public function getByIds($ids){
        return self::query()->whereIn('id', $ids)->get();
    }
}
