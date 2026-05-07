<?php

namespace app\controller;

use app\BaseController;

class IndexController extends BaseController
{
    public function index()
    {
//         $this->testModel();
        return 'Fuck You!';
    }

    public function pageNotFound()
    {
        return ApiResponse::fail([], 803);
    }

    public function testModel()
    {
        $dataList = [
            [
                'open_id' => 'oJ0ae6C9Vz0aLu2ypVv9SXXL1eqs',
            ],
            [
                'open_id' => 'oJ0ae6C9daddVz0aLu2ypVv9v9SX',
            ],
        ];
        $keyName = 'open_id';
        $model = new \app\model\UserModel();
        $keyValueInDataList = array_column($dataList, $keyName);
        $keyValueInDb = $model->where($keyName, 'IN', $keyValueInDataList)->column($keyName);
        dd($keyValueInDb);
    }

    public function phpinfo()
    {
        echo phpinfo();
    }
}
