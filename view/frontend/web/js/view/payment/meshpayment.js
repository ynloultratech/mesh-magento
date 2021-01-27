define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'meshpayment',
                component: 'Mesh_MeshPayment/js/view/payment/method-renderer/meshpayment'
            }
        );

        return Component.extend({});
    });
