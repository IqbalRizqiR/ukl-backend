<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserBankAccount;
use Illuminate\Database\Seeder;

class UserBankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::where('is_seller', true)->get();
        $banks = ['BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga', 'Bank Jago', 'SeaBank'];

        foreach ($sellers as $seller) {
            UserBankAccount::create([
                'user_id'             => $seller->id,
                'bank_name'           => fake()->randomElement($banks),
                'account_number'      => fake()->numerify('##########'),
                'account_holder_name' => strtoupper($seller->name),
                'is_default'          => true,
                'is_verified'         => true,
            ]);
        }
    }
}
