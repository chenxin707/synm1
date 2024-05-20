<?php
declare(strict_types=1);
// 应用公共文件
use app\common\enum\FileEnum;
use app\common\model\File;

/**
 * @notes 生成密码加密密钥
 * @param string $plaintext
 * @param string $salt
 * @return string
 * @author 令狐冲
 * @date 2021/7/5 11:52
 */
function create_password(string $plaintext, string $salt): string
{
    return md5($salt . md5($plaintext . $salt));
}

/**
 * @notes 随机生成token值
 * @param string $extra
 * @return string
 * @author 令狐冲
 * @date 2021/7/1 19:04
 */
function create_token(string $extra = ''): string
{
    return md5($extra . time().uniqid('likeshop'));
}

/**
 * @notes 截取某字符字符串
 * @param $str
 * @param string $symbol
 * @return string
 * @author 令狐冲
 * @date 2021/7/7 18:42
 */
function substr_symbol_behind($str, $symbol = '.')
{
    $result = strripos($str, $symbol);
    if ($result === false) {
        return $str;
    };
    return substr($str, $result + 1);
}

/**
 * @notes 商品规格三维转二维
 * @param $array
 * @return array
 * @author cjhao
 * @date 2021/7/16 11:14
 */
function array_converting($array):array
{
    $list = array_map(function ($val){
        return array_column($val,'value');
    },$array);

    return $list;

}

/**
 * $list = [
 *   [
 *      0 => "黑巧风暴"
 *      1 => "白桃乌龙"
 *   ],
 *   [
 *      0 => "4G"
 *      1 => "8G"
 *      2 => "12G"
 *   ]
 * ]
 * 或
 * $color = ['黑色','白色'];
 * $memory = ['4G','6G']
 * @notes 返回笛卡尔积，可接收多个数组或一个二维数组
 * @return array|mixed
 * @author cjhao
 * @date 2021/7/13 14:47
 */
function cartesian_product():array
{
    $params = func_get_args();

    if (!is_array($params)) {
        return [];
    }
    if (count($params[0]) != count($params[0], 1)) {
//        $params = array_shift($params);
        $params = $params[0];
    }
    $firstArr = array_shift($params);

    array_walk($firstArr, function (&$val, $key) {
        $val = ['ids' => $key, 'spec_value' => $val];
    });
    //单个规格项目直接返回
    if (empty($params)) {
        return $firstArr;
    }


    $pCount = count($params);
    $specList = $firstArr;
    $separator = app\common\enum\GoodsEnum::SPEC_SEPARATOR;
    //多个规格项返回笛卡尔积
    for ($i = 0; $i < $pCount; $i++) {
        $tmp = [];
        foreach ($specList as $specVal) {
            foreach ($params[$i] as $paramKey => $paramVal) {
                $tmp[] = [
                    'ids'           => $specVal['ids'].$separator.$paramKey,
                    'spec_value'    => $specVal['spec_value'].$separator.$paramVal,
                ];
            }
        }
        $specList = $tmp;
    }

    return $specList;
}

/**
 * @notes 生成指定长度编码
 * @param int $len
 * @return string
 * @author 张无忌
 * @date 2021/7/20 15:52
 */
function create_code($len=6)
{
    $letter_all = range('A', 'Z');
    shuffle($letter_all);
    //排除I、O字母
    $letter_array = array_diff($letter_all, ['I', 'O']);
    //随机获取四位字母
    $letter = array_rand(array_flip($letter_array), 4);
    //排除1、0
    $num_array = range('2', '9');
    shuffle($num_array);
    //获取随机六位数字
    $num = array_rand(array_flip($num_array), $len);
    $code = implode('', array_merge($letter, $num));
    return $code;
}

/**
 * @notes 获取前几天的日期
 * @param int $day
 * @return array
 * @author 张无忌
 * @date 2021/7/21 15:53
 */
function get_datetime($day=7)
{
    $d = [];
    for ($i=1; $i<=$day; $i++) {
        $d[] = date('Y-m-d', strtotime('-'.$i.' days'));
    }

    return array_reverse($d);
}


/**
 * @notes 返回是否有下一页
 * @param $count
 * @param $page
 * @param $size
 * @return int
 * @author Tab
 * @date 2021/7/17 17:56
 */
function is_more($count, $page, $size)
{
    $more = 0;

    $last_page = ceil($count / $size);

    if ($last_page && $last_page > $page) {
        $more = 1;
    }
    return $more;
}

