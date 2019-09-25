<?php
namespace by\component\ram;

/**
 * Class ByAuthContext
 * 校验类 - 不会涉及数据库操作
 * @package by\component\ram
 */
class ByAuthContext
{

    public $supportKeys = [];

    private $getParams;
    private $postParams;
    private $serviceName;
    private $actionName;
    private $uid;
    private $clientIp;
    private $allowStatementArr;
    private $denyStatementArr;
    private $ua;

    public function __construct($serviceType)
    {
        $serviceType = str_replace("by_", "", $serviceType);
        $splitIndex = strpos($serviceType, "_");
        if ($splitIndex === false) throw new \InvalidArgumentException(["%param% invalid", ["%param%" => "serviceType"]]);
        $this->serviceName = substr($serviceType, 0, $splitIndex);
        $this->actionName = substr($serviceType, $splitIndex + 1, strlen($serviceType) - $splitIndex + 1);
        $this->supportKeys = [
            "by:SourceIp" => "getClientIp",
            "by:CurrentTime" => "getReqTime",
            "by:Ua" => "getUa"
        ];

    }

    /**
     * @return mixed
     */
    public function getGetParams()
    {
        return $this->getParams;
    }

    /**
     * @param mixed $getParams
     */
    public function setGetParams($getParams): void
    {
        $this->getParams = $getParams;
    }

    /**
     * @return mixed
     */
    public function getPostParams()
    {
        return $this->postParams;
    }

    /**
     * @param mixed $postParams
     */
    public function setPostParams($postParams): void
    {
        $this->postParams = $postParams;
    }

    public function getParam($key) {
        return array_key_exists($key, $this->getParams) ? $this->getParams[$key] : "";
    }

    /**
     * @return mixed
     */
    public function getUa()
    {
        return $this->ua;
    }

    /**
     * @param mixed $ua
     */
    public function setUa($ua): void
    {
        $this->ua = $ua;
    }

    /**
     * 权限校验
     * 1. 默认必须授权才能访问
     * 2.
     * @throws ForbidException
     */
    public function checkAuth() {
        $this->filterAllAction();
        // 过滤后没有策略了，则禁止访问
        if (count($this->allowStatementArr) === 0)  {
            throw new ForbidException();
        }

        // 禁止语句检测
        foreach ($this->denyStatementArr as &$denyStatement) {
            if ($denyStatement instanceof ByStatement) {
                $condition = $denyStatement->getCondition();
                $resource = $denyStatement->getResource();
                $resource = $this->checkResource($resource);
                $denyStatement->setResource($resource);
                if (count($resource) == 0) {
                    continue;
                }
                if ($this->checkCondition($condition)) {
                    $denyStatement->setIsSatisfy(true);
                    throw new ForbidException();
                }
            }
        }

        // 允许语句检测
        foreach ($this->allowStatementArr as &$allowStatement) {
            if ($allowStatement instanceof ByStatement) {
                $resource = $this->checkResource($allowStatement->getResource());
                $allowStatement->setResource($resource);
                if (count($resource) == 0) {
                    continue;
                }
                $condition = $allowStatement->getCondition();
                if ($this->checkCondition($condition)) {
                    $allowStatement->setIsSatisfy(true);
                    return ;
                }
            }
        }

    }

    public function filterAllAction() {
        $this->allowStatementArr = $this->filterAction($this->allowStatementArr);
        $this->denyStatementArr = $this->filterAction($this->denyStatementArr);
    }

    public function filterAction($arr) {
        $newArr = [];
        foreach ($arr as $st) {
            if ($st instanceof ByStatement) {
                if (is_string($st->getAction()) && $this->isCurAction($st->getAction())) {
                    $newArr[] = $st;
                } elseif (is_array($st->getAction())) {
                    foreach ($st->getAction() as $action) {
                        if ($this->isCurAction($action)) {
                            $newArr[] = $st;
                        }
                    }
                }
            }
        }
        return $newArr;
    }

