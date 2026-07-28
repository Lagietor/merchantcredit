{*
 * Merchantcredit — additional information displayed under the payment option on checkout.
 *}
<div class="merchantcredit-infos" data-has-credit="{if $has_enough_credit}1{else}0{/if}">
    <p>{l s='Available merchant credit:' d='Modules.Merchantcredit.Front'} <strong>{$remaining_formatted}</strong></p>
    <p>{l s='Amount to pay:' d='Modules.Merchantcredit.Front'} <strong>{$total_formatted}</strong></p>

    {if $has_enough_credit}
        <p class="text-muted">
            {l s='The order amount will be deducted from your available credit once the order is placed.' d='Modules.Merchantcredit.Front'}
        </p>
    {else}
        <div class="alert alert-danger merchantcredit-insufficient" role="alert">
            {l s='Your merchant credit is insufficient for this order. Please choose another payment method.' d='Modules.Merchantcredit.Front'}
        </div>
    {/if}
</div>

{if !$has_enough_credit}
<script>
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
</script>
{/if}
