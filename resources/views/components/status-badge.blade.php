@props(['status'])

@php
$colors = [
    'new' => 'bg-gray-100 text-gray-700',
    'scheduled' => 'bg-blue-100 text-blue-700',
    'pre_visit_scheduled' => 'bg-blue-100 text-blue-700',
    'assessment_completed' => 'bg-indigo-100 text-indigo-700',
    'trial_scheduled' => 'bg-blue-100 text-blue-700',
    'completed' => 'bg-emerald-100 text-emerald-700',
    'converted' => 'bg-emerald-100 text-emerald-700',
    'no_show' => 'bg-red-100 text-red-700',
    'cancelled' => 'bg-red-100 text-red-700',
    'lost' => 'bg-red-100 text-red-700',
    'follow_up' => 'bg-amber-100 text-amber-700',
    'logged' => 'bg-gray-100 text-gray-700',
    'sent' => 'bg-emerald-100 text-emerald-700',
    'failed' => 'bg-red-100 text-red-700',
    'active' => 'bg-emerald-100 text-emerald-700',
    'inactive' => 'bg-gray-100 text-gray-700',
    'interested' => 'bg-emerald-100 text-emerald-700',
    'not_interested' => 'bg-red-100 text-red-700',
    'follow_up_later' => 'bg-amber-100 text-amber-700',
    'no_answer' => 'bg-gray-100 text-gray-700',
];
$classes = $colors[$status] ?? 'bg-gray-100 text-gray-700';
$label = ucwords(str_replace('_', ' ', $status));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    {{ $label }}
</span>
