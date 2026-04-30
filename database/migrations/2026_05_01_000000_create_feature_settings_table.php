<?php

use App\Models\FeatureSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key');
            $table->string('role');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['feature_key', 'role']);
        });

        foreach (array_keys(FeatureSetting::FEATURES) as $featureKey) {
            foreach (FeatureSetting::ROLES as $role) {
                FeatureSetting::query()->create([
                    'feature_key' => $featureKey,
                    'role' => $role,
                    'is_enabled' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_settings');
    }
};
