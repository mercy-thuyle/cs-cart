{$is_block_enabled = $block.status === "A"}
<div class="bm-block-manager__menu-wrapper" data-ca-block-manager-menu-wrapper>
    <div class="bm-block-manager__menu" data-ca-block-manager-menu>
        <div class="bm-block-manager__handler">
            {include_ext file="common/icon.tpl"
                class="ty-icon-handler bm-block-manager__icon"
            }
        </div>
        <a href="{fn_url("block_manager.manage&selected_location={$parent_grid.location_id}&object_id={$block.snapping_id}&type=snapping", "A")}"
           class="bm-block-manager__btn bm-block-manager__properties"
           target="_blank"
        >
            {include_ext file="common/icon.tpl"
                class="ty-icon-cog bm-block-manager__icon"
            }
        </a>
        <button type="button"
                class="bm-block-manager__btn bm-block-manager__switch {if !$is_block_enabled}bm-block-manager__block--disabled{/if}"
                data-ca-block-manager-action="switch"
                data-ca-block-manager-switch="{if $is_block_enabled}false{else}true{/if}"
        >
            {$icon_eye_open = "ty-icon-eye-open bm-block-manager__icon{if !$is_block_enabled} bm-block-manager__icon--hidden{/if}"}
            {$icon_eye_close = "ty-icon-eye-close bm-block-manager__icon{if $is_block_enabled} bm-block-manager__icon--hidden{/if}"}
            {include_ext file="common/icon.tpl"
                class=$icon_eye_open
                data=[
                    "data-ca-block-manager-switch-icon" => "show"
                ]
            }
            {include_ext file="common/icon.tpl"
                class=$icon_eye_close
                data=[
                    "data-ca-block-manager-switch-icon" => "hide"
                ]
            }
        </button>
        <button type="button" class="bm-block-manager__btn bm-block-manager__up"
                data-ca-block-manager-action="move"
                data-ca-block-manager-move="up"
        >
            {include_ext file="common/icon.tpl"
                class="ty-icon-arrow-up bm-block-manager__icon"
            }
        </button>
        <button type="button"
                class="bm-block-manager__btn bm-block-manager__down"
                data-ca-block-manager-action="move"
                data-ca-block-manager-move="down"
        >
            {include_ext file="common/icon.tpl"
                class="ty-icon-arrow-down bm-block-manager__icon"
            }
        </button>
    </div>
    <div class="bm-block-manager__arrow-wrapper">
        <div class="bm-block-manager__arrow"></div>
    </div>
</div>
