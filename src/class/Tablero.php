<?php
    /**
     * Clase Tablero para el juego de hundir la flota.
     * 
     * Esta clase incluye:
     * -El tablero dónde se almacena numericamente los barcos
     * -El tablero de juego, que será el tablero que se imprima para el jugador humano y donde podrá clickar la casilla a disparar
     * -El tablero de la IA, tablero que usará la IA para analizar dónde puede disparar y dónde se puede ocultar un barco enemigo
     * -Lista de barcos (objetos de la clase Barco) con el total de barcos activos para el jugador.
     * 
     * 
     * @author  Francisco javier González Sabariego.
     * @since   21/04/2020
     * 
     * @version 1.0
     */

    include "class/Barco.php";

    class Tablero {

        private $_tablero = array();
        private $_tableroJuego = array();
        private $_tableroIA = array();
        private $_listaBarcos = array();

        public function __construct() {
            for ($i=0; $i<10; $i++) {
                array_push($this->_tablero, array(0,0,0,0,0,0,0,0,0,0));
                array_push($this->_tableroJuego, array(0,0,0,0,0,0,0,0,0,0));
                array_push($this->_tableroIA, ( ($i==0 || $i==9) ? array(4,6,6,6,6,6,6,6,6,4) : array(6,9,9,9,9,9,9,9,9,6)));
            }
        }

        /*** GETTERS ***/

        /**
         * Devuelve el valor contenido en el tablero para las coordenadas pasadas como parámetro
         * 
         * @param {$fila}       Fila pasada por parámetro
         * @param {$columna}    Columna pasada por parámetro
         * 
         * @return {int}        Valor contenido en el tablero para la posición [$fila][$columna]
         */
        public function getValorTablero($fila,$columna) {
            return $this->_tablero[$fila][$columna];
        }

        /**
         * Devuelve el valor contenido en el tablero de juego para las coordenadas pasadas como parámetro
         * 
         * @param {$fila}       Fila pasada por parámetro
         * @param {$columna}    Columna pasada por parámetro
         * 
         * @return {int}        Valor contenido en el tablero para la posición [$fila][$columna]
         */
        public function getValorTableroJuego($fila,$columna) {
            return $this->_tableroJuego[$fila][$columna];
        }

        /**
         * Devuelve el valor contenido en el tablero de la IA para las coordenadas pasadas como parámetro
         * 
         * @param {$fila}       Fila pasada por parámetro
         * @param {$columna}    Columna pasada por parámetro
         * 
         * @return {int}        Valor contenido en el tablero para la posición [$fila][$columna]
         */
        public function getValorTableroIA($fila,$columna) {
            return $this->_tableroIA[$fila][$columna];
        }

        /**
         * Devuelve el conjunto de barcos (objetos de la clase Barco) almacenados en este tablero
         * 
         * @return {Array}      Total de objetos de la clase barco almacenados en este tablero.
         */
        public function getListaBarcos() {
            return $this->_listaBarcos;
        }

        /**
         * Devuelve el número del índice del array donde está almacenado el barco impactado
         * 
         * @param {$fila}               Fila donde se ha producido el impacto
         * @param {$columna}            Columna donde se ha producido el impacto
         * @param {$tableroEnemigo}     Objeto tablero del jugador contrario
         * 
         * @return {int}                Índice donde está almacenado el barco
         */
        public function getIndexBarcoImpactado($fila,$columna) {
            for ($i=0; $i<sizeof($this->_listaBarcos); $i++) 
                if ($this->_listaBarcos[$i]->comprobarImpacto($fila,$columna)) 
                    return $i;
            return -1;
        }

        /*** SETTERS ***/

        /**
         * Inserta un valor en el tablero para las coordenadas pasadas como parámetro
         * 
         * @param {$fila}       Fila pasada por parámetro
         * @param {$columna}    Columna pasada por parámetro
         * @param {$valor}      Valor a insertar en el tablero
         */
        public function setValorTablero($fila,$columna,$valor) {
            $this->_tablero[$fila][$columna] = $valor;
        }

        /**
         * Inserta un valor en el tablero de juego para las coordenadas pasadas como parámetro
         * 
         * @param {$fila}       Fila pasada por parámetro
         * @param {$columna}    Columna pasada por parámetro
         * @param {$valor}      Valor a insertar en el tablero
         */
        public function setValorTableroJuego($fila,$columna,$valor) {
            $this->_tableroJuego[$fila][$columna] = $valor;
        }

        /**
         * Inserta un valor en el tablero de la IA para las coordenadas pasadas como parámetro
         * 
         * @param {$fila}       Fila pasada por parámetro
         * @param {$columna}    Columna pasada por parámetro
         * @param {$valor}      Valor a insertar en el tablero
         */
        public function setValorTableroIA($fila,$columna,$valor) {
            $this->_tableroIA[$fila][$columna] = $valor;
        }

        /**
         * Elimina un barco que ha sido hundido de la lista de objetos barcos 
         * 
         * @param {$i}  Posición dónde está almacenado el barco a hundir
         */
        public function setHundirBarco($i) {
            array_splice($this->_listaBarcos,$i,1);
        }


        /*** MÉTODOS DE VALIDACIÓN E INSERCIÓN DE BARCOS ***/

        /**
         * Valida que el barco no se saldrá del tablero.
         * 
         * @param {$fila}       Fila del módulo inicial
         * @param {$columna}    Columna del módulo inicial
         * @param {$tipo}       Longitud del barco
         * @param {$direccion}  Dirección del barco (0-3)
         * 
         * @return {Boolean}    True si la posición es correcta, false si no lo es
         */
        private function longitudValida($fila,$columna,$tipo,$direccion) {
            switch ($direccion) {
                case 0:
                    return ($fila+$tipo<=10);
                case 1:
                    return ($columna-$tipo>=0);
                case 2:
                    return ($fila-$tipo>=0);
                case 3:
                    return ($columna+$tipo<=10);
            }
        }

        /**
         * Valida que el area del barco que queremos inscribir en el tablero está libre
         * de otros barcos.
         * 
         * @param {$fila}           Fila del módulo inicial
         * @param {$columna}        Columna del módulo inicial
         * @param {$tipo}           Longitud del barco
         * @param {$incrementoFil}  El incremento de la fila (si el barco es horizontal valdrá 0) si se escribe
         *                          de abajo a arriba valdrá -1, de arriba a abajo valdrá 1.
         * @param {$incrementoCol}  El incremento de la columna (si el barco es vertical valdrá 0) si se escribe
         *                          de derecha a izquierda valdrá -1, de izquierda a derecha valdrá 1.
         * 
         * @return {Boolean}        True si el área es válida, false si no lo es. 
         */
        private function validarArea($fila,$columna,$tipo,$incrementoFil,$incrementoCol) {
            $filaFinal = $fila+($tipo-1)*$incrementoFil;
            $columnaFinal = $columna+($tipo-1)*$incrementoCol;

            $inicioFilaArea = ($fila<=$filaFinal) ? max($fila-1,0) : max($filaFinal-1,0);
            $finalFilaArea = ($fila>=$filaFinal) ? min($fila+1,9) : min($filaFinal+1,9);
            
            $inicioColumnaArea = ($columna<=$columnaFinal) ? max($columna-1,0) : max($columnaFinal-1,0);
            $finalColumnaArea = ($columna>=$columnaFinal) ? min($columna+1,9) : min($columnaFinal+1,9);

            for ($i=$inicioFilaArea; $i<$finalFilaArea+1; $i++) 
                for ($j=$inicioColumnaArea; $j<$finalColumnaArea+1; $j++) 
                    if (!$this->_tablero[$i][$j]==0) 
                        return false;
            
            return true;
        }

        /**
         * Devuelve si la ubicación dónde se desea insertar el barco es válida o no
         * tras comprobar que el área esté libre de posibles colisiones con otros barcos.
         * 
         * @param {$fila}           Fila del módulo inicial
         * @param {$columna}        Columna del módulo inicial
         * @param {$tipo}           Longitud del barco
         * @param {$direccion}      Dirección a la que apunta el barco (0-3)
         */
        private function ubicacionValida($fila,$columna,$tipo,$direccion) {
            switch ($direccion) {
                case 0:
                    return $this->validarArea($fila,$columna,$tipo,1,0);
                case 1:
                    return $this->validarArea($fila,$columna,$tipo,0,-1);
                case 2:
                    return $this->validarArea($fila,$columna,$tipo,-1,0);
                case 3:
                    return $this->validarArea($fila,$columna,$tipo,0,1);
            }
        }

        /**
         * Inserta en el tablero numérico el valor del tipo de barco.
         * 
         * @param {$fila}           Fila del módulo inicial
         * @param {$columna}        Columna del módulo inicial
         * @param {$tipo}           Longitud del barco
         * @param {$incrementoFil}  El incremento de la fila (si el barco es horizontal valdrá 0) si se escribe
         *                          de abajo a arriba valdrá -1, de arriba a abajo valdrá 1.
         * @param {$incrementoCol}  El incremento de la columna (si el barco es vertical valdrá 0) si se escribe
         *                          de derecha a izquierda valdrá -1, de izquierda a derecha valdrá 1.
         */
        private function insertar($fila,$columna,$tipo,$incrementoFil,$incrementoCol) {
            $filaFinal = $fila+$tipo*$incrementoFil;
            $columnaFinal = $columna+$tipo*$incrementoCol;

            while (!($fila==$filaFinal && $columna==$columnaFinal)) {
                //$this->_tablero[$fila][$columna] = $tipo;
                $this->_tablero[$fila][$columna] = 2;
                
                $fila += $incrementoFil;
                $columna += $incrementoCol;
            }
        }

        /**
         * Inserta un barco en el tablero numérico.
         * 
         * @param {$fila}       Fila del módulo inicial
         * @param {$columna}    Columna del módulo inicial
         * @param {$tipo}       Longitud del barco
         * @param {$direccion}  Dirección del barco (0-3)
         */
        private function insertaBarco($fila,$columna,$tipo,$direccion) {
            switch ($direccion) {
                case 0:
                    return $this->insertar($fila,$columna,$tipo,1,0);
                case 1:
                    return $this->insertar($fila,$columna,$tipo,0,-1);
                case 2:
                    return $this->insertar($fila,$columna,$tipo,-1,0);
                case 3:
                    return $this->insertar($fila,$columna,$tipo,0,1);
            }
        }

        /**
         * Añade un barco en el tablero de juego tras comprobar que la ubicación 
         * para el mismo es válida.
         * 
         * @param {$fila}       Fila del módulo inicial
         * @param {$columna}    Columna del módulo inicial
         * @param {$tipo}       Longitud del barco
         * @param {$direccion}  Dirección del barco (0-3)
         */
        public function addBarco($fila,$columna,$tipo,$direccion) {
            if (($fila<0 || $fila>9) || ($columna<0 || $columna>9))
                throw new Exception('Fila/columna incorrectas');
            if (!($this->longitudValida($fila,$columna,$tipo,$direccion) && $this->ubicacionValida($fila,$columna,$tipo,$direccion)))
                throw new Exception('Posición inválida. El barco toca con otro ya ubicado o se sale del tablero en esa posición.');
            
            $this->insertaBarco($fila,$columna,$tipo,$direccion);
            array_push($this->_listaBarcos, new Barco($fila,$columna,$tipo,$direccion));
        }

        /*** MÉTODOS DE IMPRESIÓN DE TABLEROS ***/

        /**
         * Imprime la lista de los barcos con la información de cada uno.
         */
        public function imprimirListaBarcos() {
            for ($i=0; $i<sizeof($this->_listaBarcos); $i++) { 
                echo "<br/>Barco ".($i+1).":<br/>";
                echo "======<br/>";
                $this->_listaBarcos[$i]->imprimeInfoBarco();
            }
        }

        /**
         * Imprime el tablero con la ubicación de los barcos
         */
        public function imprimir() {
            echo "<table>";
            for ($i=0; $i<sizeof($this->_tablero); $i++) { 
                echo "<tr>";
                for ($j=0; $j<sizeof($this->_tablero); $j++)
                    echo "<td class="."c".$this->_tablero[$i][$j]."></td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        /**
         * Imprime el tablero visible. Si la partida ha finalizado se imprime una versión
         * sin enlaces.
         * 
         * @param {$finPartida} Booleano, true si la partida ha finalizado.
         */
        public function imprTabVis($finPartida) {
            echo "<table>";
            for ($i=0; $i<sizeof($this->_tableroJuego); $i++) { 
                echo "<tr>";
                for ($j=0; $j<sizeof($this->_tableroJuego); $j++) {
                    if ($finPartida)
                        echo "<td class="."c".$this->_tableroJuego[$i][$j]."></td>";
                    else
                        echo ($this->_tableroJuego[$i][$j]!=0) ?  
                        "<td class="."c".$this->_tableroJuego[$i][$j]."></td>" :
                        "<td class='a'><a href=".$_SERVER['PHP_SELF']."?fila=".$i."&columna=".$j."> </a></td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }

        /**
         * Imprime el tablero con la ubicación de los barcos. MODO DEV.
         */
        public function imprimirTabIA() {
            echo "<table>";
            for ($i=0; $i<sizeof($this->_tablero); $i++) { 
                echo "<tr>";
                for ($j=0; $j<sizeof($this->_tablero); $j++)
                    echo "<td>".$this->_tableroIA[$i][$j]."</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
?>