<?php
namespace by\component\ram;

/**
 * Class ByConditionHelper
 * @package by\component\ram
 */
class ByConditionHelper
{

    public static $supportMethods = [
        "IpAddress", "NotIpAddress", "Bool",
        'StringEquals', 'StringNotEquals', 'StringEqualsIgnoreCase', 'StringNotEqualsIgnoreCase', 'StringLike', 'StringNotLike',
        'NumericEquals', 'NumericNotEquals', 'NumericLessThan', 'NumericLessThanEquals', 'NumericGreaterThan', 'NumericGreaterThanEquals',
        'DateEquals', 'DateNotEquals', 'DateLessThan', 'DateLessThanEquals', 'DateGreaterThan', 'DateGreaterThanEquals',
    ];

    public function DateGreaterThanEquals($trueValue, $assertValue) {
        return strtotime($trueValue) >= strtotime($assertValue);
    }

    public function DateGreaterThan($trueValue, $assertValue) {
        return strtotime($trueValue) > strtotime($assertValue);
    }

    public function DateLessThanEquals($trueValue, $assertValue) {
        return strtotime($trueValue) <= strtotime($assertValue);
    }

    public function DateLessThan($trueValue, $assertValue) {
        return strtotime($trueValue) < strtotime($assertValue);
    }

    public function DateNotEquals($trueValue, $assertValue) {
        return strtotime($trueValue) !== strtotime($assertValue);
    }

    public function DateEquals($trueValue, $assertValue) {
        return strtotime($trueValue) === strtotime($assertValue);
    }

    public function NumericGreaterThanEquals($trueValue, $assertValue) {
        return intval($trueValue) >= intval($assertValue);
    }

    public function NumericGreaterThan($trueValue, $assertValue) {
        return intval($trueValue) > intval($assertValue);
    }


    public function NumericLessThanEquals($trueValue, $assertValue) {
        return intval($trueValue) <= intval($assertValue);
    }

    public function NumericLessThan($trueValue, $assertValue) {
        return intval($trueValue) < intval($assertValue);
    }

    public function NumericNotEquals($trueValue, $assertValue) {
        return intval($trueValue) !== intval($assertValue);
    }

    public function NumericEquals($trueValue, $assertValue) {
        return intval($trueValue) === intval($assertValue);
    }



    public function StringNotLike($trueValue, $assertValue) {
        return strpos(strval($trueValue), strval(strtolower($assertValue))) === false;
    }

    public function StringLike($trueValue, $assertValue) {
        return strpos(strval($trueValue), strval(strtolower($assertValue))) !== false;
    }

    public function StringNotEqualsIgnoreCase($trueValue, $assertValue) {
        return strval(strtolower($trueValue)) !== strval(strtolower($assertValue));
    }

    public function StringEqualsIgnoreCase($trueValue, $assertValue) {
        return strval(strtolower($trueValue)) === strval(strtolower($assertValue));
    }

    public function StringNotEquals($trueValue, $assertValue) {
        return strval($trueValue) !== strval($assertValue);
    }

    public function StringEquals($trueValue, $assertValue) {
        return strval($trueValue) === strval($assertValue);
    }

    public function IpAddress($trueIp, $assertIp) {
        if (is_string($assertIp)) {
            return $trueIp == $assertIp;
        } elseif (is_array($assertIp)) {
            return in_array($trueIp, $assertIp);
        } else {
            return false;
        }
    }

    public function NotIpAddress($trueIp, $assertIp) {
        if (is_string($assertIp)) {
            return $trueIp !== $assertIp;
        } elseif (is_array($assertIp)) {
            return !in_array($trueIp, $assertIp);
        } else {
            return false;
        }
    }

    public function Bool($trueValue, $assertValue) {
        return $trueValue == $assertValue;
    }
}
