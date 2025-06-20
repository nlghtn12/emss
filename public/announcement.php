<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    die('Access denied');
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Announcements</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<p><a href="dashboard.php">Back to Dashboard</a></p>
<h2>Announcements</h2>

<?php if ($user['role'] === 'admin'): ?>
<form id="announcementForm" enctype="multipart/form-data">
    <textarea name="content" placeholder="Write announcement..." required></textarea><br>
    <input type="file" name="media" accept="image/*,video/*"><br>
    <input type="hidden" name="action" value="post">
    <button type="submit">Post</button>
</form>
<hr>
<?php endif; ?>

<div id="message" style="color: green;"></div>
<div id="announcements"></div>

<script>
function loadAnnouncements() {
    $.get('announcement_handler.php', function(data) {
        $('#announcements').html(data);
    });
}

$('#announcementForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: 'announcement_handler.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            $('#message').text(response);
            $('#announcementForm')[0].reset();
            loadAnnouncements();
        }
    });
});

$(document).on('click', '.delete-btn', function(e) {
    e.preventDefault();
    if (confirm("Delete this announcement?")) {
        const id = $(this).data('id');
        $.post('announcement_handler.php', { action: 'delete', id: id }, function(response) {
            $('#message').text(response);
            loadAnnouncements();
        });
    }
});

$(document).ready(function() {
    loadAnnouncements();
});
</script>
</body>
</html>
