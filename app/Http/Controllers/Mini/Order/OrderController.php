<?php

namespace  App\Http\Controllers\Mini\Order; // @todo: 这里是要生成类的命名空间

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
	public function create(Request $request)
	{

		/**
		 * 组成模型
		 */
		$SnapshotDB = DB::table('snapshot');
		$AddressDB = DB::table('address');
		$OrderAddressDB = DB::table('order_address');
		$OrderDB = DB::table('order');

		$SnapshotDB->delete();
		$OrderAddressDB->delete();
		$OrderDB->delete();





		$order_id = 'ORDER' . Carbon::now()->format('YmdHis') . rand(10000, 99999);
		$pay_id = 'PAY' . Carbon::now()->format('YmdHis') . rand(10000, 99999);


		$goodsArr = $request->input('goods'); //商品
		$store_id = ''; //店铺id
		$buy_type =  $request->input('buy_type'); //交易类型
		$app_type = $request->appInfo->app_type; //app的类型，在这里指订单类型，订单来源
		$remarks = $request->input('remarks'); // 用户的备注
		$address_id = $request->input('address_id'); // 用户选择的地址，不能直接使用，需要拿出来备份

		if ($request->filled('store_id')) {
			$store_id = $request->input('store_id');
		} else {
			$store_id = $request->appInfo->store_id;
		}

		/**
		 * 拿出来收货地址
		 */

		$addressInfo = $AddressDB->where('id', $address_id)->first();
		$addressInfo = collect($addressInfo)->except(['id', 'edit_time', 'date_state']);
		$addressInfo['order_id'] = $order_id;

		/**
		 * 组成快照
		 */

		// $goodsData = [];
		$snapshotInfoArr = [];
		$price = 0.00;

		foreach ($goodsArr as $goods) {
			$goodsInfo = DB::table('goods')->where('id', $goods['id'])->first();
			$goodsInfo = collect($goodsInfo)->except(['stock', 'edit_time', 'date_state']);


			$price += $goodsInfo['price'] * $goods['quantity'];

			$snapshotInfo = [];
			$snapshotInfo['goods_id'] =  $goodsInfo['id'];
			$snapshotInfo['order_id'] = $order_id;
			$snapshotInfo['user_id'] = '';
			$snapshotInfo['store_id'] = $store_id;
			$snapshotInfo['app_id'] = $request->appInfo->app_id;
			$snapshotInfo['type'] = 'pay_order';
			$snapshotInfo['title'] =  $goodsInfo['title'];
			$snapshotInfo['data'] =	collect([$goods, $goodsInfo])->collapse()->toJson();

			$snapshotInfoArr[] = $snapshotInfo;
		}






		/**
		 * 组成订单
		 */


		$orderInfo = [];

		$orderInfo['order_id'] = $order_id;
		$orderInfo['pay_id'] = $pay_id;
		$orderInfo['user_id'] = '';
		$orderInfo['store_id'] = $store_id;
		$orderInfo['address_id'] = $address_id;
		$orderInfo['app_id'] = $request->appInfo->app_id;
		$orderInfo['price'] = $price;
		$orderInfo['app_type'] = $app_type;
		$orderInfo['remarks'] = $remarks;



		return [$snapshotInfoArr, $orderInfo];

		$address_id = $OrderAddressDB->insertGetId($addressInfo->toArray());
		$SnapshotDB->insert($snapshotInfoArr);
		$OrderDB->insert($orderInfo);



		return [
			'code' => 1,
			'msg' => 'success',
			'data' => [
				"pay_id" => $pay_id,
				"order_id" => $order_id
			],
		];
	}
}