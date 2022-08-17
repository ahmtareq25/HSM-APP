<?php

namespace App\Traits;

trait BaseModelTrait
{
    // Pagination configuration
    private int $default_item_per_page = 10;
    private int $max_item_per_page = 100;

    protected function getItemPerPage(int|null $custom_item_per_page = null): int
    {
        if (is_null($custom_item_per_page)) {
            return $this->default_item_per_page;
        }

        if ($custom_item_per_page > $this->max_item_per_page) {
            return $this->max_item_per_page;
        }

        return $custom_item_per_page;
    }
}
