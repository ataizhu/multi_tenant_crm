/**
 * Tenant Context Manager
 * Управляет контекстом тенанта в CRM системе
 */
class TenantContextManager {
    constructor() {
        this.currentTenantId = this.getTenantIdFromUrl();
        this.currentTenantName = this.getTenantNameFromPage();

        // Модульная конфигурация для модальных окон
        this.modalConfig = {
            meter: {
                title: 'Детали счетчика',
                icon: '🔧',
                fields: {
                    number: 'Номер',
                    type: 'Тип',
                    model: 'Модель',
                    last_reading: 'Последнее показание',
                    last_reading_date: 'Дата показания',
                    status: 'Статус'
                },
                colors: {
                    primary: '#3b82f6',
                    secondary: '#1d4ed8'
                }
            },
            invoice: {
                title: 'Детали счета',
                icon: '📄',
                fields: {
                    invoice_number: 'Номер счета',
                    invoice_date: 'Дата счета',
                    due_date: 'Срок оплаты',
                    amount: 'Сумма',
                    total_amount: 'Итого',
                    status: 'Статус'
                },
                colors: {
                    primary: '#059669',
                    secondary: '#047857'
                }
            },
            payment: {
                title: 'Детали платежа',
                icon: '💳',
                fields: {
                    payment_number: 'Номер платежа',
                    payment_date: 'Дата платежа',
                    payment_method: 'Способ оплаты',
                    amount: 'Сумма',
                    reference_number: 'Номер чека',
                    status: 'Статус'
                },
                colors: {
                    primary: '#dc2626',
                    secondary: '#b91c1c'
                }
            }
        };

        this.init();
    }

