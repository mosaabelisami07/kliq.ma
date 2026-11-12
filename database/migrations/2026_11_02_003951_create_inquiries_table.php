<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_name')->nullable();
            $table->string('name', 120);
            $table->string('company', 120)->nullable();
            $table->string('email', 190)->index();
            $table->string('phone', 40)->nullable();
            $table->string('budget', 40)->nullable();
            $table->string('preferred_contact', 20)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new')->index();
            $table->text('notes')->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('submission_token', 64)->nullable()->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
