<?php
$input = 'pass';
$hash = '$2y$12$ZN.bZhfGB1MI865wSYjz6u7LFAcUlh5loCVzYRkErNlqK3oXX..sC';

if (password_verify($input, $hash)) {
    echo "✅ Password matches!";
} else {
    echo "❌ Password mismatch.";
}