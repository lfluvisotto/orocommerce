/** @lends ShippingTransitionButtonComponent */
define(function(require) {
    'use strict';

    var TransitionButtonComponent = require('orocheckout/js/app/components/transition-button-component');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');

    var ShippingTransitionButtonComponent;
    ShippingTransitionButtonComponent = TransitionButtonComponent.extend(/** @exports ShippingTransitionButtonComponent.prototype */{
        /**
         * @constructor
         * @param {Object} options
         */
        initialize: function(options) {
            this.defaults = $.extend(
                true,
                {},
                this.defaults,
                {
                    selectors: {
                        shippingForm: '[data-content="shipping_method_form"]',
                        shippingMethodTypeSelector: '[name$="shippingMethodType"]',
                        shippingMethodTypeSelectorAbsolute: '[data-content="shipping_method_form"]' +
                            ' [name$="shippingMethodType"]',
                        checkoutRequire: '[data-role="checkout-require"]',
                        shippingMethod: '[name$="[shipping_method]"]',
                        shippingMethodType: '[name$="[shipping_method_type]"]'
                    }
                }
            );

            ShippingTransitionButtonComponent.__super__.initialize.call(this, options);

            this.onShippingMethodRendered();
        },

        onShippingMethodRendered: function() {
            this.getContent().on(
                'change',
                this.options.selectors.shippingMethodTypeSelectorAbsolute,
                $.proxy(this.onShippingMethodTypeChange, this)
            );

            this.initShippingMethod();
        },

        initShippingMethod: function() {
            var selectedTypeValue = this.getShippingMethodTypeElement().val();
            var selectedMethodValue = this.getShippingMethodElement().val();
            if (this.getShippingMethodTypeSelector().length && selectedTypeValue && selectedMethodValue) {
                var selectedEl = this
                  .getShippingMethodTypeSelector()
                  .filter('[value="' + selectedTypeValue + '"]')
                  .filter('[data-shipping-method="' + selectedMethodValue + '"]');
                selectedEl.prop('checked', 'checked');
                selectedEl.trigger('change');
            } else {
                var selectedType = this.getShippingMethodTypeSelector().filter(':checked');
                if (selectedType.val()) {
                    var method = $(selectedType).data('shipping-method');
                    this.setElementsValue(selectedType.val(), method);
                } else {
                    this.setElementsValue(null, null);
                }
            }
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.getContent().off('change', this.options.selectors.shippingMethodTypeSelectorAbsolute);

            ShippingTransitionButtonComponent.__super__.dispose.call(this);
        },

        /**
         *
         * @param {string} type
         * @param {string} method
         */
        setElementsValue: function(type, method) {
            this.getShippingMethodTypeElement().val(type);
            this.getShippingMethodElement().val(method);
        },

        /**
         * @param {Event} event
         */
        onShippingMethodTypeChange: function(event) {
            mediator.trigger('checkout:shipping-method:changed');
            var methodType = $(event.target);
            var method = methodType.data('shipping-method');
            this.setElementsValue(methodType.val(), method);
        },

        /**
         * @returns {jQuery|HTMLElement}
         */
        getContent: function() {
            return $(this.options.selectors.checkoutContent);
        },

        /**
         * @returns {jQuery|HTMLElement}
         */
        getShippingForm: function() {
            return $(this.options.selectors.shippingForm);
        },

        /**
         * @returns {jQuery|HTMLElement}
         */
        getShippingMethodTypeSelector: function() {
            return this.getShippingForm().find(this.options.selectors.shippingMethodTypeSelector);
        },

        /**
         * @returns {jQuery|HTMLElement}
         */
        getShippingMethodTypeElement: function() {
            return this.getContent().find(this.options.selectors.shippingMethodType);
        },

        onFail: function() {
            this.$el.removeClass('btn--info');
            this.$el.prop('disabled', true);
            this.$el.closest(this.defaults.selectors.checkoutContent)
                .find(this.defaults.selectors.checkoutRequire)
                .addClass('hidden');

            mediator.trigger('transition:failed');
            ShippingTransitionButtonComponent.__super__.onFail.call(this);
        },

        /**
         * @returns {jQuery|HTMLElement}
         */
        getShippingMethodElement: function() {
            return this.getContent().find(this.options.selectors.shippingMethod);
        }
    });

    return ShippingTransitionButtonComponent;
});
