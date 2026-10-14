@php
    $extraAttrs = isset($attributes) ? $attributes->getAttributes() : [];
@endphp
@include('livewire.components.usermanager.alert-modal', array_merge($extraAttrs, get_defined_vars()))
