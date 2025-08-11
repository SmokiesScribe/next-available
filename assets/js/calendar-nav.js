/**
 * Handles month navigation for the calendar block via AJAX.
 * 
 * This script manages the current month and year in JavaScript state,
 * fetches updated calendar HTML asynchronously on navigation button clicks,
 * and updates the calendar display without reloading the page.
 * 
 * It also updates the browser URL query parameters to reflect the current
 * selected month and year, without causing a page reload.
 * 
 * Expected HTML structure:
 * - Container with ID "nextav-calendar" that holds the calendar HTML.
 * - Buttons with classes "nextav-cal-nav-button aprev" and "nextav-cal-nav-button next" to navigate months.
 * 
 * Requires a server-side endpoint hooked to "wp_ajax_nextav_get_calendar_callback"
 * that returns the calendar HTML for the requested year and month.
 */
(function (jQuery) {
  // Initialize current month string in Y-m format, e.g. "2025-08"
  let now = new Date();

  const calendarContainer = document.getElementById('nextav-calendar');

  // Get existing atts
  const atts = calendarContainer.getAttribute('data-atts');
  let calMonth = calendarContainer.getAttribute('data-month');

  function parseYearMonth(ym) {
    const [year, month] = ym.split('-').map(Number);
    return new Date(year, month - 1);
  }

  function formatYearMonth(date) {
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
  }

  function nextavFetchCalendar(ym) {
    jQuery.ajax({
      url: calendarNavData.ajaxurl,
      method: 'POST',
      data: {
        action: 'nextav_get_calendar',
        month: ym, // Send single Y-m string
        atts: atts,
        nonce: calendarNavData.nonce,
      },
      success: function(response) {
        if (response.success && response.data) {
          if (calendarContainer) {
            calendarContainer.innerHTML = response.data;
          }
          const url = new URL(window.location);
          url.searchParams.set('cal_month', ym);
          window.history.replaceState(null, '', url.toString());

          // Update calMonth after success
          calMonth = ym;

          // Rebind event listeners to new buttons
          setupEventListeners();
        } else {
          console.error('Error fetching calendar:', response.data || 'Unknown error');
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error('AJAX error:', textStatus, errorThrown);
      }
    });
  }

  function setupEventListeners() {
    const prevButton = document.querySelector('.nextav-cal-nav-button.prev');
    const nextButton = document.querySelector('.nextav-cal-nav-button.next');

    if (prevButton) {
      prevButton.onclick = () => {
        let date = parseYearMonth(calMonth);
        date.setMonth(date.getMonth() - 1);
        calMonth = formatYearMonth(date);
        nextavFetchCalendar(calMonth);
      };
    }

    if (nextButton) {
      nextButton.onclick = () => {
        let date = parseYearMonth(calMonth);
        date.setMonth(date.getMonth() + 1);
        calMonth = formatYearMonth(date);
        nextavFetchCalendar(calMonth);
      };
    }
  }

  // Initial binding
  setupEventListeners();
})(jQuery);
