<?php

namespace App\Http\Controllers\Api\Front;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Front\Auth\LoginRequest;
use App\Http\Requests\Api\Front\Auth\SignupRequest;
use App\Http\Requests\Api\Front\Auth\ProfileRequest;
use App\Http\Requests\Api\Front\Auth\RefreshTokenRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\FrontProductCard;
use App\Models\FrontWebsiteSettings;
use App\Models\Product;
use App\Models\UserDetails;
use App\Models\Warehouse;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Claims\Custom;
use Illuminate\Support\Str;

class HomePageController extends ApiBaseController
{
	public function app($storeSlug)
	{
		$warehouse = Warehouse::where('slug', $storeSlug)->first();

		if (!$warehouse) {
			throw new ApiException("Not a valid warehouse");
		}

		$settings = FrontWebsiteSettings::withoutGlobalScope('current_warehouse')
			->where('warehouse_id', $warehouse->id)->first();

		return ApiResponse::make('App settings fetched', [
			'app' => $settings,
			'warehouse' => $warehouse
		]);
	}

	public function homepage($storeSlug)
	{
		$warehouse = Warehouse::where('slug', $storeSlug)->first();

		if (!$warehouse) {
			throw new ApiException("Not a valid warehouse");
		}

		$categories = Category::select('id', 'name', 'slug', 'image')->get();
		$products = Product::select('id', 'name', 'slug', 'image', 'description', 'category_id', 'brand_id')
			->with(['details:id,product_id,sales_price,mrp,tax_id,sales_tax_type', 'details.tax:id,rate', 'category:id,name,slug', 'brand:id,name,slug'])
			->take(20)->get();
		$frontSettings = FrontWebsiteSettings::withoutGlobalScope('current_warehouse')
			->where('warehouse_id', $warehouse->id)->first();
		$frontProductCards = FrontProductCard::withoutGlobalScope('current_warehouse')
			->where('warehouse_id', $warehouse->id)
			->get();

		return ApiResponse::make('App settings fetched', [
			'categories' => $categories,
			'products' => $products,
			'front_settings' => $frontSettings,
			'front_product_cards' => $frontProductCards,
		]);
	}

	public function login(LoginRequest $request)
	{
		$credentials = [
			'email' =>  $request->email,
			'password' =>  $request->password,
			'user_type' => 'customers'
		];
		if (!$token = auth('api_front')->attempt($credentials)) {
			return
				response()->json([
					'error_message' =>  __('auth.login_failed')
				], 406);
		} else if (auth('api_front')->user()->status == 'waiting') {
			response()->json([
				'error_message' =>  __('auth.user_not_verified')
			], 406);
		} else if (auth('api_front')->user()->status == 'disabled') {
			response()->json([
				'error_message' =>  __('auth.user_deactivated')
			], 406);
		} else
			return $this->respondWithToken($token);
	}

	public function signup(SignupRequest $request)
	{
		$newCustomer = new Customer();
		$newCustomer->name = $request->name;
		$newCustomer->email = $request->email;
		$newCustomer->phone = $request->phone;
		$newCustomer->password = $request->password;
		// $newCustomer->email_verification_code = Str::random(20);
		// $newCustomer->status = 'waiting';
		$newCustomer->save();

		// Storing Customer details for each warehouse
		$allWarehouses = Warehouse::select('id')->get();
		foreach ($allWarehouses as $allWarehouse) {
			$customerDetails = new UserDetails();
			$customerDetails->warehouse_id = $allWarehouse->id;
			$customerDetails->user_id = $newCustomer->id;
			$customerDetails->credit_period = 30;
			$customerDetails->save();
		}

		// TODO - Notifying to Warehouse and customer


		return ApiResponse::make('Signup successfully', []);
	}

	protected function respondWithToken($token)
	{
		$user = auth('api_front')->user();

		return ApiResponse::make('Loggged in successfull', [
			'token' => $token,
			'token_type' => 'bearer',
			'expires_in' => Carbon::now()->addDays(180),
			'user' => $user
		]);
	}

	public function refreshToken(RefreshTokenRequest $request)
	{
		$newToken = auth('api_front')->refresh();

		return $this->respondWithToken($newToken);
	}

	public function logout()
	{
		auth('api')->logout();

		return ApiResponse::make(__('Session closed successfully'));
	}

	public function user()
	{
		$user = auth('api_front')->user();

		return ApiResponse::make('Data successfull', [
			'user' => $user
		]);
	}

	public function profile(ProfileRequest $request)
	{
		$user = auth('api_front')->user();

		$user->name = $request->name;
		if ($request->has('profile_image')) {
			$user->profile_image = $request->profile_image;
		}
		$user->password = $request->password;
		$user->phone = $request->phone;
		$user->address = $request->address;
		$user->shipping_address = $request->shipping_address;
		$user->save();

		return ApiResponse::make('Profile updated successfull', [
			'user' => $user
		]);
	}

	public function uploadFile(Request $request)
	{
		$result = Common::uploadFile($request);

		return ApiResponse::make('File Uploaded', $result);
	}

	public function categories(Request $request)
	{
		$categories = Category::where('parent_id',null)->get();

		return ApiResponse::make('Data Fetched', [
			'categories' => $categories
		]);
	}

	public function categorieschilds(Request $request)
	{
		$slug = $request->slug;
		$category = Category::select('id','parent_id', 'name', 'slug')->where('slug', $slug)->first();
		$categories = Category::where('parent_id', $category->id)->get();

		if(count($categories) > 0){

			return ApiResponse::make('Data Fetched', [
				'categories' => $categories
			]);
	}else{
		
		$categoriess = Category::where('parent_id', $category->parent_id)->get();
		return ApiResponse::make('Data Fetched', [
			'categories' => $categoriess
		]);
	}
	}

	public function categoryBySlug(Request $request)
	{
		$slug = $request->slug;
		$category = Category::select('id', 'name', 'slug')->where('slug', $slug)->first();

		return ApiResponse::make('Data Fetched', [
			'category' => $category
		]);
	}
}
