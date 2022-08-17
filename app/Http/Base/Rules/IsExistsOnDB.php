<?php

namespace App\Http\Base\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class IsExistsOnDB implements Rule
{
    private string $table_name;
    private string $column_name;

    public function __construct($table_name, $column_name)
    {
        $this->table_name = $table_name;
        $this->column_name = $column_name;
    }

    public function passes($attribute, $value): bool
    {
        $existing_data = DB::table($this->table_name)->where($this->column_name, $value)->first();

        if (is_null($existing_data)) {
            return false;
        }

        setResource($this->table_name, $existing_data);
        return true;
    }

    public function message(): string
    {
        return ':attribute does not exists.';
    }
}
