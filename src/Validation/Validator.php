<?php
/**
 * Created by PhpStorm.
 * User: heyanlong
 * Date: 2018/7/30
 * Time: 下午3:23
 */

namespace Vanilla\Validation;


use Vanilla\Http\Request;

class Validator
{

    private $messages = [];
    private $data = [];
    private $rule = [];
    private $errors = [];

    public function __construct($data, $rule, $messages = [])
    {
        $this->data = $data;
        $this->rule = $rule;
        $this->messages = $messages;
        $this->errors = [];
    }

    public static function make($data, $rule, $messages = [])
    {
        if ($data instanceof Request) {
            $data = $data->all();
        }
        return new Validator($data, $rule, $messages);
    }

    public function fails()
    {
        foreach ($this->rule as $key => $item) {
            if (is_string($item)) {
                $item = explode('|', $item);
            }
            foreach ($item as $rule) {
                try {
                    $pos = strpos($rule, ':');
                    $params = $pos !== false ? substr($rule, $pos + 1) : '';
                    $method = $pos !== false ? substr($rule, 0, $pos) : $rule;
                    $this->$method($this->data, $key, $params);
                } catch (\Exception $e) {
                    $this->errors = $e->getMessage();
                    return true;
                }
            }

        }

        return false;
    }

    public function messages()
    {
        return $this->errors;
    }

    private function required($data, $key, $params)
    {
        if (isset($data[$key]) && is_array($data[$key])) {
            if (count($data[$key]) >= 1) {
                return;
            } else {
                throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 必填');
            }
        }
        if (isset($data[$key]) && trim($data[$key]) !== '') {
            return;
        }

        throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 必填');
    }

    private function regex($data, $key, $params)
    {
        if (isset($data[$key]) && preg_match($params, $data[$key])) {
            return;
        }

        throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 规则验证失败');
    }

    private function min($data, $key, $params)
    {
        if (isset($data[$key])) {
            if (is_numeric($data[$key]) && $data[$key] < $params) {
                throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 不能小于' . $params);
            } elseif (is_string($data[$key]) && mb_strlen($data[$key], 'UTF-8') < $params) {
                throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 不能小于' . $params . '个字符');
            }
            return;
        }
    }

    private function max($data, $key, $params)
    {
        if (isset($data[$key])) {
            if (is_numeric($data[$key]) && $data[$key] > $params) {
                throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 不能大于' . $params);
            } elseif (is_string($data[$key]) && mb_strlen($data[$key], 'UTF-8') > $params) {
                throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 不能大于' . $params . '个字符');
            }
            return;
        }
    }

    private function uuid($data, $key, $params)
    {
        if (isset($data[$key]) && preg_match('/[a-f0-9]{8}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{12}/', $data[$key])) {
            return;
        }

        throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 规则验证失败');
    }

    private function in($data, $key, $params)
    {
        if (isset($data[$key])) {
            if (in_array($data[$key], explode(',', $params))) {
                return;
            }
        }
        throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 必须为' . $params . '其中一个');
    }

    private function numeric($data, $key, $params)
    {
        if (isset($data[$key])) {
            if (is_numeric($data[$key])) {
                return;
            }
        }
        throw new \Exception($this->messages[$key . '.' . __FUNCTION__] ?? $key . ' 必须为数字类型');
    }



}