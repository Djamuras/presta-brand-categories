{* views/templates/admin/product_brand_categories.tpl *}
<div class="form-group">
    <h4>{l s='Brand Categories' mod='brandcategorymodule'}</h4>
    
    {foreach from=$manufacturers item=manufacturer}
        <div class="brand-manufacturer-section">
            <h5>{$manufacturer.name}</h5>
            <div class="row">
                {foreach from=$brand_categories item=brand_category}
                    {if $brand_category.id_manufacturer == $manufacturer.id_manufacturer}
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" 
                                           name="brand_categories[]" 
                                           value="{$brand_category.id_brand_category}"
                                           {if in_array($brand_category, $current_brand_categories)}checked{/if}
                                    >
                                    {$brand_category.name}
                                </label>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </div>
        </div>
    {/foreach}

    {if !$brand_categories}
        <div class="alert alert-info">
            {l s='No brand categories found. Create categories in the module configuration.' mod='brandcategorymodule'}
        </div>
    {/if}
</div>