<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Novanta\OrderPayment\Adapter\Install;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Language;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Tab;

class Installer
{
    /**
     * Funzione che effettua l'istallazione del modulo
     *
     * @param \Module $module
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function install(\Module $module): bool
    {
        return $this->installDatabase()
            && $this->registerHooks($module)
            && $this->initializeConfiguration($module)
            && $this->installTabs($module);
    }

    /**
     * Funzione che effettua la disistallazione del modulo
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstall()
    {
        return $this->uninstallDatabase()
            && $this->destroyConfiguration()
            && $this->removeTabs();
    }

    /**
     * Funzione che crea le tabelle del modulo
     *
     * @return bool
     * @throws \PrestaShopException
     */
    protected function installDatabase()
    {
        $queries = [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'order_payment_document` (
                `id_order_payment` INT(11) UNSIGNED NOT NULL,
                `id_order_document` INT(11) UNSIGNED NOT NULL,
                PRIMARY KEY (`id_order_payment`, `id_order_document`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',

            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'order_invoice_document` (
                `id_order_invoice` INT(11) UNSIGNED NOT NULL,
                `id_order_document` INT(11) UNSIGNED NOT NULL,
                PRIMARY KEY (`id_order_invoice`, `id_order_document`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',

            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'order_invoice_proforma` (
                `id_order_invoice_proforma` int(11) NOT NULL AUTO_INCREMENT,
                `id_order_invoice` INT(11) UNSIGNED NOT NULL,
                `payment_method` varchar(255) NULL,
                `payment_term` varchar(255) NULL,
                `amount_type` enum(\'amount\',\'percentage\') NULL,
                `amount` decimal(20,6) NULL,
                PRIMARY KEY (`id_order_invoice_proforma`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
        ];

        return $this->executeQueries($queries);
    }

    /**
     * Funzione che elimina le tabelle del modulo
     *
     * @return bool
     * @throws \PrestaShopException
     */
    protected function uninstallDatabase()
    {
        $queries = [
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'order_payment_document`;',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'order_invoice_document`;',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'order_invoice_proforma`;',
        ];

        return $this->executeQueries($queries);
    }

    /**
     * Funzione che registra gli hook del modulo
     *
     * @param \Module $module
     *
     * @return bool
     */
    protected function registerHooks(\Module $module): bool
    {
        $hooks = [
            'displayAdminOrderMainBottom',
            'actionOrderGridDefinitionModifier',
            'actionOrderGridQueryBuilderModifier',
            'actionAdminControllerSetMedia',
            'displayPDFInvoice'
        ];

        return $module->registerHook($hooks);
    }

    /**
     * Funzione che inizializza la configurazione del modulo
     *
     * @param \Module $module
     * @return bool
     */
    protected function initializeConfiguration(\Module $module): bool
    {
        return true;
    }

    /**
     * Funzione che cancella la configurazione del modulo
     *
     * @return bool
     */
    protected function destroyConfiguration(): bool
    {
        return true;
    }

    /**
     * Funzione che installa e inizializza le tabs
     *
     * @param \Module $module
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function installTabs(\Module $module): bool
    {
        $paymentsTab = $this->addTab('Payments', 'AdminOrderPayments', 'AdminParentOrders', $module);

        $invoicesTabId = SymfonyContainer::getInstance()->get('prestashop.core.admin.tab.repository')->findOneIdByClassName('AdminInvoices');

        $updateTabResults = true;
        if ($invoicesTabId) {
            $invoicesTab = new Tab($invoicesTabId);
            $invoicesTab->class_name = 'AdminOrderInvoices';
            $invoicesTab->module = $module->name;
            $updateTabResults = $invoicesTab->save();
        }


        return $paymentsTab && $updateTabResults;
    }

    /**
     * Funzione che rimuove le tab durante la disistallazione
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function removeTabs(): bool
    {
        return $this->removeTab('AdminOrderPayments')
            && $this->removeTab('AdminOrderInvoices');
    }

    /**
     * Funzione che si occupa di eseguire le query
     * per l'istallazione e la disistallazione del modulo
     *
     * @param array $queries
     *
     * @return bool
     * @throws \PrestaShopException
     */
    private function executeQueries($queries): bool
    {
        foreach ($queries as $query) {
            if (!\Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Funzione che si occupa di creare e istallare la tab
     *
     * @param $name
     * @param $className
     * @param $parentClassName
     * @param $module
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function addTab($name, $className, $parentClassName, $module): bool
    {
        $tabRepository = SymfonyContainer::getInstance()->get('prestashop.core.admin.tab.repository');
        $tabId = (int)$tabRepository->findOneIdByClassName($className);
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = $className;

        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        $tab->id_parent = (int)$tabRepository->findOneIdByClassName($parentClassName);
        $tab->wording = $name;
        $tab->wording_domain = 'Modules.Orderpayments.Admin';
        $tab->module = $module->name;

        return $tab->save();
    }

    /**
     * Funzione che si occupa di elimimare una tab
     *
     * @param [type] $className
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function removeTab($className): bool
    {
        $tabRepository = SymfonyContainer::getInstance()->get('prestashop.core.admin.tab.repository');
        $tabId = (int)$tabRepository->findOneIdByClassName($className);
        if ($tabId) {
            $tab = new \Tab($tabId);

            return $tab->delete();
        } else {
            return false;
        }
    }
}
