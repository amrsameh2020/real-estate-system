/**
 * File: public/assets/js/custom.js
 * This script handles the client-side interactions for the responsive sidebar.
 */
document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');

    // Ensure all required elements exist before adding event listeners
    if (sidebar && sidebarToggle && sidebarClose) {

        // Function to open the sidebar
        function openSidebar() {
            document.body.classList.add('sidebar-open');
        }

        // Function to close the sidebar
        function closeSidebar() {
            document.body.classList.remove('sidebar-open');
        }

        // Event listener for the main toggle button (hamburger icon)
        sidebarToggle.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent the click from bubbling up
            openSidebar();
        });

        // Event listener for the close button inside the sidebar
        sidebarClose.addEventListener('click', function () {
            closeSidebar();
        });

        // Event listener to close the sidebar when clicking on the main content area (overlay effect)
        document.addEventListener('click', function (e) {
            // If the body has the 'sidebar-open' class and the click is outside the sidebar
            if (document.body.classList.contains('sidebar-open') && !sidebar.contains(e.target)) {
                closeSidebar();
            }
        });

    }

    /**
     * Optional: Initialize Bootstrap tooltips if you use them in your application.
     * This finds all elements with data-bs-toggle="tooltip" and enables the tooltip.
     */
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

});
