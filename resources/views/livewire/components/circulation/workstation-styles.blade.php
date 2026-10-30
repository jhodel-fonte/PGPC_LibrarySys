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
    @media (max-width: 1023px) {
        .circulation-workstation .workstation-main,
        .circulation-workstation .workstation-sidebar { display: contents; }
        .circulation-workstation .workstation-search { order: 0; }
        .circulation-workstation .workstation-student { order: 1; min-height: 0; }
        .circulation-workstation .workstation-details { order: 2; }
        .circulation-workstation .workstation-summary { order: 3; }
        .circulation-workstation .workstation-table { min-height: 200px; }
    }
    @media (max-width: 767px) {
        .circulation-workstation .workstation-details { display: contents; }
        .circulation-workstation .workstation-scanner { display: contents; }
        .circulation-workstation .workstation-camera { order: 1; }
        .circulation-workstation .workstation-student { order: 2; }
        .circulation-workstation .workstation-table { order: 3; }
        .circulation-workstation .workstation-preview { order: 4; min-height: 0; }
        .circulation-workstation .workstation-summary { order: 5; }
        .circulation-workstation .workstation-camera-view { height: clamp(160px, 48vw, 220px); }
        .circulation-workstation .workstation-camera button { min-height: 44px; }
        .circulation-workstation .workstation-table button { min-height: 44px; }
        .circulation-workstation .workstation-student button { min-height: 44px; }
        .circulation-workstation .workstation-student h3 { max-width: none; white-space: normal; overflow-wrap: anywhere; }
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
        .circulation-workstation .workstation-grid { flex: 1 1 0%; min-height: 0; align-items: stretch; gap: 1rem; }
        .circulation-workstation .workstation-main { min-height: 0; gap: 0.75rem; }
        .circulation-workstation .workstation-details { flex: 1 1 0%; min-height: 0; }
        .circulation-workstation .workstation-scanner { min-height: 0; overflow-y: auto; }
        .circulation-workstation .workstation-sidebar { min-height: 0; overflow-y: auto; gap: 0.75rem; }
        .circulation-workstation .workstation-student { min-height: 0; padding: 1rem; }
        .circulation-workstation .workstation-table { min-height: 0; height: 100%; }
        .circulation-workstation .workstation-camera-view { height: clamp(100px, 18vh, 150px); }
        .circulation-workstation .workstation-preview, .circulation-workstation .workstation-preview-card { min-height: 0; }
    }
</style>
