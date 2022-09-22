{$btn_text = $btn_text|default:__("helpdesk_account.sign_in")}
{$btn_href = $btn_href|default:$app["helpdesk.connect_url"]}
<a class="btn btn-primary {$btn_class}"
   href="{fn_url($btn_href)}"
>
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 30 30"><g fill="#fff"><path d="M0 0h9.091v9.091H0zM10 10h9.091v9.091H10zM20.909 10H30v9.091h-9.091zM10 20.909h9.091V30H10zM20.909 20.909H30V30h-9.091z"/></g></svg>
    {$btn_text}
</a>
