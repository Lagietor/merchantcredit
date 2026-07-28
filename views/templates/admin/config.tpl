<div class="panel">
    <div class="panel-heading">
        <i class="icon-credit-card"></i>
        {l s='Merchant Credit Settings' mod='merchantcredit'}
    </div>
    <form method="post" action="{$merchantcredit_action_url|escape:'html'}">
        <div class="form-group">
            <label for="{$merchantcredit_key_default_limit|escape:'html'}">
                {l s='Default credit limit' mod='merchantcredit'}
            </label>
            <input
                type="number"
                id="{$merchantcredit_key_default_limit|escape:'html'}"
                name="{$merchantcredit_key_default_limit|escape:'html'}"
                value="{$merchantcredit_default_limit|floatval}"
                min="0"
                step="0.01"
                class="form-control"
                style="max-width: 200px;"
            />
            <p class="help-block">
                {l s='Credit limit assigned to new customers when their record is first created.' mod='merchantcredit'}
            </p>
        </div>
        <div class="panel-footer">
            <button type="submit" name="{$merchantcredit_submit_name|escape:'html'}" class="btn btn-default pull-right">
                <i class="process-icon-save"></i>
                {l s='Save' mod='merchantcredit'}
            </button>
        </div>
    </form>
</div>
