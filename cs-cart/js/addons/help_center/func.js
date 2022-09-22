(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    var $helpCenterMain = $('[data-ca-help-center="main"]', context);

    if (!$helpCenterMain.length) {
      return;
    }

    if ($(context).is(document)) {
      fnInitHelpCenter($helpCenterMain);
      fnInitHelpCenterEvent();
    }
  });
  $.ceEvent('on', 'ce.ajaxdone', function (elms) {
    if (elms && elms.length > 0) {
      var helpCenterParent = elms.find(function (elm) {
        return elm.find('.help-center').length > 0;
      });

      if (helpCenterParent && helpCenterParent.length > 0) {
        var $helpCenterMain = $('[data-ca-help-center="main"]', helpCenterParent);
        fnInitHelpCenter($helpCenterMain);
      }
    }
  });

  function fnInitHelpCenter($helpCenterMain) {
    $.ceAjax('request', _.help_center_server_url + "?version=".concat(_.product_version, "&edition=").concat(_.product_edition, "&lang_code=").concat(_.cart_language, "&dispatch=").concat(_.current_dispatch, "&product_build=").concat(_.product_build, "&store_domain=").concat(_.current_host), {
      caching: false,
      hidden: true,
      callback: function callback(data) {
        if (!data.blocks && !data.show_customer_care_block) {
          return;
        }

        var show_help_center = false;

        if (data.blocks) {
          data.blocks.map(function (block) {
            return fnRenderBlock(block);
          });

          if (data.footer) {
            fnRenderBlock(data.footer, 'footer');
          }

          show_help_center = true;
        }

        if (data.show_customer_care_block && $('.help-center__block-customer-care').length) {
          $('.help-center__block-customer-care').toggleClass('help-center__block-customer-care--hidden');
          show_help_center = true;
        }

        if (!show_help_center) {
          return;
        }

        $('.help-center__toolbar').toggleClass('help-center__toolbar--hidden');

        if ($('.help-center__block:not(.help-center__block--footer)').length === 1) {
          $('.help-center__content').addClass('help-center__content--single');
        }

        if (!$.cookie.get('help_center_closed') && $helpCenterMain.data('caHelpCenterIsFirstLogin')) {
          showHelpCenter();
        }
      }
    });
  }

  function fnInitHelpCenterEvent() {
    $(_.doc).on('click', '.help-center__block-link--show-more', function () {
      $(this).closest('.help-center__block-content').find('.help-center__block-link.help-center__block-link--hidden').toggleClass('help-center__block-link--hidden help-center__block-link--visible');
      $(this).addClass('help-center__block-link--hidden');
    });
    $(_.doc).on('click', '.help-center__close, .help-center__show-help-center', function () {
      if (isHelpCenterOpen()) {
        setCloseHelpTutorialCookie();
      }

      toggleHelpCenterDisplay();
    });
  }

  function isHelpCenterOpen() {
    var $helpCenterMain = $('[data-ca-help-center="main"]');
    return !$helpCenterMain.hasClass('hidden');
  }

  function fnRenderBlock(data, typeBlock) {
    var $template = $("#help_center_block").html(),
        $linkTemplate = $("#help_center_block_link").html(),
        contentSelector = typeBlock === 'footer' ? '.help-center__footer' : '.help-center__content';
    data.is_lines_more_limit = 'items_display_limit' in data && data.items.length > data.items_display_limit;
    data.see_all_n_results = _.tr('see_all_n_results').replace('[n]', data.items.length);
    data.type_block = typeBlock ? "help-center__block--".concat(typeBlock) : '';
    data.all_items_name_short = _.tr('all');
    $(contentSelector).append(fnRenderTemplate(data, $template));

    for (var i = 0; i < data.items.length; i++) {
      if (data.items_display_limit) {
        data.items[i].link_limit_class = i + 1 > data.items_display_limit ? ' help-center__block-link--hidden' : '';
      }

      $("".concat(contentSelector, " .help-center__block:last-child .help-center__block-items")).append(fnRenderTemplate(data.items[i], $linkTemplate));
    }
  }

  function fnRenderTemplate(data, template) {
    var templater = new Function('data', "return `".concat(template, "`;"));
    return templater(data);
  }

  function showHelpCenter() {
    toggleHelpCenterDisplay(true);
  }

  function hideHelpCenter() {
    toggleHelpCenterDisplay(false);
  }

  function toggleHelpCenterDisplay(isOpen) {
    if (typeof isOpen !== 'boolean') {
      isOpen = !isHelpCenterOpen();
    }

    var isHide = !isOpen;
    $('.help-center').toggleClass('hidden', isHide);
    $('.help-center__show-help-center').toggleClass('active', !isHide);
    $('.help-center__block-link--show-more').removeClass('help-center__block-link--hidden', isHide);
    $('.help-center__block-link.help-center__block-link--visible').toggleClass('help-center__block-link--hidden help-center__block-link--visible', isHide);
    $.scrollToElm('.help-center');
  }

  function setCloseHelpTutorialCookie() {
    if (!$.cookie.get('help_center_closed')) {
      $.cookie.set('help_center_closed', 1);
    }
  }
})(Tygh, Tygh.$);