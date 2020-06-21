<?php
    /**
     * Clase Barco para el juego de hundir la flota.
     * 
     * @author  Francisco javier González Sabariego.
     * @since   21/04/2020
     * 
     * @version 1.0
     */

    class Barco {

        private $_modulos;
        private $_tipo;
        private $_direccion;

        public function __construct($fila,$columna,$longitudBarco,$direccion) {
            $this->_tipo = $longitudBarco;
            $this->_direccion = $direccion;
            $this->_modulos = $this->asignaModulos($fila,$columna,$longitudBarco,$direccion);
        }

        /**
         * Devuelve el valor del tipo de barco (la longitud o total de módulos)
         * 
         * @return {int} Valor tipo de barco (total de módulos) 1-4
         */
        public function getTipo() {
            return $this->_tipo;
        }

        /**
         * Devuelve el nombre del tipo de barco.
         * 
         * @return {String} Tipo de barco: submarino, acorazado, destructor o portaaviones.
         */
        public function getNombreTipo() {
            switch ($this->_tipo) {
                case 1:
                    return "submarino";
                case 2:
                    return "acorazado";
                case 3:
                    return "destructor";
                case 4:
                    return "portaaviones";
            }
        }

        /**
         * Devuelve la dirección a la que apunta el barco 
         * 
         * @return {int} Dirección a la que apunta el barco 0-3
         */
        public function getDireccion() {
            return $this->_direccion;
        }

        /**
         * Devuelve las coordenadas del módulo inicial
         * 
         * @return {Array}  Devuelve las coordenadas del módulo inicial en formato array ([0] -> fila | [1] -> columna)
         */
        public function getCoordModInicial() {
            return array($this->_modulos[0]["fila"],$this->_modulos[0]["columna"]);
        }

        /**
         * Devuelve si el barco está hundido (todos los módulos en estado = 0)
         * 
         * @return {Boolean} True si está hundido false si no lo está
         */
        public function getHundido() {
            $mod = 0;
            for ($i=0; $i<$this->_tipo; $i++) 
                $mod += $this->_modulos[$i]["estado"];
            return $mod == 0;
        }

        /**
         * Devuelve el mensaje de que el barco se ha hundido.
         * 
         * @return {String} Mensaje de barco hundido.
         */
        public function getMensajeHundido() {
            return "¡Enhorabuena, has hundido un ".$this->getNombreTipo()."!";
        }

        /**
         * Crea los módulos del barco, cada módulo cuenta con la siguiente información:
         *  -Número de módulo.
         *  -Fila.
         *  -Columna.
         *  -Estado (1 ó 0) de inicio el estado es 1.
         * 
         * @param {$fila}                   Fila en la que se encuentra el módulo inicial
         * @param {$columna}                Columna en la que se encuentra el módulo inicial
         * @param {$totalModulos}           El total de módulos que se van a crear para el barco
         * @param {$incrementoFila}         El incremento de la fila (si el barco es horizontal valdrá 0) si se escribe
         *                                  de abajo a arriba valdrá -1, de arriba a abajo valdrá 1.
         * @param {$incrementoColumna}      El incremento de la columna (si el barco es vertical valdrá 0) si se escribe
         *                                  de derecha a izquierda valdrá -1, de izquierda a derecha valdrá 1.
         * 
         * @return {Array}                  El array con el total de módulos creados, con toda su información, para este barco.
         */
        private function creaModulos($fila,$columna,$totalModulos,$incrementoFila,$incrementoColumna) {
            $array = array();
            for ($i=0; $i<$totalModulos; $i++)
                array_push($array, array(
                    "numModulo" => ($i+1),
                    "fila" => ($fila+$i*$incrementoFila),
                    "columna" => ($columna+$i*$incrementoColumna),
                    "estado" => 1,
                ));
            return $array;
        }

        /**
         * Devuelve el conjunto de módulos creados para el barco.
         * 
         * @param {$fila}                   Fila en la que se encuentra el módulo inicial
         * @param {$columna}                Columna en la que se encuentra el módulo inicial
         * @param {$totalModulos}           El total de módulos que se van a crear para el barco
         * @param {$direccion}              La dirección que posee el barco desde su casilla inicial (0-3).
         * 
         * @return {Array}                  El array con el total de módulos creados, con toda su información, para este barco.
         */
        private function asignaModulos($fila,$columna,$totalModulos,$direccion) {
            switch ($direccion) {
                case 0:
                    return $this->creaModulos($fila,$columna,$totalModulos,1,0);
                case 1:
                    return $this->creaModulos($fila,$columna,$totalModulos,0,-1);
                case 2:
                    return $this->creaModulos($fila,$columna,$totalModulos,-1,0);
                case 3:
                    return $this->creaModulos($fila,$columna,$totalModulos,0,1);
            }
        }

        /**
         * Comprueba si este barco ha sido impactado al pasarle las coordenadas de un impacto
         * 
         * @return {Boolean} True si ha sido impactado, false si no
         */
        public function comprobarImpacto($fila,$columna) {
            for ($i=0; $i<$this->_tipo; $i++) 
                if ($this->_modulos[$i]["fila"] == $fila && $this->_modulos[$i]["columna"] == $columna) 
                    return true;
            return false;
        }

        /**
         * Comprueba si este barco ha sido impactado al pasarle las coordenadas de un impacto
         * 
         * @return {Boolean} True si ha sido impactado, false si no
         */
        public function destruirModulo($fila,$columna) {
            for ($i=0; $i<$this->_tipo; $i++) 
                if ($this->_modulos[$i]["fila"] == $fila && $this->_modulos[$i]["columna"] == $columna) {
                    $this->_modulos[$i]["estado"] = 0;
                    break;
                }
        }

        /**
         * Imprime la información del barco. Muestra el tipo de barco, por ejemplo "acorazado", 
         * y a continuación el total de módulos que posee con su respectiva información.
         */
        public function imprimeInfoBarco() {
            echo "<br/>Tipo de barco: ".$this->getNombreTipo()."<br/>";
            for ($i=0; $i<sizeof($this->_modulos); $i++) { 
                echo "Módulo ".$this->_modulos[$i]["numModulo"].": <br/>";
                echo "Fila: ".($this->_modulos[$i]["fila"])." | Columna: ".($this->_modulos[$i]["columna"])."<br/>";
                echo "Estado: ".$this->_modulos[$i]["estado"]."<br/>";
            }
        }
    }

?>