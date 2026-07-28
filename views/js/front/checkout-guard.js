(function ($) {
    $(function () {
        let $infos = $('.merchantcredit-infos[data-has-credit="0"]').last();
        if (!$infos.length) {
            return;
        }

        let $paymentOption = $infos.closest('.payment-option, .js-payment-option-form');
        if (!$paymentOption.length) {
            $paymentOption = $infos.closest('div').parent();
        }

        let $radio = $paymentOption.find('input[type="radio"][name="payment-option"]');
        $radio.prop('disabled', true).prop('checked', false);

        let $label = $paymentOption.find('label[for="' + $radio.attr('id') + '"]');
        $label.css({ 'opacity': '0.5', 'cursor': 'not-allowed' });
    });
})(jQuery);
