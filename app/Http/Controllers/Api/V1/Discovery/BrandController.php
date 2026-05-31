<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Discovery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Discovery\BrandResource;
use App\Services\Discovery\BrandService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BrandController extends Controller
{
    public function __construct(
        private readonly BrandService $brandService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $brands = $this->brandService->getActive();

        return BrandResource::collection($brands);
    }
}
