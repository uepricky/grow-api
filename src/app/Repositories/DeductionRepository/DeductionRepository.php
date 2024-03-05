<?php

namespace App\Repositories\DeductionRepository;

use Illuminate\Support\Collection;
use App\Repositories\DeductionRepository\DeductionRepositoryInterface;
use App\Models\{
    Deduction,
    Store
};

class DeductionRepository implements DeductionRepositoryInterface
{
    public function __construct(Deduction $store)
    {
        $this->model = $store;
    }

    /***********************************************************
     * Create系
     ***********************************************************/


    /***********************************************************
     * Read系
     ***********************************************************/


    /***********************************************************
     * Update系
     ***********************************************************/


    /***********************************************************
     * Delete系
     ***********************************************************/


    /***********************************************************
     * その他
     ***********************************************************/
}
