<?php

namespace app\service;

use app\common\HttpRequest;
use app\controller\ApiResponse;

abstract class CoreService
{
    protected $currentModel = null;

    public function __construct()
    {
        // 服务初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

    public function get($params)
    {
        if (!$this->currentModel) {
            ApiResponse::fail([], 802);
        }
        // 处理 currentPage 和 size
        $currentPage = isset($params['currentPage']) ? $params['currentPage'] : 1;
        $size = isset($params['size']) ? $params['size'] : 10;
        $offset = ($currentPage - 1) * $size;

        // 处理 where
        $where = [];
        if (isset($params['where'])) {
            $where = $params['where'];
        }

        // 处理 field
        $field = '*';
        if (isset($params['field'])) {
            $field = $params['field'];
        }

        // 处理 order
        $order = '';
        if (isset($params['order'])) {
            $order = $params['order'];
        }

        $result = $this->currentModel->field($field)->where($where)->order($order)->limit($offset, $size)->select()->toArray();
        $total = $this->currentModel->where($where)->count();
        $returnResult = [
            'list' => $result,
            'total' => $total,
        ];
        return $returnResult;
    }

    public function add($data)
    {
        // save data
        $result = $this->currentModel->save($data);
        if ($result) {
            $id = $this->currentModel->id;
        } else {
            $id = 0;
        }
        return $id;
    }

    public function update($data)
    {
        $id = $data['id'];
        unset($data['id']);
        $oldData = $this->currentModel::find($id);
        if (empty($oldData)) {
            ApiResponse::fail([], 1003, 'The user does not exist');
        }

        // update data
        $oldData->save($data);
        return true;
    }

    public function delete($data)
    {
        $id = $data['id'];
        $this->currentModel::destroy($id);
        return [];
    }

    public function getOne($model, $where)
    {
        return $model->where($where)->find();
    }

    /**
     * 批量插入数据，支持更新
     *
     * @datetime 2024-04-23
     * @param array $dataList
     * @param object $model
     * @param string $keyName
     * @return bool
     */
    public function saveData(array $dataList, object $model, string $keyName = ''): bool
    {
        $model->startTrans();
        if (!empty($keyName)) {
            // 需要判断是否存在，如果存在则更新，否则新增
            $keyValueInDataList = array_column($dataList, $keyName);
            $keyValueInDataList = $this->arrayUnique($keyValueInDataList);
            $keyValueInDb = $model->where($keyName, 'IN', $keyValueInDataList)->column($keyName);
            // 分组，如果存在则更新，否则新增
            $dataListToUpdate = [];
            $dataListToInsert = [];
            foreach ($dataList as $data) {
                if (in_array($data[$keyName], $keyValueInDb)) {
                    $dataListToUpdate[] = $data;
                } else {
                    $dataListToInsert[] = $data;
                }
            }
            try {
                if (!empty($dataListToUpdate)) {
                    foreach ($dataListToUpdate as $data) {
                        $model->where($keyName, $data[$keyName])->update($data);
                    }
                }
                if (!empty($dataListToInsert)) {
                    $model->saveAll($dataListToInsert);
                }
                $model->commit();
                return true;
            } catch (\Exception $e) {
                $model->rollback();
                ApiResponse::fail([], 801, $e->getMessage());
            }
        } else {
            try {
                $model->saveAll($dataList);
                $model->commit();
                return true;
            } catch (\Exception $e) {
                $model->rollback();
                ApiResponse::fail([], 801, $e->getMessage());
            }
        }
    }

    public function arrayUnique($array)
    {
        $array = array_unique($array);  // 去重
        $array = array_filter($array);  // 去空
        $array = array_values($array);  // 重置索引
        return $array;
    }

    public function httpGet($url)
    {
        $httpRequest = new HttpRequest($url);
        return $httpRequest->send();
    }

    public function httpPost($url, $data)
    {
        $httpRequest = new HttpRequest($url, 'POST', [], $data);
        return $httpRequest->send();
    }
}
