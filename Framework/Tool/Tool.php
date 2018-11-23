<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/5
 * Time: 13:33
 */

namespace Framework\Tool;


class Tool
{
    public static function createDir($path, $mode = 0777)
    {
        if (is_dir($path))
            return TRUE;
        $ftp_enable = 0;
        $path = self::dir_path($path);
        $temp = explode('/', $path);
        $cur_dir = '';
        $max = count($temp) - 1;
        for ($i = 0; $i < $max; $i++) {
            $cur_dir .= $temp[$i] . '/';
            if (@is_dir($cur_dir))
                continue;
            @mkdir($cur_dir, 0777, true);
            @chmod($cur_dir, 0777);
        }
        return is_dir($path);
    }

    /**
     * 转化 \ 为 /
     *
     * @param    string $path 路径
     * @return    string    路径
     */
    public static function dir_path($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/')
            $path = $path . '/';
        return $path;
    }
}