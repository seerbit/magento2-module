define(
    [
        "jquery",
        'mage/url',
        "Magento_Checkout/js/view/payment/default",
        "Magento_Checkout/js/action/place-order",
        "Magento_Checkout/js/model/quote",
        "Magento_Checkout/js/model/full-screen-loader",
        "Magento_Checkout/js/action/redirect-on-success"
    ],
    function (
        $,
        mageUrl,
        Component,
        placeOrderAction,
        quote,
        fullScreenLoader,
        redirectOnSuccessAction,
    ) {
        'use strict';
        quote.billingAddress.subscribe(function (t) {
            window.currentCheckoutBillingAddress = t
        });

        return Component.extend({
            defaults: {
                template: 'Seerbit_Payment/payment/seerbit_payment',
                code: 'seerbit_payment',
            },
            seerbitLogo: window.SeerbitAssets.seerbit_logo,

            redirectAfterPlaceOrder: false,

            getCode: function () {
                return this.code;
            },

            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },

            getConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            },

            //Callback function to be called when SeerBit popup is closed by shopper
            cancelCallback: function (url) {
                fullScreenLoader.stopLoader();
                alert( 'Payment cancelled.');
                window.location.replace(mageUrl.build(url));
            },

            //Callback function to be called when SeerBit transaction fails
            errorCallback: function(message,url) {
                fullScreenLoader.stopLoader();
                alert( message || "Transaction Failed");
                window.location.replace(mageUrl.build(url));
            },

            getCustomerName: function(){
                try {
                    var fn = window.currentCheckoutBillingAddress.firstname || "";
                    var ln = window.currentCheckoutBillingAddress.lastname || "";
                    return fn + ' ' +ln
                }catch (e) {
                    return '';
                }
            },

            getStoreName: function(name){
                try {
                    return name.replace(/ /g,"_").substring(0,7).toLowerCase();
                }catch (e) {
                    return 'sbmg';
                }
            },

            /**
             * Override this method from the parent component.
             * The parent component is actually empty
             * @override
             */
            afterPlaceOrder: function () {
                var self = this;
                var checkoutConfig = window.checkoutConfig;
                var paymentData = quote.billingAddress();
                var seerbitConfiguration = this.getConfig();
                var quoteId = checkoutConfig.quoteItemData[0].quote_id;

                this.isPlaceOrderActionAllowed(false);

                    if (checkoutConfig.isCustomerLoggedIn) {
                        var customerData = checkoutConfig.customerData;
                        paymentData.email = customerData.email;
                    } else {
                        paymentData.email = quote.guestEmail;
                    }

                    this.isPlaceOrderActionAllowed(false);
                    var dateString = new Date().getTime();
                    var reference = this.getStoreName(seerbitConfiguration.store)+'sbmg'+quoteId+dateString
                    SeerbitPay({
                        tranref: reference,
                        orderId:quoteId,
                        full_name:self.getCustomerName(),
                        description:"SeerBit Transaction from Magento Store",
                        public_key: seerbitConfiguration.public_key,
                        email: paymentData.email,
                        amount: Math.ceil(quote.totals().grand_total),
                        mobile_no: paymentData.telephone,
                        country: window.currentCheckoutBillingAddress.countryId,
                        currency: checkoutConfig.totalsData.quote_currency_code
                    },
                         function (response) {
                             fullScreenLoader.startLoader();
                             if (response.code === "00") {
                                 $.ajax({
                                     url:seerbitConfiguration.api_url+'V1/seerbit/verify/'+response.transaction.reference,
                                     timeout:1000*60
                                 }).done(function (res) {
                                     fullScreenLoader.stopLoader();
                                     var verify_response = res[0];
                                     if (verify_response.status === 'success'){
                                         self.placeOrder();
                                         redirectOnSuccessAction.execute();
                                     }else{
                                         self.errorCallback(verify_response.message|| "Error Completing transaction. Kindly contact us to verify.", seerbitConfiguration.restore_cart_url);
                                     }
                                 }).fail(function (e) {
                                     fullScreenLoader.stopLoader();
                                     self.errorCallback(e.message || "Error Completing transaction. Kindly contact us to verify.",seerbitConfiguration.restore_cart_url);
                                 });

                             }
                             else if (response.code !== "S20") {
                                 fullScreenLoader.stopLoader();
                                self.errorCallback(response.message || "Error Completing transaction. Kindly contact us to verify.",seerbitConfiguration.restore_cart_url);
                             }
                         },
                         function(){
                             self.cancelCallback(seerbitConfiguration.restore_cart_url);
                        }
                    );

                }

        });
    }
);
