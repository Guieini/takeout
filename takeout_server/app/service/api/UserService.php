<?php

namespace app\service\api;

use app\controller\ApiResponse;
use app\service\CoreService;

class UserService extends CoreService
{
    public function initialize()
    {
        $this->currentModel = new \app\model\UserModel();
    }

    public function get($params)
    {
        $params['field'] = ['id', 'user_id', 'username', 'phone', 'open_id', 'gender', 'is_subscribe'];
        $result = parent::get($params);
        if (!empty($result)) {
            foreach ($result['list'] as $key => $value) {
                $result['list'][$key]['roles'] = 'admin';
            }
        }
        return $result;
    }

    public function add($data)
    {
        // check if the username already exists
        $username = $data['username'];
        $isExist = $this->currentModel->where('username', $username)->find();
        if ($isExist) {
            ApiResponse::fail([], 1004, 'The username already exists');
        }

        // add default data
        $data['user_id'] = $this->getNewUserId();
        $data['is_subscribe'] = 0;

        // handle password
        if (!empty($data['password'])) {
            $common = new \app\common\Common();
            $data['password'] = $common->encryptPassword($data['password']);
        }


        $result = parent::add($data);
        if (!$result) {
            ApiResponse::fail([], 801, 'Add failed');
        }
        $returnData = [
            'id' => $result,
        ];
        return $returnData;
    }

    public function update($data)
    {
        $id = $data['id'];
        unset($data['id']);
        $oldData = $this->currentModel::find($id);
        if (empty($oldData)) {
            ApiResponse::fail([], 1003, 'The user does not exist');
        }

        // check if the username already exists
        if (!empty($oldData['username'])) {
            if ($oldData['username'] != $data['username']) {
                $isExistUsername = $this->currentModel->where('username', $data['username'])->find();
                if ($isExistUsername) {
                    ApiResponse::fail([], 1004, 'The username already exists');
                }
            }
        }

        // handle password
        if (!empty($data['password'])) {
            $common = new \app\common\Common();
            $data['password'] = $common->encryptPassword($data['password']);
        }

        // update data
        $oldData->save($data);
        return true;
    }

    public function info($params)
    {
        return [];
    }

    public function refreshPassword($id)
    {
        $common = new \app\common\Common();
        $newPassword = '123456';
        $encryptPassword = $common->encryptPassword($newPassword);
        if ($id) {
            $result = $this->currentModel->where('id', $id)->update(['password' => $encryptPassword]);
            return $result;
        }
        $result = $this->currentModel->where('id', '>', 0)->update(['password' => $encryptPassword]);
        return $result;
    }

    public function getNewUserId()
    {
        $newUserId = $this->getLastUserId() + 1;
        return $newUserId;
    }

    public function getLastUserId()
    {
        $lastUserId = $this->currentModel::withTrashed()->order('id', 'desc')->value('user_id');
        return $lastUserId;
    }
}
