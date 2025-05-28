    // Set the end time for the flash sale (for example, 2 hours and 30 minutes from now)
    const endTime = new Date(Date.now() + 2 * 60 * 60 * 1000 + 30 * 60 * 1000); // 2 hours and 30 minutes

    function updateCountdown() {
        const now = new Date();
        const timeRemaining = endTime - now;

        // Calculate hours, minutes, and seconds
        const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

        // Display the result in the countdown element
        document.getElementById('countdown').innerHTML = 
            `Ends in ${hours}h : ${minutes}m : ${seconds}s`;

        // If the countdown is finished, display a message
        if (timeRemaining < 0) {
            clearInterval(countdownInterval);
            document.getElementById('countdown').innerHTML = "Flash Sale Ended";
        }
    }

    // Update the countdown every second
    const countdownInterval = setInterval(updateCountdown, 1000);