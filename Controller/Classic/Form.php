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
    protected $_session;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Orba\Payupl\Model\Session $session
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Orba\Payupl\Model\Session $session,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->_session = $session;
        $this->_resultPageFactory = $resultPageFactory;
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
        $orderCreateData = $this->_session->getOrderCreateData();
        if ($orderCreateData) {
            $this->_session->setOrderCreateData(null);
            $resultPage = $this->_resultPageFactory->create(true, ['template' => 'Orba_Payupl::emptyroot.phtml']);
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