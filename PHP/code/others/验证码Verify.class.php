<?php

// +----------------------------------------------------------------------
// | 类功能:验证码verify
// +----------------------------------------------------------------------
// | 参数:$config(array)
// +----------------------------------------------------------------------
// | 备注:参照thinkphp官方该写的 步骤清晰多了,session保存还需要加密处理
// +----------------------------------------------------------------------

class Verify{
    //各默认配置项
    protected $config=array(
        'length'=>'4',                  //验证码个数
        'width'=>'250',                 //验证码宽度
        'height'=>'60',                 //验证码高度
        'seed'=>'2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',//验证码种子
        'size'=>'40',                   //验证码大小
        'filePath'=>'font.ttf',         //验证码字体文件位置
        'bg'=>array(243, 251, 254),     //背景颜色
        'disturb'=>40,                  //干扰字符个数
        'sessionVerify'=>'verifyCode',//session保存的验证码下标
        'sessionTime'=>'verifyTime',  //session保存的验证码时间戳
    );
    
    private $img;       //图像资源
    private $fontcolor; //字体颜色
    
    /**
     * 本类参数为数组
     * */
    public function __construct($config=array()){
        $this->config = array_merge($this->config, $config);
        $this->show();
    }
    
    //主体函数，显示验证码
    private function show(){
        
        //创建背景
        $this->img=imagecreatetruecolor($this->config['width'],$this->config['height']);
        $bgcolor=imagecolorallocate($this->img,$this->config['bg'][0],$this->config['bg'][1],$this->config['bg'][2]);
        imagefill($this->img,0,0,$bgcolor);
        
        //设置字体颜色
        $this->fontcolor=imagecolorallocate($this->img,mt_rand(0,200),mt_rand(0,200),mt_rand(0,200));
        
        //写入验证码,并保存session
        $this->font();
        
        //写入干扰字母
        $this->letter();
        
        //写入干扰曲线
        $this->curve();
        
        //输出图像
        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);		
        header('Pragma: no-cache');
        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }
    
    //验证码
    private function font(){
        
        //验证码两边空余
        $left = ($this->config['width'] - $this->config['size'] * $this->config['length']) / 2;
        $y = $this->config['height'] - ($this->config['height'] - $this->config['size']) / 2;
        
        $code='';   //验证码
        $slen=strlen($this->config['seed']) - 1;
        
        for($i=0;$i < $this->config['length'];$i++){
            
            $str=$this->config['seed'][mt_rand(0,$slen)];
            $x = $i * $this->config['size'] + $left;
            imagettftext($this->img,$this->config['size'],mt_rand(-30,30),$x,$y,$this->fontcolor,$this->config['filePath'],$str);
            $code.=$str;
        }
        
        //保存验证码信息............后续需要先加密再保存
        $_SESSION[$this->config['sessionVerify']]=$code;
        $_SESSION[$this->config['sessionTime']]=time();
    }
    
    //写干扰字母
    private function letter(){
        
        $slen=strlen($this->config['seed']) - 1;
        
        for($i=0;$i< $this->config['disturb'];$i++){
            
            $s=$this->config['seed'][mt_rand(0,$slen)];
            $c=imagecolorallocate($this->img,mt_rand(100,255),mt_rand(100,255),mt_rand(100,255));
            
            imagestring($this->img,5,mt_rand(0,$this->config['width']),mt_rand(0,$this->config['height']),$s,$c);

        }
    }
    
    /** 
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数) 
     *      
     *      本函数me写不出来 扣的onethink那的^_^
     *		正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     *
     */
    private function curve() {
        $px = $py = 0;
        $imageW=$this->config['width'];
        $imageH=$this->config['height'];
        // 曲线前部分
        $A = mt_rand(1, $imageH/2);                  // 振幅
        $b = mt_rand(-$imageH/4, $imageH/4);   // Y轴方向偏移量
        $f = mt_rand(-$imageH/4, $imageH/4);   // X轴方向偏移量
        $T = mt_rand($imageH, $imageW*2);  // 周期
        $w = (2* M_PI)/$T;
                        
        $px1 = 0;  // 曲线横坐标起始位置
        $px2 = mt_rand($imageW/2, $imageW * 0.8);  // 曲线横坐标结束位置
    
        for ($px=$px1; $px<=$px2; $px = $px + 1) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $imageH/2;  // y = Asin(ωx+φ) + b
                $i = (int) ($this->config['size']/5);
                while ($i > 0) {	
                    imagesetpixel($this->img, $px + $i , $py + $i, $this->fontcolor);  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多				
                    $i--;
                }
            }
        }
        
        // 曲线后部分
        $A = mt_rand(1, $imageH/2);                  // 振幅		
        $f = mt_rand(-$imageH/4, $imageH/4);   // X轴方向偏移量
        $T = mt_rand($imageH, $imageW*2);  // 周期
        $w = (2* M_PI)/$T;		
        $b = $py - $A * sin($w*$px + $f) - $imageH/2;
        $px1 = $px2;
        $px2 = $imageW;
    
        for ($px=$px1; $px<=$px2; $px=$px+ 1) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $imageH/2;  // y = Asin(ωx+φ) + b
                $i = (int) ($this->config['size']/5);
                while ($i > 0) {			
                    imagesetpixel($this->img, $px + $i, $py + $i, $this->fontcolor);	
                    $i--;
                }
            }
        }
    }
}




