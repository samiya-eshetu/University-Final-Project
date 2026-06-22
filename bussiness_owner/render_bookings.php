<?php
function renderTable($bookings) {
    if (empty($bookings)) {
        echo "<p>No bookings found.</p>";
        return;
    }

    echo '<div class="table-responsive">
        <table class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Service</th>
                <th>Scheduled For</th>
                <th>Contact</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($bookings as $index => $booking) {
        echo "<tr>
            <td>" . ($index + 1) . "</td>
            <td>" . htmlspecialchars($booking['fullName']) . "</td>
            <td>" . htmlspecialchars($booking['serviceType']) . " - #" . $booking['serviceID'] . "</td>
            <td>" . $booking['scheduledFor'] . "</td>
            <td>" . htmlspecialchars($booking['phoneNumber']) . "</td>
            <td><span class='badge bg-secondary'>" . htmlspecialchars($booking['status']) . "</span></td>
        </tr>";
    }

    echo '</tbody></table></div>';
}
?>
