<?php

namespace App\Models;

class CardInformation extends BaseModel
{
    protected $guarded = [];

    protected $table = 'card_informations';

    public function insertEntry($data){
        return self::create($data);
    }

    public function findByBrandToken($app_setting_id, $brand_token){
        $id = $this->extractIdFromBrandToken($brand_token);

        return self::query()
            ->where('id', $id)
            ->where('application_id', $app_setting_id)
            ->where('brand_token', $brand_token)
            ->latest()
            ->first();
    }

    public function findByHsmToken($hsm_token){
        return self::query()
            ->where('hsm_token', $hsm_token)
            ->latest()
            ->first();
    }

    public function deleteById($id){
        return self::destroy($id);
    }

    public function deleteByBrandToken($app_setting_id, $brand_token){
        return self::query()
            ->where('application_id', $app_setting_id)
            ->where('brand_token', $brand_token)
            ->latest()
            ->delete();
    }

    public function updateDataById($id, $data){
        return self::query()->where('id', $id)->update($data);
    }

    private function extractIdFromBrandToken($brand_token){
        $id = 0;
        $dataArr = explode('-', $brand_token);

        if (is_array($dataArr)){
            $id = ltrim($dataArr[0], '0');
        }
        return $id;
    }



}
