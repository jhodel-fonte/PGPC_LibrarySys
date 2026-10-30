<style>
.admin-approval-table .mobile-cell { display: none; }
@media (max-width: 767px) {
    main .admin-page-table { flex: none; min-height: auto; overflow: visible; }
    main .admin-page-table > div { flex: none; min-height: auto; overflow: visible; }
    main .admin-table-container { flex: none; min-height: auto; }
    main .admin-table-scroll { flex: none; min-height: auto; overflow: visible !important; }
    main .admin-mobile-table-scroll { overflow: visible !important; max-height: none !important; }
    main .admin-mobile-table { display: block; min-width: 0 !important; width: 100%; }
    main .admin-mobile-table thead { display: none; }
    main .admin-mobile-table tbody { display: block; }
    main .admin-mobile-table tr { display: block; height: auto !important; padding: .75rem; border-bottom: 1px solid #e2e8f0; }
    main .admin-mobile-table td, main .admin-mobile-table tbody th { display: flex; align-items: baseline; justify-content: space-between; gap: .75rem; width: 100%; min-width: 0 !important; padding: .4rem 0; white-space: normal; text-align: left; overflow-wrap: anywhere; }
    main .admin-mobile-table [data-label]::before { content: attr(data-label); flex: 0 0 40%; color: #64748b; font-size: .75rem; font-weight: 600; }
    main .admin-mobile-table td > * { min-width: 0; }
    main .admin-mobile-table .truncate { overflow: visible; white-space: normal; text-overflow: clip; overflow-wrap: anywhere; max-width: none; }
    main .admin-marc-table td { display: block; }
    main .admin-marc-table [data-label]::before { display: block; margin-bottom: .2rem; }
    main .admin-approval-table { display: block; width: 100%; }
    main .admin-approval-table thead { display: none; }
    main .admin-approval-table tbody, main .admin-approval-table tr { display: block; width: 100%; }
    main .admin-approval-table tr > td:not(.mobile-cell) { display: none; }
    main .admin-approval-table tr > td.mobile-cell { display: block; width: 100%; }
    main .admin-approval-table tr > td[colspan]:not(.mobile-cell) { display: block; }
    main .circulation-workstation .workstation-table > div,
    main .circulation-workstation .workstation-table .custom-scrollbar { overflow: visible !important; }
}
</style>
