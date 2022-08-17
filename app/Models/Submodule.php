<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submodule extends BaseModel
{
    use HasFactory;

    protected $table = 'submodules';
    protected $guarded = [];

    public function pages()
    {
        return $this->hasMany(\App\Models\Page::class);
    }

    public function modules()
    {
        return $this->hasOne(\App\Models\Module::class, 'id', 'module_id');
    }
}
