<?php

use App\Models\FrontWebsiteSettings;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddFromWarehouseIdColumnInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('from_warehouse_id')->unsigned()->nullable()->default(null)->after('warehouse_id');
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')->onDelete('cascade')->onUpdate('cascade');
        });

        DB::statement("ALTER TABLE `order_items` CHANGE COLUMN `user_id` `user_id` BIGINT UNSIGNED NULL DEFAULT NULL  COMMENT '' AFTER `id`;");


        // Add front website settings for each warehouse
        $allWarehouses = Warehouse::all();
        foreach ($allWarehouses as $allWarehouse) {

            $frontSetting = FrontWebsiteSettings::where('warehouse_id', $allWarehouse->id)
                ->first();

            if (!$frontSetting) {
                $frontSetting = new FrontWebsiteSettings();
                $frontSetting->warehouse_id = $allWarehouse->id;
                $frontSetting->featured_categories = [];
                $frontSetting->featured_products = [];
                $frontSetting->features_lists = [];
                $frontSetting->pages_widget = [];
                $frontSetting->contact_info_widget = [];
                $frontSetting->links_widget = [];
                $frontSetting->top_banners = [];
                $frontSetting->bottom_banners_1 = [];
                $frontSetting->bottom_banners_2 = [];
                $frontSetting->bottom_banners_3 = [];
                $frontSetting->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_id_column_in_orders', function (Blueprint $table) {
            $table->dropColumn('from_warehouse_id');
        });
    }
}
