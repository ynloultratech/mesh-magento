<?php

namespace Mesh\MeshPayment\Controller\Paynup;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DB\Transaction;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\ScopeInterface;
use Mesh\MeshPayment\Helper\Data as MeshPaymentHelper;

class Paynup extends Action
{

    const API_KEY = 'payment/meshpayment/api_key';
    const API_ID = 'payment/meshpayment/api_iss';
    const AUTO_REDIRECT = 'payment/meshpayment/autoRedirect';
    const AUTO_REDEEM = 'payment/meshpayment/autoRedeem';
    const ALLOW_SHARE = 'payment/meshpayment/allowShare';
    const QR_CODE = 'payment/meshpayment/qrCode';

    /**
     * @var Order
     */
    protected $_order;
    /**
     * @var InvoiceService
     */
    protected $_invoiceService;
    /**
     * @var InvoiceSender
     */
    protected $_invoiceSender;
    /**
     * @var Transaction
     */
    protected $_transaction;
    /**
     * @var ResponseFactory
     */
    private $_responseFactory;
    /**
     * @var MeshPaymentHelper
     */
    private $meshPaymentHelper;
    /**
     * @var CheckoutSession
     */
    private $_checkoutSession;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Response constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Order $order
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param MeshPaymentHelper $meshPaymentHelper
     * @param Redirect $resultRedirectFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Order $order,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        MeshPaymentHelper $meshPaymentHelper,
        Redirect $resultRedirectFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_order = $order;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_invoiceSender = $invoiceSender;
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->meshPaymentHelper = $meshPaymentHelper;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $api_key = $this->scopeConfig->getValue(self::API_KEY, ScopeInterface::SCOPE_STORE);
        $api_iss = $this->scopeConfig->getValue(self::API_ID, ScopeInterface::SCOPE_STORE);
        $autoRedirect = $this->scopeConfig->isSetFlag(self::AUTO_REDIRECT, ScopeInterface::SCOPE_STORE);
        $autoRedeem = $this->scopeConfig->isSetFlag(self::AUTO_REDEEM, ScopeInterface::SCOPE_STORE);
        $allowShare = $this->scopeConfig->isSetFlag(self::ALLOW_SHARE, ScopeInterface::SCOPE_STORE);
        $qrCode = $this->scopeConfig->isSetFlag(self::QR_CODE, ScopeInterface::SCOPE_STORE);

        $order = $this->_checkoutSession->getLastRealOrder();
        $total = $order->getBaseGrandTotal() * 100;

        $order->setState(Order::STATE_PENDING_PAYMENT)
            ->setStatus(Order::STATE_PENDING_PAYMENT);
        $order->save();

        if ($order->canInvoice()) {

            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->_transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->_invoiceSender->send($invoice);

        }

        $orderId = $order->getIncrementId();
        $amount = $order->getGrandTotal();

        $email = $order->getBillingAddress()->getEmail();
        $name = $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();
        $phone = $order->getBillingAddress()->getTelephone();
        $city = $order->getBillingAddress()->getRegion();
        $state = $order->getBillingAddress()->getRegionCode();
        $postcode = $order->getBillingAddress()->getPostcode();
        $address = $order->getBillingAddress()->getStreet();

        $token = $this->meshPaymentHelper->getToken($orderId);

        $sucessUrl = $this->_url->getUrl('checkout/onepage/success');
        $HandlerUrl = $this->_url->getUrl('meshpayment/ipn/handler');

//        $testWebHok = "https://webhook.site/43aef863-9708-41c0-a61e-360043f50ecc";

        $claim_data = [
            "iss" => $api_iss,
            "jti" => $api_key,
            "iat" => time(),
            "params" => [
                "redirectUrl" => $sucessUrl,
                "autoRedirect" => $autoRedirect,
                "autoRedeem" => $autoRedeem,
//                "allowRedeem" => true,
                "qrCode" => $qrCode,
                "orderNumber" => $orderId,
                "receiptEmail" => $email,
                "amount" => $amount,
                "customerName" => $name,
                "customerPhone" => $phone,
                "billingAddress" => $address[0],
                "billingCity" => $city,
                "billingState" => $state,
                "billingZipCode" => $postcode,
                "IPNHandlerUrl" => $HandlerUrl,
                "token" => $token
            ]
        ];

        $claim = $this->meshPaymentHelper->getClaim($claim_data);

        $resultRedirect = $this->resultRedirectFactory->create();

        $redLinkClaim = 'https://egiftcert.paynup.com?claim=' . $claim;

        $redLink = 'https://egiftcert.paynup.com?token=' . $token
            . '&orderNumber=' . $orderId
            . '&amount=' . $amount
            . '&redirectUrl=' . $sucessUrl
            . '&IPNHandlerUrl=' . $HandlerUrl
            . '&autoRedirect=' . $autoRedirect
            . '&allowShare=' . $allowShare
            . '&autoRedeem=' . $autoRedeem
            . '&qrCode=' . $qrCode;

        $resultRedirect->setUrl($redLinkClaim);

        return $resultRedirect;
    }
}
