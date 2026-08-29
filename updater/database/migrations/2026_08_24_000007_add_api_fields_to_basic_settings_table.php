<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApiFieldsToBasicSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->boolean('api_integration_status')->default(false)->after('tawkto_status');
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
