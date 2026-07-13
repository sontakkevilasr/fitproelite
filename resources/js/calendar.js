import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

function initCalendars() {
    document.querySelectorAll('[data-calendar]').forEach((el) => {
        if (el.dataset.calendarInitialized) return;
        el.dataset.calendarInitialized = 'true';

        const calendar = new Calendar(el, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
            initialView: el.dataset.initialView || 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay,listWeek',
            },
            height: 'auto',
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',
            nowIndicator: true,
            events: el.dataset.eventsUrl,
            eventClick: function (info) {
                el.dispatchEvent(new CustomEvent('calendar:event-click', { detail: { event: info.event, jsEvent: info.jsEvent } }));
            },
        });

        calendar.render();
        el.fullCalendar = calendar;
    });
}

document.addEventListener('DOMContentLoaded', initCalendars);

export { initCalendars };
