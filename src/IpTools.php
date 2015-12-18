<?php
/**
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @license
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @link http://phpwhois.pw
 * @copyright Copyright (C)1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @copyright Maintained by David Saez
 * @copyright Copyright (c) 2014 Dmitry Lukashin
 */

namespace phpWhois;

/**
 * Utilities for parsing ip addresses
 */
class IpTools
{
    /**
     * Check if ip address is valid
     *
     * @param string $ip IP address for validation
     * @param string $type Type of ip address. Possible value are: any, ipv4, ipv6
     * @param boolean $strict If true - fail validation on reserved and private ip ranges
     *
     * @return boolean True if ip is valid. False otherwise
     */
    public static function validIp($ip, $type = 'any', $strict = true)
    {
        switch ($type) {
            case 'any':
                return self::validIpv4($ip, $strict) || self::validIpv6($ip, $strict);
            case 'ipv4':
                return self::validIpv4($ip, $strict);
            case 'ipv6':
                return self::validIpv6($ip, $strict);
        }
        return false;
    }

    /**
     * Check if given IP is a valid ipv4 address and doesn't belong to private and
     * reserved ranges
     *
     * @param string $ip Ip address
     * @param boolean $strict If true - fail validation on reserved and private ip ranges
     *
     * @return boolean
     */
    public static function validIpv4($ip, $strict = true)
    {
        $flags = FILTER_FLAG_IPV4;
        if ($strict) {
            $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, array('flags' => $flags)) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Check if given IP is a valid ipv6 address and doesn't belong to private ranges
     *
     * @param string $ip Ip address
     * @param boolean $strict If true - fail validation on reserved and private ip ranges
     *
     * @return boolean
     */
    public static function validIpv6($ip, $strict = true)
    {
        $flags = FILTER_FLAG_IPV6;
        if ($strict) {
            $flags = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, array('flags' => $flags)) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Convert CIDR to net range
     *
     * @TODO provide example
     *
     * @param string $net
     * @return string
     */
    public static function cidrConv($net)
    {
        $start = strtok($net, '/');
        $n = 3 - substr_count($net, '.');

        if ($n > 0) {
            for ($i = $n; $i > 0; $i--) {
                $start .= '.0';
            }
        }

        $bits1 = str_pad(decbin(ip2long($start)), 32, '0', 'STR_PAD_LEFT');
        $net = pow(2, (32 - substr(strstr($net, '/'), 1))) - 1;
        $bits2 = str_pad(decbin($net), 32, '0', 'STR_PAD_LEFT');
        $final = '';

        for ($i = 0; $i < 32; $i++) {
            if ($bits1[$i] == $bits2[$i]) {
                $final .= $bits1[$i];
            }
            if ($bits1[$i] == 1 and $bits2[$i] == 0) {
                $final .= $bits1[$i];
            }
            if ($bits1[$i] == 0 and $bits2[$i] == 1) {
                $final .= $bits2[$i];
            }
        }

        return $start . " - " . long2ip(bindec($final));
    }
}
