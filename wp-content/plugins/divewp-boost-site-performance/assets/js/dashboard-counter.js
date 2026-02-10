document.addEventListener('DOMContentLoaded', function () {
    // Count status pills from rendered tabs
    function countStatusPills() {
        const counts = {
            success: { total: 0, tabs: {} },
            warning: { total: 0, tabs: {} },
            danger: { total: 0, tabs: {} }
        };

        // Get all feature tabs (excluding overview and user-events)
        document.querySelectorAll('.divewp-tabs li[data-tab]').forEach(tabLink => {
            const tabId = tabLink.getAttribute('data-tab');
            const tabName = tabLink.textContent.trim();

            // Skip non-countable tabs, Dashboard entries, and welcome tab
            if (tabId === 'user-events' || tabId === 'welcome' || tabName === 'Dashboard' || tabName === 'Табло') return;

            const tab = document.getElementById(tabId);
            if (!tab) return;

            // Count pills for each status in this tab
            ['success', 'warning', 'danger'].forEach(status => {
                // Exclude pills from dashboard overview and welcome tab
                const pills = tab.querySelectorAll(`.status-pill-${status}`);
                const filteredPills = Array.from(pills).filter(pill => {
                    // Skip if the pill is within the dashboard overview or welcome tab
                    return !pill.closest('.divewp-dashboard-overview') &&
                        !pill.closest('#welcome') &&
                        !pill.closest('.divewp-dashboard-grid');
                });

                if (filteredPills.length > 0) {
                    counts[status].total += filteredPills.length;
                    counts[status].tabs[tabId] = {
                        name: tabName,
                        count: filteredPills.length,
                        items: filteredPills
                            .map(pill => pill.closest('tr')?.querySelector('strong')?.textContent || '')
                            .filter(text => text)
                    };
                }
            });
        });

        updateDashboardCards(counts);
    }

    // Update dashboard cards with counts
    function updateDashboardCards(counts) {
        ['success', 'warning', 'danger'].forEach(status => {
            const card = document.querySelector(`.divewp-card-${status}`);
            if (!card) return;

            // Update total count
            card.querySelector('.divewp-card-count').textContent = counts[status].total;

            // Update tab list
            const list = card.querySelector('.divewp-status-list');
            list.innerHTML = '';

            Object.entries(counts[status].tabs).forEach(([tabId, data]) => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <a href="javascript:void(0);" 
                       class="divewp-tab-link" 
                       data-tab="${tabId}">
                        ${data.name}
                        <span class="divewp-count-badge">${data.count}</span>
                    </a>
                `;
                list.appendChild(li);
            });
        });
    }

    // Initial count
    countStatusPills();
}); 