@props(['eventsUrl', 'initialView' => 'timeGridWeek'])

<div
    data-calendar
    data-events-url="{{ $eventsUrl }}"
    data-initial-view="{{ $initialView }}"
    {{ $attributes }}
></div>
