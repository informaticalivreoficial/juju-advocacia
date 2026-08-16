<?php

use App\Enums\ProcessAreaEnum;
use App\Enums\ProcessPriorityEnum;
use App\Enums\ProcessStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('process_number')->nullable();
            $table->string('title');
            $table->string('area')->default(ProcessAreaEnum::Other->value);
            $table->string('action_type')->nullable();
            $table->string('court')->nullable();
            $table->string('district')->nullable();
            $table->string('court_division')->nullable();
            $table->string('instance')->nullable();
            $table->string('plaintiff')->nullable();
            $table->string('defendant')->nullable();
            $table->decimal('case_value', 15, 2)->nullable();
            $table->date('distribution_date')->nullable();
            $table->string('status')->default(ProcessStatusEnum::Analysis->value);
            $table->string('priority')->default(ProcessPriorityEnum::Normal->value);
            $table->boolean('confidentiality')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('process_number');
            $table->index('status');
            $table->index('priority');
            $table->index('area');
            $table->index('client_id');
            $table->index('responsible_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};
