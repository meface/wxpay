<?php

namespace WxPay\Request;

use WxPay\Exception\WxPayException;

/**
 * 接口调用结果类
 */
class WxPayResults extends WxPayRequestBase
{
    public function __construct($key)
    {
        parent::__construct($key);
    }

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

    /**
     * 回调入口
     *
     * @param bool $needSign 是否需要签名输出
     */
    public function Handle($needSign = true)
    {
        //当返回false的时候，表示notify中调用NotifyCallBack回调失败获取签名校验失败，此时直接回复失败
        $result = WxpayApi::notify([$this, 'NotifyCallBack'], $msg);
        if ($result == false) {
            $this->SetReturn_code('FAIL');
            $this->SetReturn_msg($msg);
            $this->ReplyNotify(false);
            return;
        } else {
            //该分支在成功回调到NotifyCallBack方法，处理完成之后流程
            $this->SetReturn_code('SUCCESS');
            $this->SetReturn_msg('OK');
        }
        $this->ReplyNotify($needSign);
    }
}