<?php
    /**
     * Clase Jugador para el juego de hundir la flota.
     * 
     * @author  Francisco javier González Sabariego.
     * @since   21/04/2020
     * 
     * @version 1.0
     */

    include "class/Tablero.php";

    class Jugador {
    
        protected $_tablero;            //Objeto de la clase tablero
        protected $_nombre;             //Nombre del jugador/a
        protected $_numDisparos;        //Total de disparods hechos por el jugador
        protected $_barcosHundidos;     //Array con la lista de barcos hundidos al enemigo

        public function __construct($nombre) {
            $this->_tablero = new Tablero();
            $this->colocarBarcos();
            $this->_nombre = $nombre;
            $this->_numDisparos = 0;
            $this->_barcosHundidos = array(
                array(
                    "tipo" => "Submarinos",
                    "hundidos" => 0
                ),
                array(
                    "tipo" => "Acorazados",
                    "hundidos" => 0
                ),array(
                    "tipo" => "Destructures",
                    "hundidos" => 0
                ),array(
                    "tipo" => "Portaaviones",
                    "hundidos" => 0
                ));
        }

        /**
         * Devuelve la longitud del barco a crear según el número de barcos que se hayan creado
         * 
         * @param {$i}  Número de barcos creados
         */
        private function longitudBarco($i) {
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

        /**
         * Añade 10 barcos en el tablero del jugador:
         * 
         * -De longitud 4    x1
         * -De longitud 3    x2
         * -De longitud 2    x3
         * -De longitud 1    x4
         */
        private function colocarBarcos() {
            for ($i=1; $i<11; $i++) {               //Añadimos los barcos en posiciones válidas
                do {
                    $fila = rand(0,9);
                    $columna = rand(0,9);
        
                    $sentido = rand(0,3);
                    
                    try {
                        $this->_tablero->addBarco($fila,$columna,longitudBarco($i),$sentido);
                        $ubicarBarco = false;
                    } catch (Exception $e) {
                        $ubicarBarco = true;
                    }
                } while ($ubicarBarco);
            }
        }

        /**
         * Devuelve el nombre del jugador.
         * 
         * @return {String} Nombre del jugador.
         */
        public function getNombre() {
            return $this->_nombre;
        }

        /**
         * Devuelve el objeto tablero del jugador
         * 
         * @return {Object} Tablero
         */
        public function getTablero() {
            return $this->_tablero;
        }

        /**
         * Devuelve un booleano informando si el juegador ha sido derrotado
         * 
         * @return {Boolean} True si el juegador ha perdido, false si no.
         */
        public function getDerrotado() {
            return sizeof($this->getTablero()->getListaBarcos())==0;
        }

        /**
         * Devuelve el números de disparos realizados por el jugador.
         * 
         * @return {int} Total de disparos realizados por el jugador.
         */
        public function getDisparos() {
            return $this->_numDisparos;
        }

        /**
         * Incrementa el total de barcos hundidos en función del tipo de barco que se le pase como parámetro
         * 
         * @param {$tipoBarco}  El tipo de barco que se deseaincrementar
         */
        public function incrementaBarcosHundidos($tipoBarco) {
            $this->_barcosHundidos[$tipoBarco-1]["hundidos"]++;
        }

        /**
         * Imprime la lista de barcos hundidos por el jugador.
         */
        public function imprimeBarcosHundidos() {
            echo "<table>";
            for ($i=0; $i<sizeof($this->_barcosHundidos); $i++) 
                echo "<tr><td>".$this->_barcosHundidos[$i]["tipo"]."</td><td>x".$this->_barcosHundidos[$i]["hundidos"]."</td></tr>";
            echo "</table>";
        }

        /**
         * El jugador dispara al enemigo en las coordenadas dadas.
         * 
         * @param {$fila}           Fila del disparo
         * @param {$columna}        Columna del disparo
         * @param {$tableroEnemigo} Objeto tablero del jugador enemigo
         */
        public function disparar($fila,$columna,$tableroEnemigo) {
            if ($this->_tablero->getValorTableroJuego($fila,$columna)!=0) return;
            if (!$tableroEnemigo->getValorTablero($fila,$columna)==0) {                                  //Si no hemos impactado en agua
                $this->getTablero()->setValorTableroJuego($fila,$columna,1);
                $tableroEnemigo->setValorTablero($fila,$columna,1);
                $indexBarco = $tableroEnemigo->getIndexBarcoImpactado($fila,$columna);
                $tableroEnemigo->getListaBarcos()[$indexBarco]->destruirModulo($fila,$columna);
                $_SESSION['mensajesJ1'] = ($tableroEnemigo->getListaBarcos()[$indexBarco]->getHundido()) //Añadimos mensaje
                    ? "<div class='impactoJ1'>".$tableroEnemigo->getListaBarcos()[$indexBarco]->getMensajeHundido()."</div>" : "";
                if ($tableroEnemigo->getListaBarcos()[$indexBarco]->getHundido()) {                      //Si el barco está hundido
                    $this->incrementaBarcosHundidos($tableroEnemigo->getListaBarcos()[$indexBarco]->getTipo());
                    $tableroEnemigo->setHundirBarco($indexBarco);
                }
            } else {
                $this->getTablero()->setValorTableroJuego($fila,$columna,4);
                $tableroEnemigo->setValorTablero($fila,$columna,4);
                $_SESSION['mensajesJ1'] = "";
            }
            $this->_numDisparos++;
        }
    }
?>