    /**
     * Получает ID тенанта из URL
     */
    getTenantIdFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('tenant');
    }

    /**
     * Получает название тенанта со страницы
     */
    getTenantNameFromPage() {
        const tenantNameElement = document.querySelector('[data-tenant-name]');
        return tenantNameElement ? tenantNameElement.textContent.trim() : null;
    }

    /**
     * Инициализация менеджера
     */
    init() {
        this.updateAllLinks();
        this.setupEventListeners();
        this.setupMutationObserver();
        this.initModals();
    }

    /**
     * Настройка обработчиков событий
     */
    setupEventListeners() {
        // Слушаем изменения URL для обновления контекста
        window.addEventListener('popstate', () => {
            this.currentTenantId = this.getTenantIdFromUrl();
            this.updateAllLinks();
        });

        // Слушаем клики по ссылкам для проверки tenant параметра
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href*="/tenant-crm/"]');
            if (link && !link.href.includes('tenant=') && this.currentTenantId) {
                event.preventDefault();
                const separator = link.href.includes('?') ? '&' : '?';
                window.location.href = link.href + separator + 'tenant=' + this.currentTenantId;
            }
        });
    }

    /**
     * Настройка наблюдателя за изменениями DOM
     */
    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            let shouldUpdate = false;
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            if (node.tagName === 'A' || node.querySelector('a[href*="/tenant-crm/"]')) {
                                shouldUpdate = true;
                            }
                        }
                    });
                }
            });

            if (shouldUpdate) {
                this.updateAllLinks();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Обновляет все ссылки на странице
     */
    updateAllLinks() {
        const links = document.querySelectorAll('a[href*="/tenant-crm/"]');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href && !href.includes('tenant=') && this.currentTenantId) {
                const separator = href.includes('?') ? '&' : '?';
                link.setAttribute('href', href + separator + 'tenant=' + this.currentTenantId);
            }
        });
    }

    /**
     * Создает URL с tenant параметром
     */
    createUrl(path, tenantId = null) {
        const id = tenantId || this.currentTenantId;
        if (!id) return path;

        const separator = path.includes('?') ? '&' : '?';
        return path + separator + 'tenant=' + id;
    }

    /**
     * Получает текущий контекст тенанта
     */
    getCurrentContext() {
        return {
            tenantId: this.currentTenantId,
            tenantName: this.currentTenantName,
            url: window.location.href
        };
    }

    /**
     * Проверяет, находится ли пользователь в контексте тенанта
     */
    isInTenantContext() {
        return !!this.currentTenantId;
    }

    /**
     * Инициализация модальных окон
     */
    initModals() {
        this.setupModalHandlers();
        this.setupClickableItems();
    }

    /**
     * Настройка обработчиков модальных окон
     */
    setupModalHandlers() {
        // Закрытие модала по клику на overlay
        document.addEventListener('click', (event) => {
            if (event.target.classList.contains('modal-overlay')) {
                this.closeModal();
            }
        });

        // Закрытие модала по кнопке закрытия
        document.addEventListener('click', (event) => {
            if (event.target.classList.contains('modal-close')) {
                this.closeModal();
            }
        });

        // Закрытие модала по Escape
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.closeModal();
            }
        });
    }

    /**
     * Настройка кликабельных элементов - упрощенная версия для модальных окон
     */
    setupClickableItems() {
        document.addEventListener('click', (event) => {
            // Ищем только элементы с классом horizontal-list-item для модальных окон
            const clickableItem = event.target.closest('.horizontal-list-item .fi-in-repeatable-item');

            if (clickableItem) {
                // Предотвращаем всплытие события только если это не действие Filament
                if (!event.target.closest('a, button')) {
                    event.preventDefault();
                    event.stopPropagation();

                    const dataType = this.detectDataType(clickableItem);
                    if (dataType && dataType !== 'unknown') {
                        this.openDetailModal(dataType, null, clickableItem);
                    }
                }
            }
        });
    }

    /**
     * Универсальный поиск кликабельного элемента
     */
    findClickableItem(target) {
        console.log('Looking for clickable item, target:', target);

        // Список селекторов для поиска кликабельных элементов
        const selectors = [
            '.horizontal-list-item .fi-in-repeatable-item',
            '.clickable-list-item',
            '.fi-in-repeatable-item',
            '.listitem',
            'li[class*="fi-in-repeatable"]',
            'li[class*="repeatable"]'
        ];

        // Ищем по селекторам
        for (const selector of selectors) {
            const element = target.closest(selector);
            if (element) {
                console.log('Found clickable element with selector:', selector, element);
                return element;
            }
        }

        // Если не найдены, ищем в активной панели
        const activeTabPanel = document.querySelector('[role="tabpanel"]');
        if (activeTabPanel && activeTabPanel.contains(target)) {
            const listItem = target.closest('li, dt, dd, .generic, [data-field]');
            if (listItem) {
                console.log('Found clickable item in tab panel:', listItem);
                return {
                    dataset: { type: this.detectTypeFromContent(activeTabPanel) },
                    querySelectorAll: (selector) => listItem.querySelectorAll(selector),
                    textContent: listItem.textContent,
                    closest: (selector) => listItem.closest(selector)
                };
            }
        }

        return null;
    }

    /**
     * Определение типа данных
     */
    detectDataType(element) {
        console.log('Detecting data type for element:', element);

        // Сначала проверяем data-type атрибут
        if (element.dataset && element.dataset.type) {
            console.log('Found data-type attribute:', element.dataset.type);
            return element.dataset.type;
        }

        // Проверяем родительский элемент с классом horizontal-list-item
        const parentContainer = element.closest('.horizontal-list-item');
        if (parentContainer) {
            const dataType = parentContainer.getAttribute('data-type');
            if (dataType) {
                console.log('Found parent data-type:', dataType);
                return dataType;
            }
        }

        // Определяем по содержимому активной панели
        const activeTabPanel = element.closest('[role="tabpanel"]');
        if (activeTabPanel) {
            const tabType = this.detectTypeFromContent(activeTabPanel);
            console.log('Detected type from tab panel:', tabType);
            return tabType;
        }

        // Определяем по содержимому самого элемента
        const elementType = this.detectTypeFromElementContent(element);
        console.log('Detected type from element content:', elementType);
        return elementType;
    }

    /**
     * Определение типа по содержимому элемента
     */
    detectTypeFromElementContent(element) {
        const content = element.textContent.toLowerCase();

        // Проверяем наличие специфичных полей
        if (content.includes('номер счетчика') || content.includes('тип') && (content.includes('вода') || content.includes('электричество') || content.includes('газ') || content.includes('отопление'))) {
            return 'meter';
        }

        if (content.includes('номер счета') || content.includes('дата счета') || content.includes('срок оплаты') || content.includes('сумма') && content.includes('руб')) {
            return 'invoice';
        }

        if (content.includes('номер платежа') || content.includes('способ оплаты') || content.includes('дата платежа') || content.includes('наличные') || content.includes('карта')) {
            return 'payment';
        }

        return 'unknown';
    }

    /**
     * Определяет тип данных по содержимому активной панели
     */
    detectTypeFromContent(panel) {
        const heading = panel.querySelector('h3, h4, h5');
        if (heading) {
            const headingText = heading.textContent.toLowerCase();
            if (headingText.includes('счетчик')) return 'meter';
            if (headingText.includes('счет')) return 'invoice';
            if (headingText.includes('платеж')) return 'payment';
        }

        // Определяем по содержимому
        const content = panel.textContent.toLowerCase();
        if (content.includes('номер счетчика') || (content.includes('тип') && content.includes('вода'))) return 'meter';
        if (content.includes('номер счета') || content.includes('дата счета') || content.includes('срок оплаты')) return 'invoice';
        if (content.includes('номер платежа') || content.includes('способ оплаты') || content.includes('дата платежа')) return 'payment';

        return 'unknown';
    }

    /**
     * Открытие модального окна с детальной информацией
     */
    openDetailModal(type, id, element) {
        const data = this.extractDataFromElement(element, type);
        const modalHtml = this.generateModalHtml(type, data);

        // Удаляем существующий модал
        const existingModal = document.querySelector('.modal-overlay');
        if (existingModal) {
            existingModal.remove();
        }

        // Создаем новый модал
        const modal = document.createElement('div');
        modal.className = 'modal-overlay active';
        modal.innerHTML = modalHtml;

        document.body.appendChild(modal);

        // Предотвращаем скролл body
        document.body.style.overflow = 'hidden';
    }

    /**
     * Закрытие модального окна
     */
    closeModal() {
        const modal = document.querySelector('.modal-overlay');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = '';
            }, 300);
        }
    }

    /**
     * Извлечение данных из элемента
     */
    extractDataFromElement(element, type) {
        const data = {};

        // Сначала ищем все элементы с data-field в родительском элементе
        const parentElement = element.closest('.horizontal-list-item') || element.closest('.fi-in-repeatable-item');
        if (parentElement) {
            const allFields = parentElement.querySelectorAll('[data-field]');
            allFields.forEach(field => {
                const fieldName = field.dataset.field;
                const fieldValue = field.textContent.trim();
                data[fieldName] = fieldValue;
            });
        }

        // Если не нашли в родительском элементе, ищем в самом элементе
        if (Object.keys(data).length === 0) {
            const fields = element.querySelectorAll('[data-field]');
            fields.forEach(field => {
                const fieldName = field.dataset.field;
                const fieldValue = field.textContent.trim();
                data[fieldName] = fieldValue;
            });
        }

        // Если все еще нет данных, извлекаем из структуры
        if (Object.keys(data).length === 0) {
            const labels = element.querySelectorAll('dt, .fi-in-entry-wrp-label');
            const values = element.querySelectorAll('dd, .fi-in-text-item');

            labels.forEach((label, index) => {
                const value = values[index];
                if (label && value) {
                    const key = label.textContent.trim().toLowerCase().replace(/\s+/g, '_');
                    data[key] = value.textContent.trim();
                }
            });
        }

        // Если ID не найден, попробуем извлечь из других полей
        if (!data.id) {
            // Для счетчиков используем номер как ID
            if (type === 'meter' && data.number) {
                data.id = data.number;
            }
            // Для счетов используем номер счета как ID
            if (type === 'invoice' && data.invoice_number) {
                data.id = data.invoice_number;
            }
            // Для платежей используем номер платежа как ID
            if (type === 'payment' && data.payment_number) {
                data.id = data.payment_number;
            }
        }

        console.log('Extracted data for type', type, ':', data);
        return data;
    }

    /**
     * Генерация HTML для модального окна
     */
    generateModalHtml(type, data) {
        const config = this.modalConfig[type];
        if (!config) {
            return this.generateGenericModalHtml(type, data);
        }

        const title = `${config.icon} ${config.title}`;
        const colors = config.colors;

        let detailItems = '';
        Object.entries(data).forEach(([key, value]) => {
            if (value && value !== '') {
                const label = config.fields[key] || this.formatFieldLabel(key);
                detailItems += this.generateDetailItem(label, value, colors);
            }
        });

        // Генерируем URL для перехода на полную страницу
        const viewUrl = this.generateViewUrl(type, data);

        return `
            <div class="modal-content" style="--modal-primary: ${colors.primary}; --modal-secondary: ${colors.secondary};">
                <div class="modal-header">
                    <h2 class="modal-title" style="background: linear-gradient(135deg, ${colors.primary}, ${colors.secondary}); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">${title}</h2>
                    <button class="modal-close" type="button">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="detail-grid">
                        ${detailItems}
                    </div>
                    ${viewUrl ? `
                        <div class="modal-actions">
                            <a href="${viewUrl}" class="view-full-btn" target="_blank">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15,3 21,3 21,9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                                Перейти к карточке
                            </a>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    /**
     * Генерация URL для перехода на полную страницу - упрощенная версия
     */
    generateViewUrl(type, data) {
        // Теперь у нас есть прямые действия в Filament, поэтому этот метод не нужен
        // Но оставляем для совместимости с модальными окнами
        return null;
    }

    /**
     * Извлечение ID из данных
     */
    extractIdFromData(data, possibleKeys) {
        for (const key of possibleKeys) {
            if (data[key]) {
                return data[key];
            }
        }
        return null;
    }

    /**
     * Генерация элемента детали
     */
    generateDetailItem(label, value, colors) {
        // Определяем тип значения для специальных стилей
        const valueType = this.detectValueType(label, value);

        return `
            <div class="detail-item" style="--modal-primary: ${colors.primary}; --modal-secondary: ${colors.secondary};">
                <div class="detail-label">${label}</div>
                <div class="detail-value" data-type="${valueType}">${value}</div>
            </div>
        `;
    }

    /**
     * Определение типа значения для стилизации
     */
    detectValueType(label, value) {
        const labelLower = label.toLowerCase();
        const valueStr = value.toString().toLowerCase();

        // Проверяем по лейблу
        if (labelLower.includes('номер') || labelLower.includes('id')) {
            return 'number';
        }

        if (labelLower.includes('сумма') || labelLower.includes('цена') || labelLower.includes('стоимость')) {
            return 'amount';
        }

        if (labelLower.includes('статус') || labelLower.includes('состояние')) {
            return 'status';
        }

        if (labelLower.includes('дата') || labelLower.includes('время')) {
            return 'date';
        }

        // Проверяем по значению
        if (valueStr.match(/^\d+$/) || valueStr.match(/^[a-z0-9-]+$/i)) {
            return 'number';
        }

        if (valueStr.includes('₽') || valueStr.includes('руб') || valueStr.match(/^\d+[\.,]\d+$/)) {
            return 'amount';
        }

        if (valueStr.match(/^\d{1,2}[\.\/]\d{1,2}[\.\/]\d{2,4}$/)) {
            return 'date';
        }

        return 'text';
    }

    /**
     * Генерация универсального модального окна
     */
    generateGenericModalHtml(type, data) {
        const title = `Детали ${type}`;
        let detailItems = '';

        Object.entries(data).forEach(([key, value]) => {
            if (value && value !== '') {
                const label = this.formatFieldLabel(key);
                detailItems += `
                    <div class="detail-item">
                        <div class="detail-label">${label}</div>
                        <div class="detail-value">${value}</div>
                    </div>
                `;
            }
        });

        return `
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">${title}</h2>
                    <button class="modal-close" type="button">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="detail-grid">
                        ${detailItems}
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Добавление нового типа модального окна
     */
    addModalType(type, config) {
        this.modalConfig[type] = {
            title: config.title || `Детали ${type}`,
            icon: config.icon || '📋',
            fields: config.fields || {},
            colors: {
                primary: config.colors?.primary || '#3b82f6',
                secondary: config.colors?.secondary || '#1d4ed8'
            }
        };
    }

    /**
     * Получение конфигурации модального окна
     */
    getModalConfig(type) {
        return this.modalConfig[type] || null;
    }

    /**
     * Форматирование названий полей
     */
    formatFieldLabel(key) {
        const labels = {
            'number': 'Номер',
            'type': 'Тип',
            'model': 'Модель',
            'last_reading': 'Последнее показание',
            'last_reading_date': 'Дата показания',
            'status': 'Статус',
            'invoice_number': 'Номер счета',
            'invoice_date': 'Дата счета',
            'due_date': 'Срок оплаты',
            'amount': 'Сумма',
            'total_amount': 'Итого',
            'payment_number': 'Номер платежа',
            'payment_date': 'Дата платежа',
            'payment_method': 'Способ оплаты',
            'reference_number': 'Номер чека'
        };

        return labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
}

// Инициализируем при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing TenantContextManager...');
    window.tenantContext = new TenantContextManager();
    console.log('TenantContextManager initialized:', window.tenantContext);

    // Добавляем глобальные методы для удобства
    window.createTenantUrl = (path, tenantId) => window.tenantContext.createUrl(path, tenantId);
    window.getTenantContext = () => window.tenantContext.getCurrentContext();

    // Глобальные методы для работы с модальными окнами
    window.addModalType = (type, config) => window.tenantContext.addModalType(type, config);
    window.getModalConfig = (type) => window.tenantContext.getModalConfig(type);
    window.openModal = (type, data) => window.tenantContext.openDetailModal(type, null, { querySelectorAll: () => [], textContent: '', closest: () => null });

    console.log('Global methods added to window object');
});
