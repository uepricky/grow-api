<?php

namespace App\Repositories\PrinterSetupRepository;

use Illuminate\Support\Carbon;
use App\Models\{
    PrinterSetup,
    Store
};
use Illuminate\Support\Collection;

interface PrinterSetupRepositoryInterface
{
    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * @param array $data
     * @return PrinterSetup
     */
    public function createPrinterSetUp(array $data): PrinterSetup;

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * @param Store $store
     * @return Collection
     */
    public function getStorePrinterSetups(Store $store): Collection;

    /***********************************************************
     * Update系
     ***********************************************************/

    /***********************************************************
     * Delete系
     ***********************************************************/
    /**
     * @param Store $store
     */
    public function deleteStorePrinterSetUps(Store $store): void;

    /***********************************************************
     * その他
     ***********************************************************/
}
