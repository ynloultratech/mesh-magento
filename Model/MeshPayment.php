<?php

namespace Mesh\MeshPayment\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class MeshPayment extends AbstractMethod
{
    const CODE = 'meshpayment';

    protected $_code = self::CODE;
}
