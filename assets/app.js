import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


document.addEventListener('DOMContentLoaded', () => {
    const calendar = document.getElementById('monthCalendar');
    if (!calendar) return;

    const events = JSON.parse(calendar.dataset.events);
    const monthLabel = document.getElementById('monthLabel');
    let currentDate = new Date();

    function renderMonth() {
        calendar.innerHTML = '';

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();

        monthLabel.textContent = currentDate.toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric'
        });

        const adjustedFirstDay = firstDay === 0 ? 6 : firstDay - 1;

        for (let i = 0; i < adjustedFirstDay; i++) {
            const empty = document.createElement('div');
            empty.classList.add('day');
            calendar.appendChild(empty);
        }

        for (let d = 1; d <= lastDate; d++) {
            const day = document.createElement('div');
            day.classList.add('day');

            const header = document.createElement('h4');
            header.textContent = d;
            day.appendChild(header);

            const dateStr = `${year}-${String(month + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;

            events.forEach(ev => {
                if (ev.date === dateStr) {
                    const evEl = document.createElement('span');
                    evEl.textContent = ev.label;
                    evEl.classList.add('event', 'event-' + ev.type);
                    day.appendChild(evEl);
                }
            });

            calendar.appendChild(day);
        }
    }

    document.getElementById('prevMonth').onclick = () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderMonth();
    };

    document.getElementById('nextMonth').onclick = () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderMonth();
    };

    renderMonth();
});
