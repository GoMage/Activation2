<?php
/**
 * Created by PhpStorm.
 * User: Димасик
 * Date: 01.08.2018
 * Time: 22:14
 */

namespace GoMage\Core\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $random;
    public function __construct(
        \Magento\Framework\Math\Random $random
    )
    {
        $this->random = $random;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'gomage/key/act',
            'value' => substr(hash('sha512', $this->random->getRandomString(20)), -32),
        ];
        $setup->getConnection()
            ->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);
        $setup->endSetup();
    }
}