/**
 * 多级线性结构排序
 * 转换前：
 * [{"id":1,"pid":0,"name":"a"},{"id":2,"pid":0,"name":"b"},{"id":3,"pid":1,"name":"c"},
 * {"id":4,"pid":2,"name":"d"},{"id":5,"pid":4,"name":"e"},{"id":6,"pid":5,"name":"f"},
 * {"id":7,"pid":3,"name":"g"}]
 * 转换后：
 * [{"id":1,"pid":0,"name":"a","level":1},{"id":3,"pid":1,"name":"c","level":2},{"id":7,"pid":3,"name":"g","level":3},
 * {"id":2,"pid":0,"name":"b","level":1},{"id":4,"pid":2,"name":"d","level":2},{"id":5,"pid":4,"name":"e","level":3},
 * {"id":6,"pid":5,"name":"f","level":4}]
 * @param array $data 线性结构数组
 * @param string $symbol 名称前面加符号
 * @param string $name 名称
 * @param string $id_name 数组id名
 * @param string $parent_id_name 数组祖先id名
 * @param int $level 此值请勿给参数
 * @param int $parent_id 此值请勿给参数
 * @return array
 */
function linear_to_tree($data, $sub_key_name = 'sub', $id_name = 'id', $parent_id_name = 'pid', $parent_id = 0)
{
    $tree = [];
    foreach ($data as $row) {
        if ($row[$parent_id_name] == $parent_id) {
            $temp = $row;
            $child = linear_to_tree($data, $sub_key_name, $id_name, $parent_id_name, $row[$id_name]);
            if($child){
                $temp[$sub_key_name] = $child;
            }
            $tree[] = $temp;
        }
    }
    return $tree;
}


/**
 * @notes 通过时间生成订单编号
 * @param $table
 * @param $field
 * @param string $prefix
 * @param int $rand_suffix_length
 * @param array $pool
 * @return string
 * @author 段誉
 * @date 2021/7/23 14:15
 */
function generate_sn($table, $field, $prefix = '', $rand_suffix_length = 4, $pool = []) : string
{
    $suffix = '';
    for ($i = 0; $i < $rand_suffix_length; $i++) {
        if (empty($pool)) {
            $suffix .= rand(0, 9);
        } else {
            $suffix .= $pool[array_rand($pool)];
        }
    }
    $sn = $prefix . date('YmdHis') . $suffix;
    if ($table->where($field, $sn)->find()) {
        return generate_sn($table, $field, $prefix, $rand_suffix_length, $pool);
    }
    return $sn;
}

/**
 * @notes 随机生成邀请码
 * @param $length
 * @return string
 * @author Tab
 * @date 2021/7/26 11:17
 */
function generate_code($length = 6)
{
    // 去除字母IO数字012
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ3456789';
    // 随机起始索引
    $start = mt_rand(0, strlen($letters) - $length);
    // 打乱字符串
    $shuffleStr = str_shuffle($letters);
    // 截取字符
    $randomStr = substr($shuffleStr, $start, $length);
    // 判断是否已被使用
    $user = \app\common\model\User::where('code', $randomStr)->findOrEmpty();
    if($user->isEmpty()) {
        return $randomStr;
    }
    generate_code($length);
}

/**
 * User: 意象信息科技 lr
 * Desc: 下载文件
 * @param $url 文件url
 * @param $save_dir
 * @param $file_name 文件名
 * @return string
 */
function download_file($url, $save_dir, $file_name)
{
    if (!file_exists($save_dir)) {
        mkdir($save_dir, 0775, true);
    }
    $file_src = $save_dir . $file_name;
    file_exists($file_src) && unlink($file_src);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $file = curl_exec($ch);
    curl_close($ch);
    $resource = fopen($file_src, 'a');
    fwrite($resource, $file);
    fclose($resource);
    if (filesize($file_src) == 0) {
        unlink($file_src);
        return '';
    }
    return $file_src;
}

function create_user_sn($prefix = '', $length = 8)
{
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= mt_rand(0, 9);
    }
    $sn = $prefix . $rand_str;
    if (\app\common\model\User::where(['sn' => $sn])->find()) {
        return create_user_sn($prefix, $length);
    }
    return $sn;
}

/**
 * @notes 当前是否为cli模式
 * @return bool
 * @author 段誉
 * @date 2021/8/3 14:45
 */
