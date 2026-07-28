@include('filament.admin.partials.login-style')

<style>
    .fi-simple-layout .bkd-login-hero {
        background: linear-gradient(130deg, #333c55, #5f6f9b);
    }

    /* Dashboard filtreleri */
    .crm-dashboard-filters .fi-fo-field-wrp,
    .crm-dashboard-filters .choices,
    .crm-dashboard-filters .fi-select-input {
        min-width: 0;
        width: 100%;
    }

    .crm-dashboard-filters .fi-section-content {
        overflow: visible;
    }

    /* Dashboard widget düzeni */
    .fi-page-dashboard .fi-wi {
        height: 100%;
    }

    .fi-page-dashboard .fi-wi-widget {
        height: 100%;
    }

    .fi-page-dashboard .fi-wi-stats-overview-stat {
        min-height: 5.5rem;
    }

    .fi-page-dashboard .fi-section-header-heading {
        font-size: 0.95rem;
    }

    .crm-dashboard-filters > .fi-section {
        margin-bottom: 0.25rem;
    }

    /* Afiş tasarımcısı */
    .bkd-poster-layout {
        display: flex;
        gap: 18px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .bkd-poster-stage {
        flex: 1 1 520px;
        min-width: 0;
        width: 100%;
        background: #f1f5f9;
        border-radius: 14px;
        padding: 14px;
        overflow: auto;
        display: flex;
        justify-content: center;
        -webkit-overflow-scrolling: touch;
    }

    .bkd-poster-props {
        width: 300px;
        flex: 0 0 300px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
    }

    .bkd-poster-toolbar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    /* Genel CRM mobil düzen */
    .fi-ta-content {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .fi-header-actions,
    .fi-ta-header-actions,
    .fi-ta-actions {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .fi-fo-tabs-tab {
        white-space: nowrap;
    }

    .fi-tabs {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Faaliyet raporları */
    .crm-activity-report {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .crm-activity-report__meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.75rem;
        padding: 1rem 1.1rem;
        background: linear-gradient(135deg, #f5f7fb, #e8edf6);
        border: 1px solid #d1dbec;
        border-radius: 14px;
    }

    .crm-activity-report__meta-label {
        display: block;
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .crm-activity-report__stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.85rem;
    }

    .crm-activity-report__stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .crm-activity-report__stat-label {
        display: block;
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .crm-activity-report__stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #3f4c6b;
        line-height: 1.2;
    }

    .crm-activity-report__section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        margin: 0 0 0.65rem;
    }

    .crm-activity-report__hint {
        font-size: 0.8rem;
        font-weight: 400;
        color: #64748b;
    }

    .crm-activity-report__table-wrap {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        -webkit-overflow-scrolling: touch;
    }

    .crm-activity-report__table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .crm-activity-report__table th,
    .crm-activity-report__table td {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
        white-space: nowrap;
    }

    .crm-activity-report__table th {
        background: #3f4c6b;
        color: #fff;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .crm-activity-report__table tbody tr:nth-child(even) {
        background: #f8fafc;
    }

    .crm-activity-report__table tbody tr:hover {
        background: #f5f7fb;
    }

    .crm-activity-report__table td.is-amount,
    .crm-activity-report__table tfoot td {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .crm-activity-report__table tfoot td {
        background: #e8edf6;
        font-weight: 700;
        color: #3f4c6b;
        border-bottom: none;
    }

    .crm-activity-report__table td.is-empty {
        text-align: center;
        color: #94a3b8;
        padding: 1.25rem;
    }

    .crm-activity-report-filters > .fi-section {
        margin-bottom: 0.25rem;
    }

    @media (max-width: 768px) {
        #bkd-live-clock {
            display: none !important;
        }

        .fi-simple-layout .fi-simple-main {
            margin: 0.75rem;
            max-width: calc(100vw - 1.5rem);
        }

        .bkd-poster-props {
            width: 100%;
            flex: 1 1 100%;
        }

        .bkd-poster-stage {
            flex: 1 1 100%;
        }

        .fi-page-header-heading {
            font-size: 1.125rem;
            line-height: 1.4;
            word-break: break-word;
        }

        .fi-ta-filters .fi-fo-component-ctn {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 480px) {
        .fi-simple-layout .bkd-login-hero {
            padding: 0.75rem;
            font-size: 0.9rem;
        }
    }
</style>
