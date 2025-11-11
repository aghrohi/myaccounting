<?php
// Profile Page
requireLogin();

// Redirect to settings page profile section
header('Location: index.php?page=settings#profile');
exit;
