<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\ConversationResource;
use App\Models\Product;
use App\Services\Chat\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversationService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = $this->conversationService->getConversations($request->user()->id);

        return ConversationResource::collection($conversations);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'string', 'exists:products,id'],
        ]);

        $product = Product::findOrFail($request->input('product_id'));

        $conversation = $this->conversationService->findOrCreate(
            $request->user()->id,
            $product->seller_id,
            $product->id,
        );

        return response()->json([
            'message' => 'Percakapan berhasil dibuat.',
            'data' => new ConversationResource($conversation),
        ], 201);
    }
}
