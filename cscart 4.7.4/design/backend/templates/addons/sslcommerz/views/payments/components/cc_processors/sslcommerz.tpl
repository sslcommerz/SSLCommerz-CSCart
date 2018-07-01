<div class="control-group">
    {__("addons.sslcommerz.help", ["[callback_url]" => "payment_notification.sslcommerz"|fn_url:"C"])}
</div>

<div class="control-group">
    <label class="control-label" for="cryptext">{__("addons.sslcommerz.store_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][store_id]" id="store_id" value="{$processor_params.store_id}" class="input-text"  size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cryptext">{__("addons.sslcommerz.store_password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][store_password]" id="store_password" value="{$processor_params.store_password}" class="input-text"  size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test"{if $processor_params.mode == "test"} selected="selected"{/if}>{__("test")}</option>
            <option value="live"{if $processor_params.mode == "live"} selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="BDT" {if $processor_params.currency == "BDT"}selected="selected"{/if}>BDT</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" class="input-text" size="60" />
    </div>
</div>
