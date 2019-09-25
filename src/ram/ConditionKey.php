<?php
namespace by\component\ram;

/**
 * Class ConditionKey
 */
class ConditionKey
{
    /**
     * 以 ISO 8601 格式表示
     * 如2012-11-11T23:59:59Z
     * @var string
     */
    const CurrentTime = "dbh:CurrentTime";


    /**
     * 发送请求时的客户端 IP 地址
     */
    const SourceIp = "dbh:SourceIp";

}
