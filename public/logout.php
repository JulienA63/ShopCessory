<?php
session_start();
session_unset(); //supprime les cariables de session
session_destroy();

// Redirige vers l'accueil
header("Location: index.php");
exit;