function is_cli()
{
    return preg_match("/cli/i", php_sapi_name()) ? true : false;
}

/**
 * Notes:判断文件是否存在（远程和本地文件）
 * @param $file string 完整的文件链接
 * @return bool
 */
function check_file_exists($file)
{
    if ('http' == strtolower(substr($file, 0, 4))) {
        //远程文件
        $header = get_headers($file, true);
        return isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'));
    }
    // 本地文件
    return file_exists($file);
}

/**
 * Notes:去掉名称中的表情
 * @param $str
 * @return string|string[]|null
 */
function filterEmoji($str)
{
    $str = preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);
    return $str;
}

/**
 * 将图片切成圆角
 */
function rounded_corner($src_img)
{
    $w = imagesx($src_img);//微信头像宽度 正方形的
    $h = imagesy($src_img);//微信头像宽度 正方形的
    $w = min($w, $h);
    $h = $w;
    $img = imagecreatetruecolor($w, $h);
    //这一句一定要有
    imagesavealpha($img, true);
    //拾取一个完全透明的颜色,最后一个参数127为全透明
    $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
    imagefill($img, 0, 0, $bg);
    $r = $w / 2; //圆半径
//    $y_x = $r; //圆心X坐标
//    $y_y = $r; //圆心Y坐标
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $rgbColor = imagecolorat($src_img, $x, $y);
            if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                imagesetpixel($img, $x, $y, $rgbColor);
            }
        }
    }
    unset($src_img);
    return $img;
}

/**
 * @notes 浮点数去除无效的0
 * @param $float
 * @return int|mixed|string
 * @author Tab
 * @date 2021/8/11 10:17
 */
function clear_zero($float)
{
    if($float == intval($float)) {
        return intval($float);
    }else if($float == sprintf('%.1f', $float)) {
        return sprintf('%.1f', $float);
    }
    return $float;
}


/**
 * @notes 获取文件扩展名
 * @param $file
 * @return array|string|string[]
 * @author 段誉
 * @date 2021/8/14 15:24
 */
function get_extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}


/**
 * @notes 遍历指定目录下的文件(目标目录,排除文件)
 * @param $dir //目标文件
 * @param string $exclude_file //要排除的文件
 * @param string $target_suffix //指定后缀
 * @return array|false
 * @author 段誉
 * @date 2021/8/14 14:44
 */
function get_scandir($dir, $exclude_file = '', $target_suffix = '')
{
    if (!file_exists($dir) || empty(trim($dir))) {
        return [];
    }

    $files = scandir($dir);
    $res = [];
    foreach ($files as $item) {
        if ($item == "." || $item == ".." || $item == $exclude_file) {
            continue;
        }
        if (!empty($target_suffix)) {
            if (get_extension($item) == $target_suffix) {
                $res[] = $item;
            }
        } else {
            $res[] = $item;
        }
    }

    if (empty($item)) {
        return false;
    }
    return $res;
}


/**
 * @notes 解压压缩包
 * @param $file
 * @param $save_dir
 * @return bool
 * @author 段誉
 * @date 2021/8/14 15:27
 */
function unzip($file, $save_dir)
{
    if (!file_exists($file)) {
        return false;
    }
    $zip = new \ZipArchive();
    if ($zip->open($file) !== TRUE) {//中文文件名要使用ANSI编码的文件格式
        return false;
    }
    $zip->extractTo($save_dir);
    $zip->close();
    return true;
}


/**
 * @notes 删除目标目录
 * @param $path
 * @param $delDir
 * @return bool|void
 * @author 段誉
 * @date 2021/8/14 15:33
 */
function del_target_dir($path, $delDir)
{
    //没找到，不处理
    if (!file_exists($path)) {
        return false;
    }

    //打开目录句柄
    $handle = opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir("$path/$item")) {
                    del_target_dir("$path/$item", $delDir);
                } else {
                    unlink("$path/$item");
                }
            }
        }
        closedir($handle);
        if ($delDir) {
            return rmdir($path);
        }
    } else {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
}


/**
 * @notes 本地版本
 * @return mixed
 * @author 段誉
 * @date 2021/8/14 15:33
 */
