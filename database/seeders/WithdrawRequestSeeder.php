<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\DB;

class WithdrawRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
WithdrawRequest::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        
    }
}
