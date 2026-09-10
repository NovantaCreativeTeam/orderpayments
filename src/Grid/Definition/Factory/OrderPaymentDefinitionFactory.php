<?php

namespace Novanta\OrderPayment\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DateTimeColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\HtmlColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\ModalOptions;

final class OrderPaymentDefinitionFactory extends AbstractGridDefinitionFactory
{
    const GRID_ID = 'order_payments';

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Payments', [], 'Modules.Orderpayments.Admin');
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add((new DataColumn('id_order_payment'))
                ->setName($this->trans('ID', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'id_order_payment',
                ])
            )
            ->add((new DataColumn('order_reference'))
                ->setName($this->trans('Order', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'order_reference',
                ])
            )
            ->add((new DataColumn('customer'))
                ->setName($this->trans('Customer', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'customer',
                ])
            )
            ->add((new HtmlColumn('products'))
                ->setName($this->trans('Products', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'products',
                ])
            )
            ->add((new DateTimeColumn('date_add'))
                ->setName($this->trans('Date', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'date_add',
                    'format' => 'd/m/Y H:i:s',
                ])
            )
            ->add((new DataColumn('payment_method'))
                ->setName($this->trans('Payment method', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'payment_method',
                ])
            )
            ->add((new DataColumn('transaction_id'))
                ->setName($this->trans('Transaction ID', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'transaction_id',
                ])
            )
            ->add((new DataColumn('amount'))
                ->setName($this->trans('Amount', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'amount_formatted',
                ])
            )
            ->add((new DataColumn('invoice_number'))
                ->setName($this->trans('Invoice', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'invoice_number',
                ])
            )
            ->add((new ActionColumn('actions'))
                ->setName($this->trans('Actions', [], 'Admin.Global'))
                ->setOptions([
                    'actions' => (new RowActionCollection())
                        ->add((new LinkRowAction('view'))
                            ->setName($this->trans('View', [], 'Admin.Actions'))
                            ->setIcon('zoom_in')
                            ->setOptions([
                                'route' => 'admin_orders_view',
                                'route_param_name' => 'orderId',
                                'route_param_field' => 'id_order',
                            ])
                        )
                        ->add((new LinkRowAction('download'))
                            ->setName($this->trans('Download', [], 'Admin.Actions'))
                            ->setIcon('file_download')
                            ->setOptions([
                                'route' => 'order_payment_download',
                                'route_param_name' => 'orderId',
                                'route_param_field' => 'id_order',
                                'extra_route_params' => [
                                    'paymentId' => 'id_order_payment',
                                    'documentId' => 'id_order_document',
                                ],
                                'accessibility_checker' => function (array $record) {
                                    return !empty($record['id_order_document']);
                                },
                            ])
                        ),
                ])
            );
    }

    protected function getFilters()
    {
        return (new FilterCollection())
            ->add((new Filter('id_order_payment', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('id_order_payment')
            )
            ->add((new Filter('order_reference', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('order_reference')
            )
            ->add((new Filter('customer', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('customer')
            )
            ->add((new Filter('products', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('products')
            )
            ->add((new Filter('payment_method', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('payment_method')
            )
            ->add((new Filter('transaction_id', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('transaction_id')
            )
            ->add((new Filter('invoice_number', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('invoice_number')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'admin_order_payments_search',
                    ])
                    ->setAssociatedColumn('actions')
            );
    }
}
