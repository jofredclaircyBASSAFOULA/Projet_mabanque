<?php
session_start();

/* -------------------------------
   DÉCONNEXION
--------------------------------*/
session_unset();
session_destroy();

/* -------------------------------
   REDIRECTION VERS TA PAGE CONNEXION
--------------------------------*/
header("Location: page_connexion.php.php");
exit;
