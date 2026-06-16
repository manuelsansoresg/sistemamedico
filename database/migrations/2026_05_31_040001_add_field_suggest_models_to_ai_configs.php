<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_configs', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_configs', 'model_for_field_suggest')) {
                $table->string('model_for_field_suggest')->nullable()->after('model_for_prescription');
            }

            if (! Schema::hasColumn('ai_configs', 'model_for_study_order')) {
                $table->string('model_for_study_order')->nullable()->after('model_for_field_suggest');
            }
        });

        DB::table('ai_configs')
            ->where('provider', 'openai')
            ->update([
                'model_for_field_suggest' => DB::raw("COALESCE(model_for_field_suggest, 'gpt-4o-mini')"),
                'model_for_study_order' => DB::raw("COALESCE(model_for_study_order, 'gpt-4o')"),
            ]);

        DB::table('ai_configs')
            ->where('provider', 'deepseek')
            ->update([
                'model_for_field_suggest' => DB::raw("COALESCE(model_for_field_suggest, 'deepseek-chat')"),
                'model_for_study_order' => DB::raw("COALESCE(model_for_study_order, 'deepseek-reasoner')"),
            ]);

        DB::table('ai_configs')
            ->where('provider', 'anthropic')
            ->update([
                'model_for_field_suggest' => DB::raw("COALESCE(model_for_field_suggest, 'claude-3-5-haiku-latest')"),
                'model_for_study_order' => DB::raw("COALESCE(model_for_study_order, 'claude-3-5-sonnet-latest')"),
            ]);
    }

    public function down(): void
    {
        Schema::table('ai_configs', function (Blueprint $table) {
            if (Schema::hasColumn('ai_configs', 'model_for_field_suggest')) {
                $table->dropColumn('model_for_field_suggest');
            }

            if (Schema::hasColumn('ai_configs', 'model_for_study_order')) {
                $table->dropColumn('model_for_study_order');
            }
        });
    }
};