function local_version()
{
    if(!file_exists('./upgrade/')) {
        // 若文件夹不存在，先创建文件夹
        mkdir('./upgrade/', 0777, true);
    }
    if(!file_exists('./upgrade/version.json')) {
        // 获取本地版本号
        $version = config('project.version');
        $data = ['version' => $version];
        $src = './upgrade/version.json';
        // 新建文件
        file_put_contents($src, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    $json_string = file_get_contents('./upgrade/version.json');
    // 用参数true把JSON字符串强制转成PHP数组
    $data = json_decode($json_string, true);
    return $data;
}

/**
 * @notes 隐藏字符串
 * @param string $str 需要隐藏的字符串
 * @param string $replaceStr 隐藏的字符串符号
 * @param int $replaceLength 隐藏的字符串符号长度
 * @return string
 * @author cjhao
 * @date 2021/8/16 19:23
 */
function hide_substr(string $str,string $replaceStr = '*',int $replaceLength = 3)
{
    $strlen   = mb_strlen($str, 'utf-8');
    $firstStr = mb_substr($str, 0, 1, 'utf-8');
    $lastStr  = mb_substr($str, -1, 1, 'utf-8');

    if($strlen > 3){
        return $firstStr . str_repeat($replaceStr, $replaceLength) . $lastStr;
    }
    return $firstStr . str_repeat($replaceStr, $replaceLength);
}


function day_time(int $days = 7,bool $timestamp = false,bool $isBefore = true,$starting = '')
{

    empty($starting) && $starting = strtotime(date('Y-m-d'));

    $time = $starting;
    if(false === $timestamp){
        $time = date('Y-m-d',$starting);
    }
    $dayList[] = $time;

    while ($days > 1){

        if($isBefore){
            $starting -= 86400;
        }else{
            $starting += 86400;
        }

        $time = $starting;
        if(false === $timestamp){
            $time = date('Y-m-d',$starting);
        }
        $dayList[] = $time;
        $days--;
    }
    return $dayList;

}


/**
 * @notes 自定义长度纯数字随机编码
 * @param $table
 * @param string $field
 * @param int $length
 * @param string $prefix
 * @return string
 * @author ljj
 * @date 2021/8/26 2:57 下午
 */
function create_number_sn($table, $field = 'sn', $length = 8, $prefix = '')
{
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= mt_rand(0, 9);
    }
    $sn = $prefix . $rand_str;
    if ($table->where($field, $sn)->find()) {
        return generate_sn($table, $field = 'sn', $length = 8, $prefix = '');
    }
    return $sn;
}

/**
 * @notes 腾讯地图转百度地图(gcj02->bd09)
 * @param $lat
 * @param $lng
 * @return float[]
 * @author ljj
 * @date 2021/8/27 2:42 下午
 */
function convert_gcj02_to_bd09($lat, $lng)
{
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng;
    $y = $lat;
    $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
    $lng = $z * cos($theta) + 0.0065;
    $lat = $z * sin($theta) + 0.006;
    return array('lng' => $lng, 'lat' => $lat);
}

/**
 * @notes 百度地图BD09坐标---->中国正常GCJ02坐标
 * @param $lat
 * @param $lng
 * @return float[]|int[]
 * @author ljj
 * @date 2021/8/27 2:43 下午
 */
function Convert_BD09_To_GCJ02($lat, $lng)
{
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng - 0.0065;
    $y = $lat - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $lng = $z * cos($theta);
    $lat = $z * sin($theta);
    return array('lng' => $lng, 'lat' => $lat);
}

/**
 * @notes 浮点数去除无效的0
 * @param $float
 * @return int|mixed|string
 * @author Tab
 * @date 2021/9/18 11:06
 */
function clearZero($float)
{
    if($float == intval($float)) {
        return intval($float);
    }else if($float == sprintf('%.1f', $float)) {
        return sprintf('%.1f', $float);
    }

    return $float;
}

/**
 * @notes 获取要使用的图片(用于多图片可选的时候，例：规格图片、商品图片，有规格图时先用规格图，没有则使用商品图)
 * @author Tab
 * @date 2021/9/18 11:40
 */
function get_image(array $images)
{
    foreach ($images as $item) {
        if (empty($item)) {
            continue;
        }
        return \app\common\service\FileService::getFileUrl(trim($item, '/'));
    }
    return '';
}

/**
 * @notes 天数格式化 例：1 格式化后变为 01
 * @param $day
 * @return mixed|string
 * @author Tab
 * @date 2021/9/27 11:01
 */
function day_format($day)
{
    if ($day < 10) {
        return '0' . $day;
    }
    return $day;
}



/**
 * @notes 获取服务端ip
 * @return array|false|mixed|string
 * @author 段誉
 * @date 2021/10/9 15:29
 */
function get_server_ip()
{
    if (!isset($_SERVER)) {
        return getenv('SERVER_ADDR');
    }

    if($_SERVER['SERVER_ADDR']) {
        return $_SERVER['SERVER_ADDR'];
    }

    return $_SERVER['LOCAL_ADDR'];
}

/**
 * @notes 创建唯一商户号
 * @param $model 数据表模型
 * @param $len 长度
 * @param $type 类型 all-任意大小写  upper-全大写 lower-全小写
 * @author Tab
 * @date 2021/12/14 14:28
 */
function create_shop_sn($model, $len, $type)
{
    $upperCase = range('A', 'Z');
    $lowerCase = range('a', 'z');
    $numbers = range(0, 9);

    switch($type) {
        case 'all':
            $data = array_merge($upperCase, $lowerCase, $numbers);
            break;
        case 'upper':
            $data = array_merge($upperCase, $numbers);
            break;
        case 'lower':
            $data = array_merge($lowerCase, $numbers);
            break;
    }
    shuffle($data);
    $dataStr = implode('', $data);
    $maxIndex = strlen($dataStr) - (int)$len;
    $index = mt_rand(0, $maxIndex);
    $sn = substr($dataStr, $index, (int)$len);
    $shop = $model->withoutGlobalScope()->where('sn', $sn)->findOrEmpty();
    // 判断唯一性
    if ($shop->isEmpty()) {
        return $sn;
    }
    // 递归
    create_shop_sn($model, $len, $type);
}

/**
 * @notes 二级域名
 * @author Tab
 * @date 2021/12/15 17:04
 */
function secondary_domain($domain = '')
{
    if('' == $domain){
        $domain = request()->domain();
        $domain = str_replace(request()->scheme() . '://', '', $domain);
    }
    $domainArr = explode('.', $domain);
    array_shift($domainArr);
    return implode('.',$domainArr);
}

/**
 * @notes 三级域名
 * @author Tab
 * @date 2021/12/15 17:04
 */
function third_domain($domain = '')
{
    if('' == $domain){
        $domain = request()->domain();
        $domain = str_replace(request()->scheme() . '://', '', $domain);
    }
    $domainArr = explode('.', $domain);
    return array_shift($domainArr);
}


/**
 * Notes: 获取ip
 * @author 段誉(2021/7/9 10:19)
 * @return array|false|mixed|string
 */
if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }
}
/**
 * @notes 判断访问设备是不是移动端
 * @return bool
 * @author cjhao
 * @date 2022/3/9 18:44
 */
