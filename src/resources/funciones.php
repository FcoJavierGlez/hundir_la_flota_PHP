<?php
    function cerrarSesion() {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id();
        header('Location:index.php');
    }

    function longitudBarco($i) {
        switch ($i) {
            case 1:
                return 4;
            case 2:
            case 3:
                return 3;
            case 4:
            case 5:
            case 6:
                return 2;
            case 7:
            case 8:
            case 9:
            case 10:
                return 1;
        }
    }

?>