<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Reno\Cms\Helpers\TablePrefixHelper;

return new class extends Migration
{
    public function up(): void
    {
        $userSettingsTable = TablePrefixHelper::table('user_settings');
        $contextsTable = TablePrefixHelper::table('contexts');

        Schema::create($userSettingsTable, function (Blueprint $table) use ($contextsTable): void {
            $table->id();
            $table->foreignId('context_id')->constrained($contextsTable)->onDelete('cascade');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            $table->unique(['context_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TablePrefixHelper::table('user_settings'));
    }
};
