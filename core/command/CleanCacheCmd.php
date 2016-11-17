<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * 迁移数据库命令
 * php hunter migrate:db
 */
class CleanCacheCmd extends BaseCommand {
    protected function configure() {
        $this->setName('cc');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $dirs = array('sites/cache', 'sites/temp', 'sites/backup', 'sites/logs');

        $result = $this->clean_cache($dirs);

        if($result){
          $output->writeln('['.date("Y-m-d H:i:s").'] cache clean finished!');
        }else{
          $output->writeln('['.date("Y-m-d H:i:s").'] cache clean failed!');
        }
    }

    /**
     * 清空缓存
     * @route /admin/clean/cache
     */
    protected function clean_cache($dirs) {
      foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            //先删除目录下的文件：
            $dh = opendir($dir);
            while ($file = readdir($dh)) {
                if ($file != "." && $file != "..") {
                    $fullpath = $dir . "/" . $file;
                    if (!is_dir($fullpath)) {
                        unlink($fullpath);
                    } else {
                        $this->clean_cache($fullpath);
                    }
                }
            }
            closedir($dh);
            return true;
        } else {
            if (file_exists($dir)) {
                unlink($dir);
            }
            return true;
        }
        return false;
      }
    }
}
