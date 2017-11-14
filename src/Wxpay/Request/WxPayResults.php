<?php

namespace WxPay\Request;

use WxPay\Exception\WxPayException;

/**
 * 接口调用结果类
 */
class WxPayResults extends WxPayRequestBase
{
    /**
     *
     * 使用数组初始化对象
     *
     * @param array $array
     * @param $noCheckSign
     *
     * @return object
     */
    public function InitFromArray($array, $noCheckSign = false)
    {
        $this->FromArray($array);
        if ($noCheckSign == false) {
            $this->CheckSign();
        }
        return $this;
    }

    /**
     * 使用数组初始化
     *
     * @param array $array
     */
    public function FromArray($array)
    {
        $this->values = $array;
    }

    /**
     * 检测签名
     */
    public function CheckSign()
    {
        //fix异常
        if (!$this->IsSignSet()) {
            throw new WxPayException('签名错误！');
        }

        $sign = $this->MakeSign();
        if ($this->GetSign() == $sign) {
            return true;
        }
        throw new WxPayException('签名错误！');
    }

    /**
     * 将xml转为array
     *
     * @param string $xml
     *
     * @throws WxPayException
     * @return array
     */
    public function Init($xml)
    {
        $this->FromXml($xml);
        //fix bug 2015-06-29
        if ($this->values['return_code'] != 'SUCCESS') {
            return $this->GetValues();
        }
        $this->CheckSign();
        return $this->GetValues();
    }

    /**
     * 设置参数
     *
     * @param string $key
     * @param string $value
     */
    public function SetData($key, $value)
    {
        $this->values[$key] = $value;
    }
}