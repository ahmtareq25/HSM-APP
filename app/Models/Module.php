<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends BaseModel
{

    protected $fillable = [
        'id', 'name', 'icon', 'sequence'
    ];
    public function getModules(){
        $modules = $this->all();

        return $modules;
    }

    public function submodules(){
       return $this->hasMany(\App\Models\Submodule::class);
    }

    public function pages() {
        return $this->hasMany(\App\Models\Page::class);
    }
}
