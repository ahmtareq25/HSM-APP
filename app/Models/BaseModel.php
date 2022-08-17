<?php

namespace App\Models;

use App\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;

class BaseModel extends Model
{
    use HasFactory, BaseModelTrait;

    protected static function boot()
    {
        parent::boot();
        $cloumn_must_be_exits = 'company_id';
        $get_current_table_class_name = self::getTableName();
        $dont_need_to_check_table_list = self::table_list();

        // AUTH CHECK AND SOME TABLE DONT NEED TO VERRIFY IT
        if(Auth::check() && !in_array($get_current_table_class_name, $dont_need_to_check_table_list)){

            $login_company_id = Auth::user()->$cloumn_must_be_exits;
            // SSADMIN DONT NEED TO RUN THIS QURIES
            // GET TABLE CLOUMN NAME
            $get_table_cloumn_name = self::getTableCloumnName();
            if(in_array($cloumn_must_be_exits,$get_table_cloumn_name)){

                //FOR FETCHING DATA
                static::addGlobalScope('get_aml_data', function (Builder $builder) use ($cloumn_must_be_exits,$login_company_id,$get_current_table_class_name) {
                    $builder->where($get_current_table_class_name.'.'.$cloumn_must_be_exits,$login_company_id);
                });

                // FOR CREATE DATA
                static::creating(function ($builder) use ($cloumn_must_be_exits,$login_company_id) {
                    $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                });
                static::created(function ($builder) use ($cloumn_must_be_exits,$login_company_id) {
                    $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                });

                // FOR SAVE DATA
                static::saving(function ($builder) use ($cloumn_must_be_exits,$login_company_id) {
                    $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                });
                static::saved(function ($builder) use ($cloumn_must_be_exits,$login_company_id) {
                    $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                });

                // FOR UPDATEING DATA
                static::updating(function ($builder) use ($cloumn_must_be_exits,$login_company_id){
                    $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                });
                static::updated(function ($builder) use ($cloumn_must_be_exits,$login_company_id) {
                    $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                });

                // FOR DELETED DATA
                static::deleted(function ($builder) use ($cloumn_must_be_exits,$login_company_id){
                    $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                });
                static::deleting(function ($builder) use ($cloumn_must_be_exits,$login_company_id){
                    $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                });

                // FOR RESOTRED DATA
                // static::restoring(function ($builder) use ($cloumn_must_be_exits,$login_company_id){
                //     $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                // });
                // static::restored(function ($builder) use ($cloumn_must_be_exits,$login_company_id){
                //     $builder->$cloumn_must_be_exits = $login_company_id; //assigning value
                // });
            }

        }

    }

    // GETTING MODEL NAME
    protected static function getModelName(){
        return static::class;
    }
    // GET MODEL SORT NAME
    protected static function getModelShortName(){
        $reflection = new ReflectionClass(self::getModelName());
        return $reflection->getShortName();
    }
    // GETTING TABLE NAME
    protected static function getTableName(){
        return with(new static)->getTable();
    }
    // GETTING TABLE CLOUMN NAME
    protected static function getTableCloumnName(){
        return  Schema::getColumnListing(self::getTableName());
    }
    // THE TABLE LIST WHERE WE DONT NEED TO RUN THIS
    protected static function table_list(){
        // SYSTEM TABLE RAW TABLE NAME
        $system_table_list = [];
        // BUSINESS_TABLE
        $business_table_list = [];

        return array_merge($system_table_list,$business_table_list);
    }
}
