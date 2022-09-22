<div id="vendor_panel_config">

    <div class="control-group">
        <label for="vendor_panel_element_color" class="control-label">{__("vendor_panel_configurator.element_color")}</label>
        <div class="controls">
            <div id="vendor_panel_element_color" class="colorpicker--wrapper">
                {include file="common/colorpicker.tpl"
                cp_name="vendor_panel[element_color]"
                cp_id="feature_value_color_picker_{$num}"
                cp_value=$vendor_panel.element_color
                show_picker=true
                cp_meta="js-feature-variant-conditional-column"
                cp_attrs=["data-ca-column-for-feature-style" => "ProductFeatureStyles::COLOR"|enum, "data-ca-column-for-filter-style" => "ProductFilterStyles::COLOR"|enum]
                }
            </div>
        </div>
    </div>
    <div class="control-group">
        <label for="vendor_panel_sidebar_color" class="control-label">{__("vendor_panel_configurator.sidebar_color")}</label>
        <div class="controls">
            <div id="vendor_panel_sidebar_color" class="colorpicker--wrapper">
                {include file="common/colorpicker.tpl"
                cp_name="vendor_panel[sidebar_color]"
                cp_id="feature_value_color_picker_{$num}"
                cp_value=$vendor_panel.sidebar_color
                show_picker=true
                cp_meta="js-feature-variant-conditional-column"
                cp_attrs=["data-ca-column-for-feature-style" => "ProductFeatureStyles::COLOR"|enum, "data-ca-column-for-filter-style" => "ProductFilterStyles::COLOR"|enum]
                }
            </div>
        </div>
    </div>
    <div class="control-group">
        <label for="vendor_panel_background_image" class="control-label">{__("vendor_panel_configurator.sidebar_background_image")}</label>
        <div class="controls">
            {include file="common/attach_images.tpl"
                image_name="vendor_panel_background"
                image_object_type="vendor_panel"
                image_pair=$vendor_panel.main_pair
                image_object_id=0
                no_detailed=true
                hide_titles=true
            }
            <p class="muted description">{__("vendor_panel_configurator.sidebar_background_image_description")}</p>
        </div>
    </div>
</div>