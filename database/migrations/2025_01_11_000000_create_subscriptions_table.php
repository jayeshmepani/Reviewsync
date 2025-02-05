<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 8, 2)->default(0);
            $table->string('billing_cycle')->nullable();
            $table->integer('business_limit');
            $table->integer('ai_generated_replies');
            $table->timestamps();
        });

        DB::table('subscriptions')->insert([
            [
                'name' => 'Trial',
                'description' => 'Free trial for 1 month with access to up to 2 businesses',
                'price' => 0.00,
                'billing_cycle' => null,
                'business_limit' => 1,
                'ai_generated_replies' => 0,
            ],
            [
                'name' => 'Standard',
                'description' => 'Access to all businesses and AI replies for 1 year',
                'price' => 99.99,
                'billing_cycle' => 'Yearly',
                'business_limit' => 5,
                'ai_generated_replies' => 700,
            ],
            [
                'name' => 'Premium',
                'description' => 'Lifetime access to all businesses and AI replies',
                'price' => 999.99,
                'billing_cycle' => 'One-Time (Lifetime)',
                'business_limit' => -1,
                'ai_generated_replies' => -1,
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}