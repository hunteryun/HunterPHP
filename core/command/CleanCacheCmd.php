<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * 清空缓存命令
 * php hunter cc
 */
class CleanCacheCmd extends BaseCommand {
    protected function configure() {
        $this->setName('cc')->setDescription('commands.clean.cache.description');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $dirs = array('sites/cache', 'sites/html', 'sites/temp', 'sites/backup', 'sites/logs');

        foreach ($dirs as $dir) {
          $this->clean_cache($dir);
        }

        $output->writeln('['.date("Y-m-d H:i:s").'] cache clean finished!');
    }

    /**
     * 清空缓存
     */
    protected function clean_cache($dir) {
      if(is_dir($dir)){
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
          if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                $this->clean_cache($fullpath);
            }
          }
        }

        closedir($dh);
      }
    }
}
