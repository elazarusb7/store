Drupal.behaviors.shipping_method = {
    attach: function (context, settings) {
        //console.log(document.title);
        //edit-recalculate-shipping
        if(document.title == 'Add shipment | SAMHSA Publications') {

            //edit-shipping-method-0-1-default
            var shipping_method2 = document.querySelectorAll('input[data-drupal-selector="edit-shipping-method-0-1-default"]');
            var label2 = document.querySelector("label[for=" + shipping_method2[0].id + "]");
            //alert(label2.textContent);
            //var n2 = (label2.textContent == "USPS Standard Free Shipping: 3-4 weeks shipping time");
            var n2 = (label2.textContent.indexOf('USPS Standard Free Shipping') > -1);
            //var n2 = label2.innerHTML.includes("USPS Standard Free Shipping");

            if(n2) {
                shipping_method2[0].className += " visually-hidden";

                var recalculate_shipping_btn2 = document.querySelector('input[data-drupal-selector="edit-recalculate-shipping"]');
                if (recalculate_shipping_btn2) {
                    recalculate_shipping_btn2.className += " visually-hidden";
                }
            }
        }
        //edit-shipping-information-shipments-0-shipping-method-0-1-default // edit-shipping-method-0-1-default
        var shipping_method = document.querySelectorAll('input[data-drupal-selector*="edit-shipping-information-shipments-0-shipping-method-0-"]');
        if(shipping_method.length == 1){
            var label = document.querySelector("label[for=" + shipping_method[0].id + "]");
            //alert(label3.textContent);
            //var n = (label.textContent == "USPS Standard Free Shipping: 3-4 weeks shipping time");
            var n = (label.textContent.indexOf('USPS Standard Free Shipping') > -1);
            //var n = label.innerHTML.includes("USPS Standard Free Shipping");

            if(n) {
                shipping_method[0].className += " visually-hidden";

                //hide edit-shipping-information-recalculate-shipping button
                var recalculate_shipping_btn = document.querySelector('input[data-drupal-selector*="edit-shipping-information-recalculate-shipping"]');
                if (recalculate_shipping_btn) {
                    recalculate_shipping_btn.className += " visually-hidden";
                }
            }
        }

        //edit order shipping page
        var shipping_method3 = document.querySelectorAll('input[data-drupal-selector*="edit-shipping-method-0-1-default"]');
        if(shipping_method3.length == 1){

            var label3 = document.querySelector("label[for=" + shipping_method3[0].id + "]");
            //alert(label3.textContent);
            //var n3 = (label3.textContent == "USPS Standard Free Shipping: 3-4 weeks shipping time");
            var n3 = (label3.textContent.indexOf('USPS Standard Free Shipping') > -1);
            //var n3 = label3.innerHTML.includes("USPS Standard Free Shipping");

            if(n3) {
                shipping_method3[0].className += " visually-hidden";

                //hide edit-shipping-information-recalculate-shipping button
                var recalculate_shipping_btn3 = document.querySelector('input[data-drupal-selector*="edit-recalculate-shipping"]');
                if (recalculate_shipping_btn3) {
                    recalculate_shipping_btn3.className += " visually-hidden";
                }
            }
        }


    }
};