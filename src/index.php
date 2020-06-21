<?php
    /**
     * Juego de hundir la flota. Capa de presentación.
     * 
     * @author  Francisco javier González Sabariego.
     * @since   21/04/2020
     * 
     * @version 1.0.0
     */

    include "class/Jugador.php";
    include "class/IA.php";
    include "resources/funciones.php";

    session_start();

    if (!isset($_SESSION['jugador1'])) {
        $_SESSION['jugador1'] = new Jugador("Humano/a");
        $_SESSION['jugador2'] = new IA("Skynet");
        $_SESSION['mensajesJ1'] = "";
        $_SESSION['mensajesJ2'] = "";
        $_SESSION['turno'] = rand(1,2);
        $_SESSION['finPartida'] = false;
    }

    if (isset($_POST['borrar'])) {
        cerrarSesion();
    }
    
    if ($_SESSION['turno']==1 && !$_SESSION['finPartida']) {
        if (isset($_GET['fila'])) {
            $_SESSION['jugador1']->disparar($_GET['fila'],$_GET['columna'],$_SESSION['jugador2']->getTablero());
            $_SESSION['turno'] = 2;
        }
    }

    $_SESSION['finPartida'] = $_SESSION['jugador1']->getDerrotado() || $_SESSION['jugador2']->getDerrotado();
    
    if ($_SESSION['turno']==2 && !$_SESSION['finPartida']) {
        $_SESSION['jugador2']->jugar($_SESSION['jugador1']->getTablero());
        $_SESSION['turno'] = 1;
    }

    $_SESSION['finPartida'] = $_SESSION['jugador1']->getDerrotado() || $_SESSION['jugador2']->getDerrotado();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Francisco Javier González Sabariego">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Hundir la flota</title>
</head>
<body>
    <header>
        <h1>Hundir la flota</h1>
    </header>
    <main>
        <?php 
            if ($_SESSION['finPartida']) {
                echo "<div class='contenedorFinPartida'>";
                    echo "<div class=".(($_SESSION['jugador2']->getDerrotado()) ? "victoria" : "derrota").">";
                        echo "<h4>".(($_SESSION['jugador2']->getDerrotado()) ? "¡Felicidades, has ganado!" : "¡Lo siento, has perdido!")."</h4>";
                        echo "<form action='index.php' method='post'>";
                            echo "<input type='submit' name='borrar' value='Nueva partida'>";
                        echo "</form>";
                    echo "</div>";
                echo "</div>";
            }
        ?>
        <div class="juego">
            <div class="informacion rival">
                <div class="mensaje">
                    <h4 class="center">Disparos realizados por <?php echo $_SESSION['jugador2']->getNombre().": ".$_SESSION['jugador2']->getDisparos(); ?></h4>
                    <h4>Mensaje:</h4>
                    <?php echo $_SESSION['mensajesJ2']; ?>
                </div>
                <div class="barcos_hundidos">
                    <h4>Barcos hundidos por <?php echo $_SESSION['jugador2']->getNombre(); ?>:</h4>
                    <?php 
                        $_SESSION['jugador2']->imprimeBarcosHundidos();
                    ?>
                </div>
            </div>
            <div class="tablero">
                <div>
                    <?php
                        echo "<h4>TABLERO PROPIO</h4>";
                        $_SESSION['jugador1']->getTablero()->imprimir();
                    ?>
                </div>
                <div>
                    <?php
                        echo "<h4>TABLERO ENEMIGO</h4>";
                        $_SESSION['jugador1']->getTablero()->imprTabVis($_SESSION['finPartida']);
                    ?>
                </div>                
            </div>
            <div class="informacion">
                <div class="mensaje">
                    <h4 class="center">Disparos realizados por <?php echo $_SESSION['jugador1']->getNombre().": ".$_SESSION['jugador1']->getDisparos(); ?></h4>
                    <h4>Mensaje:</h4>
                    <?php echo $_SESSION['mensajesJ1']; ?>
                </div>
                <div class="barcos_hundidos">
                    <h4>Barcos hundidos por <?php echo $_SESSION['jugador1']->getNombre(); ?>:</h4>
                    <?php 
                        $_SESSION['jugador1']->imprimeBarcosHundidos();
                    ?>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <h4>RRSS del autor:</h4>
        <div class="rrss">
            <a href="https://twitter.com/Fco_Javier_Glez" target="_blank"><img src="img/twitter.png" alt="Enlace a cuenta de Twitter del autor"></a>
            <a href="https://github.com/FcoJavierGlez" target="_blank"><img src="img/github.png" alt="Enlace a cuenta de GitHub del autor"></a>
            <a href="https://www.linkedin.com/in/francisco-javier-gonz%C3%A1lez-sabariego-51052a175/" target="_blank"><img src="img/linkedin.png" alt="Enlace a cuenta de Linkedin del autor"></a>
        </div>
    </footer>
    <?php 
        //Modo Dev:
        /* $_SESSION['jugador2']->getTablero()->imprimirTabIA();
        $_SESSION['jugador1']->getTablero()->imprimirListaBarcos();
        echo "<form action='index.php' method='post'>";
        echo "<input type='submit' name='borrar' value='Nueva partida'>";
        echo "</form>"; */
    ?>
</body>
</html>