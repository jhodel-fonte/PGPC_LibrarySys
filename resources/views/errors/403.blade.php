<x-layouts.error
    code="403"
    title="Forbidden"
    :message="__($exception->getMessage() ?: 'Forbidden')"
/>
