<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('preferred_language')->default('fr');
            $table->boolean('reduced_mobility')->default(false);
            $table->boolean('needs_shade')->default(false);
            $table->enum('physical_endurance', ['low', 'medium', 'high'])->default('medium');
            $table->enum('budget', ['budget', 'moderate', 'luxury'])->default('moderate');
            $table->enum('comfort_level', ['basic', 'standard', 'premium'])->default('standard');
            $table->json('allergies')->nullable(); // ['pollen', 'dust', 'animals']
            $table->json('tourism_preferences')->nullable(); // ['artisanat', 'jardins', 'musées', 'histoire']
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
