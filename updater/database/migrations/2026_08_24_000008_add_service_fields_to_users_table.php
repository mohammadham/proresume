<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('service_type')->nullable()->after('username');
            $table->string('specialty')->nullable()->after('service_type');
            $table->string('district')->nullable()->after('city');
            $table->decimal('lat', 10, 7)->nullable()->after('district');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'specialty', 'district', 'lat', 'lng']);
        });
    }
}
