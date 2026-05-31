<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreAddressRequest;
use App\Http\Resources\User\AddressResource;
use App\Services\User\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AddressController extends Controller
{
    public function __construct(
        private readonly AddressService $addressService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = $this->addressService->getByUser($request->user()->id);

        return AddressResource::collection($addresses);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressService->create(
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => new AddressResource($address),
        ], 201);
    }

    public function update(StoreAddressRequest $request, string $addressId): JsonResponse
    {
        $address = $this->addressService->update(
            $request->user()->id,
            $addressId,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
            'data' => new AddressResource($address),
        ]);
    }

    public function destroy(Request $request, string $addressId): JsonResponse
    {
        $this->addressService->delete($request->user()->id, $addressId);

        return response()->json([
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }
}
