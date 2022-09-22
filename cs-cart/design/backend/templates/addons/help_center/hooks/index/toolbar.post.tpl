{if $smarty.const.ACCOUNT_TYPE === "admin"}
    <div class="help-center__toolbar help-center__toolbar--hidden">
        <a class="btn help-center__show-help-center">
            {include_ext file="common/icon.tpl"
                class="icon-question-sign help-center__show-help-center--icon"
            }
        </a>
    </div>
{/if}
