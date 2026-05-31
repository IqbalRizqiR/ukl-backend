<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\StoreMessageRequest;
use App\Http\Resources\Chat\MessageResource;
use App\Services\Chat\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService,
    ) {}

    public function index(Request $request, string $conversationId): AnonymousResourceCollection
    {
        $messages = $this->messageService->getMessages(
            $conversationId,
            $request->integer('per_page', 50),
        );

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request, string $conversationId): JsonResponse
    {
        $message = $this->messageService->send(
            $conversationId,
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Pesan berhasil dikirim.',
            'data' => new MessageResource($message),
        ], 201);
    }

    public function markAsRead(Request $request, string $conversationId): JsonResponse
    {
        $count = $this->messageService->markAsRead($conversationId, $request->user()->id);

        return response()->json([
            'message' => "{$count} pesan ditandai sudah dibaca.",
        ]);
    }
}
