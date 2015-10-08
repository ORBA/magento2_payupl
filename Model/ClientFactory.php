<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class ClientFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param array $data
     * @return object
     */
    public function create(array $data = [])
    {
        if ($this->_scopeConfig->isSetFlag(Payupl::XML_PATH_CLASSIC_API)) {
            $class = Client\Classic::class;
        } else {
            $class = Client\Rest::class;
        }
        return $this->_objectManager->create($class, []);
    }
}