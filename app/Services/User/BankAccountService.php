<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\UserBankAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class BankAccountService
{
    /**
     * Get all bank accounts for a user.
     *
     * @param  string  $userId
     * @return Collection
     */
    public function getByUser(string $userId): Collection
    {
        return UserBankAccount::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Create a new bank account.
     *
     * @param  string  $userId
     * @param  array{bank_name: string, account_number: string, account_holder_name: string, is_default?: bool}  $data
     * @return UserBankAccount
     */
    public function create(string $userId, array $data): UserBankAccount
    {
        return DB::transaction(function () use ($userId, $data): UserBankAccount {
            $isDefault = $data['is_default'] ?? 'false';

            if ($isDefault === 'true') {
                $this->unsetDefaults($userId);
            }

            // If first bank account, make it default
            $count = UserBankAccount::where('user_id', $userId)->count();
            if ($count === 0) {
                $isDefault = 'true';
            }

            return UserBankAccount::create([
                ...$data,
                'user_id' => $userId,
                'is_default' => $isDefault,
            ]);
        });
    }

    /**
     * Delete a bank account (soft delete).
     *
     * @param  string  $userId
     * @param  string  $bankAccountId
     * @return bool
     */
    public function delete(string $userId, string $bankAccountId): bool
    {
        $account = UserBankAccount::where('id', $bankAccountId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return $account->delete();
    }

    /**
     * Set a bank account as default.
     *
     * @param  string  $userId
     * @param  string  $bankAccountId
     * @return UserBankAccount
     */
    public function setDefault(string $userId, string $bankAccountId): UserBankAccount
    {
        return DB::transaction(function () use ($userId, $bankAccountId): UserBankAccount {
            $this->unsetDefaults($userId);

            $account = UserBankAccount::where('id', $bankAccountId)
                ->where('user_id', $userId)
                ->firstOrFail();

            $account->update(['is_default' => 'true']);

            return $account->refresh();
        });
    }

    /**
     * Unset all default bank accounts for a user.
     */
    private function unsetDefaults(string $userId): void
    {
        UserBankAccount::where('user_id', $userId)
            ->where('is_default', 'true')
            ->get()
            ->each(fn ($account) => $account->update(['is_default' => 'false']));
    }
}
