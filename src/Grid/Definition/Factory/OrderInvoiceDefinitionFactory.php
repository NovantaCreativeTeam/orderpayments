<?php

namespace Novanta\OrderPayment\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BadgeColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DateTimeColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShopBundle\Form\Admin\Type\DateRangeType;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;

final class OrderInvoiceDefinitionFactory extends AbstractGridDefinitionFactory
{
    const GRID_ID = 'order_invoices';

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Invoices', [], 'Modules.Orderpayments.Admin');
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add((new DataColumn('id_order_invoice'))
                ->setName($this->trans('ID', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'id_order_invoice',
                ])
            )
            ->add((new DataColumn('invoice_number'))
                ->setName($this->trans('Number', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'invoice_number',
                ])
            )
            ->add((new DateTimeColumn('date_add'))
                ->setName($this->trans('Invoice Date', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'date_add',
                    'format' => 'd/m/Y H:i:s',
                ])
            )
            ->add((new DataColumn('order_reference'))
                ->setName($this->trans('Order', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'order_reference',
                ])
            )
//            ->add((new DataColumn('total_order'))
//                ->setName($this->trans('Total Order', [], 'Admin.Global'))
//                ->setOptions([
//                    'field' => 'total_order_formatted',
//                ])
//            )
            ->add((new DataColumn('payment_method'))
                ->setName($this->trans('Payment method', [], 'Modules.Orderpayments.Admin'))
                ->setOptions([
                    'field' => 'payment_method_formatted',
                ])
            )
            ->add((new DataColumn('payment_term'))
                ->setName($this->trans('Payment term', [], 'Modules.Orderpayments.Admin'))
                ->setOptions([
                    'field' => 'payment_term_formatted',
                ])
            )
            ->add((new DataColumn('total_to_pay'))
                ->setName($this->trans('Total', [], 'Admin.Global'))
                ->setOptions([
                    'field' => 'total_to_pay_formatted',
                ])
            )
            ->add((new BadgeColumn('total_paid'))
                ->setName($this->trans('Total paid', [], 'Modules.Orderpayments.Admin'))
                ->setOptions([
                    'field' => 'total_paid_formatted',
                    'badge_type' => '',
                    'badge_type_field' => 'total_paid_badge_type',
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
                                'route' => 'order_invoice_download',
                                'route_param_name' => 'orderId',
                                'route_param_field' => 'id_order',
                                'extra_route_params' => [
                                    'orderInvoiceId' => 'id_order_invoice',
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
            ->add((new Filter('id_order_invoice', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('id_order_invoice')
            )
            ->add((new Filter('invoice_number', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('invoice_number')
            )
            ->add((new Filter('order_reference', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('order_reference')
            )
            ->add((new Filter('date_add', DateRangeType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('date_add')
            )
            ->add((new Filter('payment_method', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('payment_method')
            )
            ->add((new Filter('payment_term', TextType::class))
                ->setTypeOptions([
                    'required' => false,
                ])
                ->setAssociatedColumn('payment_term')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'admin_order_invoices_search',
                    ])
                    ->setAssociatedColumn('actions')
            );
    }
}
