const $ = window.$;

$(() => {
    // Setup Grid Panel
    const orderPaymentsGrid = new window.prestashop.component.Grid('order_payments')
    orderPaymentsGrid.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension())
    orderPaymentsGrid.addExtension(new window.prestashop.component.GridExtensions.SortingExtension())
});