<style>
    .circulation-workstation { max-width: 1600px; margin-inline: auto; font-size: 0.875rem; line-height: 1.4; }
    .circulation-workstation h1 { font-size: 1.375rem; line-height: 1.2; }
    .circulation-workstation .text-base { font-size: 0.875rem; line-height: 1.4; }
    .circulation-workstation .circulation-item-title { overflow-wrap: anywhere; word-break: normal; }
    .circulation-workstation .circulation-table { table-layout: fixed; }
    .circulation-workstation .circulation-table th,
    .circulation-workstation .circulation-table td { overflow-wrap: anywhere; }
    .circulation-workstation .workstation-table { container: circulation-list / inline-size; }
    @container circulation-list (max-width: 580px) {
        .circulation-table thead, .circulation-table .circulation-desktop-cell { display: none !important; }
        .circulation-table .circulation-mobile-cell { display: block !important; }
    }
    @media (min-width: 1024px) {
        .circulation-workstation .workstation-summary { grid-area: 2 / 3; min-width: 0; overflow-y: auto; }
    }
    @media (max-width: 1023px) {
        .circulation-workstation .workstation-table { min-height: 200px; }
    }
    @media (max-width: 767px) {
        .circulation-workstation .workstation-camera button { min-height: 44px; }
        .circulation-workstation .workstation-table button { min-height: 44px; }
        .circulation-workstation .workstation-search-row { flex-wrap: wrap; gap: 0.5rem; }
        .circulation-workstation .workstation-search-row input { padding-right: 0.75rem; font-size: 1rem; }
        .circulation-workstation .workstation-modal-search { font-size: 1rem; }
        .circulation-workstation .workstation-scanner-button { bottom: auto; height: 3.5rem; }
        .circulation-workstation .workstation-search-actions { position: static; width: 100%; justify-content: flex-end; }
        .circulation-workstation .workstation-search-actions button { min-height: 44px; }
        .circulation-workstation .workstation-search-actions > div { justify-content: flex-end; }
    }
    @media (min-width: 1100px) and (min-height: 760px) {
        .return-workstation-root, .check-out-workstation { flex: 1 1 0%; min-height: 0; }
        .return-workstation-root { display: flex; flex-direction: column; }
        .circulation-workstation { flex: 1 1 0%; min-height: 0; }
        .circulation-workstation .workstation-content { flex: 1 1 0%; min-height: 0; }
        .circulation-workstation .workstation-header { margin: 0 0 0.75rem; }
        .circulation-workstation .workstation-grid { flex: 1 1 0%; min-height: 0; grid-template-rows: auto minmax(0, 1fr); }
        .circulation-workstation .workstation-scanner { min-height: 0; overflow-y: auto; }
        .circulation-workstation .workstation-table { min-height: 0; }
        .circulation-workstation .workstation-preview, .circulation-workstation .workstation-preview-card { min-height: 0; }
    }
</style>
