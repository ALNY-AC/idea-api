<?php

namespace  App\Http\Controllers\Admin\Feedback; // @todo: 这里是要生成类的命名空间

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{

	public function save(Request $request)
	{

		if ($request->filled('id')) {
			$result = DB::table('feedback')
				->where('id',  $request->input('id'))
				->update($request->all());
			return [
				'code' => $result ? 1 : -1,
				'msg' => $result ? 'success' : 'error',
				'data' => $result,
			];
		} else {
			$result = DB::table('feedback')->insert($request->all());
			return [
				'code' => $result ? 1 : -1,
				'msg' => $result ? 'success' : 'error',
				'data' => $result,
			];
		}
	}

	public function list(Request $request)
	{

		$result = DB::table('feedback') //定义表
			->orderBy('add_time', 'desc') //排序
			->get(); //取列表


		return [
			'code' => $result ? 1 : -1,
			'msg' => $result ? 'success' : 'error',
			'data' => $result,
		];
	}

	public function info(Request $request)
	{

		$result = DB::table('feedback') //定义表
			->where('id', $request->input('id')) //前台传过来的id
			->first(); //获取数据


		return [
			'code' => $result ? 1 : -1,
			'msg' => $result ? 'success' : 'error',
			'data' => $result,
		];
	}

}
