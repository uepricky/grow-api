<?php

namespace App\Repositories\PrinterSetupRepository;

use Illuminate\Support\Carbon;
use App\Repositories\PrinterSetupRepository\PrinterSetupRepositoryInterface;
use App\Models\{
    PrinterSetup,
    Store
};
use Illuminate\Support\Collection;

class PrinterSetupRepository implements PrinterSetupRepositoryInterface
{
    private $model;

    public function __construct(PrinterSetup $printerSetup)
    {
        $this->model = $printerSetup;
    }

    /***********************************************************
     * Create系
     ***********************************************************/
    /**
     * @param array $data
     * @return PrinterSetup
     */
    public function createPrinterSetUp(array $data): PrinterSetup
    {
        return $this->model->create($data);
    }

    /***********************************************************
     * Read系
     ***********************************************************/
    /**
     * @param Store $store
     * @return Collection
     */
    public function getStorePrinterSetups(Store $store): Collection
    {
        return $store->printerSetups;
    }

    /***********************************************************
     * Update系
     ***********************************************************/

    /***********************************************************
     * Delete系
     ***********************************************************/
    /**
     * @param Store $store
     */
    public function deleteStorePrinterSetUps(Store $store): void
    {
        $this->model->where('store_id', $store->id)->delete();
    }

    /***********************************************************
     * その他
     ***********************************************************/
}
