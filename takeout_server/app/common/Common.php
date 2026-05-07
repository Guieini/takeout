<?php

namespace app\common;

class Common
{
    // 密码盐，请勿随意修改，以免影响现有用户登录
    protected $salt = 'vcseemisbest';

    // 加密密码
    public function encryptPassword(string $password): string
    {
        $saltSHA3 = $this->shaCode($this->salt);
        return $this->shaCode($password . $saltSHA3);
    }
    /**
     * @Description: sha3-512 encryption
     * @DateTime: 2023-10-07
     * @param string $string
     * @return string
     */
    protected function shaCode(string $string): string
    {
        return hash('sha3-512', $string);
    }
}
