<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Response\Response;
use App\Http\Search\SearchManager;
use App\Models\AbstractModel;
use App\Supplier\AbstractSupplier;
use App\Supplier\SupplierManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController
{

    /** @var SupplierManager  */
    private SupplierManager $supplierManager;

    /**
     * SupplierController constructor.
     */
    public function __construct()
    {
        $this->supplierManager = new SupplierManager();
    }

    /**
     * @param $supplier
     * @return JsonResponse
     */
    public function forcePull($supplier): JsonResponse
    {
        $response = new Response();
        $check = $this->supplierManager->initializeSuppliers($supplier);
        $launches = [];

        if (!$check) {
            return $response->setStatusCode(400)->setErrorMessage(sprintf("Unknown Supplier Type '%s'", $supplier))->build();
        }

        /** @var AbstractSupplier $supplier */
        foreach ($this->supplierManager->getSuppliers() as $supplierModel) {
            $launches[] = $supplierModel->execute();
        }

        return $response->setResult($launches)->build();
    }
}
