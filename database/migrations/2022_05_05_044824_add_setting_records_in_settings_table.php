<?php

use App\Classes\NotificationSeed;
use App\Models\Settings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddSettingRecordsInSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('mysqldump_command')->default("/usr/bin/mysqldump")->after('rtl');
        });

        // Create Menu Setting
        $setting = new Settings();
        $setting->setting_type = 'shortcut_menus';
        $setting->name = 'Add Menu';
        $setting->name_key = 'shortcut_menus';
        $setting->credentials = [
            'staff_member',
            'customer',
            'supplier',
            'brand',
            'category',
            'product',
            'purchase',
            'sales',
            'expense_category',
            'expense',
            'warehouse',
            'currency',
            'unit',
            'language',
            'role',
            'tax',
            'payment_mode',
        ];
        $setting->status = 1;
        $setting->save();

        // Seed for quotations
        NotificationSeed::seedAllModulesNotifications();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('mysqldump_command');
        });
    }
}
