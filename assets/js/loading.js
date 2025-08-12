/**
 * Creates and manages a loading indicator.
 * 
 * @param {boolean} show - Whether to show or hide the indicator.
 * @param {string} [message] - Optional message to display in the indicator.
 */
function nextavLoadingIndicator( show, message ) {
    // Check if the indicator already exists
    var loadingIndicator = document.getElementById('nextav-loading-indicator-container');
    
    if ( show ) {
        if ( ! loadingIndicator ) {

            // Create the loading indicator element if it does not exist
            loadingIndicator = document.createElement('div');
            loadingIndicator.id = 'nextav-loading-indicator-container';
            loadingIndicator.style.opacity = '1';
            loadingIndicator.style.display = 'block';
            loadingIndicator.style.visibility = 'visible';
            
            // Build spinner
            loadingIndicator.innerHTML = '<div id="nextav-loading-indicator"></div>';

            // Add message
            if ( message ) {
                loadingIndicator.innerHTML += '<div id="nextav-loading-indicator-message">' + message + '</div>';
            }

            // Insert the loading indicator into the body
            document.body.appendChild(loadingIndicator);
        }

        // Show the loading indicator
        loadingIndicator.style.display = 'block';
        loadingIndicator.style.opacity = '1';
        loadingIndicator.style.visibility = 'visible'; // Initially hidden
    } else {
        if ( loadingIndicator ) {
            // Hide the loading indicator
            loadingIndicator.style.opacity = '0';
            setTimeout(function() {
                loadingIndicator.style.display = 'none';
                // Optionally, remove the element from the DOM
                loadingIndicator.remove();
            }, 300); // Match the duration if there are transitions
        }
    }
}