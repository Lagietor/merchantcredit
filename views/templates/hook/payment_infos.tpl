{*
 * Merchantcredit — additional information displayed under the payment option on checkout.
 *}
<div class="merchantcredit-infos">
    <p>{l s='Available merchant credit:' d='Modules.Merchantcredit.Front'} <strong>{$remaining_formatted}</strong></p>
    <p>{l s='Amount to pay:' d='Modules.Merchantcredit.Front'} <strong>{$total_formatted}</strong></p>
    <p class="text-muted">
        {l s='The order amount will be deducted from your available credit once the order is placed.' d='Modules.Merchantcredit.Front'}
    </p>
</div>
