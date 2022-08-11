<?php

use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Models\WarehouseHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStaffUserIdInPaymentsWarehouseHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('staff_user_id')->unsigned()->nullable()->after('notes');
            $table->foreign('staff_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
        Schema::table('warehouse_history', function (Blueprint $table) {
            $table->bigInteger('staff_user_id')->unsigned()->nullable()->after('transaction_number');
            $table->foreign('staff_user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });

        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            $firstAdmin = User::where('role_id', $adminRole->id)->first();

            if ($firstAdmin) {
                $allPayments = Payment::withoutGlobalScope('current_warehouse')->get();

                foreach ($allPayments as $allPayment) {
                    $allPayment->staff_user_id = $firstAdmin->id;
                    $allPayment->save();
                }

                $allWarehouseHistories = WarehouseHistory::withoutGlobalScope('current_warehouse')->get();

                foreach ($allWarehouseHistories as $allWarehouseHistory) {
                    if ($allWarehouseHistory->order_id != null) {
                        $order = Order::select('staff_user_id')->where('id', $allWarehouseHistory->order_id)->first();
                        $allWarehouseHistory->staff_user_id = $order->staff_user_id;
                        $allWarehouseHistory->save();
                    } else if ($allWarehouseHistory->payment_id != null) {
                        $allWarehouseHistory->staff_user_id = $firstAdmin->id;
                        $allWarehouseHistory->save();
                    } else if ($allWarehouseHistory->expense_id != null) {
                        $expense = Expense::where('id', $allWarehouseHistory->expense_id)->first();

                        $allWarehouseHistory->staff_user_id = $expense->user_id;
                        $allWarehouseHistory->save();
                    }
                }
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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_staff_user_id_foreign');
            $table->dropColumn('staff_user_id');
        });

        Schema::table('warehouse_history', function (Blueprint $table) {
            $table->dropForeign('warehouse_history_staff_user_id_foreign');
            $table->dropColumn('staff_user_id');
        });
    }
}
