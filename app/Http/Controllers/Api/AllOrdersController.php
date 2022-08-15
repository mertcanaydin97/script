<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\OnlineOrders\IndexRequest;
use App\Models\OrderItem;
use App\Models\Order;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Http\Request;

use Examyou\RestAPI\RequestParser;
use Examyou\RestAPI\Exceptions\Parse\NotAllowedToFilterOnThisFieldException;
use Vinkla\Hashids\Facades\Hashids;
class AllOrdersController extends ApiBaseController
{
	protected $model = OrderItem::class;

	protected $indexRequest = IndexRequest::class;

	protected function modifyIndex($query)
	{
		$request = request();
		$warehouse = warehouse();

		

		// Dates Filters
		
		
		// Can see only order of warehouses which is assigned to him
		

		return $query;
	}

	public function cancelOrder($id)
	{
		$order = Order::where('unique_id', $id)->first();

		if ($order->order_type == "online-orders" && $order->order_status != 'delivered') {
			$order->cancelled = 1;
			$order->cancelled_by = auth('api')->user()->id;
			$order->save();

			return ApiResponse::make('Success', []);
		}
	}

	public function confirmOrder(Request $request, $id)
	{
		$order = Order::where('unique_id', $id)->first();

		if ($order->order_type == "online-orders" && $order->order_status == 'ordered' && $order->cancelled != 1) {
			$order->order_status = "confirmed";
			$order->save();

			return ApiResponse::make('Success', []);
		}
	}

	public function changeOrderStatus(Request $request, $id)
	{
		$order = Order::where('unique_id', $id)->first();

		if (
			$order->order_type == "online-orders" &&
			$order->order_status != 'ordered' &&
			$order->order_status != 'delivered' &&
			$order->cancelled != 1 &&
			($request->order_status == 'confirmed' || $request->order_status == 'processing' || $request->order_status == 'shipping')
		) {
			$order->order_status = $request->order_status;
			$order->save();

			return ApiResponse::make('Success', []);
		}
	}

	public function markAsDelivered(Request $request, $id)
	{
		$order = Order::where('unique_id', $id)->first();

		if (
			$order->order_type == "online-orders" &&
			$order->cancelled != 1 &&
			($order->order_status == 'confirmed' || $order->order_status == 'processing' || $order->order_status == 'shipping')
		) {
			$order->order_status = "delivered";
			$order->save();

			return ApiResponse::make('Success', []);
		}
	}
	public function all(Request $request){
			if ($request->get('filters')) {
				$user_id = '';
				$product_id = '';
				$filters = explode(' and ', $request->get('filters'));
				foreach ($filters as $key => $value) {
					$datas = explode(' eq ', $value);
					
					if($datas[0] == 'user_id' && isset($datas[1])){
						$convertedId = Hashids::decode(str_replace('"','',$datas[1]));
				$hsahToId = $convertedId[0];
				
				$where[] = ['user_id','=', $hsahToId];
				
					}
					if($datas[0] == 'product_id' && isset($datas[1])){
						$convertedId2 = Hashids::decode(str_replace('"','',$datas[1]));
				$hsahToId2 = $convertedId2[0];
				
				$where[] = ['product_id','=', $hsahToId2];
				
					}
				}
				
				
					
				$orderItems = OrderItem::select('*')->with('order','product')->where($where)->where('status', '0')->get();
	
							
	}else{

		$orderItems = OrderItem::select('*')->with('order','product')->where('status', '0')->get();
	}

		
		foreach ($orderItems as $key => $value) {
			$newItems[] = [
				"id" => $value->id,
				"xid" => $value->xid,
				"invoice_number"=>$value->order->invoice_number,
				"order_date"=>$value->order->order_date,
				"product_name"=>$value->product->name,
				'quantity' => $value->quantity
			];
		}
		return response()->json([
			"data"=>$newItems,
			"message" => "Success",
			"meta"=>[
			"paging"=>[
				"links"=>[],"total"=>3],
				"time"=>0.208]
		]);
	}
}