function is_mobile()
{
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'textml') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'textml')))) {
            return true;
        }
    }
    return false;
}

/**
 * @notes 获取内容中的图片
 * @param string $str
 * @author ljj
 * @date 2023/3/14 5:14 下午
 */
function getContentImage($str = '')
{
    $result = '';
    if (!empty($str)) {
        preg_match_all('/<img[^>]*?src="([^"]*?)"[^>]*?>/i',$str,$result);
    }

    return $result;
}


/**
 * @notes 保存远程图片到本地
 * @param $file_name //图片名称
 * @param $absolute_path//绝对路径
 * @param $save_url//保存路径
 * @return File|\think\Model
 * @author ljj
 * @date 2023/3/16 3:24 下午
 */
function saveImageToLocal($file_name,$absolute_path,$save_url = 'uploads/images/')
{
    try {
        $data = file_get_contents($absolute_path);
//        file_put_contents($save_url.$file_name,$data,FILE_USE_INCLUDE_PATH);
        $f = fopen($save_url.$file_name,"w");
        fwrite($f,$data);
        fclose($f);
        //写入数据库中
        $file = File::duokaiCreate([
            'cid'         => 0,
            'type'        => FileEnum::IMAGE_TYPE,
            'name'        => $file_name,
            'uri'         => $save_url.$file_name,
            'source'      => 0,
            'source_id'   => 0,
        ]);
    } catch (\Exception $e) {
        $file = [];
    }


    return $file;
}


/**
 * @notes 校验HTTP
 * @param $url
 * @return mixed|string
 * @author ljj
 * @date 2023/3/16 3:39 下午
 */
function checkHttp($url)
{
    $preg = "/^http(s)?:\\/\\/.+/";
    if(!preg_match($preg,$url))
    {
        $url = ltrim($url,'//');
        $url = 'http://'.$url;
    }

    return $url;
}
