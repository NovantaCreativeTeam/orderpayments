import axios from 'axios'

const i18nApi = {
    getTranslations: () => {
        // var endpoint = window.prestashop.instance.router.generate('admin_get_order_charging_translations')
        // return axios.get(endpoint)

        // Così utilizzo la struttura di prestashop, con la versione sopra uso quella del modulo
        return axios.get(translationUrl)
    }
}

export default i18nApi;