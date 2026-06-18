<?php
try { 
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=db_form_risk', 'root', ''); 
    echo 'DB OK'; 
} catch (Exception $e) { 
    echo 'DB ERROR: ' . $e->getMessage(); 
}
