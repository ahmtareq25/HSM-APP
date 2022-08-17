<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTypeSubmodules extends BaseModel
{
    use HasFactory;

    protected $table = 'user_type_submodules';
    protected $guarded = [];

    public function sub_module(){
        return $this->belongsTo('App\Models\Submodule','submodule_id');
    }
    public function pages(){
        return $this->hasMany(\App\Models\Page::class);
    }
}
