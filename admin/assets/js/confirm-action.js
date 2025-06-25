/**
 * Requires confirmation before proceeding with an action.
 *
 * @since 1.0.17
 *
 * @param {string} url The URL to redirect to if the action is confirmed.
 * @param {string|null} [message=null] The message to display in the confirmation dialog. 
 *                                     If not provided, a default message will be used.
 * 
 * @return {boolean} Returns false regardless of the user’s choice, to prevent 
 *                   further action in the event handler.
 */
function nextavConfirmAction( url, message = null ) {
    // Define message
    message = message ?? 'Are you sure you want to proceed?';

    // Confirm with alert
    if ( confirm( message ) ) {
        // Redirect to url
        window.location.href = url;
        return false;
    }
    return false;
}