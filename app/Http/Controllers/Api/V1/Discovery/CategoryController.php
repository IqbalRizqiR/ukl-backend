<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Discovery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Discovery\CategoryResource;
use App\Services\Discovery\CategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $categories = $this->categoryService->getActive();

        return CategoryResource::collection($categories);
    }
}
