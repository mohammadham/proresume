<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWatermarkToBasicSettings extends Migration
{
    public function up()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->tinyInteger('watermark_status')->default(0)->after('copyright_section');
            $table->text('watermark_text')->nullable()->after('watermark_status');
            $table->string('watermark_url')->nullable()->after('watermark_text');
            $table->string('watermark_image')->nullable()->after('watermark_url');
        });
    }

    public function down()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn(['watermark_status', 'watermark_text', 'watermark_url', 'watermark_image']);
        });
    }
}