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
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param array $data
     * @return \Orba\Payupl\Model\Client
     */
    public function create(array $data = [])
    {
        if ($this->scopeConfig->isSetFlag(Payupl::XML_PATH_CLASSIC_API, 'store')) {
            $class = Client\Classic::class;
        } else {
            $class = Client\Rest::class;
        }
        return $this->objectManager->create($class, $data);
    }
}
