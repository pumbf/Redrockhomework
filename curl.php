<?php
class PaCong 
{
    //匹配url的正则表达式
    private  $pattern = array(
        'html'  => array(
            '/<link.*?href[ \t\n]*?=[ \t\n]*?[\'"]([^\'"]+)[\'"].*?>/',
            '/<script.*?src[ \t\n]*?=[ \t\n]*?[\'"]([^\'"]+)[\'"].*?>/',
            '/<img.*?src[ \t\n]*?=[ \t\n]*?[\'"]([^\'"]+)[\'"].*?>/',
            '/[ \t\n]*?url\(([^\)]*?)\).*?[,;\}]/'
        ),
        'css'    => array(
            '/[ \t\n]*?url\(["\']?([^\)]*?)["\']?\).*?[,;\}]/',
        ),
        'js'    =>  array(           
        )
    );

    private $annotation = array(
        'html'  => array(
            '/\\t/',
            '/\\n/',
            '/<!--(?s).*?-->/'
        ),
        'css'   => array(
            '/\\/\\*.*?\\*\\//'
        ),
        'js'    => array(
            '/\\/\\/.*?$/',
            '/\\/\\*.*?\*\\//'
        )
    );

    public function __construct($url, $fileAddress)
    {

       $this->runIt($url, $fileAddress);
       echo '加载完整</br>';

    }
    
    /**
     * 执行方法
     * @param  string $url         需抓取的网址
     * @param  string $fileAddress 存放目标文件的目录
     */
    private function runIt($url, $fileAddress)
    {
        //确保文件结构完整
        $this->makedir($fileAddress);
        $basename = basename($url);

        //如果带参数，把参数除去
        if($pos = strpos($basename, '?'))
            $basename = substr($basename, 0, $pos);
        $fileAddress = $fileAddress.'/'.$basename;
        $type = substr($fileAddress, strrpos($fileAddress, '.') + 1);
        $text = $this->runCurl($url, $fileAddress, $type);

        //如果返回了文本，就进行匹配工作
        if ($text) {
            $text = preg_replace($this->annotation[$type], '', $text);
            $get_url = array();

            //匹配所有可用url
            foreach ($this->pattern[$type] as $value) {
                preg_match_all($value, $text, $b);
                $get_url = array_merge($get_url, $b[1]);
            }

            foreach ($get_url as $value) {
                $file = $fileAddress;
                $lurl = $url;
                //将..转换到指定目录
                $this->subdir($value, $file, $lurl);
                $this->runIt($lurl.'/'.$value, $file.'/'.dirname($value));
            }
            // var_dump($get_url);
        }
    }

    /**
     * curl抓取
     * @param  string $url  需抓取的网址
     * @param  string $file 储存网站内容文件的地址
     * @param  string $type 文件类型
     * @return string       网站内容
     */
    private function runCurl($url, $file, $type)
    {

        // if ($type == 'html'|| $type == 'js' || $type == 'css')
        //     $f = fopen($file, 'w');
        // else 
        //     $f = fopen($file, 'wb');
        $curl = curl_init();
        
        $opt = array(
            CURLOPT_RETURNTRANSFER  => true,        //curl文件转换
            CURLOPT_URL             => $url,        //目标地址
            CURLOPT_HEADER          => false,       //是否显示请求头
            CURLOPT_NOBODY          => false,       //是否不显示<body>标签
            CURLOPT_CONNECTTIMEOUT  => 60           //超时时间
            //CURLOPT_FILE            => $f
        );
        curl_setopt_array($curl, $opt);             //加载参数
        $text = curl_exec($curl);                   //执行
        curl_close($curl);
        file_put_contents($file, $text);
        // fclose($f);
         if ($type == 'html'|| $type == 'js' || $type == 'css')        //文本文件返回文本信息
             return $text;
        return;
    }
    
    private function subdir(&$value, &$file, &$url)
    {
       if ($pos = strpos($value, '..') !== false) {
            $value = substr($value, $pos+2);
            //var_dump($value);
            $this->subdir($value,$file,$url);
            //var_dump($value);
        }
         $file = dirname($file);
         $url = dirname($url);
    }
    
    /**
     * 根据地址创建完整目录
     * @param  string $address 目标地址
     */
    private function makedir($address)
    {

        if (!file_exists(dirname($address)))
            $this->makedir(dirname($address));
        if (!file_exists($address))
            mkdir($address);
    }
}


$url = 'http://www.17sucai.com/preview/1/2016-01-24/%E5%B9%BB%E7%81%AF%E7%89%87/index.html';
$obj = new PaCong($url, './curl');
