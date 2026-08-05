Sei un esperto di PrestaShop. Vorrei che creassi un modulo PrestaShop per la versione 9.0.3  e superiori, seguendo rigorosamente tutte le linee guida ufficiali per una personalizzazione sicura, comprese quelle elencate qui:
https://docs.cloud.prestashop.com/10-validation-checklist/#common-rules.

## Introduzione al modulo

Genera un modulo per la gestione di pagamenti multipli associati ad un singolo ordine e alla generazione di fatture proforma.
La soluzione permetterà agli amministratori del sito di gestire ordini per i quali il pagamento viene effettuato attraverso più tranche, mantenendo uno storico ordinato dei versamenti ricevuti e consentendo una gestione più flessibile delle condizioni di pagamento concordate con il cliente.

## Elenco Funzionalià

### Visualizzazione dei pagamenti
All'interno di ogni singolo ordine effettuato dal cliente, sarà possibile visualizzare la lista dei pagamenti effettuati dal cliente per quel determinato ordine mostrati attraverso l'hook *displayAdminOrderMainBottom*, per ogni pagamento sarà possibile inoltre accedere alle funzionalità CRUD attraverso azioni contestuali per ogni riga (modifica e cancellazione).
Sarà inoltre possibile aggiungere nuovi pagamenti sempre da interfaccia, tramite apposito bottone collocato sotto la lista dei pagamenti "Nuovo Pagamento".

In testa alla lista dei pagamenti verrà riportato il riepilogo dei pagamento con il totale da versare, quello che è stato versato e quello che manca a completare i pagamenti. Nella lista degli ordini la colonna dei totali dovrà essere rappresentata tramite badge colorato con verde, giallo e rosso, che indicano rispettivamente che tutto l'importo è stato saldato, che solo in parte l'importo risulta saldato e che non ci sono stati ancora pagamenti.

### Creazione pagamento
Clicccando su "Nuovo Pagamento" il sistema aprirà una modale che permetterà la visualizzazione della form per l'inserimento del pagamento presentando i campi:
- Importo pagato;
- Data del pagamento;
- Metodo di pagamento (bonifico, carta, PayPal, ecc.);
- Riferimenti della transazione (opzionale).
- Id della fattura (menù a discesca precompilata con le fatture dell'ordine.) (opzionale)
- Documento della contabile o altro (opzionale)

### Modifica del pagamento
Clicccando su "Modifica Pagamento" il sistema aprirà una modale che permetterà la visualizzazione della form di modifica del pagamento presentando i campi già precompilati relativi al pagamento:
- Importo pagato;
- Data del pagamento;
- Metodo di pagamento (bonifico, carta, PayPal, ecc.);
- Riferimenti della transazione.
- Id della fattura (menù a discesca precompilata con le fatture dell'ordine.)

### Cancellazione del pagamento
Cliccando su Cancella  il sistema aprirà una modalità con la richiesta di conferma della cancellazione, accettando il sistema procederà alla cancellazione del pagamento.

### Download documento
Per ogni pagamento dovrà esserci a disposizione la funzionalità per scaricare il documento che è stato caricato per il pagamento.

## Indicazioni tecniche per lo sviluppo
Database
Per la rappresentazione dei pagamenti dei clienti devi utilizzare la tabella ps_order_payment che è già presente all'interno di prestashop e utilizza già tutti i campi necessari, ad esclusione del riferimento del documento. La tabella ps_order_document contiene al suo interno la lista dei documento dell'ordine, quindi dovrà essere creata la tabella order_payment_document così da metterle in relazione.

Namespace
Il modulo dovrà utilizzare la spceicifiche psr-4 in merito ai namespaces e struttura delle cartelle

Entità
Dovrà essere

I~~l modulo sarà integrato direttamente all’interno del pannello amministrativo Prestashop, nella gestione dell’ordine, così da permettere una gestione centralizzata di pagamenti, documentazione e situazione economica dell’ordine.~~

~~Il modulo consentirà di associare ad ogni ordine Prestashop uno o più pagamenti personalizzati, permettendo all’amministratore del sito di registrare manualmente i diversi versamenti ricevuti.~~
~~Per ogni pagamento sarà possibile inserire le principali informazioni necessarie alla gestione amministrativa, tra cui importo versato, data del pagamento, modalità utilizzata ed eventuali note interne.~~
~~L’amministratore potrà gestire ogni singolo pagamento associato all’ordine, con la  possibilità di modificarne i dati o eliminarlo in caso di necessità, mantenendo così un  controllo completo e aggiornato dei versamenti effettuati.~~
~~Per ogni pagamento registrato sarà inoltre disponibile la possibilità di caricare la relativa contabile del pagamento, come ad esempio una ricevuta bancaria o una distinta di  bonifico inviata dal cliente. Il documento sarà associato allo specifico pagamento e potrà  essere consultato direttamente dal pannello amministrativo dell’ordine.~~
~~Il modulo prevederà inoltre una sezione riepilogativa dedicata alla situazione economica~~
~~dell’ordine, con evidenza del totale dell’ordine, degli importi complessivamente ricevuti~~
~~attraverso i diversi pagamenti registrati e dell’eventuale saldo ancora da corrispondere.~~


---

## ⚙️ Constraints:

The module must follow these rules and best practices **without exception**:

🧱 Structure & Technical Compliance

- Folder name = main PHP file name = main class name. 
- All .php files (except Ajax/cron) must begin with: if (!defined('*PS&#95;VERSION*')) { exit; } 
- The use of smarty / Twig templates is mandatory to display HTML. Your PHP code should not contain HTML. 
- Compatibility declaration using $this->ps_versions_compliancy is required with specific version range (e.g., 'max' => '8.9.99' instead of *PS&#95;VERSION*). 
- No override of core PrestaShop files or native classes. 
- No direct external dependencies (CDNs, third-party APIs) without validation. 
- Code and comments must be in English only. 
- Hook executions must be context-aware to reduce performance impact (e.g. if ($this->context->controller->controller_name === 'product')). 
- Include a docs/ folder with documentation (documentation.md or PDF). 
- Include a proper .gitignore file. 
- Include a .htaccess file at the root to prevent direct access to .php files. 
- Every single file of the package has to contain a valid license header.