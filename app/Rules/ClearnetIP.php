<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ClearnetIP implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        $ip = ip2long($value);
        if (!$ip) {
                return false;
        }
        $net_local = ip2long('127.255.255.255') >> 24; //127.x.x.x
        $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址 
        $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址 
        $net_c = ip2long('192.168.255.255') >> 16; //C类网预留ip的网络地址
        return !($ip >> 24 === $net_local || $ip >> 24 === $net_a 
                || $ip >> 20 === $net_b || $ip >> 16 === $net_c); 
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '你输入的不是公网IP.';
    }
}
