<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Classic;

class Form extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Orba\Payupl\Model\Session $session
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Orba\Payupl\Model\Session $session,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /**
         * @var $resultRedirect \Magento\Framework\Controller\Result\Redirect
         * @var $resultPage \Magento\Framework\View\Result\Page
         */
        $orderCreateData = $this->session->getOrderCreateData();
        if ($orderCreateData) {
            $this->session->setOrderCreateData(null);
            $resultPage = $this->resultPageFactory->create(true, ['template' => 'Orba_Payupl::emptyroot.phtml']);
            $resultPage->addHandle($resultPage->getDefaultLayoutHandle());
            $resultPage->getLayout()->getBlock('orba.payupl.classic.form')->setOrderCreateData($orderCreateData);
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }
    }
}
