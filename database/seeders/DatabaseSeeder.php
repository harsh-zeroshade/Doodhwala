<?php

namespace Database\Seeders;

use App\Models\DailyAttendance;
use App\Models\ExtraOrder;
use App\Models\House;
use App\Models\LiveStatus;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Ramesh Sharma',
            'phone_number' => '9876543210',
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('password'),
        ]);

        $customers = [
            ['name' => 'Sharma Ji', 'phone' => '9876500001', 'address' => 'A-12 Green Colony · 1st floor', 'cow' => 1.5, 'buffalo' => 0.5, 'order' => 1],
            ['name' => 'Verma Aunty', 'phone' => '9876500002', 'address' => 'B-3 Shastri Nagar · Ground', 'cow' => 2.0, 'buffalo' => 0, 'order' => 2],
            ['name' => 'Gupta Family', 'phone' => '9876500003', 'address' => 'C-7 DLF Phase 2 · 2nd floor', 'cow' => 1.0, 'buffalo' => 1.0, 'order' => 3],
            ['name' => 'Tiwari Bhaiya', 'phone' => '9876500004', 'address' => 'Near Hanuman Mandir · Flat 2B', 'cow' => 0, 'buffalo' => 2.0, 'order' => 4],
            ['name' => 'Mehta Sahab', 'phone' => '9876500005', 'address' => 'D-1 Old Kathauta · Top floor', 'cow' => 2.0, 'buffalo' => 0, 'order' => 5],
        ];

        $houses = [];

        foreach ($customers as $customer) {
            $user = User::create([
                'name' => $customer['name'],
                'phone_number' => $customer['phone'],
                'role' => User::ROLE_CUSTOMER,
                'password' => Hash::make('password'),
            ]);

            $houses[] = House::create([
                'user_id' => $user->id,
                'customer_name' => $customer['name'],
                'address' => $customer['address'],
                'route_order' => $customer['order'],
                'default_cow_milk' => $customer['cow'],
                'default_buffalo_milk' => $customer['buffalo'],
            ]);
        }

        $cowPrice = config('doodhwala.cow_price_per_liter');
        $buffaloPrice = config('doodhwala.buffalo_price_per_liter');
        $month = now()->startOfMonth();

        foreach ($houses as $index => $house) {
            for ($day = 1; $day <= min(9, now()->day); $day++) {
                if ($index === 4 && $day === now()->day) {
                    continue;
                }

                if ($index === 4 && $day >= 6) {
                    continue;
                }

                DailyAttendance::create([
                    'house_id' => $house->id,
                    'delivery_date' => $month->copy()->day($day),
                    'status' => 'delivered',
                    'cow_milk_delivered' => $house->default_cow_milk,
                    'buffalo_milk_delivered' => $house->default_buffalo_milk,
                    'cow_price_per_liter' => $cowPrice,
                    'buffalo_price_per_liter' => $buffaloPrice,
                ]);
            }
        }

        if (now()->day >= 9) {
            ExtraOrder::create([
                'house_id' => $houses[4]->id,
                'order_date' => now()->toDateString(),
                'type' => 'hold',
                'status' => 'approved',
                'note' => 'Vacation',
            ]);
        }

        Payment::create([
            'house_id' => $houses[2]->id,
            'amount' => 2880,
            'payment_mode' => 'upi',
            'paid_at' => $month->copy()->day(5),
        ]);

        Payment::create([
            'house_id' => $houses[4]->id,
            'amount' => 3000,
            'payment_mode' => 'cash',
            'paid_at' => $month->copy()->day(3),
        ]);

        ExtraOrder::create([
            'house_id' => $houses[0]->id,
            'order_date' => $month->copy()->addDays(6),
            'type' => 'hold',
            'status' => 'approved',
            'note' => 'Family trip',
        ]);

        ExtraOrder::create([
            'house_id' => $houses[0]->id,
            'order_date' => $month->copy()->addDays(13),
            'type' => 'extra',
            'extra_cow_milk' => 2,
            'status' => 'pending',
            'note' => 'Pooja',
        ]);

        LiveStatus::create([
            'message' => 'On the Way 🛵',
            'created_at' => now()->subMinutes(8),
        ]);

        $this->command->info('Admin login: 9876543210 / password');
        $this->command->info('Customer login (Sharma Ji): 9876500001 / password');
    }
}
