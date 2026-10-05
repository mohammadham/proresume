<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnamadFieldsToBasicSettingsTable extends Migration
{
    public function up()
    {
        // `watermark_*` columns are not present on this schema, so anchor only
        // when the column really exists; otherwise let MySQL append the fields.
        $after = Schema::hasColumn('basic_settings', 'watermark_image') ? 'watermark_image' : null;

        Schema::table('basic_settings', function (Blueprint $table) use ($after) {
            $table->boolean('enamad_status')->default(false)->after($after);
            $table->string('enamad_code')->nullable()->after('enamad_status');
            $table->string('enamad_site_id')->nullable()->after('enamad_code');
            $table->text('enamad_secret_key')->nullable()->after('enamad_site_id');
            $table->date('enamad_expire_date')->nullable()->after('enamad_secret_key');
            $table->string('enamad_logo_type')->default('auto')->after('enamad_expire_date'); // auto, light, dark
        });
    }

    public function down()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['enamad_status', 'enamad_code', 'enamad_site_id', 'enamad_secret_key', 'enamad_expire_date', 'enamad_logo_type']);
        });
    }
}