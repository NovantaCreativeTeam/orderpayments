const $ = window.$;

$(() => {
    // Setup Grid Panel
    const orderInvoicesGrid = new window.prestashop.component.Grid('order_invoices')
    orderInvoicesGrid.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension())
    orderInvoicesGrid.addExtension(new window.prestashop.component.GridExtensions.SortingExtension())
});