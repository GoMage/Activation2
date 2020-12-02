<?php

/**
 * GoMage.com
 *
 * GoMage Core M2
 *
 * @category  Extension
 * @copyright Copyright (c) 2018-2018 GoMage.com (https://www.gomage.com)
 * @author    GoMage.com
 * @license   https://www.gomage.com/licensing  Single domain license
 * @terms     of use https://www.gomage.com/terms-of-use
 * @version   Release: 2.0.0
 * @since     Class available since Release 2.0.0
 */

namespace GoMage\Core\Model\Processors;

/**
 * Class ProcessorR
 * @package GoMage\Core\Model\Processors
 */
class ProcessorR
{

    const M_XML = '/module.xml';
    const S_XML = '/adminhtml/system.xml';

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $reader;

    /**
     * @var \Magento\Framework\Xml\Parser
     */
    private $parser;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $fullModuleList;

    /**
     * ProcessorR constructor.
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param \Magento\Framework\Xml\Parser $parser
     * @param \Magento\Framework\Module\ModuleListInterface $fullModuleList
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\Module\ModuleListInterface $fullModuleList
    )
    {
        $this->reader = $reader;
        $this->parser = $parser;
        $this->fullModuleList = $fullModuleList;
    }

    /**
     * @return array
     */
    public function getPermS()
    {
        $p = [];
        $names = $this->fullModuleList->getNames();
        foreach ($names as $name) {
            $nn = strpos($name, 'GoMage');
            if (0 === $nn) {
                if ($this->isD($name)) {
                    foreach ($this->getPermMS($name) as $item) {
                        $p[$item] = $item;
                    }
                }
            }
        }
        return $p;
    }

    /**
     * @param $name
     * @return bool
     */
    public function isD($name)
    {
        return isset($this->getSMC($name)[$this->getMN()]);
    }

    /**
     * @param $name
     * @return bool
     */
    private function getSMC($name)
    {
        $c = $this->getMC($name);
        $seq = [];
        if (!empty($c['sequence'])) {
            foreach ($c['sequence']['module'] as $item) {
                if (isset($item['name'])) {
                    $seq[$item['name']] = $item['name'];
                } else {
                    if (isset($item['_attribute']['name'])) {
                        $seq[$item['_attribute']['name']] = $item['_attribute']['name'];
                    }
                }
            }
        }
        return $seq;
    }

    /**
     * @param $name
     * @return mixed
     */
    private function readC($name, $s = self::M_XML)
    {
        $filePath = $this->reader->getModuleDir('etc', $name)
            . $s;
        return $this->parser->load($filePath)->xmlToArray()['config']['_value'];
    }

    /**
     * @param $name
     * @return mixed
     */
    private function getMC($name)
    {
        return $this->readC($name)['module']['_value'];
    }

    /**
     * @return string
     */
    private function getMN()
    {
        $class = explode('\\', get_class($this));
        return $class[0] . '_' . $class[1];
    }

    /**
     * @param $name
     * @return array
     */
    private function getPermMS($name)
    {
        $perm_s = [];
        $s = $this->readC($name, self::S_XML)['system']['section'];
        if (isset($s['_attribute'])) {
            $perm_s[$s['_attribute']['id']] = $s['_attribute']['id'];
        } else {
            foreach ($s as $item) {
                $perm_s[$item['_attribute']['id']] = $item['_attribute']['id'];
            }
        }
        return $perm_s;
    }
}