<?php
/**
*
*    @copyright  Copyright (c) 2015 Yuri Zhang (http://blog.yurilab.com)
*    All rights reserved
*
*    file:            File.php
*    description:     文件操作封装
*
*    @author Yuri <zhang1437@gmail.com>
*    @license Apache v2 License
*
**/

/**SERVER_ROOT
* 文件操作
*/
class Library_File
{
    function __construct() {}

    /**
     * 载入签到结果
     * @param  string $fileName 活动token
     * @return array           签到结果数组
     */
    public static function load($fileName)
    {
        $dir = SERVER_ROOT . 'data/rollcall/%s.data';
        $file = sprintf($dir, $fileName);
        if (!file_exists($file)) {
            return false;
        }
        $content = file_get_contents($file);
        return unserialize($content);
    }

    /**
     * 写入签到结果
     * @param  string $fileName 活动token
     * @param  array $newlist  签到结果数组
     * @return int           写入文件的字节数，失败返回false
     */
    public static function write($fileName, $newlist)
    {
        $dir = SERVER_ROOT . 'data/rollcall/%s.data';
        if($oldList = self::load($fileName)) {
            if (count($oldList) >= count($newlist)) {
                //此处不使用双等于是为了避免$newList异常,覆写了原有的正确数据
                return true;
            }
        }
        $file = sprintf($dir, $fileName);
        return file_put_contents($file, serialize($newlist));
    }
}
 ?>
