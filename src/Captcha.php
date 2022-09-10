<?php

namespace Ddddocr;

class Captcha
{
    public static string $python_execute;

    /**
     * 初始化执行路径
     * 版本要求3.7以上
     *
     * @param string $python_execute
     * Python执行路径,默认python3
     *
     * @return void
     */
    public static function init(string $python_execute = ''): void
    {
        self::$python_execute = $python_execute != '' ? $python_execute : 'python3';
    }

    /**
     * @throws \Exception
     */
    public static function file(string $filename)
    {
        if(!self::hasExec()) throw new \Exception('Please open function Exec.');
        if(!self::hasPython()) throw new \Exception('Please install Python3.7 or higher.');
        if(!self::isLinux()) throw new \Exception('Non Linux systems are not supported.');
        if(!file_exists($filename)) throw new \Exception('File not found.');
        $file = fopen($filename,'r');
        $image = fread($file,filesize($filename));
        $base64_string = base64_encode($image);
        if(!imagecreatefromstring($image)) throw new \Exception('Invalid image.');
        $temp_file = tempnam(sys_get_temp_dir(), '');
        $handle = fopen($temp_file, "w");
        $script = "import ddddocr\rimport base64\rimport json\rimg = base64.b64decode('$base64_string')\rocr = ddddocr.DdddOcr()\rprint(ocr.classification(img))";
        fwrite($handle,$script);
        $commands = exec(self::$python_execute.' '. $temp_file);
        fclose($handle);
        @unlink($temp_file);
        return $commands;
    }

    /**
     * @throws \Exception
     */
    public static function base64(string $base64_string)
    {
        if(!self::hasExec()) throw new \Exception('Please open function Exec.');
        if(!self::hasPython()) throw new \Exception('Please install Python3.7 or higher.');
        if(!self::isLinux()) throw new \Exception('Non Linux systems are not supported.');
        if(base64_decode($base64_string) === false) throw new \Exception('Invalid Base64 string.');
        if(!imagecreatefromstring(base64_decode($base64_string))) throw new \Exception('Invalid image');
        //create temp file
        $temp_file = tempnam(sys_get_temp_dir(), '');
        $handle = fopen($temp_file, "w");
        $script = "import ddddocr\rimport base64\rimport json\rimg = base64.b64decode('$base64_string')\rocr = ddddocr.DdddOcr()\rprint(ocr.classification(img))";
        fwrite($handle,$script);
        $commands = exec(self::$python_execute.' '. $temp_file);
        fclose($handle);
        @unlink($temp_file);
        return $commands;
    }

    private static function hasPython(){
        $python_version = exec(self::$python_execute.' -V');
        preg_match_all('/\d+/',$python_version,$matches);
        if($matches[0][0] == '3' and (int)$matches[0][1] >= 7) return true;
        return false;
    }

    private static function hasExec(){
        return function_exists('exec');
    }

    private static function hasddddocr()
    {
        exec('pip3 show ddddocr',$commands);
        return !empty($commands);
    }

    private static function isLinux()
    {
        return !str_contains(PHP_OS, 'WIN');
    }
}