    /**
     * Clients:create*
     *
     * @param $action
     * @return bool
     */
    public function isCurAction($action) {
        $pattern = str_replace("*", "([\w:]*)", $action);
        $pattern = '/^'.$pattern.'$/i';
        return preg_match($pattern, $this->serviceName.":".$this->actionName, $match) > 0;
    }

    public function checkResource($resource) {
        $satisfy = [];
        if (is_string($resource)) {
            if (strpos($resource, $this->serviceName.":") !== false) {
                array_push($satisfy, $resource);
            }
        } elseif (is_array($resource)) {
            foreach ($resource as $item) {
                if (strpos($item, $this->serviceName.":") !== false) {
                    array_push($satisfy, $item);
                }
            }
        }
        // 解析
        $newSatisfy = [];
        foreach ($satisfy as $vo) {
            $newVo = str_replace($this->serviceName.":", "", $vo);
            $arr = explode(":", $newVo);
            $flag = true;
            foreach ($arr as $kv) {
                $kvArr = explode("/", $kv);
                if (count($kvArr) == 2) {
                    if ($this->postParam($kvArr[0]) != $kvArr[1]) {
                        $flag = false;
                    }
                }
            }
            if ($flag) {
                array_push($newSatisfy, $vo);
            }

        }
        return $newSatisfy;
    }

    public function postParam($key) {
        return array_key_exists($key, $this->postParams) ? $this->postParams[$key] : "";
    }

    /**
     * 检测是否满足条件
     * @param $condition
     * @return bool
     */
    public function checkCondition($condition) {
        if (empty($condition)) return true;
        $that = new ByConditionHelper();
        $conditionFlag = true;
        foreach ($condition as $conditionType => $one) {
            $flag = true;
//            var_dump($conditionType);
            foreach ($one as $conditionKey => $assertValue) {
                if (in_array($conditionType, ByConditionHelper::$supportMethods)) {
                    $method = $this->getConditionKey($conditionKey);
                    if (empty($method)) throw new \InvalidArgumentException('invalid method');
                    // 获取实际值
                    $trueValue = call_user_func([$this, $method[0]], $method[1]);
//                    var_dump($trueValue);
//                    var_dump($assertValue);
                    // 调用对比方法
                    $flag = call_user_func_array([$that, $conditionType], [$trueValue, $assertValue]);
//                    var_dump($flag);
                    if ($flag === false) break;
                }
            }
            $conditionFlag = $flag;
            if (!$conditionFlag) break;
        }
        return $conditionFlag;
    }

    public function getConditionKey($key) {
        if (array_key_exists($key, $this->supportKeys)) {
            $method = $this->supportKeys[$key];
            return [$method, ""];
        }

        // get请求参数
        if (strpos($key, "by:get_") === 0) {
            return ["getParam", str_replace("by:get_", "", $key)];
        }
        // post请求参数
        if (strpos($key, "by:post_") === 0) {
            return ["postParam", str_replace("by:post_", "", $key)];
        }
        return "";
    }

    /**
     * @return bool|string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param bool|string $serviceName
     */
    public function setServiceName($serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return bool|string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param bool|string $actionName
     */
    public function setActionName($actionName): void
    {
        $this->actionName = $actionName;
    }

    /**
     * @return mixed
     */
    public function getAllowStatementArr()
    {
        return $this->allowStatementArr;
    }

    /**
     * @param mixed $allowStatementArr
     */
    public function setAllowStatementArr($allowStatementArr): void
    {
        $this->allowStatementArr = $allowStatementArr;
    }

    /**
     * @return mixed
     */
    public function getDenyStatementArr()
    {
        return $this->denyStatementArr;
    }

    /**
     * @param mixed $denyStatementArr
     */
    public function setDenyStatementArr($denyStatementArr): void
    {
        $this->denyStatementArr = $denyStatementArr;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @param mixed $clientIp
     */
    public function setClientIp($clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    /**
     * 返回请求时间
     * @return string
     */
    public function getReqTime():string {
        return date("Y-m-d H:i:s", time());
    }
}
