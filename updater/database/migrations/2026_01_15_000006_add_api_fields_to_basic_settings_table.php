<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApiFieldsToBasicSettingsTable extends Migration
{
    public function up()
    {
        // `tawkto_status` does not exist on every install (this schema uses
        // `tawk_to_script` / `is_tawkto` instead). `after()` is a MySQL-only
        // physical column-ordering hint, so fall back to appending the columns
        // when the anchor is absent instead of failing the whole migration.
        $after = Schema::hasColumn('basic_settings', 'tawkto_status') ? 'tawkto_status' : null;

        Schema::table('basic_settings', function (Blueprint $table) use ($after) {
            $table->boolean('api_integration_status')->default(false)->after($after);
            $table->string('api_key')->nullable()->after('api_integration_status');
        });
    }

    public function down()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['api_integration_status', 'api_key']);
        });
    }
}