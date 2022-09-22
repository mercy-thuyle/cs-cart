import { Tygh } from "../..";
import $ from "jquery";

const _ = Tygh;

export const methods = {
    init: function () {
        if (_.deferred_scripts) {
            for (let i = 0; i < _.deferred_scripts.length; i++) {
                setTimeout(function() {
                    $.getScript(_.deferred_scripts[i].src, function() {
                        $.ceEvent('trigger', `ce.lazy_script_load_${_.deferred_scripts[i].event_suffix}`);
                    });            
                }, _.deferred_scripts[i].delay || 3000);
            }
        }
    },
};

/**
 * Accordion
 * @param {JQueryStatic} $ 
 */
export const ceLazyLoaderInit = function ($) {
    $.ceLazyLoader = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            $.error('ty.lazyloader: method ' + method + ' does not exist');
        }
    };
}
