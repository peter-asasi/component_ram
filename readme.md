# ram


# 调用例子
```
$serviceType = 调用服务(一个字符串)
$context = new ByAuthContext($serviceType);
// 设置get请求参数
$context->setGetParams($this->request->query->all());
$post = $this->request->request->all();
if ($this->context && is_array($this->context->getDecryptData())) {
    $post = array_merge($post, $this->context->getDecryptData());
}
// 设置Post请求参数
$context->setPostParams($post);
// 设置UA
$context->setUa($this->request->headers->has("User-Agent") ? $this->request->headers->get("User-Agent") : "");
// 设置请求client对应的用户id
$context->setUid($this->getClientUid());
// 设置请求客户端的ip
$context->setClientIp($this->request->getClientIp());


// *** 获取 Policies Start *************************************************
// 获取用户对应角色对应的策略数组
$user = $this->userAccountService->info(['id' => $this->getClientUid()]);
if (!($user instanceof UserAccount)) {
    throw new InvalidArgumentException(["%param% invalid" => ["%param%" => "client_id"]]);
}
$roles = $user->getRoles()->filter(function ($item) {
    return $item instanceof AuthRole && $item->getEnable() == StatusEnum::ENABLE;
});

if ($roles->count() == 0) {
    throw new ForbidException();
}

$statements = [];
foreach ($roles as $role) {
    if ($role instanceof AuthRole) {
        foreach ($role->getPolicies() as $policy) {
            if ($policy instanceof AuthPolicy && !empty($policy->getStatements())) {
                array_push($statements, $policy->getStatements());
            }
        }
    }
}
return $statements;
// *** 获取 Policies END *************************************************

$allowStatements = [];
$denyStatements = [];
// 处理获取 禁止策略 和 允许策略 2个
foreach ($statements as $statement) {
    $stArr = json_decode($statement, JSON_OBJECT_AS_ARRAY);
    if (is_array($stArr)) {
        foreach ($stArr as $item) {
            if (array_key_exists('Effect', $item)
                && array_key_exists("Resource", $item)
                && array_key_exists("Action", $item)) {
                if ($item['Effect'] === "Allow") {
                    array_push($allowStatements, new ByStatement($item));
                } elseif ($item['Effect'] === "Deny") {
                    array_push($denyStatements, new ByStatement($item));
                }
            }
        }
    } else {
        throw new InvalidArgumentException("invalid statements");
    }
}
// 如果用户没有对应策略则禁止访问该接口
if (count($allowStatements) == 0 && count($denyStatements) == 0) {
    throw new ForbidException();
}
$context->setAllowStatementArr($allowStatements);
$context->setDenyStatementArr($denyStatements);
// 最后验证 ，失败会抛出 by\component\ram\ForbidException 异常
$context->checkAuth();
```
