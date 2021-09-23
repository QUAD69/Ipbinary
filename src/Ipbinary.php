<?php
declare(strict_types=1);

namespace Quad69;

/**
 * Класс для работы с IP адресами 4 и 6 версий.
 * Каждый адрес преобразуется в бинарную строку 16 байт, что делает
 * удобным его хранение в базах данных, а также работу с ними.
 *
 * Тип колонки для MySQL - BINARY(16)
 * Поиск происходит с помощью BETWEEN
 *
 * ВНИМАНИЕ! Только для PHP версии 8 и выше!
 *
 * @author QUAD69 <https://vk.com/quad69>
 */
class IP
{
    /**
     * Константа нулевого адреса. Возвращается при ошибке конвертирования.
     */
    public const NULLED = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    /**
     * @param string $addr IPv4 адрес в исходном виде
     * @return string IP адрес в бинарном виде
     */
    public static function buildV4(string $addr):string {
        if (!filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return self::NULLED;
        }

        $addr = explode('.', $addr);
        $addr = pack('n*', ...$addr);

        return "\0\0\0\0\0\0\xff\xff{$addr}";
    }

    /**
     * @param string $addr IPv6 адрес в исходном виде
     * @return string IP адрес в бинарном виде
     */
    public static function buildV6(string $addr):string {
        if (!filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return self::NULLED;
        }

        $addr = explode(':', $addr);
        $size = count($addr);
        $econ = array_search('', $addr);

        if ($size !== 8 and $econ !== FALSE) {

            array_splice($addr, $econ, 1, array_fill(0, 9 - $size, '0'));
        }

        $addr = array_map('hexdec', $addr);

        return pack('n*', ...$addr);
    }

    /**
     * @param string $addr IP Адрес в исходном виде (IPv4 или IPv6)
     * @return string IP адрес в бинарном виде
     */
    public static function build(string $addr):string {
        if (str_contains($addr, ':')) return self::buildV6($addr);
        elseif (str_contains($addr, '.')) return self::buildV4($addr);
        else return self::NULLED;
    }

    /**
     * Конвертирует IPv4/IPv6 из бинарной строки в понятный вид.
     *
     * @param string $addr IPv4 или IPv6 адрес в бинарном виде
     * @return string Отформатированная строка с IPv4 или IPv6 адресом
     */
    public static function format(string $addr):string {

        if (str_starts_with($addr, "\0\0\0\0\0\0\xff\xff")) {

            $addr = substr($addr, 8);
            $addr = unpack('n*', $addr);
            $addr = implode('.', $addr);

        } else {

            $addr = unpack('n*', $addr);
            $addr = array_map('dechex', $addr);
            $addr = implode(':', $addr);
            $addr = preg_replace('/(?::?0:)+/', '::', $addr, 1);

        }

        return $addr;
    }
}