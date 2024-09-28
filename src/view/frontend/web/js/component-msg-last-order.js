/**
 * Copyright © All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'jquery',
    'ko',
    'mage/url',
    'mage/translate',
    'mage/storage'
], function (Component, $, ko, urlBuilder, $t, storage) {
    'use strict';

    return Component.extend({
        isVisible: ko.observable(false),
        orderLink: ko.observable(''),
        orderDate: ko.observable(''),
        cacheKey: '',

        /**
         * Initializes the function.
         *
         * @param {Object} config - The configuration object.
         * @param {number} config.customerId - The ID of the customer.
         * @param {number} config.productId - The ID of the product.
         */
        initialize: function (config) {
            this._super();
            this.customerId = config.customerId;
            this.productId = config.productId;
            this.cacheKey = `customer_${this.customerId}_product_${this.productId}_purchase`;

            this.checkData() && this.checkCacheOrMakeRequest();
        },

        /**
         * Checks if the data is valid.
         *
         * @returns {boolean} - True if valid, false otherwise.
         */
        checkData: function () {
            if (!this.customerId || !this.productId) {
                return false;
            }
            return true;
        },

        /**
         * Updates the UI with the purchase data.
         *
         * @param {Object} purchaseData - The purchase data object.
         */
        updateUI: function ({ hasPurchased, orderLink, orderDate }) {
            this.isVisible(hasPurchased);
            this.orderLink(orderLink);
            this.orderDate(orderDate);
        },

        /**
         * Handles errors from the GraphQL request.
         *
         * @param {Object} error - The error object.
         */
        handleError: function (error) {
            console.error(error);
        },

        /**
         * Checks the cache before making a request.
         */
        checkCacheOrMakeRequest: function () {
            const cachedResponse = localStorage.getItem(this.cacheKey);
            cachedResponse ? this.handleResponse(JSON.parse(cachedResponse)) : this.makeGraphQLRequest();
        },

        /**
         * Makes a GraphQL request to check if a customer has purchased a product.
         */
        makeGraphQLRequest: function () {
            const urlGraphql = urlBuilder.build('graphql');
            const queryGraphql = `
                query CheckCustomerProductPurchase($customerId: String!, $productId: String!) {
                    hasCustomerPurchasedProduct(customerId: $customerId, productId: $productId) {
                        hasPurchased
                        orderLink
                        orderDate
                    }
                }`;

            const requestData = {
                query: queryGraphql,
                variables: { customerId: this.customerId, productId: this.productId }
            };

            storage.post(urlGraphql, JSON.stringify(requestData), true)
                .done(this.handleResponse)
                .fail(this.handleError);
        },

        /**
         * Handles the response from the GraphQL request.
         *
         * @param {Object} response - The response object.
         */
        handleResponse: function (response) {
            const purchaseData = response.data?.hasCustomerPurchasedProduct;
        
            if (!purchaseData || purchaseData.hasPurchased === false) {
                return;
            }
        
            this.updateUI(purchaseData);
            localStorage.setItem(this.cacheKey, JSON.stringify(response));
        }
    });
});
