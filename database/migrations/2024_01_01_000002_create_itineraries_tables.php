<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('trip_title');
            $table->enum('mode', ['simple', 'detailed'])->default('simple');
            $table->tinyInteger('eco_score')->unsigned()->default(0); // 0-100
            $table->tinyInteger('comfort_score')->unsigned()->default(0);
            $table->integer('duration_days');
            $table->json('user_preferences_snapshot'); // snapshot at generation time
            $table->json('health_snapshot');
            $table->string('status')->default('active'); // active | archived | favourite
            $table->timestamps();
        });

        Schema::create('itinerary_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day')->unsigned();
            $table->tinyInteger('order')->unsigned(); // order within the day
            $table->string('name');
            $table->text('description');
            $table->text('reason_responsible')->nullable();
            $table->text('health_reason')->nullable();
            $table->enum('effort_level', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('accessible')->default(true);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('image_query')->nullable();
            $table->timestamps();
        });

        Schema::create('health_conditions_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('itinerary_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('reduced_mobility');
            $table->boolean('needs_shade');
            $table->enum('physical_endurance', ['low', 'medium', 'high']);
            $table->json('allergies')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_conditions_history');
        Schema::dropIfExists('itinerary_places');
        Schema::dropIfExists('itineraries');
    }
};
