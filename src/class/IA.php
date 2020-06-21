<?php
    /**
     * Clase IA (hereda de Jugador) para el juego de hundir la flota.
     * 
     * @author  Francisco javier González Sabariego.
     * @since   21/04/2020
     * 
     * @version 1.0
     */

    class IA extends Jugador {

        private $_faseHundir = array(false,false,false);
        private $_direccionValida = array(true,true,true,true);
        private $_direccionDiparo = 0;
        private $_coordImpacto = array(0,0);    //Coordenadas del primer impacto a un barco ($_coordImpacto[0]=fila,$_coordImpacto[1]=columna)
        private $_coordPuntero = array(0,0);    //Coordenadas del puntero que se actualiza disparo tras disparo
        private $_coordSonar = array();         //Cuando el sonar está activo almacena las coordenadas dónde es más probable encontrar barco
        private $_heDisparado = false;          //Indica si la IA ha disparado en su turno.
        
        public function __construc() {
            parent::__construct();
        }

        /**
         * Determina si está activo el sistema de hundimiento de barco (este sistema se activa tras impactar en un barco)
         * 
         * @return {Boolean}    True si el sistema estáa ctivo, false si no lo está
         */
        private function activoSistemaHundir() {
            for ($i=0; $i<sizeof($this->_faseHundir); $i++) 
                if ($this->_faseHundir[$i]) return true;
            return false;
        }

        /**
         * Resetea el sistema de hundimiento de barco
         */
        private function resetearSistemaHundir() {
            for ($i=0; $i<sizeof($this->_faseHundir); $i++) 
                $this->_faseHundir[$i] = false;
            for ($i=0; $i<sizeof($this->_direccionValida); $i++) 
                $this->_direccionValida[$i] = true;
        }

        /**
         * Añade el valor pasado por parámetro al tablero de la IA para las coordendas indicadas y
         * resta en 1 el valor de las coordenadas que haya alrededor salvo si éstas valen 0 ó -1.
         * 
         * @param {$fila}      Fila donde se ha producido el impacto
         * @param {$columna}   Columna donde se ha producido el impacto
         * @param {$valor}     Valor a insertar en la coordenada del impacto (0 si es agua, -1 si es barco)
         */
        private function impacto($fila,$columna,$valor) {
            for ($i=max($fila-1,0); $i<=min($fila+1,9); $i++) { 
                for ($j=max($columna-1,0); $j<=min($columna+1,9); $j++) { 
                    if ($i == $fila && $j == $columna)
                        $this->getTablero()->setValorTableroIA($i,$j,$valor);
                    elseif ($this->getTablero()->getValorTableroIA($i,$j) > 0)
                        $this->getTablero()->setValorTableroIA($i,$j,$this->getTablero()->getValorTableroIA($i,$j)-1);
                }
            }
        }

        /**
         * Añade las coordenadas del impacto al array coordImpacto y coordPuntero,
         * éste método tiene como finalidad guardar las coordenadas del primer 
         * impacto realizado a un barco.
         * 
         * @param {$fila}      Fila donde se ha producido el impacto
         * @param {$columna}   Columna donde se ha producido el impacto
         */
        private function setCoordImpacto($fila,$columna) {
            $this->_coordImpacto[0] = $fila;
            $this->_coordImpacto[1] = $columna;
            $this->_coordPuntero[0] = $fila;
            $this->_coordPuntero[1] = $columna;
        }

        /**
         * Impacta con valor 0 (agua) las casillas alrededor del barco que esté hundido
         * 
         * @param {$fila}           Fila del módulo inicial
         * @param {$columna}        Columna del módulo inicial
         * @param {$tipo}           Longitud del barco
         * @param {$incrementoFil}  El incremento de la fila (si el barco es horizontal valdrá 0) si se escribe
         *                          de abajo a arriba valdrá -1, de arriba a abajo valdrá 1.
         * @param {$incrementoCol}  El incremento de la columna (si el barco es vertical valdrá 0) si se escribe
         *                          de derecha a izquierda valdrá -1, de izquierda a derecha valdrá 1.
         */
        private function rodaConAgua($fila,$columna,$tipo,$incrementoFil,$incrementoCol) {
            $filaFinal = $fila+($tipo-1)*$incrementoFil;
            $columnaFinal = $columna+($tipo-1)*$incrementoCol;

            $inicioFilaArea = ($fila<=$filaFinal) ? max($fila-1,0) : max($filaFinal-1,0);
            $finalFilaArea = ($fila>=$filaFinal) ? min($fila+1,9) : min($filaFinal+1,9);
            
            $inicioColumnaArea = ($columna<=$columnaFinal) ? max($columna-1,0) : max($columnaFinal-1,0);
            $finalColumnaArea = ($columna>=$columnaFinal) ? min($columna+1,9) : min($columnaFinal+1,9);

            for ($i=$inicioFilaArea; $i<$finalFilaArea+1; $i++) 
                for ($j=$inicioColumnaArea; $j<$finalColumnaArea+1; $j++) 
                    if ($this->getTablero()->getValorTableroIA($i,$j)>0) 
                        $this->impacto($i,$j,0);
        }

        /**
         * Este método es llamado tras haber hundido un barco lo rodeamos con agua
         * 
         * @param {$fila}           Fila del módulo inicial
         * @param {$columna}        Columna del módulo inicial
         * @param {$tipo}           Longitud del barco
         * @param {$direccion}      Dirección a la que apunta el barco (0-3)
         */
        private function rodearConAgua($fila,$columna,$tipo,$direccion) {
            switch ($direccion) {
                case 0:
                    return $this->rodaConAgua($fila,$columna,$tipo,1,0);
                case 1:
                    return $this->rodaConAgua($fila,$columna,$tipo,0,-1);
                case 2:
                    return $this->rodaConAgua($fila,$columna,$tipo,-1,0);
                case 3:
                    return $this->rodaConAgua($fila,$columna,$tipo,0,1);
            }
        }

        /**
         * Cuando el disparo impacta en agua.
         * 
         * @param {$fila}           Fila de la coordenada del disparo
         * @param {$columna}        Columna de la coordenada del disparo
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        private function agua($fila,$columna,$tableroEnemigo) {
            $tableroEnemigo->setValorTablero($fila,$columna,4);
            $this->impacto($fila,$columna,0);
            if ($this->_faseHundir[1]) {                                    //Si estamos en la 2a fase de hundir negamos dirección
                $this->_direccionValida[$this->_direccionDiparo] = false;
                $this->desplazaPuntero(4);
            }
            if ($this->_faseHundir[2])                                      //Si estamos en la 3a fase de hundir invertimos disparo
                $this->_direccionDiparo = $this->invierteDireccionDisparo();
        }

        /**
         * El barco impactado no está hundido todavía
         * 
         * @param {$fila}           Fila de la coordenada del disparo
         * @param {$columna}        Columna de la coordenada del disparo
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        private function noHundido($fila,$columna) {
            if (!$this->activoSistemaHundir()) {
                $this->setCoordImpacto($fila,$columna);
                $this->_faseHundir[0] = true;
            } elseif($this->_faseHundir[1]) {  //Si verificamos impacto estando en fase 2 y no se ha hundido el barco activamos la fase 3
                $this->_faseHundir[1] = false;
                $this->_faseHundir[2] = true;
            }
        }

        /**
         * El barco impactado se ha hundido
         * 
         * @param {$fila}           Fila de la coordenada del disparo
         * @param {$columna}        Columna de la coordenada del disparo
         * @param {$tableroEnemigo} Objeto tablero enemigo
         * @param {$indexBarco}     El índice donde está almacenado, en el array de barcos enemigo, el barco que se ha hundido
         */
        private function hundido($fila,$columna,$tableroEnemigo,$indexBarco) {
            $_SESSION['mensajesJ1'] .= (($_SESSION['mensajesJ1']=="") ? "" : "<br/>")."<div class='impactoIA'>".
                $this->_nombre." ha hundido nuestro ".$tableroEnemigo->getListaBarcos()[$indexBarco]->getNombreTipo()."</div>";
            $this->rodearConAgua(
                $tableroEnemigo->getListaBarcos()[$indexBarco]->getCoordModInicial()[0],    //Fila del módulo inicial del barco
                $tableroEnemigo->getListaBarcos()[$indexBarco]->getCoordModInicial()[1],    //Columna del módulo inicial del barco
                $tableroEnemigo->getListaBarcos()[$indexBarco]->getTipo(),                  //Total módulos (longitud) del barco
                $tableroEnemigo->getListaBarcos()[$indexBarco]->getDireccion());            //Dirección a la que apunta el barco
            $this->incrementaBarcosHundidos($tableroEnemigo->getListaBarcos()[$indexBarco]->getTipo());
            $tableroEnemigo->setHundirBarco($indexBarco);
            $this->resetearSistemaHundir();
        }

        /**
         * El disparo ha impactado en un barco
         * 
         * @param {$fila}           Fila de la coordenada del disparo
         * @param {$columna}        Columna de la coordenada del disparo
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        private function tocado($fila,$columna,$tableroEnemigo) {
            $tableroEnemigo->setValorTablero($fila,$columna,1);
            $this->impacto($fila,$columna,-1);
            $indexBarco = $tableroEnemigo->getIndexBarcoImpactado($fila,$columna);
            $tableroEnemigo->getListaBarcos()[$indexBarco]->destruirModulo($fila,$columna);
            if (!$tableroEnemigo->getListaBarcos()[$indexBarco]->getHundido())               //Si el barco no está hundido
                $this->noHundido($fila,$columna);
            else                                                                             //Si el barco está hundido
                $this->hundido($fila,$columna,$tableroEnemigo,$indexBarco);
        }

        /**
         * La IA dispara en el tablero enemigo en las coordenadas dadas
         * 
         * @param {$fila}           Fila de la coordenada del disparo
         * @param {$columna}        Columna de la coordenada del disparo
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        private function dispara($fila,$columna,$tableroEnemigo) {
            $_SESSION['mensajesJ2'] = "Disparo en las coordenadas (".($fila+1).",".($columna+1)."): ".
                (($tableroEnemigo->getValorTablero($fila,$columna)==0) ? "agua." : "tocado.");
            if ($tableroEnemigo->getValorTablero($fila,$columna)==0)       //Si el disparo impacta en agua
                $this->agua($fila,$columna,$tableroEnemigo);
            else                                                           //Si el disparo impacta en un barco
                $this->tocado($fila,$columna,$tableroEnemigo);
            $this->_numDisparos++;
            $this->_heDisparado = true;
        }

        /**
         * Se realiza un disparo a unas coordenadas aleatorias
         * 
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        private function disparoRandom($tableroEnemigo) {
            do {
                $fila = rand(0,9);
                $columna = rand(0,9);
            } while ($this->getTablero()->getValorTableroIA($fila,$columna)<1);
            $this->dispara($fila,$columna,$tableroEnemigo);
        }

        /**
         * Valida de las 4 direcciones posibles de disparo. Sí encuentra que alguna de las direcciones
         * de disparo para las coordenadas almacenadas no es válida niega esa dirección
         * 
         * @param {$i}          Dirección de disparo a validar.
         * 
         * @return {Boolean}    True si la dirección es válida, false si no lo es.
         */
        private function validarDirecciones($i) {
            switch ($i) {
                case 0:     //arriba (fila -1, columna 0)
                    if (($this->_coordPuntero[0]-1)<0 || $this->getTablero()->getValorTableroIA($this->_coordPuntero[0]-1,$this->_coordPuntero[1])==0)
                        return false;
                    else
                        return true;
                case 1:     //derecha (fila 0, columna +1)
                    if (($this->_coordPuntero[1]+1)>9 || $this->getTablero()->getValorTableroIA($this->_coordPuntero[0],$this->_coordPuntero[1]+1)==0)
                        return false;
                    else
                        return true;
                case 2:     //abajo (fila +1, columna 0)
                    if (($this->_coordPuntero[0]+1)>9 || $this->getTablero()->getValorTableroIA($this->_coordPuntero[0]+1,$this->_coordPuntero[1])==0)
                        return false;
                    else
                        return true;
                default:    //izquierda (fila 0, columna -1)
                    if (($this->_coordPuntero[1]-1)<0 || $this->getTablero()->getValorTableroIA($this->_coordPuntero[0],$this->_coordPuntero[1]-1)==0)
                        return false;
                    else
                        return true;
            }
        }

        /**
         * Desplaza el puntero en la dirección pasada por parámetro
         * 
         * * @param {$direccion}    Dirección en la que se debe desplazar el puntero:
         *                          0: arriba
         *                          1: derecha
         *                          2: abajo
         *                          3: izquierda
         *                          4: origen impacto (devuelve al puntero a las coordenadas donde se originó el primer impacto al barco)
         * 
         */
        private function desplazaPuntero($direccion) {
            switch ($direccion) {
                case 0:                             //arriba -> fila--
                    $this->_coordPuntero[0]--;
                    break;
                case 1:                             //derecha -> columna++
                    $this->_coordPuntero[1]++;
                    break;
                case 2:                             //abajo -> fila++
                    $this->_coordPuntero[0]++;
                    break;
                case 3:                             //izquierda -> columna--
                    $this->_coordPuntero[1]--;
                    break;
                case 4:                             //Coordenadas origen impacto
                    $this->_coordPuntero[0] = $this->_coordImpacto[0];
                    $this->_coordPuntero[1] = $this->_coordImpacto[1];
                    break;
            }
        }

        /**
         * Invierte la dirección en la que debe seguir disparando la IA hasta hundir el barco detectado y
         * devuelve el puntero de disparo a las coordenadas del primer impacto.
         * 
         * @return {int}    Dirección de disparo invertida.
         */
        private function invierteDireccionDisparo() {
            $this->desplazaPuntero(4);
            switch ($this->_direccionDiparo) {
                case 0:
                    return 2;
                case 1:
                    return 3;
                case 2:
                    return 0;
                default:
                    return 1;
            }
        }

        /**
         * Primera fase del siste de hundimiento (validar direcciones posibles de disparo)
         */
        private function primeraFaseHundir() {
            for ($i=0; $i<sizeof($this->_direccionValida); $i++) 
                $this->_direccionValida[$i] = $this->validarDirecciones($i);
            $this->_faseHundir[0]=false;
            $this->_faseHundir[1]=true;
        }

        /**
         * Segunda fase de hundimiento (descubrir alineamiento del barco -> "horizontal" | "vertical")
         * 
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        private function segundaFaseHundir($tableroEnemigo) {
            do {
                $this->_direccionDiparo = rand(0,3);
            } while (!$this->_direccionValida[$this->_direccionDiparo]);
            $this->desplazaPuntero($this->_direccionDiparo);
            $this->dispara($this->_coordPuntero[0],$this->_coordPuntero[1],$tableroEnemigo);
        }

        /**
         * Tercera fase de hundimiento (continuar los disparos hasta terminar de hundir el barco en caso 
         * de no haberlo hundido antes (3+ módulos))
         * 
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        private function terceraFaseHundir($tableroEnemigo) {
            if (!$this->validarDirecciones($this->_direccionDiparo)) {       //Si la siguiente coordenada de disparo no es válida (sale de tablero o hay agua)
                $this->_direccionDiparo = $this->invierteDireccionDisparo();//Invierte dirección de disparo y devuelve puntero a origen de impacto
                $this->desplazaPuntero($this->_direccionDiparo);            //Desplaza el puntero desde el origen en la nueva dirección
            }
            else
                $this->desplazaPuntero($this->_direccionDiparo);
            $this->dispara($this->_coordPuntero[0],$this->_coordPuntero[1],$tableroEnemigo);
        }

        /**
         * Sistema de hundimiento de barco
         * 
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        private function hundirBarco($tableroEnemigo) {
            if ($this->_faseHundir[0]) 
                $this->primeraFaseHundir();
            if ($this->_faseHundir[1] && !$this->_heDisparado) 
                $this->segundaFaseHundir($tableroEnemigo);
            elseif ($this->_faseHundir[2] && !$this->_heDisparado) 
                $this->terceraFaseHundir($tableroEnemigo);
        }

        /**
         * Devuelve un array con el conjuto de coordenadas de aquellos puntos
         * donde se almacena el valor más alto en el tableroIA
         * 
         * @return {Array}  Conjunto de oordenadas donde están ubicados los valores más altos en el tablero IA
         */
        private function extraeCoordSonar() {
            $num = 0;
            $salida = array();
            for ($i=0; $i<10; $i++)     //Buscamos el valor mas alto almacenado en el TableroIA
                for ($j=0; $j<10; $j++) 
                    if ($num < $this->getTablero()->getValorTableroIA($i,$j)) $num = $this->getTablero()->getValorTableroIA($i,$j);
            for ($i=0; $i<10; $i++)     //Por cada ubicación donde esté el valor más alto guardamos las coordenadas del mismo
                for ($j=0; $j<10; $j++) 
                    if ($num == $this->getTablero()->getValorTableroIA($i,$j)) array_push($salida, array($i,$j));
            return $salida;
        }

        /**
         * Una vez la IA ha acumulado 20 disparos comienza a usar el sonar.
         * 
         * El sona rastrea los valores más altos en el TableroIA, que son, en teoría,
         * los puntos menos afectados por impactos cercanos y, por tanto, donde es más
         * probable hayar un barco. Representaría los claros de un tablero.
         * 
         * Una vez obtenidas las coordenadas de posibles ubicaciones de barcos 
         * la IA lanza un disparo a una de ellas.
         */
        private function activaSonar($tableroEnemigo) {
            $this->_coordSonar = $this->extraeCoordSonar();            //Extraemos el conjunto de coordenadas donde puede haber un barco
            $indice = rand(0,sizeof($this->_coordSonar)-1);     //Elegimos una coordenada al azar y disparamos
            $this->dispara($this->_coordSonar[$indice][0],$this->_coordSonar[$indice][1],$tableroEnemigo);
        }

        /**
         * La IA juega, si ha detectado un barco antes usa el sistema de hundimiento
         * si no hay ningún barco detectado y lleva más de 20 disparos activa el sistema de rastreo, 
         * si no hace un disparo random.
         * 
         * Tras su disparo incrementa en 1 su número de disparos.
         * 
         * @param {$tableroEnemigo} Objeto tablero enemigo
         */
        public function jugar($tableroEnemigo) {
            $this->_heDisparado = false;
            if ($this->activoSistemaHundir())
                $this->hundirBarco($tableroEnemigo);
            elseif ($this->_numDisparos>20 && !$this->_heDisparado) 
                $this->activaSonar($tableroEnemigo);
            elseif (!$this->_heDisparado)
                $this->disparoRandom($tableroEnemigo);
        }
    }
?>