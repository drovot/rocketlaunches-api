<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Response\Response;
use App\Supplier\AbstractSupplier;
use App\Supplier\SupplierManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class SupplierController extends BaseController
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
     * @param Request $request
     * @return JsonResponse
     */
    public function forcePull($supplier, Request $request): JsonResponse
    {
        $response = new Response();
        $check = $this->supplierManager->initializeSuppliers($supplier);
        $launches = [];

        $trackingId = $request->attributes->has('tracking_id') ? $request->attributes->get('tracking_id') : null;
        $response->setTrackingId($trackingId);

        if (!$check) {
            return $response->setStatusCode(400)->setErrorMessage(sprintf("Unknown Supplier '%s'", $supplier))->build();
        }

        /** @var AbstractSupplier $supplierModel */
        foreach ($this->supplierManager->getSuppliers() as $supplierModel) {
            $launches[] = $supplierModel->execute();
        }

        return $response->setResult($launches)->build();
    }
}
