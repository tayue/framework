<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/13
 * Time: 9:35
 */

namespace Framework\SwServer;


class View
{
    public $config = [];
    public $smarty;

    public function __construct($config = [])
    {
        $this->smarty = new \Smarty();
        $this->smarty->setCompileDir(SMARTY_COMPILE_DIR);
        $this->smarty->setCacheDir(SMARTY_CACHE_DIR);
        $smarty_template_path = rtrim(SMARTY_TEMPLATE_PATH) . '/';
        $this->smarty->setTemplateDir($smarty_template_path);
        $this->smarty->caching = 0; //开启缓存,为flase的时侯缓存无效
        $this->smarty->cache_lifetime = 0; //缓存时间
    }

    /**
     * assign 赋值
     * @param    $name
     * @param    $value
     * @return
     */
    public function assign($name, $value)
    {
        $this->smarty->assign($name, $value);
    }

    /**
     * mAssign 批量赋值
     * @param    $arr
     * @return   boolean|null
     */
    public function mAssign($arr = [])
    {
        if (!empty($arr)) {
            if (is_string($arr)) {
                return false;
            }
            foreach ($arr as $name => $value) {
                $this->assign($name, $value);
            }
        }
        return false;
    }


    public function display($template_file = '')
    {
        $template_file = rtrim($template_file, '/');
        $module = ServerManager::getModule();
        $controller = ServerManager::getController();
        $action = ServerManager::getAction();
        $fileType = ".html";
        $projectType = ServerManager::getProjectType();
        if (!$template_file) {
            $template_file = $action . $fileType;
        }
        $filePath = SMARTY_TEMPLATE_PATH . '/' . $controller . '/' . $template_file;
        $fetchFile = $controller . '/' . $template_file;
        if ($projectType) {
            $filePath = SMARTY_TEMPLATE_PATH . '/' . $module . '/' . $controller . '/' . $template_file;
            $fetchFile = $module . '/' . $controller . '/' . $template_file;
        }
        if (is_file($filePath)) {
            $tpl = $this->smarty->fetch($fetchFile);
        } else { //进入404页面
            $fetchFile = 'Error/error.html';
            $tpl = $this->smarty->fetch($fetchFile);
        }
        echo $tpl;


    }

}